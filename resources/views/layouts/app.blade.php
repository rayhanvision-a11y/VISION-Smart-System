<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'ISP Ticket System') }}</title>
        @php $siteFavicon = \App\Models\Setting::get('favicon_path'); @endphp
        @if($siteFavicon)
        <link rel="icon" href="{{ asset('storage/' . $siteFavicon) }}">
        @endif
        <script>
            // Applied before first paint to avoid a light-mode flash
            (function () {
                const userPref = @json(auth()->user()?->theme_preference ?? 'system');
                const stored = localStorage.getItem('theme');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                const effective = (userPref === 'light' || userPref === 'dark') ? userPref : (stored || (prefersDark ? 'dark' : 'light'));
                if (effective === 'dark') {
                    document.documentElement.classList.add('dark');
                }
            })();

            // Apply sidebar width before first paint to prevent layout jump
            (function () {
                const collapsed = localStorage.getItem('sidebarCollapsed') === 'true';
                const w = collapsed ? '64px' : '256px';
                document.documentElement.style.setProperty('--sidebar-w', w);
            })();

            // Fallback for broken/missing avatar images
            document.addEventListener('error', function (e) {
                if (e.target && e.target.tagName && e.target.tagName.toLowerCase() === 'img') {
                    e.target.onerror = null;
                    e.target.src = "{{ asset('images/default-avatar.svg') }}";
                }
            }, true);
        </script>
        <style>
            /* Pre-paint & dynamic sidebar layout rules */
            @media (min-width: 1024px) {
                #main-sidebar { width: var(--sidebar-w, 256px); transition: width 0.2s ease; }
                #main-content { margin-left: var(--sidebar-w, 256px); transition: margin-left 0.2s ease; }
                #main-sidebar.sidebar-collapsed { width: 64px !important; }
                #main-sidebar.sidebar-collapsed ~ #main-content { margin-left: 64px !important; }
                #main-sidebar:not(.sidebar-collapsed) { width: 256px !important; }
                #main-sidebar:not(.sidebar-collapsed) ~ #main-content { margin-left: 256px !important; }
            }
        </style>
        @php
            $themePrimary   = \App\Models\Setting::get('theme_primary_color',   '#4f46e5');
            $themeSecondary = \App\Models\Setting::get('theme_secondary_color',  '#10b981');
        @endphp
        <style>
            :root { --cp: {{ $themePrimary }}; --cs: {{ $themeSecondary }}; }

            /* ── Primary color overrides (indigo → --cp) ── */
            .bg-indigo-600, .bg-indigo-500                { background-color: var(--cp) !important; }
            .bg-indigo-700                                { background-color: color-mix(in srgb, var(--cp) 82%, #000) !important; }
            .hover\:bg-indigo-700:hover                   { background-color: color-mix(in srgb, var(--cp) 82%, #000) !important; }
            .hover\:bg-indigo-600:hover                   { background-color: color-mix(in srgb, var(--cp) 90%, #000) !important; }
            .bg-indigo-50                                 { background-color: color-mix(in srgb, var(--cp) 10%, #fff) !important; }
            .bg-indigo-100                                { background-color: color-mix(in srgb, var(--cp) 18%, #fff) !important; }
            .text-indigo-600, .text-indigo-700            { color: var(--cp) !important; }
            .text-indigo-500                              { color: color-mix(in srgb, var(--cp) 80%, #fff) !important; }
            .hover\:text-indigo-600:hover                 { color: var(--cp) !important; }
            .border-indigo-600, .border-indigo-500        { border-color: var(--cp) !important; }
            .ring-indigo-500                              { --tw-ring-color: var(--cp) !important; }
            .focus\:ring-indigo-500:focus                 { --tw-ring-color: var(--cp) !important; }
            /* Dark mode */
            html.dark .bg-indigo-50  { background-color: color-mix(in srgb, var(--cp) 14%, transparent) !important; }
            html.dark .bg-indigo-100 { background-color: color-mix(in srgb, var(--cp) 20%, transparent) !important; }
            html.dark .text-indigo-500, html.dark .text-indigo-600, html.dark .text-indigo-700 { color: color-mix(in srgb, var(--cp) 70%, #fff) !important; }

            /* ── Secondary: only solid buttons (50/100/950 left alone for badges) ── */
            .bg-emerald-500, .bg-emerald-600              { background-color: var(--cs) !important; }
            .hover\:bg-emerald-600:hover                  { background-color: color-mix(in srgb, var(--cs) 85%, #000) !important; }
        </style>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
        <style>
            [x-cloak] { display: none !important; }

            /* Quill editor Jira-style */
            #quill-editor {
                background-color: #ffffff;
                border-color: #e2e8f0;
            }
            #quill-editor .ql-toolbar {
                border: none;
                border-bottom: 1px solid #e2e8f0;
                background: #f8fafc;
                border-radius: 12px 12px 0 0;
                padding: 6px 10px;
            }
            #quill-editor .ql-container {
                border: none;
                font-family: inherit;
            }
            #quill-editor .ql-editor {
                font-size: 14px;
                min-height: 80px;
                padding: 12px 16px;
                color: #1e293b !important;
                background-color: #ffffff !important;
            }
            #quill-editor .ql-editor.ql-blank::before {
                color: #94a3b8 !important;
                font-style: normal;
            }
            #quill-editor .ql-toolbar .ql-stroke { stroke: #64748b; }
            #quill-editor .ql-toolbar .ql-fill   { fill: #64748b; }
            #quill-editor .ql-toolbar button:hover .ql-stroke,
            #quill-editor .ql-toolbar button.ql-active .ql-stroke { stroke: #4f46e5; }
            #quill-editor .ql-toolbar button:hover .ql-fill,
            #quill-editor .ql-toolbar button.ql-active .ql-fill   { fill: #4f46e5; }

            /* Dark mode support for Quill editor */
            .dark #quill-editor {
                background-color: #1e293b !important;
                border-color: #334155 !important;
            }
            .dark #quill-editor .ql-toolbar {
                border-bottom-color: #334155 !important;
                background-color: #0f172a !important;
            }
            .dark #quill-editor .ql-editor {
                color: #f8fafc !important;
                background-color: #1e293b !important;
            }
            .dark #quill-editor .ql-editor.ql-blank::before {
                color: #64748b !important;
            }
            .dark #quill-editor .ql-toolbar .ql-stroke { stroke: #94a3b8 !important; }
            .dark #quill-editor .ql-toolbar .ql-fill   { fill: #94a3b8 !important; }
            .dark #quill-editor .ql-toolbar button:hover .ql-stroke,
            .dark #quill-editor .ql-toolbar button.ql-active .ql-stroke { stroke: #818cf8 !important; }
            .dark #quill-editor .ql-toolbar button:hover .ql-fill,
            .dark #quill-editor .ql-toolbar button.ql-active .ql-fill   { fill: #818cf8 !important; }
            .dark #quill-editor .ql-picker { color: #94a3b8 !important; }
            .dark #quill-editor .ql-picker-options { background-color: #1e293b !important; border-color: #334155 !important; color: #f8fafc !important; }

            /* @mention dropdown */
            #mention-dropdown {
                position: absolute;
                background: #fff;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                box-shadow: 0 8px 24px rgba(0,0,0,.14);
                width: 260px;
                z-index: 9999;
                overflow: hidden;
                display: none;
            }
            #mention-dropdown.show { display: block; }
            .mention-item {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 9px 14px;
                cursor: pointer;
                font-size: 13px;
                transition: background .12s;
            }
            .mention-item:hover, .mention-item.active { background: #f1f5f9; }
            .mention-item img { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; flex-shrink: 0; }
            .mention-item .mi-name { font-weight: 600; color: #1e293b; display: block; }
            .mention-item .mi-role { font-size: 11px; color: #94a3b8; display: block; }

            /* Status pulse animations */
            /* Status pulse animations for Pending & In Progress */
            @keyframes status-pulse-amber {
                0% {
                    transform: scale(0.95);
                    box-shadow: 0 0 0 0 rgba(234, 88, 12, 0.7);
                }
                70% {
                    transform: scale(1);
                    box-shadow: 0 0 0 6px rgba(234, 88, 12, 0);
                }
                100% {
                    transform: scale(0.95);
                    box-shadow: 0 0 0 0 rgba(234, 88, 12, 0);
                }
            }

            @keyframes status-pulse-blue {
                0% {
                    transform: scale(0.95);
                    box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.7);
                }
                70% {
                    transform: scale(1);
                    box-shadow: 0 0 0 6px rgba(37, 99, 235, 0);
                }
                100% {
                    transform: scale(0.95);
                    box-shadow: 0 0 0 0 rgba(37, 99, 235, 0);
                }
            }

            .status-dot-pending {
                display: inline-block !important;
                width: 8px !important;
                height: 8px !important;
                border-radius: 9999px !important;
                background-color: #ea580c !important;
                animation: status-pulse-amber 1.5s infinite !important;
                flex-shrink: 0 !important;
            }

            .status-dot-inprogress {
                display: inline-block !important;
                width: 8px !important;
                height: 8px !important;
                border-radius: 9999px !important;
                background-color: #2563eb !important;
                animation: status-pulse-blue 1.5s infinite !important;
                flex-shrink: 0 !important;
            }

            .status-select {
                background-image: none !important;
                -webkit-appearance: none !important;
                -moz-appearance: none !important;
                appearance: none !important;
            }

            /* mention chip — exact match to Image 1 */
            .ql-editor .mention-chip,
            .mention-chip {
                background: #f1f3f5 !important;
                color: #495057 !important;
                border-radius: 9999px !important;
                padding: 2px 10px !important;
                font-weight: 500 !important;
                font-size: 13px !important;
                display: inline-flex !important;
                align-items: center !important;
                text-decoration: none !important;
                line-height: 1.4 !important;
                border: none !important;
            }
            .ql-editor .mention-chip::before,
            .mention-chip::before {
                content: none !important;
            }
            /* Inside indigo message bubble (my message) */
            .bg-indigo-600 .mention-chip {
                background: #ffffff !important;
                color: #374151 !important;
                border-radius: 9999px !important;
            }

            /* Emoji Picker Panel (Quill toolbar popover) */
            #emoji-picker-panel {
                position: absolute !important;
                z-index: 9999 !important;
                top: 34px !important;
                right: 0 !important;
                left: auto !important;
                width: 290px !important;
                background: #ffffff !important;
                border: 1px solid #e2e8f0 !important;
                border-radius: 12px !important;
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.12), 0 8px 10px -6px rgba(0, 0, 0, 0.08) !important;
                overflow: hidden !important;
            }

            /* Explicit Emoji Grid & Popover styling to prevent layout collapse */
            #emoji-grid,
            #fp-grid {
                display: grid !important;
                grid-template-columns: repeat(7, 1fr) !important;
                gap: 4px !important;
                padding: 8px !important;
                max-height: 200px !important;
                overflow-y: auto !important;
                width: 100% !important;
                box-sizing: border-box !important;
            }

            /* Emoji buttons inside grid */
            .emoji-insert-btn,
            #fp-grid .msg-react-btn {
                width: 34px !important;
                height: 34px !important;
                font-size: 18px !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                border-radius: 8px !important;
                cursor: pointer !important;
                border: none !important;
                background: transparent !important;
                transition: transform 0.12s ease, background-color 0.12s ease !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .emoji-insert-btn:hover,
            #fp-grid .msg-react-btn:hover {
                background-color: #f1f5f9 !important;
                transform: scale(1.2) !important;
            }

            /* Category tab buttons */
            .emoji-tab-btn,
            .fp-cat-tab {
                font-size: 12px !important;
                font-weight: 500 !important;
                padding: 4px 8px !important;
                border-radius: 6px !important;
                border: none !important;
                background: transparent !important;
                color: #64748b !important;
                cursor: pointer !important;
                white-space: nowrap !important;
                transition: all 0.12s ease !important;
            }
            .emoji-tab-btn.active,
            .fp-cat-tab.active {
                background-color: #e0e7ff !important;
                color: #4338ca !important;
            }

            /* Collapsed sidebar: all nav items same size & centered */
            .sidebar-collapsed nav a,
            .sidebar-collapsed nav > div > div > button:not([type="button"]),
            .sidebar-collapsed nav .mb-1 > button {
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                width: 40px !important;
                height: 40px !important;
                margin: 0 auto 4px auto !important;
                padding: 0 !important;
                gap: 0 !important;
                border-radius: 10px !important;
            }
            .sidebar-collapsed nav a svg,
            .sidebar-collapsed nav .mb-1 > button svg:first-child {
                width: 20px !important;
                height: 20px !important;
                flex-shrink: 0 !important;
            }

            /* Hide scrollbar for sidebar navigation but allow mouse wheel scrolling */
            #main-sidebar nav {
                -ms-overflow-style: none !important;
                scrollbar-width: none !important;
            }
            #main-sidebar nav::-webkit-scrollbar {
                display: none !important;
                width: 0 !important;
                height: 0 !important;
            }
        </style>
    </head>
    <body class="font-sans antialiased bg-slate-50 dark:bg-slate-950">
        <div x-data="{ sidebarOpen: false, sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true' }" class="flex min-h-screen">

            {{-- Mobile Overlay --}}
            <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
                 class="fixed inset-0 bg-black/40 z-20 lg:hidden"></div>            {{-- Sidebar --}}
            <aside id="main-sidebar"
                   :class="(sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0') + (sidebarCollapsed ? ' sidebar-collapsed' : '')"
                   :style="'width:' + (sidebarCollapsed ? '64px' : '256px')"
                   class="bg-white dark:bg-slate-950 text-slate-800 dark:text-white border border-slate-200 dark:border-slate-800 flex flex-col flex-shrink-0 fixed top-0 left-0 h-full z-30 lg:translate-x-0 overflow-visible transition-colors duration-200">

                {{-- Logo & Prominent Collapse Toggle Icon --}}
                <div class="h-16 border-b border-r border-slate-200 dark:border-slate-800/80 bg-white dark:bg-slate-950 flex flex-row items-center justify-between flex-shrink-0 relative overflow-visible transition-all duration-300"
                     :class="sidebarCollapsed ? 'justify-center px-1' : 'px-3 gap-2'">
                    @php 
                        $siteLogo = \App\Models\Setting::get('logo_path');
                        $siteFavicon = \App\Models\Setting::get('favicon_path');
                    @endphp

                    {{-- Floating Collapse Toggle Button on Border --}}
                    <button type="button"
                            @click="sidebarCollapsed = !sidebarCollapsed; localStorage.setItem('sidebarCollapsed', sidebarCollapsed); document.documentElement.style.setProperty('--sidebar-w', sidebarCollapsed ? '64px' : '256px')"
                            class="flex items-center justify-center w-8 h-8 rounded-full bg-white dark:bg-slate-800 hover:bg-indigo-50 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 hover:text-indigo-600 border border-slate-200 dark:border-slate-700 shadow-md transition-all cursor-pointer"
                            style="position: absolute; right: -21px; top: 50%; transform: translateY(-50%); z-index: 50;"
                            :title="sidebarCollapsed ? 'Expand Sidebar' : 'Collapse Sidebar'">
                        <svg x-show="!sidebarCollapsed" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                        </svg>
                        <svg x-show="sidebarCollapsed" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>

                    {{-- Collapsed Mode: Favicon AT THE VERY TOP (Fav Show Korbe) --}}
                    <div x-show="sidebarCollapsed" x-cloak class="flex items-center justify-center w-full">
                        {{-- Favicon Logo Icon --}}
                        <a href="{{ route('dashboard') }}" title="VISION Technologies Limited" class="flex items-center justify-center group">
                            <img src="{{ $siteFavicon ? asset('storage/' . $siteFavicon) : ($siteLogo ? asset('storage/' . $siteLogo) : 'https://visiontech.com.bd/wp-content/uploads/2017/11/vision-logo.png') }}"
                                 alt="Favicon"
                                 class="w-9 h-9 object-contain group-hover:scale-110 transition-transform">
                        </a>
                    </div>

                    {{-- Expanded Mode: Full VISION Logo aligned with h-16 header --}}
                    <a href="{{ route('dashboard') }}" x-show="!sidebarCollapsed" class="flex items-center overflow-hidden min-w-0 pl-1">
                        <img src="{{ $siteLogo ? asset('storage/' . $siteLogo) : 'https://visiontech.com.bd/wp-content/uploads/2017/11/vision-logo.png' }}"
                             alt="VISION Technologies Limited"
                             class="h-10 sm:h-11 max-w-[195px] object-contain transition-all duration-300">
                    </a>
                </div>

                {{-- Navigation --}}
                <nav :class="sidebarCollapsed ? 'px-1' : 'px-3'" class="flex-1 py-4 overflow-y-auto overflow-x-hidden">
                    <div class="mb-5">
                        <p x-show="!sidebarCollapsed" x-cloak class="px-3 mb-2 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Main Menu') }}</p>

                        <a href="{{ route('dashboard') }}"
                           :title="sidebarCollapsed ? 'Dashboard' : ''"
                           :class="sidebarCollapsed ? 'justify-center px-0 gap-0' : 'px-3 gap-3'"
                           class="flex items-center py-2.5 mb-1 rounded-lg text-sm font-medium transition-colors
                                  {{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            <span x-show="!sidebarCollapsed" x-cloak class="truncate">{{ __('Dashboard') }}</span>
                        </a>

                        @if(auth()->user()?->isAdmin() || auth()->user()?->isNoc())
                        <a href="{{ route('whatsapp.index') }}"
                           :title="sidebarCollapsed ? 'WhatsApp Message' : ''"
                           :class="sidebarCollapsed ? 'justify-center px-0 gap-0' : 'px-3 gap-3'"
                           class="flex items-center py-2.5 mb-1 rounded-lg text-sm font-medium transition-colors
                                  {{ request()->routeIs('whatsapp.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <svg class="w-5 h-5 flex-shrink-0 text-emerald-500 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            <span x-show="!sidebarCollapsed" x-cloak class="truncate">{{ __('WhatsApp Message') }}</span>
                        </a>
                        @endif

                        @if(auth()->user()?->isAdmin())
                        <a href="{{ route('tickets.index') }}"
                           :title="sidebarCollapsed ? 'All Tickets' : ''"
                           :class="sidebarCollapsed ? 'justify-center px-0 gap-0' : 'px-3 gap-3'"
                           class="flex items-center py-2.5 mb-1 rounded-lg text-sm font-medium transition-colors
                                  {{ request()->routeIs('tickets.*') && !request()->routeIs('tickets.create') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 022 2h2a2 2 0 022-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                            </svg>
                            <span x-show="!sidebarCollapsed" x-cloak class="truncate">{{ __('All Tickets') }}</span>
                        </a>
                        @endif

                        @if(auth()->user()?->isNoc())
                        <a href="{{ route('tickets.index', ['assigned' => 'me']) }}"
                           :title="sidebarCollapsed ? 'My Assigned' : ''"
                           :class="sidebarCollapsed ? 'justify-center px-0 gap-0' : 'px-3 gap-3'"
                           class="flex items-center py-2.5 mb-1 rounded-lg text-sm font-medium transition-colors
                                  {{ request()->routeIs('tickets.*') && request('assigned') === 'me' && !request()->routeIs('tickets.create') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span x-show="!sidebarCollapsed" x-cloak class="truncate">{{ __('My Assigned') }}</span>
                        </a>
                        <a href="{{ route('tickets.index') }}"
                           :title="sidebarCollapsed ? 'All Tickets' : ''"
                           :class="sidebarCollapsed ? 'justify-center px-0 gap-0' : 'px-3 gap-3'"
                           class="flex items-center py-2.5 mb-1 rounded-lg text-sm font-medium transition-colors
                                  {{ request()->routeIs('tickets.*') && !request('assigned') && !request()->routeIs('tickets.create') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <span x-show="!sidebarCollapsed" x-cloak class="truncate">{{ __('All Tickets') }}</span>
                        </a>
                        @endif

                        @if(auth()->user()?->isReseller())
                        <a href="{{ route('tickets.index') }}"
                           :title="sidebarCollapsed ? 'My Tickets' : ''"
                           :class="sidebarCollapsed ? 'justify-center px-0 gap-0' : 'px-3 gap-3'"
                           class="flex items-center py-2.5 mb-1 rounded-lg text-sm font-medium transition-colors
                                  {{ request()->routeIs('tickets.*') && !request()->routeIs('tickets.create') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 022 2h2a2 2 0 022-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <span x-show="!sidebarCollapsed" x-cloak class="truncate">{{ __('My Tickets') }}</span>
                        </a>
                        @endif

                        <a href="{{ route('tickets.create') }}"
                           :title="sidebarCollapsed ? 'Create Ticket' : ''"
                           :class="sidebarCollapsed ? 'justify-center px-0 gap-0' : 'px-3 gap-3'"
                           class="flex items-center py-2.5 mb-1 rounded-lg text-sm font-medium transition-colors
                                  {{ request()->routeIs('tickets.create') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span x-show="!sidebarCollapsed" x-cloak class="truncate">{{ __('Create Ticket') }}</span>
                        </a>

                        <a href="{{ route('board.index') }}"
                           :title="sidebarCollapsed ? 'Board' : ''"
                           :class="sidebarCollapsed ? 'justify-center px-0 gap-0' : 'px-3 gap-3'"
                           class="flex items-center py-2.5 mb-1 rounded-lg text-sm font-medium transition-colors
                                  {{ request()->routeIs('board.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                            </svg>
                            <span x-show="!sidebarCollapsed" x-cloak class="truncate">{{ __('Board') }}</span>
                        </a>

                        <a href="{{ route('knowledge-base.index') }}"
                           :title="sidebarCollapsed ? 'Knowledge Base' : ''"
                           :class="sidebarCollapsed ? 'justify-center px-0 gap-0' : 'px-3 gap-3'"
                           class="relative flex items-center py-2.5 mb-1 rounded-lg text-sm font-medium transition-colors
                                  {{ request()->routeIs('knowledge-base.*') || request()->routeIs('kb.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <div class="relative flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                                @if(($unreadKbCount ?? 0) > 0)
                                    <span class="absolute -top-1 -right-1 flex h-2.5 w-2.5" x-show="sidebarCollapsed">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-rose-500"></span>
                                    </span>
                                @endif
                            </div>
                            <span x-show="!sidebarCollapsed" x-cloak class="truncate flex-1">Knowledge Base</span>
                            @if(($unreadKbCount ?? 0) > 0)
                                <span x-show="!sidebarCollapsed" x-cloak class="ml-auto inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-gradient-to-r from-rose-500 to-red-600 text-white shadow-sm animate-bounce">
                                    <span class="mr-1 h-1.5 w-1.5 rounded-full bg-white animate-ping"></span>
                                    NEW
                                </span>
                            @endif
                        </a>

                        @if(auth()->user()?->isAdmin() || auth()->user()?->isNoc())
                        <a href="{{ route('canned-responses.index') }}"
                           :title="sidebarCollapsed ? 'Canned Responses' : ''"
                           :class="sidebarCollapsed ? 'justify-center px-0 gap-0' : 'px-3 gap-3'"
                           class="flex items-center py-2.5 mb-1 rounded-lg text-sm font-medium transition-colors
                                  {{ request()->routeIs('canned-responses.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 01-2-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-6l-4 4v-4z"/>
                            </svg>
                            <span x-show="!sidebarCollapsed" x-cloak class="truncate">{{ __('Canned Responses') }}</span>
                        </a>
                        @endif

                        @if(auth()->user()?->isAdmin())
                        <a href="{{ route('reports.index') }}"
                           :title="sidebarCollapsed ? 'Reports' : ''"
                           :class="sidebarCollapsed ? 'justify-center px-0 gap-0' : 'px-3 gap-3'"
                           class="flex items-center py-2.5 mb-1 rounded-lg text-sm font-medium transition-colors
                                  {{ request()->routeIs('reports.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span x-show="!sidebarCollapsed" x-cloak class="truncate">{{ __('Reports') }}</span>
                        </a>
                        @endif

                        @if(auth()->user()?->isAdmin())
                        <a href="{{ route('users.index') }}"
                           :title="sidebarCollapsed ? 'Users' : ''"
                           :class="sidebarCollapsed ? 'justify-center px-0 gap-0' : 'px-3 gap-3'"
                           class="flex items-center py-2.5 mb-1 rounded-lg text-sm font-medium transition-colors
                                  {{ request()->routeIs('users.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            <span x-show="!sidebarCollapsed" x-cloak class="truncate">{{ __('Users') }}</span>
                        </a>
                        <a href="{{ route('labels.index') }}"
                           :title="sidebarCollapsed ? 'Labels' : ''"
                           :class="sidebarCollapsed ? 'justify-center px-0 gap-0' : 'px-3 gap-3'"
                           class="flex items-center py-2.5 mb-1 rounded-lg text-sm font-medium transition-colors
                                  {{ request()->routeIs('labels.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                            <span x-show="!sidebarCollapsed" x-cloak class="truncate">{{ __('Labels') }}</span>
                        </a>
                        <a href="{{ route('sla-policies.index') }}"
                           :title="sidebarCollapsed ? 'SLA Policies' : ''"
                           :class="sidebarCollapsed ? 'justify-center px-0 gap-0' : 'px-3 gap-3'"
                           class="flex items-center py-2.5 mb-1 rounded-lg text-sm font-medium transition-colors
                                  {{ request()->routeIs('sla-policies.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span x-show="!sidebarCollapsed" x-cloak class="truncate">{{ __('SLA Policies') }}</span>
                        </a>
                        @endif

                        @if(auth()->user()?->isSuperAdminOnly())
                        <a href="{{ route('settings.edit') }}"
                           :title="sidebarCollapsed ? 'Site Settings' : ''"
                           :class="sidebarCollapsed ? 'justify-center px-0 gap-0' : 'px-3 gap-3'"
                           class="flex items-center py-2.5 mb-1 rounded-lg text-sm font-medium transition-colors
                                  {{ request()->routeIs('settings.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span x-show="!sidebarCollapsed" x-cloak class="truncate">{{ __('Site Settings') }}</span>
                        </a>
                        @endif

                        @if(auth()->user()?->isAdmin())
                        <a href="{{ route('activity-logs.index') }}"
                           :title="sidebarCollapsed ? 'Activity Log' : ''"
                           :class="sidebarCollapsed ? 'justify-center px-0 gap-0' : 'px-3 gap-3'"
                           class="flex items-center py-2.5 mb-1 rounded-lg text-sm font-medium transition-colors
                                  {{ request()->routeIs('activity-logs.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                            <span x-show="!sidebarCollapsed" x-cloak class="truncate">{{ __('Activity Log') }}</span>
                        </a>
                        @endif

                        @if(auth()->user()?->isSuperAdminOnly())
                        <a href="{{ route('custom-menu-links.index') }}"
                           :title="sidebarCollapsed ? 'Manage Important URLs' : ''"
                           :class="sidebarCollapsed ? 'justify-center px-0 gap-0' : 'px-3 gap-3'"
                           class="flex items-center py-2.5 mb-1 rounded-lg text-sm font-medium transition-colors
                                  {{ request()->routeIs('custom-menu-links.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                            </svg>
                            <span x-show="!sidebarCollapsed" x-cloak class="truncate">{{ __('Manage Important URLs') }}</span>
                        </a>
                        @endif

                        @if(isset($customMenuLinks) && $customMenuLinks->count() > 0)
                        {{-- Expanded Mode Accordion --}}
                        <div x-show="!sidebarCollapsed" x-cloak class="mb-1" x-data="{ open: false }">
                            <button @click="open = !open"
                                    class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-colors text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800">
                                <span class="flex items-center gap-3">
                                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                    </svg>
                                    <span class="truncate">{{ __('Important URL') }}</span>
                                </span>
                                <svg class="w-3.5 h-3.5 flex-shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="open" x-collapse x-cloak class="mt-0.5 space-y-0.5">
                                @foreach($customMenuLinks as $cLink)
                                <a href="{{ $cLink->url }}" target="{{ $cLink->open_in_new_tab ? '_blank' : '_self' }}"
                                   class="flex items-center justify-between gap-2 pl-5 pr-3 py-2 rounded-lg text-xs font-medium transition-colors text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <div class="w-5 h-5 flex items-center justify-center flex-shrink-0">
                                            {!! $cLink->svgIcon('w-4 h-4 text-slate-500 dark:text-slate-400') !!}
                                        </div>
                                        <span class="truncate">{{ $cLink->name ?? $cLink->title }}</span>
                                    </div>
                                </a>
                                @endforeach
                            </div>
                        </div>

                        {{-- Collapsed Mode: Render Sub-menu links directly as Main Icons --}}
                        <div x-show="sidebarCollapsed" x-cloak>
                            @foreach($customMenuLinks as $cLink)
                            <a href="{{ $cLink->url }}" target="{{ $cLink->open_in_new_tab ? '_blank' : '_self' }}"
                               title="{{ $cLink->name ?? $cLink->title }}"
                               class="flex items-center justify-center py-2.5 mb-1 rounded-lg text-sm font-medium transition-colors text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800">
                                <div class="w-5 h-5 flex items-center justify-center flex-shrink-0">
                                    {!! $cLink->svgIcon('w-5 h-5 text-slate-500 dark:text-slate-400') !!}
                                </div>
                            </a>
                            @endforeach
                        </div>
                        @endif

                        @if(auth()->user()?->isAdmin())
                        @php
                            $sidebarResellers = \App\Models\User::where('role', 'reseller')
                                ->withCount(['tickets as pending_count' => fn($q) => $q->whereNotIn('status', ['resolved','closed'])])
                                ->addSelect([
                                    'last_ticket_at' => \App\Models\Ticket::select('created_at')
                                        ->whereColumn('created_by', 'users.id')
                                        ->latest()
                                        ->limit(1)
                                ])
                                ->orderByDesc('last_ticket_at')
                                ->orderBy('name')
                                ->get();
                            $totalResellerPending = $sidebarResellers->sum('pending_count');
                            $resellerOpen = request('created_by') && $sidebarResellers->pluck('id')->contains(request('created_by'));
                        @endphp

                        {{-- Expanded Mode Accordion --}}
                        <div x-show="!sidebarCollapsed" x-cloak class="mb-1" x-data="{ open: {{ $resellerOpen ? 'true' : 'false' }} }">
                            <button @click="open = !open"
                                    class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                                           {{ $resellerOpen ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                                <div class="relative flex items-center gap-3">
                                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span class="truncate">{{ __('Reseller') }}</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    @if($totalResellerPending > 0)
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-red-500 text-white text-xs font-bold">{{ $totalResellerPending > 9 ? '9+' : $totalResellerPending }}</span>
                                    @endif
                                    <svg class="w-3.5 h-3.5 flex-shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </button>

                            <div x-show="open" x-collapse x-cloak class="mt-0.5 space-y-0.5">
                                @forelse($sidebarResellers as $reseller)
                                <a href="{{ route('tickets.index', ['created_by' => $reseller->id]) }}"
                                   class="flex items-center justify-between gap-2 pl-5 pr-3 py-2 rounded-lg text-xs font-medium transition-colors
                                          {{ request('created_by') == $reseller->id ? 'bg-indigo-50 dark:bg-slate-700 text-indigo-700 dark:text-white font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <img src="{{ $reseller->avatarUrl() }}" class="w-5 h-5 rounded-full object-cover flex-shrink-0">
                                        <span class="truncate">{{ $reseller->name }}</span>
                                    </div>
                                    @if($reseller->pending_count > 0)
                                    <span class="inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full bg-red-500 text-white text-xs font-bold flex-shrink-0">{{ $reseller->pending_count }}</span>
                                    @endif
                                </a>
                                @empty
                                <p class="pl-5 pr-3 py-2 text-xs text-slate-500 dark:text-slate-400">{{ __('No resellers yet.') }}</p>
                                @endforelse
                            </div>
                        </div>

                        {{-- Collapsed Mode: Render Resellers directly as Main Icons --}}
                        <div x-show="sidebarCollapsed" x-cloak>
                            @foreach($sidebarResellers as $reseller)
                            <a href="{{ route('tickets.index', ['created_by' => $reseller->id]) }}"
                               title="{{ $reseller->name }} @if($reseller->pending_count > 0)({{ $reseller->pending_count }})@endif"
                               class="relative flex items-center justify-center py-2.5 mb-1 rounded-lg text-sm font-medium transition-colors
                                      {{ request('created_by') == $reseller->id ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                                <div class="relative flex-shrink-0">
                                    <img src="{{ $reseller->avatarUrl() }}" class="w-5 h-5 rounded-full object-cover">
                                    @if($reseller->pending_count > 0)
                                    <span class="absolute -top-1.5 -right-1.5 min-w-[14px] h-[14px] px-0.5 rounded-full bg-red-500 text-white text-[9px] font-bold flex items-center justify-center">{{ $reseller->pending_count > 9 ? '9+' : $reseller->pending_count }}</span>
                                    @endif
                                </div>
                            </a>
                            @endforeach
                        </div>
                        @endif

                    </div>
                </nav>

                {{-- User Info --}}
                <div :class="sidebarCollapsed ? 'px-1' : 'px-4'" class="py-4 border-t border-slate-200 dark:border-slate-800/80 flex-shrink-0 bg-slate-50 dark:bg-slate-900/40">
                    <a href="{{ route('profile.edit') }}" :class="sidebarCollapsed ? 'justify-center' : 'gap-3'" class="flex items-center mb-3 hover:opacity-80 transition-opacity" :title="sidebarCollapsed ? '{{ auth()->user()->name }}' : ''">
                        <img src="{{ auth()->user()->avatarUrl() }}" alt="{{ auth()->user()->name }}"
                             class="w-9 h-9 rounded-full object-cover flex-shrink-0 border border-slate-200 dark:border-slate-700 shadow-sm">
                        <div class="min-w-0" x-show="!sidebarCollapsed" x-cloak>
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 truncate">{{ auth()->user()->name }}</p>
                            <span class="inline-block mt-0.5 px-2 py-0.5 rounded-full text-xs font-medium border
                                @if(auth()->user()?->isSuperAdmin()) bg-purple-100 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border-purple-200 dark:border-purple-800/50
                                @elseif(auth()->user()?->isAdmin()) bg-red-100 dark:bg-red-950/60 text-red-700 dark:text-red-300 border-red-200 dark:border-red-800/50
                                @elseif(auth()->user()?->isNoc()) bg-blue-100 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800/50
                                @else bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/50 @endif">
                                {{ strtoupper(str_replace('_', ' ', auth()->user()->role)) }}
                            </span>
                        </div>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold text-rose-700 dark:text-rose-300 bg-rose-50 dark:bg-rose-950/50 hover:bg-rose-100 dark:hover:bg-rose-900/60 border border-rose-200 dark:border-rose-800/60 transition-colors shadow-sm"
                                :title="sidebarCollapsed ? 'Sign Out' : ''">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <span x-show="!sidebarCollapsed" x-cloak>{{ __('Sign Out') }}</span>
                        </button>
                    </form>
                </div>
            </aside>

            {{-- Main Content Wrapper --}}
            <div id="main-content"
                 :style="window.innerWidth >= 1024 ? 'margin-left:' + (sidebarCollapsed ? '64px' : '256px') : ''"
                 class="flex-1 flex flex-col min-h-screen">

                {{-- Top Bar --}}
                <header class="h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-700 px-4 lg:px-6 flex items-center justify-between sticky top-0 z-10 gap-3">
                    <div class="flex items-center gap-3 flex-shrink-0">
                        {{-- Mobile-only menu toggle --}}
                        <button type="button"
                                @click="sidebarOpen = !sidebarOpen"
                                class="lg:hidden flex items-center justify-center p-2.5 rounded-lg bg-indigo-50 dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-600 dark:hover:text-white border border-indigo-200 dark:border-slate-700 shadow-sm transition-all flex-shrink-0 cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 18h14"/>
                            </svg>
                        </button>
                        @isset($pageTitle)
                            <h1 class="text-lg font-semibold text-slate-800 dark:text-slate-100 flex-shrink-0">{{ $pageTitle }}</h1>
                        @endisset
                    </div>

                    {{-- Dynamic Header Notice Marquee Ticker (Supports Multiple Notices & Continuous Position across pages) --}}
                    @php
                        $hNoticeActive = \App\Models\Setting::get('header_notice_active', '1') === '1';
                        $hNoticeRaw = \App\Models\Setting::get('header_notice_text');
                        $hNoticeSpeed = \App\Models\Setting::get('header_notice_speed', '8');
                        
                        $hNoticeItems = [];
                        if (!empty($hNoticeRaw)) {
                            $splitItems = preg_split('/\r\n|\r|\n|\|/', $hNoticeRaw);
                            foreach ($splitItems as $sItem) {
                                $trimmed = trim($sItem);
                                if (!empty($trimmed)) {
                                    $hNoticeItems[] = $trimmed;
                                }
                            }
                        }
                    @endphp

                    @if($hNoticeActive && count($hNoticeItems) > 0)
                    <div class="flex-1 min-w-0 mx-2 flex items-center gap-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-1.5 shadow-sm">
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] sm:text-xs font-black bg-red-600 text-white shadow-sm flex-shrink-0 uppercase tracking-wide">
                            📢 NOTICE {{ count($hNoticeItems) > 1 ? '('.count($hNoticeItems).')' : '' }}
                        </span>
                        <div class="flex-1 min-w-0 overflow-hidden text-xs sm:text-sm font-bold text-slate-900 dark:text-slate-100">
                            <marquee id="header-notice-marquee" scrollamount="{{ $hNoticeSpeed }}" onmouseover="this.stop();" onmouseout="this.start();" class="block whitespace-nowrap">
                                @foreach($hNoticeItems as $index => $noticeItem)
                                    <span class="inline-block">{{ $noticeItem }}</span>
                                    <span class="inline-block px-14 sm:px-20 text-red-600 font-extrabold text-sm sm:text-base">•</span>
                                @endforeach
                            </marquee>
                            <script>
                                (function () {
                                    const m = document.getElementById('header-notice-marquee');
                                    if (!m) return;
                                    const savedPos = sessionStorage.getItem('header_notice_scroll');
                                    if (savedPos !== null) {
                                        m.scrollLeft = parseFloat(savedPos);
                                    }
                                    window.addEventListener('beforeunload', function () {
                                        if (m) {
                                            sessionStorage.setItem('header_notice_scroll', m.scrollLeft);
                                        }
                                    });
                                })();
                            </script>
                        </div>
                    </div>
                    @endif

                    <div class="flex items-center gap-3 flex-shrink-0">
                        {{-- Global Search --}}
                        <form method="GET" action="{{ route('search') }}" class="flex items-center">
                            <div class="relative">
                                <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __('Search tickets...') }}"
                                       class="border border-slate-200 dark:border-slate-700 rounded-lg pl-8 pr-3 py-1.5 text-sm focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 dark:text-slate-100 w-48 lg:w-64">
                                <svg class="absolute left-2.5 top-2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                        </form>
                        {{-- Language Switcher --}}
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" @click.outside="open = false"
                                    class="flex items-center gap-1.5 px-2.5 py-1.5 text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors text-xs font-semibold select-none">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                                </svg>
                                <span>{{ app()->getLocale() === 'bn' ? 'বাংলা (BN)' : 'English (EN)' }}</span>
                                <svg class="w-3.5 h-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="open" x-cloak @click.outside="open = false"
                                 class="absolute right-0 top-full mt-2 w-44 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl z-50 overflow-hidden py-1">
                                <form method="POST" action="{{ route('locale.set', 'en') }}" style="display:contents;">
                                    @csrf
                                    <button type="submit" class="flex items-center justify-between w-full px-3.5 py-2 text-xs font-medium hover:bg-indigo-50 dark:hover:bg-slate-700/60 {{ app()->getLocale() === 'en' ? 'text-indigo-600 dark:text-indigo-400 font-bold bg-indigo-50/50 dark:bg-indigo-500/10' : 'text-slate-700 dark:text-slate-300' }}">
                                        <span>🇬🇧 English (EN)</span>
                                        @if(app()->getLocale() === 'en')<svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>@endif
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('locale.set', 'bn') }}" style="display:contents;">
                                    @csrf
                                    <button type="submit" class="flex items-center justify-between w-full px-3.5 py-2 text-xs font-medium hover:bg-indigo-50 dark:hover:bg-slate-700/60 {{ app()->getLocale() === 'bn' ? 'text-indigo-600 dark:text-indigo-400 font-bold bg-indigo-50/50 dark:bg-indigo-500/10' : 'text-slate-700 dark:text-slate-300' }}">
                                        <span>🇧🇩 বাংলা (BN)</span>
                                        @if(app()->getLocale() === 'bn')<svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>@endif
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- Sound Alert Toggle --}}
                        <button
                            x-data="{ soundOn: localStorage.getItem('soundAlerts') !== 'off' }"
                            @click="soundOn = !soundOn; localStorage.setItem('soundAlerts', soundOn ? 'on' : 'off'); if (soundOn) playNotificationSound();"
                            class="p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors"
                            :title="soundOn ? '{{ __('Mute Sound Alerts') }}' : '{{ __('Enable Sound Alerts') }}'">
                            <svg x-show="soundOn" class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
                            </svg>
                            <svg x-show="!soundOn" x-cloak class="w-5 h-5 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/>
                            </svg>
                        </button>

                        {{-- Theme Toggle --}}
                        <button
                            x-data="{ dark: document.documentElement.classList.contains('dark') }"
                            @click="dark = !dark; document.documentElement.classList.toggle('dark', dark); localStorage.setItem('theme', dark ? 'dark' : 'light');
                                    @auth fetch('{{ route('profile.theme') }}', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }, body: JSON.stringify({ theme: dark ? 'dark' : 'light' }) }); @endauth"
                            class="p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors"
                            :title="dark ? '{{ __('Switch to light mode') }}' : '{{ __('Switch to dark mode') }}'">
                            <svg x-show="!dark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                            </svg>
                            <svg x-show="dark" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </button>

                        {{-- Notification Bell --}}
                        <div x-data="notificationBell()" class="relative">
                            <button @click="toggle()"
                                    class="relative p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                                <span x-show="count > 0" x-cloak
                                      class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center"
                                      x-text="count > 9 ? '9+' : count"></span>
                            </button>

                            {{-- Dropdown --}}
                            <div x-show="open" x-cloak @click.outside="open = false"
                                 class="absolute right-0 top-full mt-2 w-80 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-lg z-50 overflow-hidden">
                                <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 dark:border-slate-700">
                                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ __('Notifications') }}
                                        <span x-show="count > 0" class="ml-1 px-1.5 py-0.5 bg-red-100 text-red-600 rounded-full text-xs font-bold" x-text="count"></span>
                                    </span>
                                    <a href="{{ route('notifications.index') }}"
                                       class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">{{ __('View all') }}</a>
                                </div>
                                <div class="max-h-72 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-700" id="notif-list">
                                    <p class="px-4 py-8 text-center text-sm text-slate-400">{{ __('Loading…') }}</p>
                                </div>
                                <div class="px-4 py-2 border-t border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 flex items-center justify-between">
                                    <form method="POST" action="{{ route('notifications.read-all') }}">
                                        @csrf
                                        <button type="submit" class="text-xs text-slate-500 hover:text-indigo-600 font-medium">
                                            {{ __('Mark all as read') }}
                                        </button>
                                    </form>
                                    <a href="{{ route('notifications.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">{{ __('See all') }} →</a>
                                </div>
                            </div>
                        </div>

                        <div class="hidden sm:flex items-center gap-2 text-sm text-slate-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            {{ now()->format('D, d M Y') }}
                        </div>
                    </div>
                </header>

                {{-- Flash Messages + Main --}}
                <main class="flex-1 {{ request()->routeIs('whatsapp.*') ? 'p-0' : 'p-4 lg:p-6' }} dark:text-slate-200">
                    @if(session('success'))
                        <div x-data="{ show: true }" x-show="show"
                             class="mb-4 flex items-center justify-between bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                {{ session('success') }}
                            </div>
                            <button @click="show = false" class="text-emerald-500 hover:text-emerald-700">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </div>
                    @endif
                    @if(session('error'))
                        <div x-data="{ show: true }" x-show="show"
                             class="mb-4 flex items-center justify-between bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                {{ session('error') }}
                            </div>
                            <button @click="show = false" class="text-red-500 hover:text-red-700">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </div>
                    @endif

                    {{ $slot }}
                </main>
            </div>
        </div>

    {{-- Floating Toast Notification Container --}}
    <div id="toast-container" class="fixed bottom-5 right-5 z-[9999] flex flex-col gap-2.5 max-w-sm w-full pointer-events-none"></div>

    <script>
    // ── Web Audio Chime Sound ────────────────────────────────────────────────
    window.playNotificationChime = function() {
        if (localStorage.getItem('soundAlerts') === 'off') return;
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(587.33, ctx.currentTime); // D5
            osc.frequency.setValueAtTime(880, ctx.currentTime + 0.1); // A5
            gain.gain.setValueAtTime(0.15, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.35);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.35);
        } catch (e) {}
    };

    window.playNotificationSound = window.playNotificationChime;

    // ── Floating Toast Popup Notification ────────────────────────────────────
    window.showToastNotification = function(title, body, url) {
        window.playNotificationChime();
        const container = document.getElementById('toast-container');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = 'pointer-events-auto flex items-start gap-3 p-3.5 bg-slate-900/95 text-white rounded-xl shadow-2xl border border-slate-700/80 transform translate-y-4 opacity-0 transition-all duration-300 cursor-pointer hover:bg-slate-800';
        toast.innerHTML = `
            <div class="w-8 h-8 rounded-lg bg-indigo-500/20 text-indigo-400 flex items-center justify-center flex-shrink-0 mt-0.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-bold text-indigo-300 uppercase tracking-wide">${title}</p>
                <p class="text-xs text-slate-200 mt-0.5 font-medium leading-snug line-clamp-2">${body}</p>
            </div>
            <button type="button" class="text-slate-400 hover:text-white text-base font-bold leading-none flex-shrink-0">&times;</button>
        `;

        const closeBtn = toast.querySelector('button');
        if (closeBtn) {
            closeBtn.onclick = (e) => { e.stopPropagation(); toast.remove(); };
        }

        if (url) {
            toast.onclick = () => { window.location.href = url; };
        }

        container.appendChild(toast);

        setTimeout(() => {
            toast.classList.remove('translate-y-4', 'opacity-0');
        }, 50);

        setTimeout(() => {
            toast.classList.add('opacity-0', 'translate-y-2');
            setTimeout(() => toast.remove(), 300);
        }, 6000);
    }

    // ── Desktop Push Permission & Dispatcher ────────────────────────────────
    function checkAndHidePushBanner() {
        if ('Notification' in window && Notification.permission === 'granted') {
            const banner = document.getElementById('desktop-push-banner');
            if (banner) {
                banner.remove();
            }
        }
    }
    window.checkAndHidePushBanner = checkAndHidePushBanner;

    function requestDesktopPermission() {
        if (!window.isSecureContext && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
            showToastNotification('🔔 Alerts Active!', 'In-App Banner Popup & Audio Chime are active! (Native OS popups require HTTPS or localhost).', '');
            checkAndHidePushBanner();
            return;
        }

        if ('Notification' in window) {
            if (Notification.permission === 'granted') {
                showToastNotification('🔔 Desktop Alerts Active', 'Desktop notifications are enabled and active.', '');
                checkAndHidePushBanner();
                return;
            }
            Notification.requestPermission().then(permission => {
                if (permission === 'granted') {
                    showToastBanner('🔔 Notifications Enabled!', 'You will now receive native desktop alerts.', '');
                    checkAndHidePushBanner();
                } else {
                    showToastBanner('🔔 In-App Alerts Active', 'In-App Banner Popup & Audio Chime are active.', '');
                }
            }).catch(() => {
                showToastBanner('🔔 In-App Alerts Active', 'In-App Banner Popup & Audio Chime are active.', '');
            });
        } else {
            showToastBanner('🔔 In-App Alerts Active', 'In-App Banner Popup & Audio Chime are active.', '');
        }
    }

    window.showToastBanner = window.showToastNotification;

    window.pushDesktopGlobal = function(title, body, url) {
        window.showToastNotification(title, body, url);

        if ('Notification' in window && Notification.permission === 'granted') {
            try {
                const n = new Notification(title, { body, icon: '/favicon.ico', tag: url });
                if (url) n.onclick = () => { window.focus(); window.location.href = url; };
            } catch (e) {}
        }
    };

    function notificationBell() {
        return {
            open: false,
            count: 0,
            loaded: false,
            lastNotifId: 0,

            init() {
                this.fetchCount();
                // Poll every 30 seconds
                setInterval(() => this.fetchCount(), 30000);
            },

            fetchCount() {
                fetch('{{ route("notifications.count") }}', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.json())
                .then(data => {
                    this.count = data.count;

                    const notifs = data.latest || [];
                    if (this.lastNotifId === 0) {
                        if (notifs.length > 0) {
                            this.lastNotifId = Math.max(...notifs.map(n => n.id));
                        }
                        return;
                    }

                    const newOnes = notifs.filter(n => n.id > this.lastNotifId);
                    newOnes.forEach(n => {
                        pushDesktopGlobal('ISP Ticket System', n.message, n.url);
                    });
                    if (newOnes.length > 0) {
                        this.lastNotifId = Math.max(...newOnes.map(n => n.id));
                    }
                })
                .catch(() => {});
            },

            toggle() {
                this.open = !this.open;
                if (this.open && !this.loaded) {
                    this.loadDropdown();
                }
            },

            loadDropdown() {
                const list = document.getElementById('notif-list');
                fetch('{{ route("notifications.dropdown") }}', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.text())
                .then(html => {
                    if (list) {
                        list.innerHTML = html;
                        this.loaded = true;
                        if (window.checkAndHidePushBanner) window.checkAndHidePushBanner();
                    }
                })
                .catch(() => {
                    if (list) list.innerHTML = '<p class="px-4 py-6 text-center text-sm text-slate-400">Could not load notifications.</p>';
                });
            }
        }
    }
    </script>

    {{-- Image modal --}}
    <div id="img-modal" onclick="this.style.display='none'"
         style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.85);z-index:9999;align-items:center;justify-content:center;cursor:zoom-out;">
        <img id="img-modal-img" src="" alt="" style="max-width:92vw;max-height:90vh;border-radius:10px;box-shadow:0 8px 40px rgba(0,0,0,.6);">
    </div>
    <script>
        function openImgModal(src) {
            var m = document.getElementById('img-modal');
            document.getElementById('img-modal-img').src = src;
            m.style.display = 'flex';
        }
    </script>
    </body>
</html>
