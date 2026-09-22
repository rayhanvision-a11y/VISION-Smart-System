<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewChatMessage extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public Ticket $ticket) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'New message on Ticket #'.$this->ticket->id);
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.new-chat-message');
    }

    public function attachments(): array
    {
        return [];
    }
}
