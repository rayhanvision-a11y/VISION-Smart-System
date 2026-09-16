<x-mail::message>
# New Message on Ticket #{{ $ticket->id }}

A new message has been posted on ticket **{{ $ticket->title }}**.

<x-mail::button :url="url('/tickets/' . $ticket->id)">
View Conversation
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
