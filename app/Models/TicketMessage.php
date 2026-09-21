<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketMessage extends Model
{
    protected $fillable = ['ticket_id', 'sender_id', 'reply_to_id', 'message', 'image_path', 'is_private'];

    protected $casts = [
        'is_private' => 'boolean',
    ];

    public function canViewPrivate(?User $user = null): bool
    {
        if (!$this->is_private) {
            return true;
        }

        if (!$user) {
            return false;
        }

        // Resellers can NEVER view private comments
        if ($user->isReseller()) {
            return false;
        }

        // Admins and Super Admins can view all private comments
        if ($user->isAdmin()) {
            return true;
        }

        // Author/Sender of the private comment
        if ((int)$this->sender_id === (int)$user->id) {
            return true;
        }

        $ticket = $this->ticket;
        if ($ticket) {
            // Ticket Creator / Assigner
            if ((int)$ticket->created_by === (int)$user->id) {
                return true;
            }
            // Ticket Assignee
            if ((int)$ticket->assigned_to === (int)$user->id) {
                return true;
            }
        }

        // Mentioned users (@mention chip with data-id="X")
        if (!empty($this->message) && preg_match('/data-id="' . $user->id . '"/', $this->message)) {
            return true;
        }

        return false;
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function replyTo()
    {
        return $this->belongsTo(TicketMessage::class, 'reply_to_id');
    }

    public function replies()
    {
        return $this->hasMany(TicketMessage::class, 'reply_to_id');
    }

    public function reactions()
    {
        return $this->hasMany(TicketMessageReaction::class);
    }

    // Grouped as [emoji => ['count' => n, 'reacted' => bool, 'users' => 'Name, Name']]
    public function reactionSummary(?int $currentUserId = null): array
    {
        $summary = [];
        foreach ($this->reactions as $reaction) {
            $emoji = $reaction->emoji;
            if (!isset($summary[$emoji])) {
                $summary[$emoji] = ['count' => 0, 'reacted' => false, 'names' => []];
            }
            $summary[$emoji]['count']++;
            $summary[$emoji]['names'][] = $reaction->user->name ?? 'Unknown';
            if ($currentUserId && $reaction->user_id === $currentUserId) {
                $summary[$emoji]['reacted'] = true;
            }
        }
        return $summary;
    }

    public function getFormattedMessageAttribute(): string
    {
        if (empty($this->message)) {
            return '';
        }

        // Safe HTML tags produced by Quill rich text editor
        $allowedTags = '<p><br><b><strong><i><em><u><a><ul><ol><li><div><span><blockquote><code><pre><h1><h2><h3><h4><h5><h6>';

        // Split on already-stored safe mention-chip spans.
        $parts = preg_split('/(<span\s+class="mention-chip"[^>]*>.*?<\/span>)/s', $this->message, -1, PREG_SPLIT_DELIM_CAPTURE);

        static $usersSorted = null;
        if ($usersSorted === null) {
            $usersSorted = User::all(['id', 'name'])->sortByDesc(fn($u) => strlen($u->name));
        }

        $result = '';
        foreach ($parts as $i => $part) {
            if ($i % 2 === 1) {
                $result .= $part; // safe mention-chip span
            } else {
                $clean = strip_tags($part, $allowedTags);

                // If message has no HTML tags (plain text), apply nl2br
                if ($clean === strip_tags($clean)) {
                    $clean = nl2br($clean);
                }

                foreach ($usersSorted as $user) {
                    $safeName = preg_quote(e($user->name), '/');
                    $clean = preg_replace(
                        '/@' . $safeName . '\b/i',
                        '<span class="mention-chip" data-id="' . $user->id . '">@' . e($user->name) . '</span>',
                        $clean
                    );
                }
                $result .= $clean;
            }
        }

        return $result;
    }

    public function getImageUrlsAttribute(): array
    {
        if (!$this->image_path) {
            return [];
        }
        if (str_starts_with($this->image_path, '[')) {
            $paths = json_decode($this->image_path, true) ?: [];
            return array_map(fn($p) => asset('storage/' . $p), $paths);
        }
        return [asset('storage/' . $this->image_path)];
    }
}
