<?php

namespace App\Services;

use App\Models\Notification;

class NotificationService
{
    public static function send(int $userId, string $message, ?int $ticketId = null): void
    {
        Notification::create([
            'user_id'   => $userId,
            'ticket_id' => $ticketId,
            'message'   => $message,
            'is_read'   => false,
        ]);
    }

    public static function sendToMany(array $userIds, string $message, ?int $ticketId = null): void
    {
        foreach (array_unique($userIds) as $userId) {
            self::send($userId, $message, $ticketId);
        }
    }
}
