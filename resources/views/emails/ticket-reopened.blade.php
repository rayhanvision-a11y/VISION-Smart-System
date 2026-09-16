<x-mail::message>
# Ticket Reopened

Ticket **#{{ $ticket->id }} — {{ $ticket->title }}** has been reopened and needs your attention.

<x-mail::button :url="url('/tickets/' . $ticket->id)">
View Ticket
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
