<x-mail::message>
# Ticket Assigned to You

A ticket has been assigned to you.

**#{{ $ticket->id }} — {{ $ticket->title }}**

Priority: {{ ucfirst($ticket->priority) }} | Category: {{ ucfirst(str_replace('_',' ',$ticket->category)) }}

<x-mail::button :url="url('/tickets/' . $ticket->id)">
View Ticket
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
