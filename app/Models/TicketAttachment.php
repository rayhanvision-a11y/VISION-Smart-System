<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketAttachment extends Model
{
    protected $fillable = [
        'ticket_id', 'ticket_message_id', 'uploaded_by',
        'path', 'original_name', 'mime_type', 'size',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function message()
    {
        return $this->belongsTo(TicketMessage::class, 'ticket_message_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function url(): string
    {
        return asset('storage/' . $this->path);
    }

    public function humanSize(): string
    {
        $bytes = $this->size;
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1048576, 1) . ' MB';
    }
}
