<?php

namespace App\Console\Commands;

use App\Models\AttendanceLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Pull today's face-recognition events from a Hikvision DS-K1T321EFWX
 * (or any ISAPI-compatible device) and store them as AttendanceLog rows.
 *
 * Read-only — never deletes anything on the device.
 *
 * Usage:
 *   php artisan attendance:pull                 (today only)
 *   php artisan attendance:pull --date=2026-10-07
 *   php artisan attendance:pull --ip=172.27.1.49 --user=admin --pass=xxxx
 */
class PullAttendanceFromDevice extends Command
{
    protected $signature = 'attendance:pull
        {--date= : Date to pull in YYYY-MM-DD (default: today in Asia/Dhaka)}
        {--ip= : Device IP override}
        {--port= : Device port override}
        {--user= : Device admin username override}
        {--pass= : Device admin password override}
        {--limit=200 : Max events to pull in one search}';

    protected $description = 'Pull today\'s face-recognition events from Hikvision device via ISAPI (read-only).';

    public function handle(): int
    {
        $ip   = $this->option('ip')   ?: config('services.attendance.device_ip');
        $port = (int) ($this->option('port') ?: config('services.attendance.device_port', 80));
        $user = $this->option('user') ?: config('services.attendance.device_user');
        $pass = $this->option('pass') ?: config('services.attendance.device_pass');

        if (! $user || ! $pass) {
            $this->error('Device credentials missing. Set ATTENDANCE_DEVICE_USER and ATTENDANCE_DEVICE_PASS in .env, or pass --user and --pass.');
            return self::FAILURE;
        }

        $tz = 'Asia/Dhaka';
        $date = $this->option('date')
            ? Carbon::parse($this->option('date'), $tz)
            : Carbon::now($tz);

        $startOfDay = $date->copy()->startOfDay();
        $endOfDay   = $date->copy()->endOfDay();
        $start = $startOfDay->format('Y-m-d\TH:i:sP');
        $end   = $endOfDay->format('Y-m-d\TH:i:sP');
        $limit = max(1, min(500, (int) $this->option('limit')));

        $url = "http://{$ip}:{$port}/ISAPI/AccessControl/AcsEvent?format=json";
        // No major/minor filter — some firmwares return 0 rows when filtered.
        // Time range is sent to the device AND re-checked in PHP for safety.
        $body = [
            'AcsEventCond' => [
                'searchID'             => (string) time(),
                'searchResultPosition' => 0,
                'maxResults'           => $limit,
                'startTime'            => $start,
                'endTime'              => $end,
            ],
        ];

        $this->line("Pulling from {$url}");
        $this->line("Date: {$date->toDateString()} ({$start} → {$end})");

        try {
            $resp = Http::withDigestAuth($user, $pass)
                ->timeout(15)
                ->asJson()
                ->post($url, $body);
        } catch (\Throwable $e) {
            $this->error('Device unreachable: ' . $e->getMessage());
            Log::warning('attendance:pull failed to reach device', ['error' => $e->getMessage()]);
            return self::FAILURE;
        }

        if (! $resp->successful()) {
            $this->error("Device returned HTTP {$resp->status()}: " . substr($resp->body(), 0, 200));
            return self::FAILURE;
        }

        $data = $resp->json();
        $events = $data['AcsEvent']['InfoList'] ?? [];
        $totalMatches = (int) ($data['AcsEvent']['totalMatches'] ?? count($events));

        $this->info("Device reported {$totalMatches} matching events; processing " . count($events) . '.');

        $inserted   = 0;
        $skipped    = 0;
        $outOfRange = 0;

        foreach ($events as $ev) {
            $employeeNo = (string) ($ev['employeeNoString'] ?? $ev['employeeNo'] ?? '');
            $personName = (string) ($ev['name'] ?? 'Unknown');
            $eventTime  = isset($ev['time'])
                ? Carbon::parse($ev['time'])->setTimezone($tz)
                : Carbon::now($tz);

            // SAFETY: hard-filter by the requested day — ignore anything outside
            if ($eventTime->lt($startOfDay) || $eventTime->gt($endOfDay)) {
                $outOfRange++;
                continue;
            }

            if ($employeeNo === '') {
                $skipped++;
                continue;
            }

            // Dedupe: same employee + same timestamp = already stored
            $exists = AttendanceLog::where('employee_no', $employeeNo)
                ->where('event_time', $eventTime)
                ->exists();
            if ($exists) {
                $skipped++;
                continue;
            }

            $user = User::where('office_id', $employeeNo)->first();
            $shift = $user ? ($eventTime->hour < 12 ? 'day_shift' : 'night_shift') : null;

            AttendanceLog::create([
                'user_id'        => $user?->id,
                'employee_no'    => $employeeNo,
                'person_name'    => $personName,
                'event_type'     => 'face_pulled',
                'event_time'     => $eventTime,
                'device_name'    => "Hikvision {$ip}",
                'shift_assigned' => $shift,
                'raw_data'       => $ev,
            ]);
            $inserted++;
        }

        $this->info("✓ Inserted: {$inserted}");
        $this->info("· Skipped (dupes/empty employee): {$skipped}");
        $this->info("· Out-of-day (device ignored time filter): {$outOfRange}");

        return self::SUCCESS;
    }
}
