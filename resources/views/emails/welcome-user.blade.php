<x-mail::message>
# Welcome to VISION Technologies Limited!

Hello **{{ $user->name }}**,

Your account has been created successfully. Here are your account details:

<x-mail::panel>
**Email:** {{ $user->email }}
**Role:** {{ strtoupper($user->role) }}
</x-mail::panel>

To set your password, click the button below or use the **"Forgot Password"** link on the login page.

<x-mail::button :url="config('app.url') . '/forgot-password'">
Set Your Password
</x-mail::button>

Thanks,
**VISION Technologies Limited**
</x-mail::message>
