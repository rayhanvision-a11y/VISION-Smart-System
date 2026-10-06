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
     * Accessible via GET /api/hikcentral/status
     */
    public function status(): JsonResponse
    {
        return response()->json([
            'status'      => 'online',
            'service'     => 'VISION Smart System - HikCentral Face Attendance Gateway',
            'webhook_url' => url('/api/hikcentral/event'),
            'timestamp'   => now()->toIso8601String(),
        ]);
    }

    /**
     * Webhook endpoint to receive real-time access events from HikCentral OpenAPI.
     * Accessible via POST /api/hikcentral/event
     */
    public function handleEvent(Request $request): JsonResponse
    {
        $payload = $request->all();

        Log::info('HikCentral Webhook Event Received:', [
            'ip'      => $request->ip(),
            'payload' => $payload,
        ]);

        $rawEvents = $this->extractEvents($payload);

        if (empty($rawEvents)) {
            Log::warning('HikCentral Webhook: No recognizable event list in payload.');

            return response()->json([
                'code' => '0',
                'msg'  => 'No recognizable events found in payload',
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

            if ($employeeNo === '') {
                continue;
            }

            // Find matching user in system by office_id or numeric user ID
            $user = User::whereRaw('LOWER(office_id) = ?', [strtolower($employeeNo)])
                ->orWhere(function ($q) use ($employeeNo) {
                    if (is_numeric($employeeNo)) {
                        $q->where('id', (int) $employeeNo);
                    }
                })
                ->first();

            $shiftAssigned = null;
            $parsedDate = Carbon::parse($eventTime);

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
                'raw_data'       => $eventItem,
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
    protected function extractEvents(array $payload): array
    {
        $normalized = [];

        // Format 1: HikCentral OpenAPI (params.events)
        if (isset($payload['params']['events']) && is_array($payload['params']['events'])) {
            foreach ($payload['params']['events'] as $evt) {
                $data = $evt['data'] ?? [];
                $empNo = $data['extEventPersonNo']
                    ?? $data['personId']
                    ?? $data['employeeNo']
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

        // Format 2: Direct Hikvision Terminal AccessControllerEvent
        if (isset($payload['AccessControllerEvent'])) {
            $ace = $payload['AccessControllerEvent'];
            $empNo = $ace['employeeNoString'] ?? $ace['employeeNo'] ?? $ace['cardNo'] ?? '';

            $normalized[] = [
                'employee_no' => (string) $empNo,
                'person_name' => $ace['name'] ?? null,
                'event_time'  => $ace['time'] ?? now(),
                'door_name'   => $ace['doorName'] ?? ($ace['doorNo'] ?? null),
                'device_name' => $payload['deviceName'] ?? null,
                'event_type'  => 'face_match',
            ];

            return $normalized;
        }

        // Format 3: Direct single object or test payload
        if (isset($payload['employeeNo']) || isset($payload['personId']) || isset($payload['office_id'])) {
            $empNo = $payload['employeeNo'] ?? $payload['personId'] ?? $payload['office_id'];

            $normalized[] = [
                'employee_no' => (string) $empNo,
                'person_name' => $payload['personName'] ?? $payload['name'] ?? null,
                'event_time'  => $payload['eventTime'] ?? $payload['time'] ?? now(),
                'door_name'   => $payload['doorName'] ?? null,
                'device_name' => $payload['deviceName'] ?? null,
                'event_type'  => $payload['eventType'] ?? 'face_match',
            ];

            return $normalized;
        }

        return $normalized;
    }
}
