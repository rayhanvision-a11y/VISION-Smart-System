<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Ticket;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleSheetSyncService
{
    /**
     * In-memory cache to prevent duplicate webhook calls in the same request.
     */
    protected static array $syncedCreatedTickets = [];
    protected static array $syncedUpdatedTickets = [];

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

        // Internal tickets never go to Google Sheet
        if (($ticket->ticket_type ?? 'external') === 'internal') {
            return;
        }

        // Prevent duplicate creation sync in the same lifecycle
        if (isset(static::$syncedCreatedTickets[$ticket->id])) {
            return;
        }
        static::$syncedCreatedTickets[$ticket->id] = true;

        try {
            $webhookUrl = Setting::get('google_sheet_webhook_url');

            // Format Category / Type name nicely to match sheet dropdown
            $categoryName = static::mapCategoryName($ticket->categoryModel ? $ticket->categoryModel->name : $ticket->category);

            // Format Creator / Received By to match sheet dropdown
            $creatorName = static::mapCreatorName($ticket->creator ? $ticket->creator->name : 'Rayhan');

            // Format Assignee / Technician name to match sheet dropdown
            $assigneeName = static::mapTechnicianName($ticket->assignee ? $ticket->assignee->name : ($ticket->forwarded_to ?: ''));

            // Map status for Google Sheet column
            $statusLabel = static::formatStatus($ticket->status);

            // Sheet tab: defaults to the creation month name (e.g. "September", "October")
            $targetSheet = $ticket->created_at ? $ticket->created_at->format('F') : now()->format('F');

            $payload = [
                'action'           => 'create',
                'sheet_name'       => $targetSheet,
                'ticket_key'       => $ticket->ticket_key, // For Column F (e.g. 260927009)
                'ticket_id'        => $ticket->ticket_key,
                'id'               => $ticket->ticket_key,
                'client_id'        => $ticket->client_id ?: '', // For Column G (Customer ID e.g. 154662)
                'customer_id'      => $ticket->client_id ?: '',
                'date'             => $ticket->created_at ? $ticket->created_at->format('d M, y') : now()->format('d M, y'),
                'time'             => $ticket->created_at ? $ticket->created_at->format('h:i A') : now()->format('h:i A'),
                'complaint_source' => $ticket->complaint_source ?: 'Phone',
                'client_name'      => $ticket->client_name ?: $ticket->title,
                'name'             => $ticket->client_name ?: $ticket->title,
                'address'          => $ticket->area ?: '',
                'area'             => $ticket->area ?: '',
                'contact'          => $ticket->client_id ? 'Client ID: '.$ticket->client_id : '',
                'phone'            => $ticket->client_id ? 'Client ID: '.$ticket->client_id : '',
                'type'             => $categoryName,
                'category'         => $categoryName,
                'received_by'      => $creatorName,
                'created_by'       => $creatorName,
                'forwarded_to'     => $ticket->forwarded_to ?: '',
                'onu_power'        => $ticket->onu_power ?: '',
                'assigned_to'      => $assigneeName,
                'status'           => $statusLabel,
                'current_status'   => $statusLabel,
                'feedback'         => 'Not Yet',
                'remarks'          => trim(html_entity_decode(strip_tags((string) ($ticket->description ?: '')), ENT_QUOTES, 'UTF-8')),
                'description'      => trim(html_entity_decode(strip_tags((string) ($ticket->description ?: '')), ENT_QUOTES, 'UTF-8')),
            ];

            $response = Http::withoutVerifying()->timeout(10)->asJson()->post($webhookUrl, $payload);

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

        // Internal tickets never go to Google Sheet
        if (($ticket->ticket_type ?? 'external') === 'internal') {
            return;
        }

        // Prevent duplicate webhook calls for identical status & assignee
        $cacheKey = $ticket->id . ':' . $ticket->status . ':' . ($ticket->assigned_to ?? 0) . ':' . ($remarks ?? '');
        if (isset(static::$syncedUpdatedTickets[$cacheKey])) {
            return;
        }
        static::$syncedUpdatedTickets[$cacheKey] = true;

        try {
            $webhookUrl = Setting::get('google_sheet_webhook_url');
            $targetSheet = $ticket->created_at ? $ticket->created_at->format('F') : now()->format('F');
            $assigneeName = static::mapTechnicianName($ticket->assignee ? $ticket->assignee->name : ($ticket->forwarded_to ?: ''));

            $payload = [
                'action'         => 'update',
                'sheet_name'     => $targetSheet,
                'ticket_key'     => $ticket->ticket_key, // Column F (e.g. 260927009)
                'ticket_id'      => $ticket->ticket_key,
                'id'             => $ticket->ticket_key,
                'client_id'      => $ticket->client_id ?: '',
                'row_id'         => $ticket->google_sheet_row_id,
                'assigned_to'    => $assigneeName,
                'forwarded_to'   => $ticket->forwarded_to ?: '',
                'status'         => static::formatStatus($ticket->status),
                'current_status' => static::formatStatus($ticket->status),
                'onu_power'      => $ticket->onu_power ?: '',
                'remarks'        => trim(html_entity_decode(strip_tags((string) ($remarks ?: '')), ENT_QUOTES, 'UTF-8')),
            ];

            Http::withoutVerifying()->timeout(10)->asJson()->post($webhookUrl, $payload);
        } catch (\Throwable $e) {
            Log::error('Google Sheet Sync Error on ticket update: '.$e->getMessage());
        }
    }

    /**
     * Format status text to match Google Sheet values:
     * Allowed values: Assigned, Pending, Processing, Solved
     */
    public static function formatStatus(?string $status): string
    {
        return match ($status) {
            'resolved', 'closed' => 'Solved',
            'pending', 'waiting_for_customer_feedback', 'on_hold' => 'Pending',
            'open' => 'Assigned',
            'in_progress', 'processing' => 'Processing',
            default => 'Assigned',
        };
    }

    /**
     * Map category to exact Google Sheet dropdown values.
     */
    public static function mapCategoryName(?string $category): string
    {
        $raw = strtolower(trim((string)$category));
        return match ($raw) {
            'other', 'others' => 'Others',
            'router_issue', 'router issue', 'router' => 'Router re-configure',
            'fiber_cut', 'fiber cut', 'net off' => 'Net Off (ONU Optical Power Los)',
            'speed_slow', 'speed problem' => 'Speed Problem (Internet)',
            'new_connection', 'reconnect' => 'Reconnect',
            'iptv' => 'SunPlex Video Loading Problem Via Wifi',
            default => $category ?: 'Others',
        };
    }

    /**
     * Map creator name to match Google Sheet Received By dropdown list.
     */
    public static function mapCreatorName(?string $fullName): string
    {
        if (empty($fullName)) return 'Rayhan';
        $name = trim($fullName);
        $allowed = [
            'Alam', 'Amena', 'Ferdous', 'Fariduzzaman', 'Feroz', 'Humaun', 'Hasanuzzaman',
            'Halima', 'Mahbub', 'Mobarak', 'Rasel', 'Rayhan', 'Riajul', 'Robiul', 'Rumon',
            'Sazzad', 'Saddam', 'Salman', 'Shourov', 'Sojol', 'Touhid', 'Tuhinur', 'Sagor', 'Wadud'
        ];
        foreach ($allowed as $one) {
            if (stripos($name, $one) !== false) {
                return $one;
            }
        }
        return $name;
    }

    /**
     * Map technician name to match Google Sheet Assigned To dropdown list.
     */
    public static function mapTechnicianName(?string $name): string
    {
        if (empty($name)) return '';
        $clean = trim($name);
        $allowed = [
            'Azim', 'Emon', 'Faruk', 'Fazlul', 'Hazrot', 'Manik', 'Masud', 'Mirju',
            'Millon (Pabna)', 'Mostakim', 'Nayan', 'Nurul', 'Office Con.', 'Rafiq',
            'Rashel', 'Riad', 'Rohan', 'Sakib 1', 'Shakil', 'Shuvo-01', 'Shuvo-02',
            'Sakib-2', 'Salman', 'Touhid', 'Shovo-3', 'Sobuj-2', 'Sourov', 'Rayhan Rabby'
        ];
        foreach ($allowed as $item) {
            if (stripos($clean, $item) !== false || stripos($item, $clean) !== false) {
                return $item;
            }
        }
        return $clean;
    }
}

