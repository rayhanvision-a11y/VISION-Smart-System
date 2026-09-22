<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    protected string $projectId;

    protected string $databaseUrl;

    protected string $serverKey;

    protected bool $enabled;

    public function __construct()
    {
        $this->projectId = (string) config('firebase.project_id', '');
        $this->databaseUrl = rtrim((string) config('firebase.database_url', ''), '/');
        $this->serverKey = (string) config('firebase.server_key', '');
        $this->enabled = (bool) config('firebase.enabled', false);
    }

    /**
     * Check if Firebase integration is enabled & configured.
     */
    public function isEnabled(): bool
    {
        return $this->enabled && (! empty($this->databaseUrl) || ! empty($this->projectId) || ! empty($this->serverKey));
    }

    /**
     * Sync ticket state to Firebase Realtime Database / Firestore.
     */
    public function syncTicket(Ticket $ticket): bool
    {
        if (! $this->isEnabled() || empty($this->databaseUrl)) {
            return false;
        }

        try {
            $data = [
                'id' => $ticket->id,
                'ticket_key' => $ticket->ticket_key,
                'title' => $ticket->title,
                'status' => $ticket->status,
                'priority' => $ticket->priority,
                'category_id' => $ticket->category_id,
                'created_by' => $ticket->created_by,
                'assigned_to' => $ticket->assigned_to,
                'updated_at' => $ticket->updated_at?->toIso8601String(),
            ];

            $url = "{$this->databaseUrl}/tickets/{$ticket->id}.json";

            $response = Http::timeout(5)->put($url, $data);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error("Firebase syncTicket failed for Ticket #{$ticket->id}: ".$e->getMessage());

            return false;
        }
    }

    /**
     * Sync new ticket message to Firebase.
     */
    public function syncMessage(TicketMessage $message): bool
    {
        if (! $this->isEnabled() || empty($this->databaseUrl)) {
            return false;
        }

        try {
            $data = [
                'id' => $message->id,
                'ticket_id' => $message->ticket_id,
                'sender_id' => $message->sender_id,
                'sender_name' => $message->sender?->name ?? 'User',
                'message' => $message->message,
                'is_private' => (bool) $message->is_private,
                'created_at' => $message->created_at?->toIso8601String(),
            ];

            $url = "{$this->databaseUrl}/tickets/{$message->ticket_id}/messages/{$message->id}.json";

            $response = Http::timeout(5)->put($url, $data);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error("Firebase syncMessage failed for Message #{$message->id}: ".$e->getMessage());

            return false;
        }
    }

    /**
     * Send FCM Push Notification to a target FCM Token.
     */
    public function sendPushNotification(string $fcmToken, string $title, string $body, array $extraData = []): bool
    {
        if (! $this->isEnabled() || empty($this->serverKey) || empty($fcmToken)) {
            return false;
        }

        try {
            $payload = [
                'to' => $fcmToken,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'sound' => 'default',
                ],
                'data' => array_merge([
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                ], $extraData),
                'priority' => 'high',
            ];

            $response = Http::withHeaders([
                'Authorization' => 'key='.$this->serverKey,
                'Content-Type' => 'application/json',
            ])->timeout(5)->post('https://fcm.googleapis.com/fcm/send', $payload);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('FCM Push Notification failed: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Send Push Notification to a User if they have an FCM token.
     */
    public function notifyUser(User $user, string $title, string $body, array $extraData = []): bool
    {
        if (! empty($user->fcm_token)) {
            return $this->sendPushNotification($user->fcm_token, $title, $body, $extraData);
        }

        return false;
    }
}
