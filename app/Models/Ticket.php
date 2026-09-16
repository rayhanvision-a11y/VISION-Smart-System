<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_key',
        'parent_id',
        'merged_into_id',
        'title',
        'description',
        'category',
        'priority',
        'status',
        'created_by',
        'assigned_to',
        'pop_office_id',
        'resolved_at',
        'due_at',
        'sla_notified_at',
        'csat_rating',
        'csat_comment',
        'csat_submitted_at',
    ];

    protected $casts = [
        'resolved_at'        => 'datetime',
        'due_at'             => 'datetime',
        'sla_notified_at'    => 'datetime',
        'csat_submitted_at'  => 'datetime',
    ];

    /**
     * Generate custom ticket key: YYMMDDNNN (e.g. 260910001)
     * NNN is the daily sequential count for that date.
     */
    public static function generateKey(): string
    {
        $prefix = now()->format('ymd'); // e.g. 260910
        $count  = static::whereDate('created_at', now()->toDateString())
                        ->whereNotNull('ticket_key')
                        ->where('ticket_key', 'like', $prefix . '%')
                        ->count();
        return $prefix . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function messages()
    {
        return $this->hasMany(TicketMessage::class);
    }

    public function notes()
    {
        return $this->hasMany(TicketNote::class);
    }

    public function history()
    {
        return $this->hasMany(TicketHistory::class);
    }

    public function labels()
    {
        return $this->belongsToMany(Label::class);
    }

    public function popOffice()
    {
        return $this->belongsTo(PopOffice::class, 'pop_office_id');
    }

    public function parent()
    {
        return $this->belongsTo(Ticket::class, 'parent_id');
    }

    public function subtasks()
    {
        return $this->hasMany(Ticket::class, 'parent_id');
    }

    public function links()
    {
        return $this->hasMany(TicketLink::class, 'ticket_id');
    }

    public function linkedFrom()
    {
        return $this->hasMany(TicketLink::class, 'linked_ticket_id');
    }

    public function attachments()
    {
        return $this->hasMany(TicketAttachment::class);
    }

    public function mergedInto()
    {
        return $this->belongsTo(Ticket::class, 'merged_into_id');
    }

    public function mergedTickets()
    {
        return $this->hasMany(Ticket::class, 'merged_into_id');
    }

    public function isMerged(): bool
    {
        return $this->merged_into_id !== null;
    }
}
