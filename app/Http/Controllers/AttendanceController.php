<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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

        // Query builder - order by newest record ID first
        $query = AttendanceLog::with('user')->latest('id');

        // Filter: Date
        $filterDate = $request->get('date');
        if ($filterDate) {
            $query->whereDate('event_time', $filterDate);
        }

        // Filter: Specific User
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->get('user_id'));
        }

        // Filter: Shift
        if ($request->filled('shift')) {
            $query->where('shift_assigned', $request->get('shift'));
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

        // Today's summary statistics
        $stats = [
            'today_total'       => AttendanceLog::whereDate('event_time', $today)->count(),
            'today_unique_staff'=> AttendanceLog::whereDate('event_time', $today)->whereNotNull('user_id')->distinct('user_id')->count('user_id'),
            'today_day_shift'   => AttendanceLog::whereDate('event_time', $today)->where('shift_assigned', 'day_shift')->count(),
            'today_night_shift' => AttendanceLog::whereDate('event_time', $today)->where('shift_assigned', 'night_shift')->count(),
            'total_unmatched'   => AttendanceLog::whereNull('user_id')->count(),
        ];

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
            'staffUsers',
            'filterDate',
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

        $days = (int) $request->get('days', 30);
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
