<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HikCentralWebhookController extends Controller
{
    /**
     * Health & status check endpoint for HikCentral integration.
     * Accessible via GET /api/hikcentral/status or GET /api/hikcentral/event
     */
    public function status(): JsonResponse
    {
        $lastHit = \Illuminate\Support\Facades\Cache::get('hikcentral_last_hit');
        $totalLogs = 0;
        $recentLogs = [];
        try {
            $totalLogs = AttendanceLog::count();
            $recentLogs = AttendanceLog::latest()->take(5)->get(['id', 'employee_no', 'person_name', 'event_type', 'event_time', 'shift_assigned']);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('HikCentral status check log query error: ' . $e->getMessage());
        }

        $deviceIp   = config('services.attendance.device_ip', '172.27.1.49');
        $devicePort = (int) config('services.attendance.device_port', 80);
        $dnsIp      = config('services.attendance.dns_ip', '172.30.20.50');
        $serverLanIp = config('services.attendance.server_ip', '103.31.179.118');

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

        $port = request()->getPort();
        $portSuffix = ($port && !in_array($port, [80, 443])) ? ":{$port}" : '';
        $lanWebhookUrl = "http://{$serverLanIp}{$portSuffix}/api/hikcentral/event";

        return response()->json([
            'status'            => 'online',
            'service'           => 'VISION Smart System - HikCentral Face Attendance Gateway',
            'webhook_url'       => url('/api/hikcentral/event'),
            'lan_webhook_url'   => $lanWebhookUrl,
            'network_health'    => [
                'device_ip'     => $deviceIp,
                'device_online' => $deviceOnline,
                'dns_ip'        => $dnsIp,
                'dns_online'    => $dnsOnline,
                'server_ip'     => $serverLanIp,
                'server_lan_ip' => $serverLanIp,
            ],
            'timestamp'         => now()->toIso8601String(),
            'total_logs_stored' => $totalLogs,
            'last_device_hit'   => $lastHit,
            'recent_attendance' => $recentLogs,
        ]);
    }

    /**
     * Webhook endpoint to receive real-time access events from HikCentral OpenAPI.
     * Accessible via POST /api/hikcentral/event or GET /api/hikcentral/event
     */
    public function handleEvent(Request $request): JsonResponse
    {
        // If accessed via GET in browser or monitoring check, return gateway status
        if ($request->isMethod('GET')) {
            return $this->status();
        }

        $rawContent = $request->getContent();
        $payload = $request->all();

        // If body was raw JSON and not parsed into $request->all()
        if (empty($payload) && !empty($rawContent)) {
            $decoded = json_decode($rawContent, true);
            if (is_array($decoded)) {
                $payload = $decoded;
            }
        }

        // Sanitize any UploadedFile objects to prevent serialization errors
        $sanitizedPayload = $this->sanitizePayload($payload);

        // Cache last raw hit telemetry for instant debugging via /api/hikcentral/status
        try {
            \Illuminate\Support\Facades\Cache::put('hikcentral_last_hit', [
                'time'       => now()->toIso8601String(),
                'ip'         => $request->ip(),
                'user_agent' => $request->userAgent(),
                'payload'    => !empty($sanitizedPayload) ? $sanitizedPayload : ['raw' => substr($rawContent, 0, 1000)],
            ], 86400);
        } catch (\Throwable $e) {
            Log::warning('HikCentral: Failed to cache last hit telemetry: ' . $e->getMessage());
        }

        Log::info('HikCentral Webhook Event Received:', [
            'ip'      => $request->ip(),
            'payload' => $sanitizedPayload,
        ]);

        $rawEvents = $this->extractEvents($payload, $rawContent);

        if (empty($rawEvents)) {
            Log::warning('HikCentral Webhook: No recognizable event list in payload. Saving fallback entry.');

            // Record raw capture so it visibly shows up in /attendance
            AttendanceLog::create([
                'employee_no'    => 'UNKNOWN',
                'person_name'    => 'Hikvision Event (Raw)',
                'event_type'     => 'raw_capture',
                'event_time'     => now(),
                'device_name'    => $request->ip(),
                'shift_assigned' => (now()->format('H') >= 14) ? 'night_shift' : 'day_shift',
                'raw_data'       => !empty($sanitizedPayload) ? $sanitizedPayload : ['raw' => substr($rawContent, 0, 1000)],
            ]);

            return response()->json([
                'code' => '0',
                'msg'  => 'Event recorded as raw capture',
            ], 200);
        }

        $processed = 0;
        $matched = 0;

        foreach ($rawEvents as $eventItem) {
            $processed++;

            $employeeNo = trim((string) ($eventItem['employee_no'] ?? ''));
            $personName = $eventItem['person_name'] ?? null;
            $eventTime  = $eventItem['event_time'] ?? now();
            $doorName   = $eventItem['door_name'] ?? null;
            $deviceName = $eventItem['device_name'] ?? null;
            $eventType  = $eventItem['event_type'] ?? 'face_match';

            $isKnown = ($employeeNo !== '');
            if (!$isKnown) {
                $employeeNo = 'UNKNOWN';
                $personName = $personName ?? 'Face/Finger Not Recognized';
                $eventType  = 'auth_unrecognized';
            }

            // Find matching user in system by office_id or numeric user ID
            $user = $isKnown ? User::whereRaw('LOWER(office_id) = ?', [strtolower($employeeNo)])
                ->orWhere(function ($q) use ($employeeNo) {
                    if (is_numeric($employeeNo)) {
                        $q->where('id', (int) $employeeNo);
                    }
                })
                ->first() : null;

            $shiftAssigned = null;
            try {
                $parsedDate = Carbon::parse($eventTime);
                if (abs($parsedDate->diffInDays(now())) > 3) {
                    $parsedDate = now();
                }
            } catch (\Throwable) {
                $parsedDate = now();
            }

            if ($user) {
                $matched++;

                // Determine shift based on check-in hour
                // 9:00 AM - 6:00 PM: Day Shift (if punch before 14:00 / 2:00 PM)
                // 2:00 PM - 10:00 PM: Night Shift (if punch at or after 14:00)
                $hour = (int) $parsedDate->format('H');
                $shiftAssigned = ($hour >= 14) ? 'night_shift' : 'day_shift';

                // Automatically activate On Duty and update shift
                $user->forceFill([
                    'current_shift' => $shiftAssigned,
                    'shift_date'    => $parsedDate->toDateString(),
                ])->save();

                Log::info("HikCentral: User {$user->name} (Office ID: {$user->office_id}, ID: {$user->id}) marked ON DUTY with {$shiftAssigned}.");
            } else {
                Log::warning("HikCentral: Received face event for unmatched Employee No: {$employeeNo} ({$personName}).");
            }

            // Save log to attendance_logs table
            AttendanceLog::create([
                'user_id'        => $user?->id,
                'employee_no'    => $employeeNo,
                'person_name'    => $personName,
                'event_type'     => (string) $eventType,
                'event_time'     => $parsedDate,
                'door_name'      => $doorName,
                'device_name'    => $deviceName,
                'shift_assigned' => $shiftAssigned,
                'raw_data'       => $this->sanitizePayload($eventItem),
            ]);
        }

        // Return standard HikCentral OpenAPI success acknowledgement
        return response()->json([
            'code' => '0',
            'msg'  => 'success',
            'data' => [
                'total_processed' => $processed,
                'users_matched'   => $matched,
            ],
        ], 200);
    }

    /**
     * Normalize events from various HikCentral / Hikvision payload formats.
     */
    protected function extractEvents(array $payload, string $rawContent = ''): array
    {
        $normalized = [];

        // If event_log is a JSON string (typical for multipart Hikvision ISAPI alarms)
        if (isset($payload['event_log']) && is_string($payload['event_log'])) {
            $parsed = json_decode($payload['event_log'], true);
            if (is_array($parsed)) {
                $payload = array_merge($payload, $parsed);
            }
        }

        // If AccessControllerEvent is stringified JSON or case variant
        $ace = $payload['AccessControllerEvent'] ?? $payload['accessControllerEvent'] ?? null;
        if (is_string($ace)) {
            $parsed = json_decode($ace, true);
            if (is_array($parsed)) {
                $ace = $parsed;
            }
        }

        // Format 1: HikCentral OpenAPI (params.events)
        if (isset($payload['params']['events']) && is_array($payload['params']['events'])) {
            foreach ($payload['params']['events'] as $evt) {
                $data = $evt['data'] ?? [];
                $empNo = $data['extEventPersonNo']
                    ?? $data['personId']
                    ?? $data['employeeNo']
                    ?? $data['employeeNoString']
                    ?? $data['cardNo']
                    ?? '';

                $normalized[] = [
                    'employee_no' => (string) $empNo,
                    'person_name' => $data['personName'] ?? null,
                    'event_time'  => $evt['eventTime'] ?? now(),
                    'door_name'   => $data['doorName'] ?? null,
                    'device_name' => $data['devName'] ?? null,
                    'event_type'  => $evt['eventType'] ?? 'face_match',
                ];
            }

            return $normalized;
        }

        // Format 2: Direct Hikvision Terminal AccessControllerEvent / Access Control Event
        if (is_array($ace)) {
            $empNo = $ace['employeeNoString'] ?? $ace['employeeNo'] ?? $ace['personId'] ?? $ace['cardNo'] ?? '';

            $normalized[] = [
                'employee_no' => (string) $empNo,
                'person_name' => $ace['name'] ?? null,
                'event_time'  => $ace['time'] ?? ($payload['dateTime'] ?? now()),
                'door_name'   => $ace['doorName'] ?? ($ace['doorNo'] ?? null),
                'device_name' => $ace['deviceName'] ?? ($payload['deviceName'] ?? null),
                'event_type'  => 'face_match',
            ];

            return $normalized;
        }

        // Format 3: Direct flat fields (employeeNoString, employeeNo, personId, office_id, etc.)
        $flatEmpNo = $payload['employeeNoString']
            ?? $payload['employeeNo']
            ?? $payload['employee_no']
            ?? $payload['personId']
            ?? $payload['person_id']
            ?? $payload['office_id']
            ?? $payload['cardNo']
            ?? null;

        if ($flatEmpNo !== null && $flatEmpNo !== '') {
            $normalized[] = [
                'employee_no' => (string) $flatEmpNo,
                'person_name' => $payload['personName'] ?? $payload['name'] ?? null,
                'event_time'  => $payload['eventTime'] ?? $payload['time'] ?? ($payload['dateTime'] ?? now()),
                'door_name'   => $payload['doorName'] ?? null,
                'device_name' => $payload['deviceName'] ?? null,
                'event_type'  => $payload['eventType'] ?? 'face_match',
            ];

            return $normalized;
        }

        // Format 4: XML payload fallback
        if (str_contains($rawContent, '<employeeNoString>') || str_contains($rawContent, '<employeeNo>')) {
            preg_match('/<employeeNoString>(.*?)<\/employeeNoString>/', $rawContent, $m1);
            preg_match('/<employeeNo>(.*?)<\/employeeNo>/', $rawContent, $m2);
            $xmlEmpNo = $m1[1] ?? ($m2[1] ?? '');

            if ($xmlEmpNo !== '') {
                preg_match('/<name>(.*?)<\/name>/', $rawContent, $mName);
                preg_match('/<time>(.*?)<\/time>/', $rawContent, $mTime);

                $normalized[] = [
                    'employee_no' => trim($xmlEmpNo),
                    'person_name' => $mName[1] ?? null,
                    'event_time'  => $mTime[1] ?? now(),
                    'door_name'   => null,
                    'device_name' => 'Hikvision XML',
                    'event_type'  => 'face_match',
                ];

                return $normalized;
            }
        }

        return $normalized;
    }

    /**
     * Public / programmatic device test endpoint for HikCentral integration.
     * Accessible via GET /api/hikcentral/test-connection
     */
    public function testDeviceConnection(): JsonResponse
    {
        $deviceIp   = config('services.attendance.device_ip', '172.27.1.49');
        $dnsIp      = config('services.attendance.dns_ip', '172.30.20.50');

        $start = microtime(true);
        $port80 = false;
        $port443 = false;
        $latencyMs = 0;

        $fp80 = @fsockopen($deviceIp, 80, $e, $es, 0.8);
        if ($fp80) {
            $port80 = true;
            $latencyMs = round((microtime(true) - $start) * 1000, 2);
            fclose($fp80);
        }

        $fp443 = @fsockopen($deviceIp, 443, $e, $es, 0.8);
        if ($fp443) {
            $port443 = true;
            if ($latencyMs == 0) {
                $latencyMs = round((microtime(true) - $start) * 1000, 2);
            }
            fclose($fp443);
        }

        $dnsOnline = false;
        $dnsFp = @fsockopen("udp://{$dnsIp}", 53, $e, $es, 0.5);
        if ($dnsFp) {
            $dnsOnline = true;
            fclose($dnsFp);
        }

        $deviceOnline = ($port80 || $port443);
        $lastHit = \Illuminate\Support\Facades\Cache::get('hikcentral_last_hit');

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

    /**
     * Recursively sanitize payload by stripping / converting UploadedFile instances
     * so they can be safely stored in Cache and JSON database columns without serialization error.
     */
    protected function sanitizePayload(mixed $data): mixed
    {
        if ($data instanceof \Illuminate\Http\UploadedFile) {
            return [
                'file_name' => $data->getClientOriginalName(),
                'size'      => $data->getSize(),
                'mime'      => $data->getClientMimeType(),
            ];
        }

        if (is_array($data)) {
            $clean = [];
            foreach ($data as $key => $val) {
                $clean[$key] = $this->sanitizePayload($val);
            }
            return $clean;
        }

        return $data;
    }
}
