<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketNote extends Model
{
    protected $fillable = ['ticket_id', 'user_id', 'note', 'type', 'is_internal'];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFormattedNoteAttribute(): string
    {
        if (empty($this->note)) {
            return '';
        }

        $note = e($this->note);
        if (str_contains($note, 'mention-chip')) {
            return $note;
        }

        static $usersSorted = null;
        if ($usersSorted === null) {
            $usersSorted = User::all(['id', 'name'])->sortByDesc(fn($u) => strlen($u->name));
        }

        foreach ($usersSorted as $user) {
            $name = preg_quote($user->name, '/');
            $pattern = '/(<span class="mention-chip"[^>]*>.*?<\/span>)|@(' . $name . ')\b/i';
            $note = preg_replace_callback($pattern, function($m) use ($user) {
                if (!empty($m[1])) {
                    return $m[1];
                }
                return '<span class="mention-chip" data-id="' . $user->id . '">@' . e($user->name) . '</span>';
            }, $note);
        }

        return $note;
    }
}
