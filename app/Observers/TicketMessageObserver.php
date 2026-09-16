<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\TicketMessage;

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
    }

    public function deleted(TicketMessage $msg): void
    {
        ActivityLog::record('deleted', "Message #{$msg->id} deleted from Ticket #{$msg->ticket_id}", $msg);
    }
}
