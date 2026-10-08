<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    /**
     * Display the Attendance Logs & Monitoring dashboard.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        abort_if(! ($user->isAdmin() || $user->isSupervisorLevel()), 403, 'Unauthorized access.');

        $today = Carbon::today();
        $sevenDaysAgo = $today->copy()->subDays(6)->startOfDay();
        $totalAllLogs = AttendanceLog::where('event_time', '>=', $sevenDaysAgo)->count();
        $latestRecord = AttendanceLog::where('event_time', '>=', $sevenDaysAgo)->latest('event_time')->first();
        $latestLogDate = $latestRecord && $latestRecord->event_time ? $latestRecord->event_time->toDateString() : null;

        $last7StartDate = $sevenDaysAgo->toDateString();
        $last7EndDate   = $today->toDateString();
        $last7Dates     = [];
        for ($i = 0; $i < 7; $i++) {
            $last7Dates[] = $today->copy()->subDays($i)->toDateString();
        }

        // Query builder - STRICT POLICY: Only load and process logs within the last 7 days!
        $query = AttendanceLog::with('user')
            ->where('event_time', '>=', $sevenDaysAgo)
            ->latest('id');

        // Filter: Date (Default to '7days')
        $filterDate = $request->get('date', '7days');

        if ($filterDate === 'today') {
            $query->whereDate('event_time', $today);
        } elseif ($filterDate && ! in_array($filterDate, ['7days', 'all'])) {
            $query->whereDate('event_time', $filterDate);
        }

        // Filter: Specific User
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->get('user_id'));
        }

        // Filter: Shift
        if ($request->filled('shift')) {
            $shiftVal = $request->get('shift');
            if (in_array($shiftVal, ['1st_shift', 'day_shift'])) {
                $query->whereIn('shift_assigned', ['1st_shift', 'day_shift']);
            } elseif (in_array($shiftVal, ['2nd_shift', 'night_shift'])) {
                $query->whereIn('shift_assigned', ['2nd_shift', 'night_shift']);
            } else {
                $query->where('shift_assigned', $shiftVal);
            }
        }

        // Filter: Match Status
        $matchStatus = $request->get('match_status');
        if ($matchStatus === 'matched') {
            $query->whereNotNull('user_id');
        } elseif ($matchStatus === 'unmatched') {
            $query->whereNull('user_id');
        }

        // Filter: Search Keyword
        if ($request->filled('search')) {
            $term = trim($request->get('search'));
            $query->where(function ($q) use ($term) {
                $q->where('employee_no', 'like', "%{$term}%")
                    ->orWhere('person_name', 'like', "%{$term}%")
                    ->orWhere('door_name', 'like', "%{$term}%")
                    ->orWhere('device_name', 'like', "%{$term}%")
                    ->orWhereHas('user', function ($uq) use ($term) {
                        $uq->where('name', 'like', "%{$term}%")
                            ->orWhere('office_id', 'like', "%{$term}%");
                    });
            });
        }

        $logs = $query->paginate(25)->withQueryString();

        // Selected Date summary statistics
        if ($filterDate === 'today') {
            $statsDate = $today->toDateString();
            $statsQuery = AttendanceLog::whereDate('event_time', $today);
        } elseif ($filterDate && ! in_array($filterDate, ['7days', 'all'])) {
            $statsDate = $filterDate;
            $statsQuery = AttendanceLog::whereDate('event_time', $filterDate);
        } else {
            $statsDate = $today->toDateString();
            $statsQuery = AttendanceLog::where('event_time', '>=', $sevenDaysAgo);
        }

        $latestLog = (clone $statsQuery)->latest('id')->first();
        if (! $latestLog) {
            $latestLog = AttendanceLog::where('event_time', '>=', $sevenDaysAgo)->latest('id')->first();
        }

        $stats = [
            'today_total'        => (clone $statsQuery)->count(),
            'today_unique_staff' => (int) ((clone $statsQuery)->selectRaw('COUNT(DISTINCT COALESCE(NULLIF(user_id, ""), NULLIF(employee_no, ""))) as cnt')->value('cnt') ?? 0),
            'today_1st_shift'    => (clone $statsQuery)->whereIn('shift_assigned', ['1st_shift', 'day_shift'])->count(),
            'today_2nd_shift'    => (clone $statsQuery)->whereIn('shift_assigned', ['2nd_shift', 'night_shift'])->count(),
            'total_unmatched'    => (clone $statsQuery)->whereNull('user_id')->count(),
            'total_all'          => $totalAllLogs,
            'latest_punch_time'  => $latestLog ? $latestLog->event_time->timezone(config('app.timezone', 'Asia/Dhaka'))->format('h:i:s A') : null,
            'latest_punch_ago'   => $latestLog ? $latestLog->event_time->diffForHumans() : null,
            'latest_person_name' => $latestLog ? ($latestLog->person_name ?: 'ID: ' . $latestLog->employee_no) : null,
        ];

        // Daily Staff Summary: First In (Entry) & Last Out (Exit) - Supports ALL punches (matched & unmatched)
        $dayGroupedLogsQuery = AttendanceLog::with('user');
        if ($filterDate === 'today') {
            $dayGroupedLogsQuery->whereDate('event_time', $today);
        } elseif ($filterDate && ! in_array($filterDate, ['7days', 'all'])) {
            $dayGroupedLogsQuery->whereDate('event_time', $statsDate);
        } else {
            $dayGroupedLogsQuery->where('event_time', '>=', $sevenDaysAgo);
        }

        $dayGroupedLogs = $dayGroupedLogsQuery
            ->orderBy('event_time', 'asc')
            ->get()
            ->groupBy(function ($log) {
                return $log->user_id ? 'user_' . $log->user_id : 'emp_' . ($log->employee_no ?: $log->id);
            });

        $staffSummaries = $dayGroupedLogs->map(function ($userLogs) {
            $firstPunch = $userLogs->first();
            $lastPunch  = $userLogs->last();
            $user       = $firstPunch->user;
            $totalCount = $userLogs->count();

            $firstTime = $firstPunch ? $firstPunch->event_time : null;
            $firstMins = $firstTime ? ((int) $firstTime->format('H') * 60 + (int) $firstTime->format('i')) : 0;
            $lastTime  = $lastPunch ? $lastPunch->event_time : null;
            $lastMins  = $lastTime ? ((int) $lastTime->format('H') * 60 + (int) $lastTime->format('i')) : 0;

            // Shift assigned:
            // 7:00 AM - 11:30 AM / before 12:00 PM -> 1st Shift (9:00 AM - 6:00 PM)
            // 12:00 PM onwards -> 2nd Shift (2:00 PM - 10:00 PM)
            $shift = $firstPunch->shift_assigned;
            if (!$shift || !in_array($shift, ['1st_shift', 'day_shift', '2nd_shift', 'night_shift'])) {
                $shift = ($firstMins < 720) ? '1st_shift' : '2nd_shift';
            }

            // Duty Complete (Out) rules:
            // 1st Shift: If scanned after 6:00 PM (1080 mins) -> Duty Complete
            // 2nd Shift: If scanned after 10:00 PM (1320 mins) -> Duty Complete
            // Otherwise within duty hours -> On Duty
            $isDutyComplete = false;
            if (in_array($shift, ['1st_shift', 'day_shift'])) {
                if ($lastMins >= 1080 && $totalCount > 1) {
                    $isDutyComplete = true;
                }
            } elseif (in_array($shift, ['2nd_shift', 'night_shift'])) {
                if ($lastMins >= 1320 && $totalCount > 1) {
                    $isDutyComplete = true;
                }
            }

            if ($user && $user->current_shift === 'off_duty') {
                $isDutyComplete = true;
            }

            return [
                'user'             => $user,
                'employee_no'      => $firstPunch->employee_no,
                'person_name'      => $firstPunch->person_name ?: ($user ? $user->name : ('ID: ' . $firstPunch->employee_no)),
                'shift'            => $shift,
                'first_in'         => $firstPunch ? $firstPunch->event_time : null,
                'last_out'         => ($totalCount > 1 && $isDutyComplete) ? $lastPunch->event_time : null,
                'latest_scan'      => $lastPunch ? $lastPunch->event_time : null,
                'total_scans'      => $totalCount,
                'is_duty_complete' => $isDutyComplete,
                'is_out'           => $isDutyComplete,
                'is_on_duty'       => ! $isDutyComplete,
            ];
        });

        // All users for filter dropdown
        $staffUsers = User::orderBy('name')->get(['id', 'name', 'office_id', 'team', 'role']);

        $cloudWebhookUrl = url('/api/hikcentral/event');
        $webhookUrl = $cloudWebhookUrl; // Backward compatibility
        $statusUrl  = url('/api/hikcentral/status');

        $deviceIp   = config('services.attendance.device_ip', '172.27.1.49');
        $devicePort = (int) config('services.attendance.device_port', 80);
        $dnsIp      = config('services.attendance.dns_ip', '172.30.20.50');
        $serverLanIp = config('services.attendance.server_ip', '103.31.179.118');

        $port = $request->getPort();
        $portSuffix = ($port && !in_array($port, [80, 443])) ? ":{$port}" : '';
        $lanWebhookUrl = "http://{$serverLanIp}{$portSuffix}/api/hikcentral/event";

        $deviceOnline = false;
        $dnsOnline = false;

        try {
            $fp = @fsockopen($deviceIp, $devicePort, $errno, $errstr, 0.4);
            if ($fp) {
                $deviceOnline = true;
                fclose($fp);
            }
        } catch (\Throwable) {}

        try {
            $fp = @fsockopen("udp://{$dnsIp}", 53, $errno, $errstr, 0.4);
            if ($fp) {
                $dnsOnline = true;
                fclose($fp);
            }
        } catch (\Throwable) {}

        $networkHealth = [
            'device_ip'     => $deviceIp,
            'device_online' => $deviceOnline,
            'dns_ip'        => $dnsIp,
            'dns_online'    => $dnsOnline,
            'server_ip'     => $serverLanIp,
            'server_lan_ip' => $serverLanIp,
        ];

        return view('attendance.index', compact(
            'logs',
            'stats',
            'statsDate',
            'staffSummaries',
            'staffUsers',
            'filterDate',
            'latestLogDate',
            'last7StartDate',
            'last7EndDate',
            'last7Dates',
            'totalAllLogs',
            'today',
            'webhookUrl',
            'cloudWebhookUrl',
            'lanWebhookUrl',
            'statusUrl',
            'networkHealth'
        ));
    }

    /**
     * Delete an individual log entry.
     */
    public function destroy(AttendanceLog $attendanceLog): RedirectResponse
    {
        abort_if(! auth()->user()->isAdmin(), 403);

        $attendanceLog->delete();

        return back()->with('success', __('Attendance log deleted successfully.'));
    }

    /**
     * Clear old logs (older than 30 or 60 days).
     */
    public function clearOldLogs(Request $request): RedirectResponse
    {
        abort_if(! auth()->user()->isSuperAdmin(), 403);

        $days = max(1, (int) $request->get('days', 7));
        $count = AttendanceLog::where('event_time', '<', now()->subDays($days))->delete();

        return back()->with('success', __(':count logs older than :days days removed.', ['count' => $count, 'days' => $days]));
    }

    /**
     * Test real-time connection to attendance machine and DNS.
     * Returns JSON diagnostic results for frontend button.
     */
    public function testDeviceConnection(Request $request): JsonResponse
    {
        $user = auth()->user();
        abort_if(! ($user->isAdmin() || $user->isSupervisorLevel()), 403);

        $deviceIp   = config('services.attendance.device_ip', '172.27.1.49');
        $dnsIp      = config('services.attendance.dns_ip', '172.30.20.50');

        $start = microtime(true);
        $port80 = false;
        $port443 = false;
        $latencyMs = 0;

        // Test Port 80
        $fp80 = @fsockopen($deviceIp, 80, $e, $es, 0.8);
        if ($fp80) {
            $port80 = true;
            $latencyMs = round((microtime(true) - $start) * 1000, 2);
            fclose($fp80);
        }

        // Test Port 443 (HTTPS Web UI)
        $fp443 = @fsockopen($deviceIp, 443, $e, $es, 0.8);
        if ($fp443) {
            $port443 = true;
            if ($latencyMs == 0) {
                $latencyMs = round((microtime(true) - $start) * 1000, 2);
            }
            fclose($fp443);
        }

        // Test DNS
        $dnsOnline = false;
        $dnsFp = @fsockopen("udp://{$dnsIp}", 53, $e, $es, 0.5);
        if ($dnsFp) {
            $dnsOnline = true;
            fclose($dnsFp);
        }

        $deviceOnline = ($port80 || $port443);
        $lastHit = Cache::get('hikcentral_last_hit');

        return response()->json([
            'success'       => $deviceOnline,
            'device_ip'     => $deviceIp,
            'device_online' => $deviceOnline,
            'port_80'       => $port80,
            'port_443'      => $port443,
            'latency_ms'    => $latencyMs,
            'dns_ip'        => $dnsIp,
            'dns_online'    => $dnsOnline,
            'last_hit'      => $lastHit,
            'message'       => $deviceOnline
                ? "Attendance Machine ({$deviceIp}) is ONLINE! Response time: {$latencyMs}ms."
                : "Attendance Machine ({$deviceIp}) did not respond on Port 80/443.",
        ]);
    }
}
