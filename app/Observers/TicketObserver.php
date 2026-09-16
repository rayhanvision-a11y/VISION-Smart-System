<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Ticket;

class TicketObserver
{
    public function created(Ticket $ticket): void
    {
        ActivityLog::record('created', "Ticket #{$ticket->id} created: \"{$ticket->title}\"", $ticket, [
            'status'   => $ticket->status,
            'priority' => $ticket->priority,
        ]);
    }

    public function updated(Ticket $ticket): void
    {
        $dirty = $ticket->getDirty();
        $tracked = array_intersect_key($dirty, array_flip(['status', 'assigned_to', 'priority', 'title']));
        if (empty($tracked)) return;

        $parts = [];
        if (isset($tracked['status']))
            $parts[] = "status → {$ticket->status}";
        if (isset($tracked['assigned_to']))
            $parts[] = "assigned to user #{$ticket->assigned_to}";
        if (isset($tracked['priority']))
            $parts[] = "priority → {$ticket->priority}";
        if (isset($tracked['title']))
            $parts[] = "title updated";

        $old = array_map(fn($k) => $ticket->getOriginal($k), array_keys($tracked));

        ActivityLog::record('updated', "Ticket #{$ticket->id}: " . implode(', ', $parts), $ticket, [
            'old' => array_combine(array_keys($tracked), $old),
            'new' => $tracked,
        ]);
    }

    public function deleted(Ticket $ticket): void
    {
        ActivityLog::record('deleted', "Ticket #{$ticket->id} \"{$ticket->title}\" deleted", $ticket);
    }
}
