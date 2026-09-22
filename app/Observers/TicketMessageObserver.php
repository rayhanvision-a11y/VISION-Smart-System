<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\TicketMessage;
use App\Services\FirebaseService;
use Illuminate\Support\Str;

class TicketMessageObserver
{
    public function created(TicketMessage $msg): void
    {
        ActivityLog::record(
            'replied',
            "Reply added to Ticket #{$msg->ticket_id}",
            $msg,
            ['ticket_id' => $msg->ticket_id, 'internal' => $msg->is_internal ?? false]
        );

        /** @var FirebaseService $firebase */
        $firebase = app(FirebaseService::class);
        $firebase->syncMessage($msg);

        // Send push notification to relevant party (creator or assigned user)
        $ticket = $msg->ticket;
        if ($ticket) {
            $senderId = $msg->sender_id;
            $recipient = ($senderId === $ticket->created_by) ? $ticket->assignedTo : $ticket->user;

            if ($recipient && $recipient->id !== $senderId) {
                $senderName = $msg->sender?->name ?? 'A user';
                $firebase->notifyUser(
                    $recipient,
                    "New Message on Ticket #{$ticket->ticket_key}",
                    "{$senderName}: ".Str::limit($msg->message, 80),
                    ['ticket_id' => (string) $ticket->id, 'type' => 'new_message']
                );
            }
        }
    }

    public function deleted(TicketMessage $msg): void
    {
        ActivityLog::record('deleted', "Message #{$msg->id} deleted from Ticket #{$msg->ticket_id}", $msg);
    }
}
