<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Ticket;
use App\Models\User;

class NotificationService
{
    public static function send(int $userId, string $message, ?int $ticketId = null, bool $checkPermissions = true): void
    {
        $user = User::find($userId);
        if (!$user) {
            return;
        }

        // Only send notification if the user has permission to view the linked ticket
        if ($ticketId && $checkPermissions) {
            $canView = Ticket::where('id', $ticketId)->forUser($user)->exists();
            if (!$canView) {
                return;
            }
        }

        Notification::create([
            'user_id'   => $userId,
            'ticket_id' => $ticketId,
            'message'   => $message,
            'is_read'   => false,
        ]);
    }

    public static function sendToMany(array $userIds, string $message, ?int $ticketId = null, bool $checkPermissions = true): void
    {
        foreach (array_unique($userIds) as $userId) {
            self::send($userId, $message, $ticketId, $checkPermissions);
        }
    }
}
