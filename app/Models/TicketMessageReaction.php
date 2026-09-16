<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketMessageReaction extends Model
{
    protected $fillable = ['ticket_message_id', 'user_id', 'emoji'];

    public function message()
    {
        return $this->belongsTo(TicketMessage::class, 'ticket_message_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
