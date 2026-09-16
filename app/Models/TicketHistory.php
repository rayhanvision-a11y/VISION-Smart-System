<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketHistory extends Model
{
    protected $table = 'ticket_history';

    protected $fillable = ['ticket_id', 'action', 'old_assignee_id', 'new_assignee_id', 'changed_by'];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function oldAssignee()
    {
        return $this->belongsTo(User::class, 'old_assignee_id');
    }

    public function newAssignee()
    {
        return $this->belongsTo(User::class, 'new_assignee_id');
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
