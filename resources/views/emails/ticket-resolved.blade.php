<x-mail::message>
# Your Ticket Has Been Resolved

Ticket **#{{ $ticket->id }} — {{ $ticket->title }}** has been resolved.

Please review the resolution and close the ticket or reopen it if the issue persists.

<x-mail::button :url="url('/tickets/' . $ticket->id)">
View Ticket
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
