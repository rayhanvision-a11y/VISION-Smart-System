<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'VISION Technologies Limited') }} - Service Portal</title>
        @php 
            $siteFavicon = \App\Models\Setting::get('favicon_path'); 
            $favUrl = $siteFavicon ? asset('storage/' . $siteFavicon) : asset('images/favicon.png');
            $appName = config('app.name', 'VISION Technologies Limited');
        @endphp
        <link rel="icon" type="image/png" href="{{ $favUrl }}">
        <link rel="shortcut icon" type="image/png" href="{{ $favUrl }}">
        <link rel="apple-touch-icon" href="{{ $favUrl }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Theme Init (Prevent Flash) -->
        <script>
            (function () {
                const stored = localStorage.getItem('theme');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                const effective = stored || (prefersDark ? 'dark' : 'dark'); // Default to dark for premium portal feel
                if (effective === 'dark') {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            })();
        </script>

        <style>
            [x-cloak] { display: none !important; }

            .login-bg {
                background-color: #060b17;
                background-image: 
                    radial-gradient(at 0% 0%, rgba(37, 99, 235, 0.25) 0px, transparent 50%),
                    radial-gradient(at 100% 0%, rgba(99, 102, 241, 0.22) 0px, transparent 50%),
                    radial-gradient(at 50% 100%, rgba(14, 165, 233, 0.2) 0px, transparent 50%),
                    radial-gradient(at 100% 100%, rgba(139, 92, 246, 0.18) 0px, transparent 50%);
            }

            .login-grid {
                background-image: 
                    linear-gradient(to right, rgba(255, 255, 255, 0.05) 1px, transparent 1px),
                    linear-gradient(to bottom, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
                background-size: 36px 36px;
                mask-image: radial-gradient(ellipse 70% 70% at 50% 45%, #000 60%, transparent 100%);
                -webkit-mask-image: radial-gradient(ellipse 70% 70% at 50% 45%, #000 60%, transparent 100%);
            }

            .orb-1 {
                position: absolute;
                top: -120px;
                left: -120px;
                width: 480px;
                height: 480px;
                border-radius: 9999px;
                background: radial-gradient(circle, rgba(37, 99, 235, 0.35) 0%, rgba(37, 99, 235, 0) 70%);
                filter: blur(50px);
                animation: pulse-slow 8s infinite alternate ease-in-out;
            }

            .orb-2 {
                position: absolute;
                top: 25%;
                right: -140px;
                width: 520px;
                height: 520px;
                border-radius: 9999px;
                background: radial-gradient(circle, rgba(99, 102, 241, 0.3) 0%, rgba(99, 102, 241, 0) 70%);
                filter: blur(60px);
                animation: pulse-slow 10s infinite alternate-reverse ease-in-out;
            }

            .orb-3 {
                position: absolute;
                bottom: -120px;
                left: 30%;
                width: 460px;
                height: 460px;
                border-radius: 9999px;
                background: radial-gradient(circle, rgba(14, 165, 233, 0.22) 0%, rgba(14, 165, 233, 0) 70%);
                filter: blur(50px);
            }

            @keyframes pulse-slow {
                0% { transform: scale(0.95) translate(0, 0); opacity: 0.7; }
                100% { transform: scale(1.1) translate(25px, -25px); opacity: 1; }
            }

            .login-card {
                background: rgba(15, 23, 42, 0.82) !important;
                backdrop-filter: blur(24px);
                -webkit-backdrop-filter: blur(24px);
                border: 1px solid rgba(255, 255, 255, 0.12) !important;
                box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.8), 0 0 35px rgba(37, 99, 235, 0.12);
            }

            .logo-container {
                background: rgba(255, 255, 255, 0.96);
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25), 0 0 15px rgba(255, 255, 255, 0.1);
            }

            .login-input {
                background-color: rgba(30, 41, 59, 0.75) !important;
                border: 1px solid rgba(255, 255, 255, 0.14) !important;
                color: #ffffff !important;
                transition: all 0.2s ease;
            }
            .login-input:focus {
                background-color: rgba(30, 41, 59, 0.95) !important;
                border-color: #3b82f6 !important;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.28) !important;
            }
            .login-input::placeholder {
                color: #64748b !important;
            }

            .login-btn {
                background: linear-gradient(135deg, #2563eb 0%, #4f46e5 50%, #3b82f6 100%) !important;
                box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.25) !important;
                transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            }
            .login-btn:hover {
                background: linear-gradient(135deg, #1d4ed8 0%, #4338ca 50%, #2563eb 100%) !important;
                box-shadow: 0 16px 32px -5px rgba(37, 99, 235, 0.7), inset 0 1px 0 rgba(255, 255, 255, 0.35) !important;
                transform: translateY(-1.5px);
            }
            .login-btn:active {
                transform: translateY(0);
            }
        </style>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased login-bg text-slate-100 min-h-screen flex flex-col justify-between selection:bg-blue-600 selection:text-white relative overflow-x-hidden">
        
        <!-- Ambient Glowing Background Elements -->
        <div class="fixed inset-0 pointer-events-none -z-10 overflow-hidden" aria-hidden="true">
            <div class="orb-1"></div>
            <div class="orb-2"></div>
            <div class="orb-3"></div>
            <div class="absolute inset-0 login-grid"></div>
        </div>

        <!-- Top Header Bar -->
        <header class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex items-center justify-between z-10">
            <!-- Left: Operational Status -->
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-900/80 border border-slate-700/60 text-xs text-slate-300 shadow-sm backdrop-blur-md">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                <span class="font-medium text-slate-200">System Operational</span>
                <span class="text-slate-600">|</span>
                <span class="text-slate-400 hidden sm:inline">ISP Core Gateway</span>
            </div>

            <!-- Right: Support Info -->
            <div class="flex items-center gap-4">
                <a href="tel:09613828828" class="inline-flex items-center gap-1.5 text-xs text-slate-400 hover:text-blue-400 transition-colors">
                    <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    <span class="hidden sm:inline">Helpline:</span>
                    <span class="font-medium text-slate-300">09613828828</span>
                </a>
            </div>
        </header>

        <!-- Main Card Section -->
        <main class="flex-grow flex items-center justify-center px-4 py-8 sm:py-12 z-10">
            <div class="w-full max-w-md">
                
                <!-- Glassmorphism Card Wrapper -->
                <div class="login-card relative rounded-3xl p-7 sm:p-9 transition-all overflow-hidden">
                    
                    <!-- Decorative Gradient Highlight on Top Border -->
                    <div class="absolute top-0 inset-x-0 h-[3px] bg-gradient-to-r from-blue-500 via-indigo-500 to-cyan-400"></div>

                    <!-- Company Logo with clean backdrop -->
                    <div class="flex flex-col items-center mb-6">
                        <a href="/" class="group inline-flex items-center justify-center p-3 rounded-2xl logo-container transition-all duration-300 hover:scale-[1.02]">
                            <x-application-logo class="h-12 w-auto max-w-[210px] object-contain" />
                        </a>
                    </div>

                    <!-- Card Body -->
                    {{ $slot }}

                </div>

                <!-- Trust & Security Assurance Footnote -->
                <div class="mt-6 flex items-center justify-center gap-4 text-xs text-slate-400">
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        256-Bit SSL Encrypted
                    </span>
                    <span class="text-slate-600">•</span>
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        High Availability Core
                    </span>
                </div>

            </div>
        </main>

        <!-- Footer -->
        <footer class="w-full max-w-7xl mx-auto px-4 sm:px-6 py-5 text-center text-xs text-slate-500 z-10">
            <p>&copy; {{ date('Y') }} {{ $appName }}. All rights reserved.</p>
        </footer>

    </body>
</html>
