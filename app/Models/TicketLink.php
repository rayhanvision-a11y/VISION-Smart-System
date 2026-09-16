<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketLink extends Model
{
    protected $fillable = ['ticket_id', 'linked_ticket_id', 'link_type', 'created_by'];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function linkedTicket()
    {
        return $this->belongsTo(Ticket::class, 'linked_ticket_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
