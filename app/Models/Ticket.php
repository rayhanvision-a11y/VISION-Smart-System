<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'resolved_at' => 'datetime',
        'due_at' => 'datetime',
        'sla_notified_at' => 'datetime',
        'csat_submitted_at' => 'datetime',
    ];

    /**
     * Generate custom ticket key: YYMMDDNNN (e.g. 260920001)
     * NNN is the daily sequential count for that date.
     */
    public static function generateKey(): string
    {
        $prefix = now()->format('ymd'); // e.g. 260920

        // Find the maximum existing numerical sequence for this prefix
        $keys = static::where('ticket_key', 'like', $prefix.'%')
            ->pluck('ticket_key');

        $maxSeq = 0;
        foreach ($keys as $k) {
            $suffix = substr($k, strlen($prefix));
            if (is_numeric($suffix)) {
                $maxSeq = max($maxSeq, (int) $suffix);
            }
        }

        $nextSeq = $maxSeq + 1;

        // Extra safety: guarantee key never collides with any existing key
        while (static::where('ticket_key', $prefix.str_pad($nextSeq, 3, '0', STR_PAD_LEFT))->exists()) {
            $nextSeq++;
        }

        return $prefix.str_pad($nextSeq, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Scope tickets according to user role and personal assignment rules.
     */
    public function scopeForUser($query, ?User $user = null)
    {
        $user = $user ?? auth()->user();
        if (! $user) {
            return $query;
        }

        // 1. Super Admin: full visibility
        if ($user->isSuperAdmin()) {
            return $query;
        }

        // 2. Reseller: only tickets created by this reseller
        if ($user->isReseller()) {
            return $query->where('created_by', $user->id);
        }

        // 3. Admin, NOC, Supervisor, Senior Supervisor, Call Center:
        // Personal Assignment Rule:
        // - Assigned tickets (assigned_to is NOT NULL) are visible ONLY to assignee, creator, and Super Admin.
        // - Unassigned tickets (assigned_to is NULL) are visible to team (NOC, Supervisor, Admin, Call Center),
        //   except Call Center cannot see reseller tickets.
        return $query->where(function ($q) use ($user) {
            $q->where('assigned_to', $user->id)
                ->orWhere('created_by', $user->id)
                ->orWhere(function ($unassignedQuery) use ($user) {
                    $unassignedQuery->whereNull('assigned_to');

                    if ($user->isCallCenter()) {
                        $unassignedQuery->whereHas('creator', function ($cq) {
                            $cq->where('role', '!=', 'reseller');
                        });
                    }
                });
        });
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function user()
    {
        return $this->creator();
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignedTo()
    {
        return $this->assignee();
    }

    public function ticketCategory()
    {
        return $this->belongsTo(TicketCategory::class, 'category_id');
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

    public function getFormattedDescriptionAttribute(): string
    {
        if (empty($this->description)) {
            return '';
        }

        $cleanText = trim(strip_tags($this->description));

        return nl2br(e($cleanText));
    }
}
