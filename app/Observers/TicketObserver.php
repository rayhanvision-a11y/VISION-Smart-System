<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Ticket;
use App\Services\FirebaseService;

class TicketObserver
{
    public function created(Ticket $ticket): void
    {
        ActivityLog::record('created', "Ticket #{$ticket->id} created: \"{$ticket->title}\"", $ticket, [
            'status' => $ticket->status,
            'priority' => $ticket->priority,
        ]);

        /** @var FirebaseService $firebase */
        $firebase = app(FirebaseService::class);
        $firebase->syncTicket($ticket);

        if ($ticket->assignedTo) {
            $firebase->notifyUser(
                $ticket->assignedTo,
                'New Ticket Assigned',
                "Ticket #{$ticket->ticket_key}: {$ticket->title}",
                ['ticket_id' => (string) $ticket->id, 'type' => 'ticket_assigned']
            );
        }
    }

    public function updated(Ticket $ticket): void
    {
        $dirty = $ticket->getDirty();
        $tracked = array_intersect_key($dirty, array_flip(['status', 'assigned_to', 'priority', 'title']));

        /** @var FirebaseService $firebase */
        $firebase = app(FirebaseService::class);
        $firebase->syncTicket($ticket);

        if (empty($tracked)) {
            return;
        }

        $parts = [];
        if (isset($tracked['status'])) {
            $parts[] = "status → {$ticket->status}";
        }
        if (isset($tracked['assigned_to'])) {
            $parts[] = "assigned to user #{$ticket->assigned_to}";
        }
        if (isset($tracked['priority'])) {
            $parts[] = "priority → {$ticket->priority}";
        }
        if (isset($tracked['title'])) {
            $parts[] = 'title updated';
        }

        $old = array_map(fn ($k) => $ticket->getOriginal($k), array_keys($tracked));

        ActivityLog::record('updated', "Ticket #{$ticket->id}: ".implode(', ', $parts), $ticket, [
            'old' => array_combine(array_keys($tracked), $old),
            'new' => $tracked,
        ]);

        if (isset($tracked['assigned_to']) && $ticket->assignedTo) {
            $firebase->notifyUser(
                $ticket->assignedTo,
                'Ticket Assigned to You',
                "Ticket #{$ticket->ticket_key}: {$ticket->title}",
                ['ticket_id' => (string) $ticket->id, 'type' => 'ticket_assigned']
            );
        }

        if (isset($tracked['status']) && $ticket->user) {
            $firebase->notifyUser(
                $ticket->user,
                'Ticket Status Updated',
                "Ticket #{$ticket->ticket_key} status changed to {$ticket->status}",
                ['ticket_id' => (string) $ticket->id, 'type' => 'ticket_status']
            );
        }
    }

    public function deleted(Ticket $ticket): void
    {
        ActivityLog::record('deleted', "Ticket #{$ticket->id} \"{$ticket->title}\" deleted", $ticket);
    }
}
