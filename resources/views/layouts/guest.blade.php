<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'ISP Ticket System') }}</title>
        @php 
            $siteFavicon = \App\Models\Setting::get('favicon_path'); 
            $favUrl = $siteFavicon ? asset('storage/' . $siteFavicon) : asset('images/favicon.png');
        @endphp
        <link rel="icon" type="image/png" href="{{ $favUrl }}">
        <link rel="shortcut icon" type="image/png" href="{{ $favUrl }}">
        <link rel="apple-touch-icon" href="{{ $favUrl }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-slate-50 dark:bg-slate-950">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-transparent px-4">
            <div class="w-full sm:max-w-md px-8 py-8 bg-white dark:bg-slate-900 shadow-xl overflow-hidden sm:rounded-2xl border border-slate-100 dark:border-slate-800">
                <div class="flex justify-center mb-6">
                    <a href="/" class="inline-block bg-transparent">
                        <x-application-logo class="w-48 h-16 object-contain bg-transparent" />
                    </a>
                </div>
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
