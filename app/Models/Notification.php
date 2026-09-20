<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = ['user_id', 'ticket_id', 'message', 'is_read'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Scope notifications for the specified user and ensure that any
     * ticket-linked notifications are only visible if the user has access to view that ticket.
     */
    public function scopeForUser($query, ?User $user = null)
    {
        $user = $user ?? auth()->user();
        if (!$user) {
            return $query;
        }

        return $query->where('user_id', $user->id)
            ->where(function ($q) use ($user) {
                $q->whereNull('ticket_id')
                  ->orWhereHas('ticket', function ($tq) use ($user) {
                      $tq->forUser($user);
                  });
            });
    }
}
