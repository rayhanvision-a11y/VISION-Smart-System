<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Ticket;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleSheetSyncService
{
    /**
     * Check if Google Sheet Sync is enabled and webhook URL configured.
     */
    public static function isEnabled(): bool
    {
        $enabled = Setting::get('google_sheet_sync_enabled', '0') === '1';
        $url = Setting::get('google_sheet_webhook_url');

        return $enabled && ! empty($url);
    }

    /**
     * Sync newly created ticket to Google Sheet.
     */
    public static function syncTicketCreated(Ticket $ticket): void
    {
        if (! static::isEnabled()) {
            return;
        }

        try {
            $webhookUrl = Setting::get('google_sheet_webhook_url');

            // Format Category / Type name nicely
            $categoryName = $ticket->category;
            if ($ticket->categoryModel) {
                $categoryName = $ticket->categoryModel->name;
            } else {
                $categoryName = ucwords(str_replace('_', ' ', (string) $ticket->category));
            }

            // Map status for Google Sheet column
            $statusLabel = static::formatStatus($ticket->status);

            // Sheet tab: defaults to the creation month name (e.g. "September", "October")
            $targetSheet = $ticket->created_at ? $ticket->created_at->format('F') : now()->format('F');

            $payload = [
                'action'           => 'create',
                'sheet_name'       => $targetSheet,
                'ticket_key'       => $ticket->ticket_key,
                'date'             => $ticket->created_at ? $ticket->created_at->format('d M, y') : now()->format('d M, y'),
                'time'             => $ticket->created_at ? $ticket->created_at->format('h:i A') : now()->format('h:i A'),
                'complaint_source' => $ticket->complaint_source ?: 'Phone',
                'client_id'        => $ticket->client_id ?: $ticket->ticket_key,
                'id'               => $ticket->client_id ?: $ticket->ticket_key,
                'client_name'      => $ticket->client_name ?: $ticket->title,
                'name'             => $ticket->client_name ?: $ticket->title,
                'address'          => $ticket->area ?: '',
                'type'             => $categoryName,
                'received_by'      => $ticket->creator ? $ticket->creator->name : 'System',
                'forwarded_to'     => $ticket->forwarded_to ?: '',
                'onu_power'        => $ticket->onu_power ?: '',
                'assigned_to'      => $ticket->assignee ? $ticket->assignee->name : '',
                'status'           => $statusLabel,
                'current_status'   => $statusLabel,
                'remarks'          => $ticket->description ?: '',
            ];

            $response = Http::timeout(6)->asJson()->post($webhookUrl, $payload);

            if ($response->successful()) {
                $resData = $response->json();
                if (! empty($resData['row']) && is_numeric($resData['row'])) {
                    $ticket->updateQuietly(['google_sheet_row_id' => (int) $resData['row']]);
                }
            } else {
                Log::warning('Google Sheet sync failed on create ticket: '.$response->body());
            }
        } catch (\Throwable $e) {
            Log::error('Google Sheet Sync Error on ticket create: '.$e->getMessage());
        }
    }

    /**
     * Sync ticket update (status, assignee, remarks) to Google Sheet.
     */
    public static function syncTicketUpdated(Ticket $ticket, ?string $remarks = null): void
    {
        if (! static::isEnabled()) {
            return;
        }

        try {
            $webhookUrl = Setting::get('google_sheet_webhook_url');
            $targetSheet = $ticket->created_at ? $ticket->created_at->format('F') : now()->format('F');

            $payload = [
                'action'         => 'update',
                'sheet_name'     => $targetSheet,
                'ticket_key'     => $ticket->ticket_key,
                'client_id'      => $ticket->client_id ?: $ticket->ticket_key,
                'id'             => $ticket->client_id ?: $ticket->ticket_key,
                'row_id'         => $ticket->google_sheet_row_id,
                'assigned_to'    => $ticket->assignee ? $ticket->assignee->name : '',
                'status'         => static::formatStatus($ticket->status),
                'current_status' => static::formatStatus($ticket->status),
                'onu_power'      => $ticket->onu_power ?: '',
                'forwarded_to'   => $ticket->forwarded_to ?: '',
                'remarks'        => $remarks ?: '',
            ];

            Http::timeout(6)->asJson()->post($webhookUrl, $payload);
        } catch (\Throwable $e) {
            Log::error('Google Sheet Sync Error on ticket update: '.$e->getMessage());
        }
    }

    /**
     * Format status text to match Google Sheet values.
     */
    public static function formatStatus(?string $status): string
    {
        return match ($status) {
            'resolved'    => 'Solved',
            'closed'      => 'Solved',
            'pending'     => 'Pending',
            'in_progress' => 'Assigned',
            'open'        => 'Assigned',
            'on_hold'     => 'Pending',
            default       => ucfirst(str_replace('_', ' ', (string) $status)),
        };
    }
}
