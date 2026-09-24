<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Notification;
use App\Models\TicketMessage;
use App\Models\User;
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

            // @mention notifications — detect both HTML-chip mentions and @Name patterns
            $mentionedIds = [];
            if (preg_match_all('/data-id="(\d+)"/', $msg->message, $m)) {
                $mentionedIds = array_map('intval', $m[1]);
            }
            if (preg_match_all('/@([\p{L}][\p{L}\s\.]{1,50})/u', strip_tags($msg->message), $mm)) {
                $candidates = collect($mm[1])->map(fn ($n) => trim($n))->filter()->unique();
                foreach ($candidates as $name) {
                    $u = User::where('name', $name)->first();
                    if ($u) $mentionedIds[] = $u->id;
                }
            }
            $mentionedIds = array_unique(array_filter($mentionedIds, fn ($id) => $id !== $senderId));
            foreach ($mentionedIds as $uid) {
                Notification::create([
                    'user_id' => $uid,
                    'ticket_id' => $ticket->id,
                    'message' => "You were mentioned on Ticket #{$ticket->ticket_key} by ".($msg->sender?->name ?? 'a user'),
                    'is_read' => false,
                ]);
                $mentionedUser = User::find($uid);
                if ($mentionedUser && ! empty($mentionedUser->fcm_token)) {
                    $firebase->notifyUser(
                        $mentionedUser,
                        "You were mentioned",
                        "Ticket #{$ticket->ticket_key}: ".Str::limit(strip_tags($msg->message), 80),
                        ['ticket_id' => (string) $ticket->id, 'type' => 'mention']
                    );
                }
            }
        }
    }

    public function deleted(TicketMessage $msg): void
    {
        ActivityLog::record('deleted', "Message #{$msg->id} deleted from Ticket #{$msg->ticket_id}", $msg);
    }
}
