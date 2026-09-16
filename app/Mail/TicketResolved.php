<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketResolved extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public \App\Models\Ticket $ticket) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Ticket Resolved: #' . $this->ticket->id);
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.ticket-resolved');
    }

    public function attachments(): array
    {
        return [];
    }
}
