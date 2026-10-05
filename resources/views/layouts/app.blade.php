<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#2563EB">
        <link rel="manifest" href="{{ asset('manifest.json') }}">
        <link rel="apple-touch-icon" href="{{ asset('images/default-avatar.svg') }}">
        <title>{{ config('app.name', 'ISP Ticket System') }}</title>
        @php 
            $siteFavicon = \App\Models\Setting::get('favicon_path'); 
            $favUrl = $siteFavicon ? asset('storage/' . $siteFavicon) : asset('images/favicon.png');
        @endphp
        <link rel="icon" type="image/png" href="{{ $favUrl }}">
        <link rel="shortcut icon" type="image/png" href="{{ $favUrl }}">
        <link rel="apple-touch-icon" href="{{ $favUrl }}">
        <script>
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('/sw.js').catch(function() {});
            }
        </script>
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

            // Apply sidebar width & class before first paint to prevent layout jump on page nav
            (function () {
                const collapsed = localStorage.getItem('sidebarCollapsed') === 'true';
                const w = collapsed ? '64px' : '256px';
                document.documentElement.style.setProperty('--sidebar-w', w);
                if (collapsed) {
                    document.documentElement.classList.add('sidebar-is-collapsed');
                } else {
                    document.documentElement.classList.remove('sidebar-is-collapsed');
                }
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
            [x-cloak] { display: none !important; }

            /* Prevent scrollbar flicker/jerk when navigating between short and long pages */
            html {
                scrollbar-gutter: stable;
            }

            /* Pure CSS sidebar logo display — renders synchronously on frame 0 */
            .sidebar-logo-collapsed { display: none !important; }
            .sidebar-logo-expanded { display: flex !important; }

            html.sidebar-is-collapsed .sidebar-logo-collapsed { display: flex !important; }
            html.sidebar-is-collapsed .sidebar-logo-expanded { display: none !important; }

            /* Pre-paint & dynamic sidebar layout rules */
            @media (min-width: 1024px) {
                #main-sidebar { width: var(--sidebar-w, 256px); }
                #main-content { margin-left: var(--sidebar-w, 256px); }
            }

            /* Synchronous, stable styling for sidebar navigation across ALL pages (prevents jumps/jerks) */
            #main-sidebar nav a,
            #main-sidebar nav .mb-1 > button {
                display: flex;
                align-items: center;
                gap: 12px;
                padding-left: 12px;
                padding-right: 12px;
            }
            html.sidebar-is-collapsed #main-sidebar nav a,
            html.sidebar-is-collapsed #main-sidebar nav .mb-1 > button,
            .sidebar-collapsed nav a,
            .sidebar-collapsed nav .mb-1 > button {
                justify-content: center !important;
                gap: 0 !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
            }

            /* Pure CSS sidebar collapse visibility (renders on Frame 0 synchronously, NO Alpine delay, NO reload jerk) */
            .sidebar-icon-expanded { display: block !important; }
            .sidebar-icon-collapsed { display: none !important; }
            html.sidebar-is-collapsed .sidebar-icon-expanded { display: none !important; }
            html.sidebar-is-collapsed .sidebar-icon-collapsed { display: block !important; }

            .sidebar-text { display: inline-block; }
            p.sidebar-text, div.sidebar-text { display: block; }
            html.sidebar-is-collapsed .sidebar-text,
            .sidebar-collapsed .sidebar-text {
                display: none !important;
            }

            .sidebar-collapsed-only { display: none !important; }
            html.sidebar-is-collapsed .sidebar-collapsed-only,
            .sidebar-collapsed .sidebar-collapsed-only {
                display: block !important;
            }

            html.sidebar-is-collapsed .sidebar-footer,
            .sidebar-collapsed .sidebar-footer {
                padding-left: 4px !important;
                padding-right: 4px !important;
            }
            html.sidebar-is-collapsed .sidebar-profile-link,
            .sidebar-collapsed .sidebar-profile-link {
                justify-content: center !important;
                gap: 0 !important;
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
            html.dark .bg-slate-50, html.dark .bg-slate-50\/50, html.dark .bg-slate-50\/60, html.dark .bg-slate-50\/70 { background-color: #0f172a !important; }

            /* ── Secondary: only solid buttons (50/100/950 left alone for badges) ── */
            .bg-emerald-500, .bg-emerald-600              { background-color: var(--cs) !important; }
            .hover\:bg-emerald-600:hover                  { background-color: color-mix(in srgb, var(--cs) 85%, #000) !important; }

            /* ── Light-mode softening (reduce harsh pure-white) ── */
            html:not(.dark) body { background-color: #eef2f7 !important; }
            html:not(.dark) .bg-slate-50 { background-color: #eef2f7 !important; }
            html:not(.dark) .bg-white { background-color: #f8fafc !important; }
            html:not(.dark) .main-top-header { background-color: #f8fafc !important; }
            html:not(.dark) aside.bg-white,
            html:not(.dark) [class*="bg-white"].border-r { background-color: #f8fafc !important; }

            /* ── Global Unified Card & Box Design System ── */
            .app-card,
            .site-box,
            .dashboard-card,
            .stat-card,
            main .bg-white.border:not(table):not(thead):not(tbody):not(tr):not(th):not(td):not(input):not(select):not(textarea):not(button):not(nav):not(ul):not(li):not([role="dialog"]):not([role="menu"]):not(.flatpickr-calendar):not(.ql-toolbar):not(.ql-container):not(.no-box-style),
            main .dark\:bg-slate-900.border:not(table):not(thead):not(tbody):not(tr):not(th):not(td):not(input):not(select):not(textarea):not(button):not(nav):not(ul):not(li):not([role="dialog"]):not([role="menu"]):not(.flatpickr-calendar):not(.ql-toolbar):not(.ql-container):not(.no-box-style) {
                height: auto !important;
                min-height: 0 !important;
                width: 100%;
                box-sizing: border-box;
                position: relative;
                border-radius: 1rem;
                box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04), 0 1px 2px -1px rgba(0, 0, 0, 0.04);
                transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1), 
                            box-shadow 0.22s cubic-bezier(0.16, 1, 0.3, 1), 
                            border-color 0.22s ease !important;
                will-change: transform, box-shadow;
            }

            /* Single Unified Hover Effect across the entire site */
            .app-card:hover,
            .site-box:hover,
            .dashboard-card:hover,
            .stat-card:hover,
            main .bg-white.border:not(table):not(thead):not(tbody):not(tr):not(th):not(td):not(input):not(select):not(textarea):not(button):not(nav):not(ul):not(li):not([role="dialog"]):not([role="menu"]):not(.flatpickr-calendar):not(.ql-toolbar):not(.ql-container):not(.no-box-style):not(.no-hover):hover,
            main .dark\:bg-slate-900.border:not(table):not(thead):not(tbody):not(tr):not(th):not(td):not(input):not(select):not(textarea):not(button):not(nav):not(ul):not(li):not([role="dialog"]):not([role="menu"]):not(.flatpickr-calendar):not(.ql-toolbar):not(.ql-container):not(.no-box-style):not(.no-hover):hover {
                transform: translateY(-2.5px);
                box-shadow: 0 12px 26px -6px rgba(0, 0, 0, 0.08), 0 6px 12px -4px rgba(0, 0, 0, 0.04) !important;
                border-color: color-mix(in srgb, var(--cp, #4f46e5) 30%, #cbd5e1) !important;
            }

            html.dark .app-card:hover,
            html.dark .site-box:hover,
            html.dark .dashboard-card:hover,
            html.dark .stat-card:hover,
            html.dark main .bg-white.border:not(table):not(thead):not(tbody):not(tr):not(th):not(td):not(input):not(select):not(textarea):not(button):not(nav):not(ul):not(li):not([role="dialog"]):not([role="menu"]):not(.flatpickr-calendar):not(.ql-toolbar):not(.ql-container):not(.no-box-style):not(.no-hover):hover,
            html.dark main .dark\:bg-slate-900.border:not(table):not(thead):not(tbody):not(tr):not(th):not(td):not(input):not(select):not(textarea):not(button):not(nav):not(ul):not(li):not([role="dialog"]):not([role="menu"]):not(.flatpickr-calendar):not(.ql-toolbar):not(.ql-container):not(.no-box-style):not(.no-hover):hover {
                box-shadow: 0 14px 28px -6px rgba(0, 0, 0, 0.45) !important;
                border-color: color-mix(in srgb, var(--cp, #4f46e5) 45%, #475569) !important;
            }
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
            html.sidebar-is-collapsed nav a,
            html.sidebar-is-collapsed nav > div > div > button:not([type="button"]),
            html.sidebar-is-collapsed nav .mb-1 > button,
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
            html.sidebar-is-collapsed nav a svg,
            html.sidebar-is-collapsed nav .mb-1 > button svg:first-child,
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
                padding-left: 12px;
                padding-right: 12px;
            }
            html.sidebar-is-collapsed #main-sidebar nav,
            .sidebar-collapsed nav {
                padding-left: 4px !important;
                padding-right: 4px !important;
            }
            #main-sidebar nav::-webkit-scrollbar {
                display: none !important;
                width: 0 !important;
                height: 0 !important;
            }

            /* Synchronize Sidebar Header & Main Top Bar heights & borders perfectly */
            .sidebar-header-box,
            .main-top-header {
                height: 64px !important;
                min-height: 64px !important;
                max-height: 64px !important;
                box-sizing: border-box !important;
            }
        </style>
    </head>
    <body class="font-sans antialiased bg-slate-50 dark:bg-slate-950">
        <div x-data="{ sidebarOpen: false, sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true' }"
             x-effect="document.body.classList.toggle('overflow-hidden', sidebarOpen)"
             class="flex min-h-screen">

            {{-- Mobile Overlay --}}
            <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
                 x-transition.opacity
                 class="fixed inset-0 bg-black/50 z-40 lg:hidden"></div>            {{-- Sidebar --}}
            <aside id="main-sidebar"
                   :class="(sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0') + (sidebarCollapsed ? ' sidebar-collapsed' : '')"
                   class="bg-white dark:bg-slate-950 text-slate-800 dark:text-white border-r border-slate-200 dark:border-slate-800 flex flex-col flex-shrink-0 fixed top-0 left-0 h-full z-50 lg:z-30 lg:translate-x-0 overflow-visible transition-colors duration-200">

                {{-- Logo & Prominent Collapse Toggle Icon --}}
                <div class="h-16 sidebar-header-box border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 flex items-center justify-center flex-shrink-0 relative px-3 overflow-visible">
                    @php 
                        $siteLogo = \App\Models\Setting::get('logo_path');
                        $siteFavicon = \App\Models\Setting::get('favicon_path');
                    @endphp

                    {{-- Floating Collapse Toggle Button on Border --}}
                    <button type="button"
                            @click="sidebarCollapsed = !sidebarCollapsed; localStorage.setItem('sidebarCollapsed', sidebarCollapsed); document.documentElement.style.setProperty('--sidebar-w', sidebarCollapsed ? '64px' : '256px'); document.documentElement.classList.toggle('sidebar-is-collapsed', sidebarCollapsed)"
                            class="flex items-center justify-center w-7 h-7 rounded-full bg-white dark:bg-slate-800 hover:bg-indigo-50 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 hover:text-indigo-600 border border-slate-200 dark:border-slate-700 shadow-md transition-transform duration-200 cursor-pointer"
                            style="position: absolute; right: -14px; top: 50%; transform: translateY(-50%); z-index: 50;"
                            :title="sidebarCollapsed ? 'Expand Sidebar' : 'Collapse Sidebar'">
                        <svg class="sidebar-icon-expanded w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                        </svg>
                        <svg class="sidebar-icon-collapsed w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>

                    {{-- Logo Container: Pure CSS visibility to eliminate any Alpine delay or layout shift --}}
                    <div class="flex items-center justify-center w-full h-full overflow-hidden">
                        {{-- Collapsed Mode: Favicon --}}
                        <a href="{{ route('dashboard') }}" title="VISION Technologies Limited" class="sidebar-logo-collapsed items-center justify-center flex-shrink-0">
                            <img src="{{ $siteFavicon ? asset('storage/' . $siteFavicon) : ($siteLogo ? asset('storage/' . $siteLogo) : 'https://visiontech.com.bd/wp-content/uploads/2017/11/vision-logo.png') }}"
                                 alt="Favicon"
                                 width="36" height="36"
                                 class="w-9 h-9 object-contain"
                                 loading="eager"
                                 decoding="sync">
                        </a>

                        {{-- Expanded Mode: Full VISION Logo --}}
                        <a href="{{ route('dashboard') }}" class="sidebar-logo-expanded items-center justify-start w-full overflow-hidden">
                            <img src="{{ $siteLogo ? asset('storage/' . $siteLogo) : 'https://visiontech.com.bd/wp-content/uploads/2017/11/vision-logo.png' }}"
                                 alt="VISION Technologies Limited"
                                 height="40"
                                 style="height: 40px; max-width: 190px;"
                                 class="h-10 max-w-[190px] object-contain flex-shrink-0"
                                 loading="eager"
                                 decoding="sync">
                        </a>
                    </div>
                </div>

                {{-- Navigation --}}
                <nav class="flex-1 py-4 overflow-y-auto overflow-x-hidden">
                    <div class="mb-5">
                        <p class="sidebar-text px-3 mb-2 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Main Menu') }}</p>

                        <a href="{{ route('dashboard') }}"
                           :title="sidebarCollapsed ? @js(__('Dashboard')) : ''"
                           class="flex items-center px-3 gap-3 py-2.5 mb-1 rounded-lg text-sm font-medium transition-colors
                                  {{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            <span class="sidebar-text truncate">{{ __('Dashboard') }}</span>
                        </a>



                        <a href="{{ route('tickets.create') }}"
                           :title="sidebarCollapsed ? @js(__('Create Ticket')) : ''"
                           class="flex items-center px-3 gap-3 py-2.5 mb-1 rounded-lg text-sm font-medium transition-colors
                                  {{ request()->routeIs('tickets.create') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span class="sidebar-text truncate">{{ __('Create Ticket') }}</span>
                        </a>

                        {{-- My Tickets (For all users: shows assigned tickets for staff, own tickets for reseller) --}}
                        <a href="{{ route('tickets.index', ['assigned' => 'me']) }}"
                           :title="sidebarCollapsed ? @js(__('My Tickets')) : ''"
                           class="flex items-center px-3 gap-3 py-2.5 mb-1 rounded-lg text-sm font-medium transition-colors
                                  {{ request()->routeIs('tickets.*') && request('assigned') === 'me' && !request()->routeIs('tickets.create') && !request()->routeIs('tickets.calendar') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span class="sidebar-text truncate">{{ __('My Tickets') }}</span>
                        </a>

                        {{-- All Tickets (For Admin, NOC, Call Center, Supervisor, Senior Supervisor) --}}
                        @if(!auth()->user()?->isReseller())
                        <a href="{{ route('tickets.index') }}"
                           :title="sidebarCollapsed ? @js(__('All Tickets')) : ''"
                           class="flex items-center px-3 gap-3 py-2.5 mb-1 rounded-lg text-sm font-medium transition-colors
                                  {{ request()->routeIs('tickets.*') && request('assigned') !== 'me' && !request()->routeIs('tickets.create') && !request()->routeIs('tickets.calendar') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                            </svg>
                            <span class="sidebar-text truncate">{{ __('All Tickets') }}</span>
                        </a>
                        @endif

                        <a href="{{ route('board.index') }}"
                           :title="sidebarCollapsed ? @js(__('Board')) : ''"
                           class="flex items-center px-3 gap-3 py-2.5 mb-1 rounded-lg text-sm font-medium transition-colors
                                  {{ request()->routeIs('board.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                            </svg>
                            <span class="sidebar-text truncate">{{ __('Board') }}</span>
                        </a>

                        <a href="{{ route('roster.index') }}"
                           :title="sidebarCollapsed ? @js(__('Shift Roster')) : ''"
                           class="flex items-center px-3 gap-3 py-2.5 mb-1 rounded-lg text-sm font-medium transition-colors
                                  {{ request()->routeIs('roster.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span class="sidebar-text truncate">{{ __('Shift Roster') }}</span>
                        </a>

                        @if(auth()->user()?->isAdmin())
                        <a href="{{ route('map.index') }}"
                           :title="sidebarCollapsed ? @js(__('Live Staff Map')) : ''"
                           class="flex items-center px-3 gap-3 py-2.5 mb-1 rounded-lg text-sm font-medium transition-colors
                                  {{ request()->routeIs('map.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span class="sidebar-text truncate">{{ __('Live Staff Map') }}</span>
                        </a>
                        @endif

                        <a href="{{ route('knowledge-base.index') }}"
                           :title="sidebarCollapsed ? @js(__('Knowledge Base')) : ''"
                           class="flex items-center px-3 gap-3 py-2.5 mb-1 rounded-lg text-sm font-medium transition-colors
                                  {{ request()->routeIs('knowledge-base.*') || request()->routeIs('kb.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                            <span class="sidebar-text truncate">{{ __('Knowledge Base') }}</span>
                        </a>

                        @if(auth()->user()?->isAdmin() || auth()->user()?->isNoc())
                        <a href="{{ route('canned-responses.index') }}"
                           :title="sidebarCollapsed ? @js(__('Canned Responses')) : ''"
                           class="flex items-center px-3 gap-3 py-2.5 mb-1 rounded-lg text-sm font-medium transition-colors
                                  {{ request()->routeIs('canned-responses.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 01-2-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-6l-4 4v-4z"/>
                            </svg>
                            <span class="sidebar-text truncate">{{ __('Canned Responses') }}</span>
                        </a>
                        @endif

                        @if(auth()->user()?->isAdmin())
                        <a href="{{ route('reports.index') }}"
                           :title="sidebarCollapsed ? @js(__('Reports')) : ''"
                           class="flex items-center px-3 gap-3 py-2.5 mb-1 rounded-lg text-sm font-medium transition-colors
                                  {{ request()->routeIs('reports.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span class="sidebar-text truncate">{{ __('Reports') }}</span>
                        </a>
                        @endif

                        @if(auth()->user()?->isSuperAdminOnly())
                        <a href="{{ route('settings.edit') }}"
                           :title="sidebarCollapsed ? @js(__('Site Settings')) : ''"
                           class="flex items-center px-3 gap-3 py-2.5 mb-1 rounded-lg text-sm font-medium transition-colors
                                  {{ request()->routeIs('settings.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span class="sidebar-text truncate">{{ __('Site Settings') }}</span>
                        </a>
                        @endif

                        @if(auth()->user()?->isSuperAdminOnly())
                        <a href="{{ route('custom-menu-links.index') }}"
                           :title="sidebarCollapsed ? @js(__('Manage Important URLs')) : ''"
                           class="flex items-center px-3 gap-3 py-2.5 mb-1 rounded-lg text-sm font-medium transition-colors
                                  {{ request()->routeIs('custom-menu-links.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                            </svg>
                            <span class="sidebar-text truncate">{{ __('Manage Important URLs') }}</span>
                        </a>
                        @endif

                        @if(isset($customMenuLinks) && $customMenuLinks->count() > 0)
                        {{-- Expanded Mode Accordion --}}
                        <div class="sidebar-text mb-1 w-full" x-data="{ open: false }">
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
                                            {!! $cLink->renderIcon('w-4 h-4 text-slate-500 dark:text-slate-400') !!}
                                        </div>
                                        <span class="truncate">{{ $cLink->name ?? $cLink->title }}</span>
                                    </div>
                                </a>
                                @endforeach
                            </div>
                        </div>

                        {{-- Collapsed Mode: Render Sub-menu links directly as Main Icons --}}
                        <div class="sidebar-collapsed-only">
                            @foreach($customMenuLinks as $cLink)
                            <a href="{{ $cLink->url }}" target="{{ $cLink->open_in_new_tab ? '_blank' : '_self' }}"
                               title="{{ $cLink->name ?? $cLink->title }}"
                               class="flex items-center justify-center py-2.5 mb-1 rounded-lg text-sm font-medium transition-colors text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800">
                                <div class="w-5 h-5 flex items-center justify-center flex-shrink-0">
                                    {!! $cLink->renderIcon('w-5 h-5 text-slate-500 dark:text-slate-400') !!}
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
                        <div class="sidebar-text mb-1 w-full" x-data="{ open: {{ $resellerOpen ? 'true' : 'false' }} }">
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
                        <div class="sidebar-collapsed-only">
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
                <div class="sidebar-footer pt-4 pb-7 border-t border-slate-200 dark:border-slate-800/80 flex-shrink-0 bg-slate-50 dark:bg-slate-900/40 px-4">
                    <a href="{{ route('profile.edit') }}" @click.prevent="window.showUserProfileModal()" class="sidebar-profile-link flex items-center mb-3 hover:opacity-80 transition-opacity gap-3 cursor-pointer" :title="sidebarCollapsed ? '{{ auth()->user()->name }}' : '{{ __('View Profile & Active Tickets') }}'">
                        <img src="{{ auth()->user()->avatarUrl() }}" alt="{{ auth()->user()->name }}"
                             class="w-9 h-9 rounded-full object-cover flex-shrink-0 border border-slate-200 dark:border-slate-700 shadow-sm">
                        <div class="min-w-0 sidebar-text">
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
                    <form method="POST" action="{{ route('logout') }}" class="mb-2">
                        @csrf
                        <button type="submit"
                                class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold text-rose-700 dark:text-rose-300 bg-rose-50 dark:bg-rose-950/50 hover:bg-rose-100 dark:hover:bg-rose-900/60 border border-rose-200 dark:border-rose-800/60 transition-colors shadow-sm"
                                :title="sidebarCollapsed ? @js(__('Sign Out')) : ''">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <span class="sidebar-text">{{ __('Sign Out') }}</span>
                        </button>
                    </form>
                </div>
            </aside>

            {{-- Main Content Wrapper --}}
            <div id="main-content"
                 class="flex-1 flex flex-col min-h-screen">

                {{-- Top Bar --}}
                <header class="h-16 main-top-header bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 px-2 sm:px-4 lg:px-6 flex items-center justify-between sticky top-0 z-30 gap-1 sm:gap-3">
                    <div class="flex items-center gap-1.5 sm:gap-3 flex-shrink-0 min-w-0">
                        {{-- Mobile-only menu toggle --}}
                        <button type="button"
                                @click="sidebarOpen = !sidebarOpen"
                                class="lg:hidden flex items-center justify-center p-1.5 sm:p-2.5 rounded-lg bg-indigo-50 dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-600 dark:hover:text-white border border-indigo-200 dark:border-slate-700 shadow-sm transition-all flex-shrink-0 cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 18h14"/>
                            </svg>
                        </button>
                        @isset($pageTitle)
                            <h1 class="text-sm sm:text-lg font-semibold text-slate-800 dark:text-slate-100 flex-shrink truncate">{{ $pageTitle }}</h1>
                        @endisset
                    </div>

                    {{-- Dynamic Role-Targeted Header Notice Marquee Ticker (Supports Global, Reseller-Specific & NOC-Specific in EN & BN) --}}
                    @php
                        $currentUser   = auth()->user();
                        $currentLocale = app()->getLocale();

                        // Helper to retrieve localized notice
                        $getChannelNotice = function(string $prefix, string $defaultBadgeEn, string $defaultBadgeBn) use ($currentLocale) {
                            $textEn      = trim((string) \App\Models\Setting::get("{$prefix}_text_en"));
                            $textBn      = trim((string) \App\Models\Setting::get("{$prefix}_text_bn"));
                            $legacy      = trim((string) \App\Models\Setting::get("{$prefix}_text"));
                            $legacyBadge = trim((string) \App\Models\Setting::get("{$prefix}_badge"));

                            // Pick according to current locale with fallback
                            if ($currentLocale === 'bn') {
                                $text = $textBn !== '' ? $textBn : ($legacy !== '' ? $legacy : $textEn);
                                $badge = \App\Models\Setting::get("{$prefix}_badge_bn") ?: ($legacyBadge !== '' ? $legacyBadge : (\App\Models\Setting::get("{$prefix}_badge_en") ?: $defaultBadgeBn));
                            } else {
                                $text = $textEn !== '' ? $textEn : ($legacy !== '' ? $legacy : $textBn);
                                $badge = \App\Models\Setting::get("{$prefix}_badge_en") ?: ($legacyBadge !== '' ? $legacyBadge : (\App\Models\Setting::get("{$prefix}_badge_bn") ?: $defaultBadgeEn));
                            }

                            return [
                                'text'  => $text,
                                'badge' => $badge,
                                'has_content' => ($textEn !== '' || $textBn !== '' || $legacy !== ''),
                            ];
                        };

                        $resellerNotice = $getChannelNotice('header_notice_reseller', 'RESELLER ALERT', 'রিসেলার নোটিশ');
                        $nocNotice      = $getChannelNotice('header_notice_noc', 'NOC DISPATCH', 'এনওসি নোটিশ');
                        $globalNotice   = $getChannelNotice('header_notice', 'GLOBAL NOTICE', 'সাধারণ নোটিশ');

                        $masterActive = \App\Models\Setting::get('header_notice_master_active', '1') === '1';

                        $activeNoticeType = null;
                        $hNoticeRaw   = null;
                        $hNoticeBadge = '';
                        $hNoticeSpeed = '8';
                        $hNoticeTheme = 'danger';

                        if ($masterActive) {
                            if ($currentUser?->isReseller() && \App\Models\Setting::get('header_notice_reseller_active', '0') === '1' && $resellerNotice['has_content']) {
                                // Dedicated Reseller notice takes precedence
                                $activeNoticeType = 'reseller';
                                $hNoticeRaw   = $resellerNotice['text'];
                                $hNoticeBadge = $resellerNotice['badge'];
                                $hNoticeSpeed = \App\Models\Setting::get('header_notice_reseller_speed', '8');
                                $hNoticeTheme = \App\Models\Setting::get('header_notice_reseller_theme', 'warning');
                            } elseif ($currentUser?->isNoc() && \App\Models\Setting::get('header_notice_noc_active', '0') === '1' && $nocNotice['has_content']) {
                                // Dedicated NOC notice takes precedence
                                $activeNoticeType = 'noc';
                                $hNoticeRaw   = $nocNotice['text'];
                                $hNoticeBadge = $nocNotice['badge'];
                                $hNoticeSpeed = \App\Models\Setting::get('header_notice_noc_speed', '8');
                                $hNoticeTheme = \App\Models\Setting::get('header_notice_noc_theme', 'indigo');
                            } elseif (\App\Models\Setting::get('header_notice_active', '0') === '1' && $globalNotice['has_content']) {
                                // Global notice shown to everyone when active (unless overridden by specific role notice)
                                $activeNoticeType = 'global';
                                $hNoticeRaw   = $globalNotice['text'];
                                $hNoticeBadge = $globalNotice['badge'];
                                $hNoticeSpeed = \App\Models\Setting::get('header_notice_speed', '8');
                                $hNoticeTheme = \App\Models\Setting::get('header_notice_theme', 'danger');
                            }
                        }

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

                        // Themes mapping
                        $themeStyles = [
                            'danger' => [
                                'border' => 'border-rose-200 dark:border-rose-900/60',
                                'badge'  => 'bg-rose-600 text-white',
                                'dot'    => 'text-rose-600',
                            ],
                            'warning' => [
                                'border' => 'border-amber-300 dark:border-amber-900/60',
                                'badge'  => 'bg-amber-500 text-white',
                                'dot'    => 'text-amber-500',
                            ],
                            'info' => [
                                'border' => 'border-sky-200 dark:border-sky-900/60',
                                'badge'  => 'bg-sky-600 text-white',
                                'dot'    => 'text-sky-500',
                            ],
                            'success' => [
                                'border' => 'border-emerald-200 dark:border-emerald-900/60',
                                'badge'  => 'bg-emerald-600 text-white',
                                'dot'    => 'text-emerald-500',
                            ],
                            'indigo' => [
                                'border' => 'border-indigo-200 dark:border-indigo-900/60',
                                'badge'  => 'bg-indigo-600 text-white',
                                'dot'    => 'text-indigo-600',
                            ],
                        ];
                        $curTheme = $themeStyles[$hNoticeTheme ?? 'danger'] ?? $themeStyles['danger'];
                    @endphp

                    @if($activeNoticeType !== null && count($hNoticeItems) > 0)
                    <div class="hidden sm:flex flex-1 min-w-0 mx-2 items-center gap-2.5 bg-white dark:bg-slate-900 border {{ $curTheme['border'] }} rounded-xl px-3 py-1.5 shadow-sm transition-colors">
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] sm:text-xs font-black {{ $curTheme['badge'] }} shadow-xs flex-shrink-0 uppercase tracking-wide">
                            📢 {{ $hNoticeBadge }} {{ count($hNoticeItems) > 1 ? '('.count($hNoticeItems).')' : '' }}
                        </span>
                        <div class="flex-1 min-w-0 overflow-hidden text-xs sm:text-sm font-bold text-slate-900 dark:text-slate-100">
                            <marquee id="header-notice-marquee" scrollamount="{{ $hNoticeSpeed }}" onmouseover="this.stop();" onmouseout="this.start();" class="block whitespace-nowrap">
                                @foreach($hNoticeItems as $index => $noticeItem)
                                    <span class="inline-block">{{ $noticeItem }}</span>
                                    <span class="inline-block px-14 sm:px-20 {{ $curTheme['dot'] }} font-extrabold text-sm sm:text-base">•</span>
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

                    <div class="flex items-center gap-2 sm:gap-3 flex-shrink-0 ml-auto">
                        {{-- Global Search on the right --}}
                        <form method="GET" action="{{ route('search') }}" class="flex items-center">
                            <div class="relative">
                                <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __('Search tickets...') }}"
                                       class="border border-slate-200 dark:border-slate-700/80 rounded-lg pl-8 pr-3 py-1.5 text-xs sm:text-sm focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-[#131b2e] text-slate-800 dark:text-slate-100 w-36 sm:w-48 lg:w-64 shadow-xs">
                                <svg class="absolute left-2.5 top-2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                        </form>
                        {{-- Language Switcher (Always visible on all screen sizes) --}}
                        <div x-data="{ open: false }" class="relative inline-block">
                            <button @click="open = !open" @click.outside="open = false"
                                    type="button"
                                    class="flex items-center gap-1.5 px-2 sm:px-2.5 py-1.5 text-slate-700 dark:text-slate-200 hover:text-indigo-600 dark:hover:text-indigo-400 bg-slate-100 hover:bg-slate-200/80 dark:bg-slate-800 dark:hover:bg-slate-700/80 border border-slate-200/80 dark:border-slate-700 rounded-lg sm:rounded-xl transition-all text-xs font-bold select-none cursor-pointer shadow-2xs"
                                    title="{{ __('Change Language') }}">
                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                                </svg>
                                <span class="inline sm:hidden">{{ app()->getLocale() === 'bn' ? '🇧🇩 বাং' : '🇬🇧 EN' }}</span>
                                <span class="hidden sm:inline">{{ app()->getLocale() === 'bn' ? '🇧🇩 বাংলা' : '🇬🇧 English' }}</span>
                                <svg class="w-3 h-3 opacity-60 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="open" x-cloak @click.outside="open = false"
                                 style="z-index: 99999;"
                                 class="absolute right-0 top-full mt-2 w-48 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl overflow-hidden py-1">
                                <div class="px-3.5 py-1.5 border-b border-slate-100 dark:border-slate-700/80 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                                    {{ __('Language / ভাষা') }}
                                </div>
                                <form method="POST" action="{{ route('locale.set', 'en') }}" style="display:contents;">
                                    @csrf
                                    <button type="submit" class="flex items-center justify-between w-full px-3.5 py-2.5 text-xs font-semibold hover:bg-indigo-50 dark:hover:bg-slate-700/60 transition-colors {{ app()->getLocale() === 'en' ? 'text-indigo-600 dark:text-indigo-400 font-bold bg-indigo-50/70 dark:bg-indigo-500/15' : 'text-slate-700 dark:text-slate-300' }}">
                                        <span class="flex items-center gap-2 text-sm">🇬🇧 <span class="text-xs font-semibold">English (EN)</span></span>
                                        @if(app()->getLocale() === 'en')
                                            <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        @endif
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('locale.set', 'bn') }}" style="display:contents;">
                                    @csrf
                                    <button type="submit" class="flex items-center justify-between w-full px-3.5 py-2.5 text-xs font-semibold hover:bg-indigo-50 dark:hover:bg-slate-700/60 transition-colors {{ app()->getLocale() === 'bn' ? 'text-indigo-600 dark:text-indigo-400 font-bold bg-indigo-50/70 dark:bg-indigo-500/15' : 'text-slate-700 dark:text-slate-300' }}">
                                        <span class="flex items-center gap-2 text-sm">🇧🇩 <span class="text-xs font-semibold">বাংলা (BN)</span></span>
                                        @if(app()->getLocale() === 'bn')
                                            <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        @endif
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- Sound Alert Toggle --}}
                        <button
                            x-data="{ soundOn: localStorage.getItem('soundAlerts') !== 'off' }"
                            @click="soundOn = !soundOn; localStorage.setItem('soundAlerts', soundOn ? 'on' : 'off'); if (soundOn) playNotificationSound();"
                            class="hidden md:inline-flex p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors"
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
                            class="p-1.5 sm:p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors"
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
                                    class="relative p-1.5 sm:p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors">
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
                                 style="width: 320px; max-width: 92vw; z-index: 99999;"
                                 class="absolute right-0 sm:right-auto sm:left-1/2 sm:-translate-x-1/2 top-full mt-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl overflow-hidden">
                                <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 dark:border-slate-700">
                                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ __('Notifications') }}
                                        <span x-show="count > 0" class="ml-1 px-1.5 py-0.5 bg-red-100 text-red-600 rounded-full text-xs font-bold" x-text="count"></span>
                                    </span>
                                    <a href="{{ route('notifications.index') }}"
                                       class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">{{ __('View all') }}</a>
                                </div>
                                <div class="max-h-[70vh] overflow-y-auto divide-y divide-slate-100 dark:divide-slate-700" id="notif-list">
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

                        {{-- User Quick Profile Pill in Header (Click to open profile details & active tickets) --}}
                        <button type="button"
                                @click="window.showUserProfileModal()"
                                class="flex items-center gap-1.5 sm:gap-2 pl-1 sm:pl-1.5 pr-2 sm:pr-2.5 py-1 rounded-full bg-slate-100 hover:bg-slate-200/80 dark:bg-slate-800 dark:hover:bg-slate-700/80 border border-slate-200/80 dark:border-slate-700 transition-all cursor-pointer shadow-2xs group"
                                title="{{ __('View Profile & Active Tickets') }}">
                            <div class="relative flex-shrink-0">
                                <img src="{{ auth()->user()->avatarUrl() }}" alt="{{ auth()->user()->name }}"
                                     class="w-6 h-6 sm:w-7 sm:h-7 rounded-full object-cover border border-indigo-400/40">
                                @if(auth()->user()->activeTicketsCount() > 0)
                                <span class="absolute -top-1 -right-1 min-w-[15px] h-[15px] px-1 rounded-full bg-indigo-600 text-white text-[9px] font-bold flex items-center justify-center shadow-xs">
                                    {{ auth()->user()->activeTicketsCount() > 99 ? '99+' : auth()->user()->activeTicketsCount() }}
                                </span>
                                @endif
                            </div>
                            <span class="hidden md:inline text-xs font-semibold text-slate-700 dark:text-slate-200 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors max-w-[110px] truncate">
                                {{ auth()->user()->name }}
                            </span>
                        </button>

                        <div class="hidden sm:flex items-center gap-2 text-sm text-slate-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            {{ now()->format('D, d M Y') }}
                        </div>
                    </div>
                </header>

                {{-- Flash Messages (floating auto-dismiss toasts, top-right) + Main --}}
                @if(session('success') || session('error'))
                    <div class="fixed top-20 right-4 z-[9999] space-y-2 w-[90%] max-w-sm pointer-events-none"
                         x-data="{ items: [
                            @if(session('success')) { id: 'ok', type: 'ok', msg: @js(session('success')), show: true }, @endif
                            @if(session('error')) { id: 'err', type: 'err', msg: @js(session('error')), show: true }, @endif
                         ] }"
                         x-init="setTimeout(() => items.forEach(i => i.show = false), 4500)">
                        <template x-for="i in items" :key="i.id">
                            <div x-show="i.show" x-transition.duration.300ms
                                 class="pointer-events-auto flex items-start gap-3 px-4 py-3 rounded-xl shadow-2xl border backdrop-blur-sm"
                                 :class="i.type === 'ok' ? 'bg-emerald-50/95 dark:bg-emerald-900/85 border-emerald-300 dark:border-emerald-700 text-emerald-900 dark:text-emerald-100' : 'bg-red-50/95 dark:bg-red-900/85 border-red-300 dark:border-red-700 text-red-900 dark:text-red-100'">
                                <span class="text-lg leading-none" x-text="i.type === 'ok' ? '✅' : '⚠️'"></span>
                                <span class="text-sm font-semibold flex-1" x-text="i.msg"></span>
                                <button @click="i.show = false" class="text-current opacity-60 hover:opacity-100 text-sm leading-none">✕</button>
                            </div>
                        </template>
                    </div>
                @endif
                <main class="flex-1 {{ request()->routeIs('map.*') ? 'p-0' : 'p-2.5 sm:p-3' }} dark:text-slate-200">
                    {{ $slot }}
                </main>
            </div>
        </div>

    {{-- Floating Toast Notification Container (Top-right, highest z-index above everything) --}}
    <div id="toast-container" class="fixed top-5 right-5 sm:top-6 sm:right-6 flex flex-col gap-2.5 max-w-sm w-full pointer-events-none" style="z-index: 9999999;"></div>

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
        toast.className = 'pointer-events-auto flex items-start gap-3 p-3.5 bg-slate-900/95 dark:bg-slate-800 text-white rounded-xl shadow-2xl border border-slate-700/80 transform -translate-y-4 opacity-0 transition-all duration-300 cursor-pointer hover:bg-slate-800';
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
            toast.classList.remove('-translate-y-4', 'opacity-0');
        }, 50);

        setTimeout(() => {
            toast.classList.add('opacity-0', '-translate-y-2');
            setTimeout(() => toast.remove(), 300);
        }, 6000);
    }

    // ── Desktop Push Permission & Dispatcher ────────────────────────────────
    function checkAndHidePushBanner() {
        if ('Notification' in window) {
            const dot = document.getElementById('desktop-push-dot');
            const btn = document.getElementById('desktop-push-nav-btn');
            const banner = document.getElementById('desktop-push-banner');
            if (Notification.permission === 'granted') {
                if (dot) dot.remove();
                if (btn) btn.title = 'Desktop Notifications Active';
                if (banner) banner.remove();
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

    document.addEventListener('DOMContentLoaded', function() {
        checkAndHidePushBanner();
        if ('Notification' in window && Notification.permission === 'default') {
            setTimeout(function() {
                Notification.requestPermission().then(permission => {
                    if (permission === 'granted') {
                        showToastNotification('🔔 Notifications Enabled', 'Desktop notifications are active for tickets & alerts.', '');
                        checkAndHidePushBanner();
                    }
                }).catch(() => {});
            }, 1000);
        }
    });

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
                // Poll every 8 seconds for fast real-time notifications
                setInterval(() => this.fetchCount(), 8000);
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

    @auth
    @php
        $authUser = auth()->user();
        $authActiveTickets = $authUser->activeTicketsCount();
        $authResolvedTickets = $authUser->resolvedTicketsCount();
        $authTotalTickets = $authUser->totalTicketsCount();
        $authSystemActive = $authUser->isAdmin()
            ? \App\Models\Ticket::whereNotIn('status', ['resolved', 'closed'])->count()
            : null;

        if ($authUser->isReseller()) {
            $authTicketsUrl = route('tickets.index', ['created_by' => $authUser->id]);
            $authActiveTicketsUrl = route('tickets.index', ['created_by' => $authUser->id, 'status' => 'active']);
        } elseif ($authUser->isAdmin()) {
            $authTicketsUrl = route('tickets.index');
            $authActiveTicketsUrl = route('tickets.index', ['status' => 'active']);
        } else {
            $authTicketsUrl = route('tickets.index', ['assigned' => 'me']);
            $authActiveTicketsUrl = route('tickets.index', ['assigned' => 'me', 'status' => 'active']);
        }

        $authProfileData = [
            'id' => $authUser->id,
            'name' => $authUser->name,
            'email' => $authUser->email,
            'phone' => $authUser->phone ?? '',
            'role' => $authUser->role,
            'role_label' => strtoupper(str_replace('_', ' ', $authUser->role)),
            'team' => $authUser->team ?? '',
            'avatar_url' => $authUser->avatarUrl(),
            'current_shift' => $authUser->current_shift ?? 'unassigned',
            'current_shift_label' => \App\Models\User::SHIFTS[$authUser->current_shift ?? 'unassigned'] ?? ($authUser->current_shift ?? 'Unassigned'),
            'is_on_duty' => $authUser->isOnDuty(),
            'active_tickets' => $authActiveTickets,
            'resolved_tickets' => $authResolvedTickets,
            'total_tickets' => $authTotalTickets,
            'system_active_tickets' => $authSystemActive,
            'tickets_url' => $authTicketsUrl,
            'active_tickets_url' => $authActiveTicketsUrl,
            'is_me' => true,
            'profile_url' => route('profile.edit'),
        ];
    @endphp

    {{-- User Profile & Active Tickets Modal (Matches Mobile App UI) --}}
    <div x-data="userProfileModal(@js($authProfileData))"
         x-show="isOpen"
         x-cloak
         @open-user-profile.window="openModal($event.detail)"
         @keydown.escape.window="closeModal()"
         class="fixed inset-0 z-[9999] flex items-center justify-center p-4 sm:p-6 overflow-y-auto"
         style="padding-top: 2.5rem; padding-bottom: 4.5rem;"
         role="dialog"
         aria-modal="true">

        {{-- Backdrop --}}
        <div x-show="isOpen"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="closeModal()"
             class="fixed inset-0 bg-slate-950/75 backdrop-blur-xs transition-opacity"></div>

        {{-- Modal Dialog --}}
        <div x-show="isOpen"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-3 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-3 sm:scale-95"
             class="relative w-full max-w-sm sm:max-w-md bg-white dark:bg-[#0f172a] rounded-3xl shadow-2xl border border-slate-200/90 dark:border-slate-800/90 overflow-hidden z-10 transition-all text-slate-800 dark:text-slate-100 my-auto mb-8 sm:mb-10">

            {{-- Top Hero Gradient with Ambient Circles --}}
            <div class="relative h-24 sm:h-28 bg-gradient-to-r from-indigo-600 via-indigo-700 to-purple-800 p-4 flex items-start justify-between overflow-hidden">
                <div class="absolute -top-10 -right-10 w-36 h-36 rounded-full bg-white/10 blur-xl pointer-events-none"></div>
                <div class="absolute -bottom-8 -left-8 w-32 h-32 rounded-full bg-indigo-400/20 blur-lg pointer-events-none"></div>

                <div class="relative z-10 flex items-center gap-2 text-white/95">
                    <svg class="w-4 h-4 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span class="text-xs font-bold uppercase tracking-wider text-indigo-100">
                        {{ app()->getLocale() === 'bn' ? 'প্রোফাইল বিবরণ' : 'Profile Details' }}
                    </span>
                </div>

                <button type="button"
                        @click="closeModal()"
                        class="relative z-10 w-7 h-7 rounded-full bg-black/25 hover:bg-black/40 text-white flex items-center justify-center transition-colors cursor-pointer"
                        title="{{ __('Close') }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Avatar & Identity Section --}}
            <div class="px-5 sm:px-6 pt-0 pb-6 sm:pb-7">
                <div class="flex flex-col items-center -mt-12 sm:-mt-14 text-center">
                    {{-- Avatar Container with Precision Positioned Duty Status Dot --}}
                    <div style="position: relative; display: inline-block; margin-bottom: 10px;">
                        <img :src="profile.avatar_url"
                             :alt="profile.name"
                             class="w-20 h-20 sm:w-24 sm:h-24 rounded-full object-cover border-4 border-white dark:border-[#0f172a] shadow-xl bg-slate-100 dark:bg-slate-800"
                             style="display: block;">

                        {{-- Status Dot (Locked at bottom-right of avatar) --}}
                        <span x-show="profile.is_on_duty"
                              style="position: absolute; right: 4px; bottom: 4px; width: 18px; height: 18px; border-radius: 9999px; display: flex; align-items: center; justify-content: center; z-index: 10; box-shadow: 0 1px 3px rgba(0,0,0,0.3);"
                              class="bg-emerald-500 border-2 border-white dark:border-[#0f172a]"
                              title="{{ app()->getLocale() === 'bn' ? 'অন ডিউটি' : 'On Duty' }}">
                            <span class="animate-pulse" style="display: block; width: 6px; height: 6px; border-radius: 9999px; background-color: #ffffff;"></span>
                        </span>
                        <span x-show="!profile.is_on_duty"
                              style="position: absolute; right: 4px; bottom: 4px; width: 18px; height: 18px; border-radius: 9999px; display: flex; align-items: center; justify-content: center; z-index: 10; box-shadow: 0 1px 3px rgba(0,0,0,0.3);"
                              class="bg-slate-400 dark:bg-slate-500 border-2 border-white dark:border-[#0f172a]"
                              title="{{ app()->getLocale() === 'bn' ? 'অফ ডিউটি' : 'Off Duty' }}">
                            <span style="display: block; width: 5px; height: 5px; border-radius: 9999px; background-color: #ffffff;"></span>
                        </span>
                    </div>

                    {{-- User Name --}}
                    <h3 class="text-lg sm:text-xl font-black text-slate-900 dark:text-white leading-snug tracking-tight" x-text="profile.name"></h3>

                    {{-- Contact Info (Email & Phone) with clean badges --}}
                    <div class="flex flex-wrap items-center justify-center gap-1.5 mt-2 text-xs">
                        <a :href="'mailto:' + profile.email"
                           class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100/90 dark:bg-slate-800/80 hover:bg-slate-200 dark:hover:bg-slate-700/80 text-slate-600 dark:text-slate-300 border border-slate-200/60 dark:border-slate-700/60 transition-colors">
                            <svg class="w-3.5 h-3.5 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <span x-text="profile.email" class="font-medium truncate max-w-[190px]"></span>
                        </a>
                        <span x-show="profile.phone"
                              class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100/90 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300 border border-slate-200/60 dark:border-slate-700/60">
                            <svg class="w-3.5 h-3.5 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            <span x-text="profile.phone" class="font-medium"></span>
                        </span>
                    </div>

                    {{-- Role & Team Badges --}}
                    <div class="flex flex-wrap items-center justify-center gap-2 mt-2.5">
                        <span class="px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider border shadow-2xs"
                              :class="roleBadgeClass()"
                              x-text="profile.role_label"></span>

                        <span x-show="profile.team"
                              class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                            <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span x-text="profile.team"></span>
                        </span>
                    </div>
                </div>

                {{-- Ticket Stats (Active, Resolved, Total in 3 Columns Side-by-Side!) --}}
                <div style="margin-top: 22px;">
                    {{-- Header with generous bottom gap --}}
                    <div class="flex items-center justify-between px-1" style="margin-bottom: 14px;">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                            {{ app()->getLocale() === 'bn' ? 'টিকিট পরিসংখ্যান' : 'Ticket Overview' }}
                        </span>
                        <a :href="profile.tickets_url" class="text-[11px] font-semibold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-0.5">
                            {{ app()->getLocale() === 'bn' ? 'সব টিকিট' : 'View all' }} →
                        </a>
                    </div>

                    {{-- 3-Column Cards Grid with ample bottom gap --}}
                    <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; margin-bottom: 18px;">
                        {{-- Active Tickets (Interactive & Perfectly Centered) --}}
                        <a :href="profile.active_tickets_url"
                           style="position: relative; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center;"
                           class="group relative bg-indigo-50/70 hover:bg-indigo-100/80 dark:bg-indigo-950/40 dark:hover:bg-indigo-900/50 border border-indigo-200/90 dark:border-indigo-700/60 rounded-2xl p-2.5 sm:p-3 transition-all hover:scale-[1.02] shadow-xs cursor-pointer">
                            
                            {{-- Top-Right Pulse Indicator Badge --}}
                            <span style="position: absolute; top: 8px; right: 8px; display: flex; height: 8px; width: 8px;">
                                <span class="animate-ping" style="position: absolute; display: inline-flex; height: 100%; width: 100%; border-radius: 9999px; background-color: #818cf8; opacity: 0.75;"></span>
                                <span style="position: relative; display: inline-flex; border-radius: 9999px; height: 8px; width: 8px; background-color: #6366f1;"></span>
                            </span>

                            {{-- Number (100% Centered) --}}
                            <span class="text-xl sm:text-2xl font-black text-indigo-600 dark:text-indigo-400 tracking-tight mb-1" x-text="profile.active_tickets"></span>

                            {{-- Label (100% Centered) --}}
                            <span class="text-[10px] sm:text-[11px] font-bold text-indigo-600 dark:text-indigo-300 flex items-center justify-center gap-0.5">
                                <span>{{ app()->getLocale() === 'bn' ? 'সক্রিয়' : 'Active' }}</span>
                                <svg class="w-3 h-3 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                            </span>
                        </a>

                        {{-- Resolved Tickets (100% Centered) --}}
                        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center;"
                             class="bg-slate-50/80 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-2.5 sm:p-3">
                            <span class="text-xl sm:text-2xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight mb-1" x-text="profile.resolved_tickets"></span>
                            <span class="text-[10px] sm:text-[11px] font-semibold text-slate-500 dark:text-slate-400">
                                {{ app()->getLocale() === 'bn' ? 'সমাধানকৃত' : 'Resolved' }}
                            </span>
                        </div>

                        {{-- Total Tickets (100% Centered) --}}
                        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center;"
                             class="bg-slate-50/80 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-2.5 sm:p-3">
                            <span class="text-xl sm:text-2xl font-black text-slate-800 dark:text-slate-200 tracking-tight mb-1" x-text="profile.total_tickets"></span>
                            <span class="text-[10px] sm:text-[11px] font-semibold text-slate-500 dark:text-slate-400">
                                {{ app()->getLocale() === 'bn' ? 'মোট টিকিট' : 'Total' }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Duty Shift Card (For Staff / NOC) --}}
                <div style="margin-bottom: 20px;" class="bg-slate-50/80 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-3 flex items-center justify-between">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                                {{ app()->getLocale() === 'bn' ? 'ডিউটি শিফট' : 'Duty Shift' }}
                            </p>
                            <p class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate mt-0.5" x-text="profile.current_shift_label"></p>
                        </div>
                    </div>

                    <div class="flex-shrink-0">
                        <span x-show="profile.is_on_duty"
                              class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/50 shadow-2xs">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            {{ app()->getLocale() === 'bn' ? 'অন ডিউটি' : 'On Duty' }}
                        </span>
                        <span x-show="!profile.is_on_duty"
                              class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                            {{ app()->getLocale() === 'bn' ? 'অফ ডিউটি' : 'Off Duty' }}
                        </span>
                    </div>
                </div>

                {{-- Action Buttons with Good Spacing & Generous Bottom Gap --}}
                <div class="flex items-center gap-2.5" style="margin-bottom: 6px;">
                    <a :href="profile.active_tickets_url"
                       class="flex-1 flex items-center justify-center gap-2 py-2.5 px-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-xs font-bold transition-all shadow-md shadow-indigo-600/20 hover:shadow-indigo-600/30 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                        </svg>
                        <span>{{ app()->getLocale() === 'bn' ? 'সক্রিয় টিকিট দেখুন' : 'View Active Tickets' }}</span>
                    </a>

                    <a :href="profile.profile_url"
                       class="flex-1 flex items-center justify-center gap-2 py-2.5 px-3.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 active:bg-slate-300 dark:active:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-bold border border-slate-200 dark:border-slate-700 transition-all cursor-pointer shadow-xs">
                        <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        <span>{{ app()->getLocale() === 'bn' ? 'প্রোফাইল সম্পাদনা' : 'Edit Profile' }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
    function userProfileModal(initialData) {
        return {
            isOpen: false,
            isLoading: false,
            profile: initialData || {},

            init() {
                window.showUserProfileModal = (userId = null) => {
                    this.openModal(userId);
                };
            },

            openModal(userId = null) {
                this.isOpen = true;
                if (!userId || (this.profile && userId === this.profile.id)) {
                    this.fetchData(null);
                } else {
                    this.fetchData(userId);
                }
            },

            closeModal() {
                this.isOpen = false;
            },

            fetchData(userId) {
                this.isLoading = true;
                const url = userId ? `{{ url('/profile/card-data') }}/${userId}` : `{{ url('/profile/card-data') }}`;
                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data && data.name) {
                        this.profile = data;
                    }
                })
                .catch(() => {})
                .finally(() => {
                    this.isLoading = false;
                });
            },

            roleBadgeClass() {
                const r = (this.profile.role || '').toLowerCase();
                if (r === 'super_admin') {
                    return 'bg-purple-100 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border-purple-200 dark:border-purple-800/50';
                } else if (r === 'admin') {
                    return 'bg-red-100 dark:bg-red-950/60 text-red-700 dark:text-red-300 border-red-200 dark:border-red-800/50';
                } else if (r === 'noc') {
                    return 'bg-blue-100 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800/50';
                } else if (r === 'reseller') {
                    return 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/50';
                }
                return 'bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800/50';
            }
        };
    }
    </script>
    @endauth

    {{-- Auto-submit filter forms on <select> change: add data-auto-filter to the form --}}
    <script>
    document.addEventListener('change', function (e) {
        const el = e.target;
        if (el.tagName !== 'SELECT') return;
        const form = el.closest('form[data-auto-filter]');
        if (!form) return;
        form.submit();
    });
    </script>
    </body>
</html>
