<x-app-layout>
    @php
        $hour = now()->hour;
        if ($hour >= 5 && $hour < 12) {
            $greeting = 'Good morning';
        } elseif ($hour >= 12 && $hour < 18) {
            $greeting = 'Good afternoon';
        } else {
            $greeting = 'Good evening';
        }
        $user = auth()->user();
    @endphp

    <style>
        /* Exact Reference Layout Engine */
        .dash-top-grid {
            display: grid !important;
            grid-template-columns: repeat(8, minmax(0, 1fr)) !important;
            gap: 10px !important;
            margin-bottom: 20px !important;
        }
        @media (max-width: 1200px) {
            .dash-top-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
                gap: 12px !important;
            }
        }
        @media (max-width: 640px) {
            .dash-top-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                gap: 8px !important;
            }
        }

        .dash-middle-grid {
            display: grid !important;
            grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
            gap: 20px !important;
            margin-bottom: 20px !important;
        }
        .dash-trend-col {
            grid-column: span 2 / span 2 !important;
        }
        .dash-cat-col {
            grid-column: span 1 / span 1 !important;
        }
        @media (max-width: 1024px) {
            .dash-middle-grid {
                grid-template-columns: 1fr !important;
            }
            .dash-trend-col,
            .dash-cat-col {
                grid-column: span 1 / span 1 !important;
            }
        }

        .dash-bottom-grid {
            display: grid !important;
            grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
            gap: 20px !important;
            margin-bottom: 20px !important;
        }
        @media (max-width: 1024px) {
            .dash-bottom-grid {
                grid-template-columns: 1fr !important;
            }
        }

        .dash-squads-grid {
            display: grid !important;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)) !important;
            gap: 16px !important;
        }
        @media (max-width: 1400px) {
            .dash-squads-grid {
                grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)) !important;
            }
        }
        @media (max-width: 860px) {
            .dash-squads-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                gap: 12px !important;
            }
        }
        @media (max-width: 540px) {
            .dash-squads-grid {
                grid-template-columns: 1fr !important;
                gap: 10px !important;
            }
        }

        .dash-card {
            border-radius: 1rem !important;
            padding: 20px 22px !important;
            height: auto !important;
            min-height: 0 !important;
            width: 100%;
            box-sizing: border-box;
            transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1), 
                        box-shadow 0.22s cubic-bezier(0.16, 1, 0.3, 1), 
                        border-color 0.22s ease !important;
            will-change: transform, box-shadow;
        }
        .dash-card:hover {
            transform: translateY(-2.5px) !important;
            box-shadow: 0 12px 26px -6px rgba(0, 0, 0, 0.08), 0 6px 12px -4px rgba(0, 0, 0, 0.04) !important;
            border-color: color-mix(in srgb, var(--cp, #4f46e5) 30%, #cbd5e1);
        }
        .dash-top-card {
            border-radius: 1rem !important;
            padding: 14px 12px !important;
            height: auto !important;
            min-height: 0 !important;
            width: 100%;
            box-sizing: border-box;
            transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1), 
                        box-shadow 0.22s cubic-bezier(0.16, 1, 0.3, 1), 
                        border-color 0.22s ease !important;
            will-change: transform, box-shadow;
        }
        .dash-top-card:hover {
            transform: translateY(-2.5px) !important;
            box-shadow: 0 12px 26px -6px rgba(0, 0, 0, 0.08), 0 6px 12px -4px rgba(0, 0, 0, 0.04) !important;
            border-color: color-mix(in srgb, var(--cp, #4f46e5) 30%, #cbd5e1);
        }

        /* Refined, Elegant Dark Mode Card Engine */
        html.dark .dash-card {
            background-color: #1e293b !important;
            border: 1px solid rgba(51, 65, 85, 0.8) !important;
            box-shadow: 0 4px 14px -2px rgba(0, 0, 0, 0.25) !important;
        }
        html.dark .dash-card:hover {
            transform: translateY(-2.5px) !important;
            border-color: color-mix(in srgb, var(--cp, #4f46e5) 45%, #475569);
            box-shadow: 0 14px 28px -6px rgba(0, 0, 0, 0.45) !important;
        }
        html.dark .dash-top-card {
            background-color: #1e293b !important;
            border: 1px solid rgba(51, 65, 85, 0.8) !important;
            box-shadow: 0 4px 14px -2px rgba(0, 0, 0, 0.25) !important;
        }
        html.dark .dash-top-card:hover {
            transform: translateY(-2.5px) !important;
            border-color: color-mix(in srgb, var(--cp, #4f46e5) 45%, #475569);
            box-shadow: 0 14px 28px -6px rgba(0, 0, 0, 0.45) !important;
        }
        html.dark .icon-squircle,
        html.dark .icon-squircle-sm {
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
        }
        html.dark .dash-squads-grid > div {
            background-color: #1a2336 !important;
            border-color: rgba(51, 65, 85, 0.7) !important;
        }
        html.dark .dash-squads-grid > div:hover {
            border-color: rgba(99, 102, 241, 0.45) !important;
        }

        .icon-squircle {
            width: 52px !important;
            height: 52px !important;
            border-radius: 16px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            flex-shrink: 0 !important;
        }
        .icon-squircle-sm {
            width: 42px !important;
            height: 42px !important;
            border-radius: 13px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            flex-shrink: 0 !important;
        }
        .dash-dots {
            width: 24px !important;
            height: 24px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            border-radius: 6px !important;
            cursor: pointer !important;
            transition: background-color 0.15s ease !important;
        }
        .dash-dots:hover {
            background-color: rgba(148, 163, 184, 0.15) !important;
        }

        /* Balanced Medium Greeting Typography */
        .dash-greeting-title {
            font-size: 21px !important;
            line-height: 1.25 !important;
            font-weight: 800 !important;
            letter-spacing: -0.02em !important;
        }
        @media (min-width: 640px) {
            .dash-greeting-title {
                font-size: 24px !important;
            }
        }
        @media (min-width: 1024px) {
            .dash-greeting-title {
                font-size: 26px !important;
            }
        }
        .dash-greeting-sub {
            font-size: 13px !important;
            line-height: 1.45 !important;
            font-weight: 500 !important;
            margin-top: 3px !important;
        }
        @media (min-width: 640px) {
            .dash-greeting-sub {
                font-size: 14px !important;
            }
        }
        .dash-live-badge {
            font-size: 11px !important;
            font-weight: 700 !important;
            padding: 4px 11px !important;
            border-radius: 9999px !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
        }
    </style>

    {{-- Top Greeting Bar (Prominent, Bold Typography) --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1.5 flex-wrap">
                <h3 class="dash-greeting-title text-slate-900 dark:text-white">
                    <span id="dash-greeting-text">{{ __($greeting) }}</span>, {{ $user->name }}!
                </h3>
                <span class="dash-live-badge bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30 shadow-2xs">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse inline-block"></span>
                    <span>{{ __('Live System') }}</span>
                </span>
            </div>
            <p class="dash-greeting-sub text-slate-600 dark:text-slate-300">
                {{ __("Here's what's happening with your tickets and team operations today.") }}
            </p>
        </div>

        @if($user->isAdmin() && isset($resellersQuick) && $resellersQuick->isNotEmpty())
        <div class="flex items-center gap-2 flex-shrink-0">
            <span class="hidden sm:inline-flex px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700/80 shadow-xs">
                {{ __('RESELLER') }}
            </span>
            <select id="reseller_quick"
                    onchange="if (this.value) window.location = this.value"
                    class="text-xs bg-white dark:bg-[#131b2e] text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700/80 rounded-xl px-3 py-1.5 pr-8 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-xs cursor-pointer">
                <option value="">{{ __('View Reseller Report') }}</option>
                @foreach($resellersQuick as $reseller)
                <option value="{{ route('reports.index', ['tab' => 'reseller', 'person_id' => $reseller->id]) }}">{{ $reseller->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
    </div>

    {{-- TOP STAT CARDS (8 Cards in a Single Row on Desktop) --}}
    @php
        $gBase = $user->isReseller()
            ? \App\Models\Ticket::where('created_by', $user->id)
            : ($user->isNoc()
                ? \App\Models\Ticket::where('assigned_to', $user->id)
                : \App\Models\Ticket::query());
        $globalAll      = (clone $gBase)->count();
        $globalInProg   = (clone $gBase)->where('status', 'in_progress')->count();
        $globalPending  = (clone $gBase)->where('status', 'pending')->count();
        $globalWaiting  = (clone $gBase)->where('status', 'waiting_for_customer_feedback')->count();
        $globalResolved = (clone $gBase)->where('status', 'resolved')->count();
        $globalCritical = (clone $gBase)->whereIn('status', ['in_progress','pending','waiting_for_customer_feedback'])->where('priority', 'critical')->count();
        $globalOverdue  = (clone $gBase)->whereNotNull('due_at')->where('due_at', '<', now())->where('status', '!=', 'resolved')->count();
    @endphp

    {{-- Top Grid: 8 Cards in 1 Row --}}
    <div class="dash-top-grid">
        {{-- Card 1: All Tickets --}}
        <a href="{{ route('tickets.index') }}"
           class="dash-top-card bg-white dark:bg-[#131b2e] border border-slate-200/80 dark:border-slate-800/80 shadow-xs hover:shadow-md hover:border-indigo-300 dark:hover:border-indigo-500/40 flex items-center justify-between group">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="icon-squircle-sm bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <div class="min-w-0">
                    <p id="stat-g-all" class="text-xl sm:text-2xl font-black text-slate-800 dark:text-white leading-tight tracking-tight">{{ $globalAll }}</p>
                    <p class="text-[11px] font-semibold text-slate-400 dark:text-slate-400 mt-0.5 truncate">{{ __('All Tickets') }}</p>
                </div>
            </div>
            <div class="dash-dots text-slate-300 dark:text-slate-600 group-hover:text-slate-500 hidden 2xl:flex">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><circle cx="4" cy="10" r="1.8"/><circle cx="10" cy="10" r="1.8"/><circle cx="16" cy="10" r="1.8"/></svg>
            </div>
        </a>

        {{-- Card 2: In Progress --}}
        <a href="{{ route('tickets.index', ['status' => 'in_progress']) }}"
           class="dash-top-card bg-white dark:bg-[#131b2e] border border-slate-200/80 dark:border-slate-800/80 shadow-xs hover:shadow-md hover:border-blue-300 dark:hover:border-blue-500/40 flex items-center justify-between group">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="icon-squircle-sm bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </div>
                <div class="min-w-0">
                    <p id="stat-g-inprog" class="text-xl sm:text-2xl font-black text-slate-800 dark:text-white leading-tight tracking-tight">{{ $globalInProg }}</p>
                    <p class="text-[11px] font-semibold text-slate-400 dark:text-slate-400 mt-0.5 truncate">{{ __('In Progress') }}</p>
                </div>
            </div>
            <div class="dash-dots text-slate-300 dark:text-slate-600 group-hover:text-slate-500 hidden 2xl:flex">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><circle cx="4" cy="10" r="1.8"/><circle cx="10" cy="10" r="1.8"/><circle cx="16" cy="10" r="1.8"/></svg>
            </div>
        </a>

        {{-- Card 3: Pending --}}
        <a href="{{ route('tickets.index', ['status' => 'pending']) }}"
           class="dash-top-card bg-white dark:bg-[#131b2e] border border-slate-200/80 dark:border-slate-800/80 shadow-xs hover:shadow-md hover:border-amber-300 dark:hover:border-amber-500/40 flex items-center justify-between group">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="icon-squircle-sm bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="min-w-0">
                    <p id="stat-g-pending" class="text-xl sm:text-2xl font-black text-slate-800 dark:text-white leading-tight tracking-tight">{{ $globalPending }}</p>
                    <p class="text-[11px] font-semibold text-slate-400 dark:text-slate-400 mt-0.5 truncate">{{ __('Pending') }}</p>
                </div>
            </div>
            <div class="dash-dots text-slate-300 dark:text-slate-600 group-hover:text-slate-500 hidden 2xl:flex">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><circle cx="4" cy="10" r="1.8"/><circle cx="10" cy="10" r="1.8"/><circle cx="16" cy="10" r="1.8"/></svg>
            </div>
        </a>

        {{-- Card 4: Waiting Feedback --}}
        <a href="{{ route('tickets.index', ['status' => 'waiting_for_customer_feedback']) }}"
           class="dash-top-card bg-white dark:bg-[#131b2e] border border-slate-200/80 dark:border-slate-800/80 shadow-xs hover:shadow-md hover:border-purple-300 dark:hover:border-purple-500/40 flex items-center justify-between group">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="icon-squircle-sm bg-pink-50 dark:bg-pink-950/60 text-pink-600 dark:text-pink-400 group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </div>
                <div class="min-w-0">
                    <p id="stat-g-waiting" class="text-xl sm:text-2xl font-black text-slate-800 dark:text-white leading-tight tracking-tight">{{ $globalWaiting }}</p>
                    <p class="text-[11px] font-semibold text-slate-400 dark:text-slate-400 mt-0.5 truncate">{{ __('Waiting') }}</p>
                </div>
            </div>
            <div class="dash-dots text-slate-300 dark:text-slate-600 group-hover:text-slate-500 hidden 2xl:flex">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><circle cx="4" cy="10" r="1.8"/><circle cx="10" cy="10" r="1.8"/><circle cx="16" cy="10" r="1.8"/></svg>
            </div>
        </a>

        {{-- Card 5: Resolved --}}
        <a href="{{ route('tickets.index', ['status' => 'resolved']) }}"
           class="dash-top-card bg-white dark:bg-[#131b2e] border border-slate-200/80 dark:border-slate-800/80 shadow-xs hover:shadow-md hover:border-emerald-300 dark:hover:border-emerald-500/40 flex items-center justify-between group">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="icon-squircle-sm bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="min-w-0">
                    <p id="stat-g-resolved" class="text-xl sm:text-2xl font-black text-slate-800 dark:text-white leading-tight tracking-tight">{{ $globalResolved }}</p>
                    <p class="text-[11px] font-semibold text-slate-400 dark:text-slate-400 mt-0.5 truncate">{{ __('Resolved') }}</p>
                </div>
            </div>
            <div class="dash-dots text-slate-300 dark:text-slate-600 group-hover:text-slate-500 hidden 2xl:flex">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><circle cx="4" cy="10" r="1.8"/><circle cx="10" cy="10" r="1.8"/><circle cx="16" cy="10" r="1.8"/></svg>
            </div>
        </a>

        {{-- Card 6: Critical --}}
        <a href="{{ route('tickets.index', ['priority' => 'critical']) }}"
           class="dash-top-card bg-white dark:bg-[#131b2e] border border-slate-200/80 dark:border-slate-800/80 shadow-xs hover:shadow-md hover:border-red-300 dark:hover:border-red-500/40 flex items-center justify-between group">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="icon-squircle-sm bg-red-50 dark:bg-red-950/60 text-red-600 dark:text-red-400 group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div class="min-w-0">
                    <p id="stat-g-critical" class="text-xl sm:text-2xl font-black text-slate-800 dark:text-white leading-tight tracking-tight">{{ $globalCritical }}</p>
                    <p class="text-[11px] font-semibold text-slate-400 dark:text-slate-400 mt-0.5 truncate">{{ __('Critical') }}</p>
                </div>
            </div>
            <div class="dash-dots text-slate-300 dark:text-slate-600 group-hover:text-slate-500 hidden 2xl:flex">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><circle cx="4" cy="10" r="1.8"/><circle cx="10" cy="10" r="1.8"/><circle cx="16" cy="10" r="1.8"/></svg>
            </div>
        </a>

        {{-- Card 7: SLA Overdue --}}
        <a href="{{ route('tickets.index', ['status' => 'overdue']) }}"
           class="dash-top-card bg-white dark:bg-[#131b2e] border border-slate-200/80 dark:border-slate-800/80 shadow-xs hover:shadow-md hover:border-rose-300 dark:hover:border-rose-500/40 flex items-center justify-between group">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="icon-squircle-sm bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="min-w-0">
                    <p id="stat-g-overdue" class="text-xl sm:text-2xl font-black text-slate-800 dark:text-white leading-tight tracking-tight">{{ $globalOverdue }}</p>
                    <p class="text-[11px] font-semibold text-slate-400 dark:text-slate-400 mt-0.5 truncate">{{ __('Overdue') }}</p>
                </div>
            </div>
            <div class="dash-dots text-slate-300 dark:text-slate-600 group-hover:text-slate-500 hidden 2xl:flex">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><circle cx="4" cy="10" r="1.8"/><circle cx="10" cy="10" r="1.8"/><circle cx="16" cy="10" r="1.8"/></svg>
            </div>
        </a>

        {{-- Card 8: Avg Resolution --}}
        <div class="dash-top-card bg-white dark:bg-[#131b2e] border border-slate-200/80 dark:border-slate-800/80 shadow-xs hover:shadow-md flex items-center justify-between group">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="icon-squircle-sm bg-teal-50 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400 group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div class="min-w-0">
                    <p id="stat-g-avg-resolution" class="text-lg sm:text-xl font-black text-teal-600 dark:text-teal-400 leading-tight tracking-tight truncate">{{ $stats['avg_resolution_time'] ?? 'N/A' }}</p>
                    <p class="text-[11px] font-semibold text-slate-400 dark:text-slate-400 mt-0.5 truncate">{{ __('Avg Resolution') }}</p>
                </div>
            </div>
            <div class="dash-dots text-slate-300 dark:text-slate-600 hidden 2xl:flex">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><circle cx="4" cy="10" r="1.8"/><circle cx="10" cy="10" r="1.8"/><circle cx="16" cy="10" r="1.8"/></svg>
            </div>
        </div>
    </div>

    {{-- MIDDLE SECTION: Analytics & Trends (Image Style: Wide Trend Line Chart + Donut Chart) --}}
    @if($user->isAdmin())
    <div class="dash-middle-grid">
        {{-- Left: Tickets Trend Chart (2/3 width) --}}
        <div class="dash-card dash-trend-col bg-white dark:bg-[#131b2e] border border-slate-200/80 dark:border-slate-800/80 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-extrabold text-slate-800 dark:text-white tracking-tight">
                        {{ __('Tickets Created Trend') }}
                    </h2>
                    <p class="text-xs text-slate-400 dark:text-slate-400 mt-0.5">
                        {{ __('Daily ticket volume comparison over the past 14 days') }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-slate-50 dark:bg-slate-800/90 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 shadow-2xs">
                        <span>{{ __('Last 14 Days') }}</span>
                        <i class="fas fa-chevron-down text-[10px] text-slate-400"></i>
                    </span>
                </div>
            </div>

            <div class="relative w-full" style="height: 250px;">
                <canvas id="lineChart"></canvas>
            </div>

            <div class="flex items-center justify-between pt-3.5 mt-2 border-t border-slate-100 dark:border-slate-800/80 text-xs">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                    <span class="font-medium text-slate-600 dark:text-slate-300">{{ __('Tickets Created') }}</span>
                </div>
                <span class="text-slate-400 font-medium">{{ __('Auto-updated') }}</span>
            </div>
        </div>

        {{-- Right: Tickets by Category Donut Chart (1/3 width) --}}
        <div class="dash-card dash-cat-col bg-white dark:bg-[#131b2e] border border-slate-200/80 dark:border-slate-800/80 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-base font-extrabold text-slate-800 dark:text-white tracking-tight">
                    {{ __('Tickets by Category') }}
                </h2>
                <div class="dash-dots text-slate-300 dark:text-slate-600 hover:text-slate-500">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><circle cx="4" cy="10" r="1.8"/><circle cx="10" cy="10" r="1.8"/><circle cx="16" cy="10" r="1.8"/></svg>
                </div>
            </div>

            @php $catTotal = $chartByCategory->sum(); @endphp
            @if($chartByCategory->isEmpty())
                <div class="h-48 flex items-center justify-center text-sm text-slate-400">{{ __('No ticket data yet.') }}</div>
            @else
                <div class="relative mx-auto my-3 flex-shrink-0" style="width: 160px; height: 160px;">
                    <canvas id="categoryChart" width="160" height="160"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                        <span class="text-2xl font-black text-slate-800 dark:text-white leading-none">{{ $catTotal }}</span>
                        <span class="text-[9px] text-slate-400 uppercase tracking-widest font-bold mt-1">{{ __('TOTAL') }}</span>
                    </div>
                </div>

                {{-- Clean Category List Legend (Like Image) --}}
                <div class="space-y-2 pt-3 border-t border-slate-100 dark:border-slate-800/80 mt-1">
                    @foreach($chartByCategory->sortDesc()->take(4) as $label => $count)
                    @php $pct = $catTotal ? round($count / $catTotal * 100) : 0; @endphp
                    <div class="flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background-color: var(--cat-color-{{ $loop->index % 7 }})"></span>
                            <span class="text-slate-600 dark:text-slate-300 truncate font-medium">{{ ucfirst(str_replace('_',' ',$label)) }}</span>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <span class="text-slate-400 font-medium">{{ $pct }}%</span>
                            <span class="font-bold text-slate-800 dark:text-white w-6 text-right">{{ $count }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
    @elseif($user->isNoc())
    {{-- NOC: Resolutions bar chart --}}
    <div class="dash-card bg-white dark:bg-[#131b2e] border border-slate-200/80 dark:border-slate-800/80 mb-6 shadow-xs">
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100 dark:border-slate-800/80">
            <h2 class="text-base font-extrabold text-slate-800 dark:text-white">
                {{ __('My Resolutions (Last 7 Days)') }}
            </h2>
            <span class="text-xs font-semibold text-slate-400">{{ __('Resolutions') }}</span>
        </div>
        <div style="height: 180px;">
            <canvas id="nocBarChart"></canvas>
        </div>
    </div>
    @endif

    {{-- BOTTOM SECTION: 3 Columns (Image Style: Division List + Leaderboard / Time + Featured Purple Card) --}}
    @if(!$user->isReseller())
        @php
            $teams = \App\Models\User::TEAMS;
            $allStaff = \App\Models\User::whereNotNull('team')->get();
            $teamConfig = [
                'IT Team' => [
                    'label'       => 'IT Team',
                    'icon'        => '⚡',
                    'icon_bg'     => 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400',
                    'bar_clr'     => 'bg-emerald-500',
                ],
                'NOC team' => [
                    'label'       => 'NOC Team',
                    'icon'        => '📡',
                    'icon_bg'     => 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400',
                    'bar_clr'     => 'bg-indigo-500',
                ],
                'Call center' => [
                    'label'       => 'Call Center',
                    'icon'        => '🎧',
                    'icon_bg'     => 'bg-cyan-50 dark:bg-cyan-950/60 text-cyan-600 dark:text-cyan-400',
                    'bar_clr'     => 'bg-cyan-500',
                ],
                'Supervisor Team' => [
                    'label'       => 'Supervisor Team',
                    'icon'        => '🛡️',
                    'icon_bg'     => 'bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400',
                    'bar_clr'     => 'bg-purple-500',
                ],
            ];
            $totalActiveStaff = $allStaff->filter(fn($u) => $u->isOnDuty())->count();
        @endphp

        <div class="dash-bottom-grid">
            {{-- Column 1: Live Team Duty (Like "Patients By Division" in Image) --}}
            <div class="dash-card bg-white dark:bg-[#131b2e] border border-slate-200/80 dark:border-slate-800/80 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-sm font-bold">
                                👥
                            </span>
                            <div>
                                <h3 class="text-sm font-extrabold text-slate-800 dark:text-white">
                                    {{ __('Team Duty & Roster') }}
                                </h3>
                                <p class="text-[11px] text-slate-400">{{ __('By Team Division') }}</p>
                            </div>
                        </div>
                        <a href="{{ route('roster.index') }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                            {{ __('Roster') }} ⌵
                        </a>
                    </div>

                    {{-- Mini Table Header (Exact Match to Image: DIVISION / PT.) --}}
                    <div class="flex items-center justify-between text-[10px] font-bold text-slate-400 uppercase tracking-wider py-1.5 border-b border-slate-100 dark:border-slate-800 mb-2">
                        <span>{{ __('DIVISION') }}</span>
                        <span>{{ __('ON DUTY') }}</span>
                    </div>

                    {{-- Division Rows --}}
                    <div class="divide-y divide-slate-100 dark:divide-slate-800/70">
                        @foreach($teams as $teamKey => $teamLabel)
                            @php
                                $tUsers = $allStaff->filter(fn($u) => $u->team === $teamKey);
                                $tot    = $tUsers->count();
                                $act    = $tUsers->filter(fn($u) => $u->isOnDuty())->count();
                                $pct    = $tot > 0 ? round(($act / $tot) * 100) : 0;
                                $cfg    = $teamConfig[$teamKey] ?? ['icon' => '👥', 'icon_bg' => 'bg-slate-100 text-slate-700', 'bar_clr' => 'bg-indigo-500'];
                            @endphp
                            <div class="py-2.5 flex items-center justify-between group">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 rounded-xl {{ $cfg['icon_bg'] }} flex items-center justify-center text-xs flex-shrink-0 shadow-2xs">
                                        {!! $cfg['icon'] !!}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate group-hover:text-indigo-500 transition-colors">
                                            {{ $teamLabel }}
                                        </p>
                                        <div class="w-20 bg-slate-100 dark:bg-slate-800 rounded-full h-1 mt-1 overflow-hidden">
                                            <div class="{{ $cfg['bar_clr'] }} h-1 rounded-full" style="width: {{ $pct }}%"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <span class="text-xs font-extrabold {{ $act > 0 ? 'text-slate-800 dark:text-white' : 'text-slate-400' }}">
                                        {{ $act }}/{{ $tot }}
                                    </span>
                                    <span class="block text-[10px] text-slate-400 leading-none mt-0.5">{{ $pct }}%</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <a href="{{ route('roster.index') }}" class="w-full mt-3 py-2 text-center text-xs font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50/70 dark:bg-indigo-950/40 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 rounded-xl transition-colors">
                    {{ __('View Full Roster Schedule') }} &rarr;
                </a>
            </div>

            {{-- Column 2: NOC Leaderboard (Rankings & Resolution) --}}
            <div class="dash-card bg-white dark:bg-[#131b2e] border border-slate-200/80 dark:border-slate-800/80 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-sm font-bold">
                                🏆
                            </span>
                            <div>
                                <h3 class="text-sm font-extrabold text-slate-800 dark:text-white">
                                    {{ __('NOC Leaderboard') }}
                                </h3>
                                <p class="text-[11px] text-slate-400">{{ __('Top Resolvers This Month') }}</p>
                            </div>
                        </div>
                        <span class="text-xs font-semibold text-slate-400">{{ __('This Month') }} ⌵</span>
                    </div>

                    {{-- Mini Table Header --}}
                    <div class="flex items-center justify-between text-[10px] font-bold text-slate-400 uppercase tracking-wider py-1.5 border-b border-slate-100 dark:border-slate-800 mb-2">
                        <span>{{ __('STAFF MEMBER') }}</span>
                        <span>{{ __('RESOLVED / TIME') }}</span>
                    </div>

                    @if(isset($nocLeaderboard) && $nocLeaderboard->isNotEmpty())
                        <div class="divide-y divide-slate-100 dark:divide-slate-800/70">
                            @foreach($nocLeaderboard->take(4) as $noc)
                            <div class="py-2.5 flex items-center justify-between">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/70 text-indigo-700 dark:text-indigo-300 font-bold text-xs flex items-center justify-center flex-shrink-0">
                                        {{ strtoupper(substr($noc->name, 0, 2)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate">{{ $noc->name }}</p>
                                        <p class="text-[10px] text-slate-400">⏱️ {{ $noc->avg_resolution_time }}</p>
                                    </div>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                        ✓ {{ $noc->resolved_count }}
                                    </span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-10 text-center text-xs text-slate-400">
                            {{ __('No leaderboard data for this month.') }}
                        </div>
                    @endif
                </div>

                <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800/70 text-[11px] text-slate-400 flex items-center justify-between">
                    <span>{{ __('Ranked by tickets resolved') }}</span>
                    <span class="text-indigo-500 font-medium">ISP Tickets</span>
                </div>
            </div>

            {{-- Column 3: Featured Light-Gradient Card (Replaced Dark Purple with Light Sky/Cyan/Indigo Gradient) --}}
            <div class="dash-card bg-white dark:bg-[#131b2e] border border-slate-200/80 dark:border-slate-800/80 shadow-xs flex flex-col justify-between relative overflow-hidden">
                @if(isset($myTechnicianTeam) && $myTechnicianTeam)
                    @php
                        $myMeta = $myTechnicianTeam->getCategoryMeta();
                        $isLeader = $myTechnicianTeam->leader_id === $user->id;
                    @endphp
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-300 border border-indigo-200/70 dark:border-indigo-500/25">
                                🛵 {{ __('My Squad Today') }}
                            </span>
                            @if($isLeader)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-100 dark:bg-amber-500/15 text-amber-700 dark:text-amber-300 border border-amber-200/70 dark:border-amber-500/25">
                                    👑 {{ __('Leader') }}
                                </span>
                            @endif
                        </div>

                        <h3 class="text-2xl font-black text-slate-900 dark:text-white leading-tight mb-1">
                            {{ $myTechnicianTeam->team_name }}
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1.5 mb-4 font-medium">
                            <span>📍 {{ $myTechnicianTeam->area ?? __('Field Operations') }}</span>
                            <span>•</span>
                            <span>{{ __($myMeta['label']) }}</span>
                        </p>

                        {{-- Teammates Mini Badges --}}
                        <div class="space-y-2">
                            @if($myTechnicianTeam->leader)
                            <div class="flex items-center justify-between bg-slate-50 dark:bg-slate-800/60 rounded-xl px-2.5 py-1.5 border border-slate-200 dark:border-slate-700/60" style="margin-bottom: 6px !important;">
                                <span class="text-xs font-bold text-slate-800 dark:text-slate-100 truncate">👑 {{ $myTechnicianTeam->leader->name }}</span>
                                @if($myTechnicianTeam->leader->phone)
                                    <a href="tel:{{ $myTechnicianTeam->leader->phone }}" class="w-6 h-6 rounded-lg bg-emerald-500 text-white hover:bg-emerald-600 flex items-center justify-center transition-colors shadow-xs" title="{{ __('Call') }}: {{ $myTechnicianTeam->leader->phone }}">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 4V3z"/></svg>
                                    </a>
                                @endif
                            </div>
                            @endif

                            @if($myTechnicianTeam->member1)
                            <div class="flex items-center justify-between bg-slate-50 dark:bg-slate-800/60 rounded-xl px-2.5 py-1.5 border border-slate-200 dark:border-slate-700/60" style="margin-bottom: 6px !important;">
                                <span class="text-xs font-bold text-slate-800 dark:text-slate-100 truncate">1. {{ $myTechnicianTeam->member1->name }}</span>
                                @if($myTechnicianTeam->member1->phone)
                                    <a href="tel:{{ $myTechnicianTeam->member1->phone }}" class="w-6 h-6 rounded-lg bg-emerald-500 text-white hover:bg-emerald-600 flex items-center justify-center transition-colors shadow-xs" title="{{ __('Call') }}: {{ $myTechnicianTeam->member1->phone }}">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 4V3z"/></svg>
                                    </a>
                                @endif
                            </div>
                            @endif

                            @if($myTechnicianTeam->member2)
                            <div class="flex items-center justify-between bg-slate-50 dark:bg-slate-800/60 rounded-xl px-2.5 py-1.5 border border-slate-200 dark:border-slate-700/60">
                                <span class="text-xs font-bold text-slate-800 dark:text-slate-100 truncate">2. {{ $myTechnicianTeam->member2->name }}</span>
                                @if($myTechnicianTeam->member2->phone)
                                    <a href="tel:{{ $myTechnicianTeam->member2->phone }}" class="w-6 h-6 rounded-lg bg-emerald-500 text-white hover:bg-emerald-600 flex items-center justify-center transition-colors shadow-xs" title="{{ __('Call') }}: {{ $myTechnicianTeam->member2->phone }}">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 4V3z"/></svg>
                                    </a>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>

                    <a href="{{ route('technician-teams.index') }}" class="w-full mt-4 py-2.5 text-center text-xs font-black text-white bg-indigo-600 hover:bg-indigo-500 rounded-xl transition-all shadow-sm shadow-indigo-600/25">
                        {{ __('Open Squad Details') }} &rarr;
                    </a>
                @else
                    <div class="flex flex-col gap-4">
                        {{-- Row 1: Header chips --}}
                        <div class="flex items-center justify-between">
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-300 border border-indigo-200/70 dark:border-indigo-500/25">
                                🛵 {{ __('Field Squads') }}
                            </span>
                            <span class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800/80 flex items-center justify-center text-sm text-indigo-500 dark:text-indigo-300">
                                <i class="fas fa-bolt"></i>
                            </span>
                        </div>

                        {{-- Row 2: Big count + sub label --}}
                        <div>
                            <p class="text-4xl sm:text-5xl font-black text-slate-900 dark:text-white leading-none">
                                {{ $todayTechnicianTeams->count() ?? 0 }}
                            </p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 font-bold tracking-wide mt-2">
                                {{ __('Field Squads Active Today') }}
                            </p>
                        </div>

                        {{-- Row 3: Category breakdown chips --}}
                        @if(isset($todayTechnicianTeams) && $todayTechnicianTeams->isNotEmpty())
                            @php
                                $catSortPriority = ['complain' => 1, 'new_connection' => 2, 'line_transfer' => 3, 'transfer' => 3];
                                $badgeSortedTeams = $todayTechnicianTeams->sortBy(fn($t) => [$catSortPriority[$t->category] ?? 99, $t->id])->values();
                            @endphp
                            <div class="flex items-center gap-2 flex-wrap">
                                @foreach($badgeSortedTeams->groupBy('category') as $catKey => $cTeams)
                                    @php $cMeta = $cTeams->first()->getCategoryMeta(); @endphp
                                    <span class="inline-flex items-center gap-1.5 text-[11px] font-bold px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800/80 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700/60">
                                        <span>{{ $cMeta['icon'] ?? '👷' }}</span>
                                        <span>{{ $cTeams->count() }} {{ __($cMeta['label']) }}</span>
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        {{-- Divider --}}
                        <div class="border-t border-dashed border-slate-200 dark:border-slate-700/60"></div>

                        {{-- Row 4: Today's Leaders avatar stack --}}
                        @php
                            $leaderList = $todayTechnicianTeams->pluck('leader')->filter()->values();
                            $totalActive = $todayTechnicianTeams->sum(fn($t) => $t->leader_open_count ?? 0);
                            $totalDone   = $todayTechnicianTeams->sum(fn($t) => $t->leader_done_count ?? 0);
                        @endphp
                        <div class="flex items-center gap-3">
                            <div class="flex -space-x-2">
                                @foreach($leaderList->take(5) as $ldr)
                                    <img src="{{ $ldr->avatarUrl() }}" alt="{{ $ldr->name }}" title="{{ $ldr->name }}"
                                         class="w-9 h-9 rounded-full border-2 border-white dark:border-[#131b2e] shadow-xs object-cover">
                                @endforeach
                                @if($leaderList->count() > 5)
                                    <span class="w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-800 border-2 border-white dark:border-[#131b2e] text-slate-700 dark:text-slate-200 text-[11px] font-black flex items-center justify-center shadow-xs">
                                        +{{ $leaderList->count() - 5 }}
                                    </span>
                                @endif
                            </div>
                            <div class="leading-tight">
                                <div class="text-xs font-bold text-slate-700 dark:text-slate-200">{{ __("Today's Leaders") }}</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 font-semibold mt-0.5">{{ $leaderList->count() }} {{ __('on duty') }}</div>
                            </div>
                        </div>

                        {{-- Row 5: Live totals (Active · Done Today) --}}
                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-xl bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-200/70 dark:border-indigo-500/25 px-3 py-2.5">
                                <div class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-300">{{ __('Active') }}</div>
                                <div class="text-2xl font-black text-indigo-700 dark:text-indigo-200 leading-none mt-1">{{ $totalActive }}</div>
                            </div>
                            <div class="rounded-xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200/70 dark:border-emerald-500/25 px-3 py-2.5">
                                <div class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-300">{{ __('Done Today') }}</div>
                                <div class="text-2xl font-black text-emerald-700 dark:text-emerald-200 leading-none mt-1">{{ $totalDone }}</div>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('technician-teams.index') }}" class="w-full mt-5 py-2.5 text-center text-xs font-black text-white bg-indigo-600 hover:bg-indigo-500 active:scale-[0.99] rounded-xl transition-all shadow-sm shadow-indigo-600/25 flex items-center justify-center gap-1.5">
                        <span>{{ __('Manage / Create Squad') }}</span> &rarr;
                    </a>
                @endif
            </div>
        </div>
    @endif

    {{-- SECTION: Daily Summary — two side-by-side boxes (Today · Previous Day) --}}
    @if(isset($summaryToday) && isset($summaryPrev))
        @php
            $catColors = ['bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-300 border-rose-200/70 dark:border-rose-500/25',
                          'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 border-indigo-200/70 dark:border-indigo-500/25',
                          'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border-emerald-200/70 dark:border-emerald-500/25',
                          'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-300 border-amber-200/70 dark:border-amber-500/25',
                          'bg-sky-50 dark:bg-sky-500/10 text-sky-700 dark:text-sky-300 border-sky-200/70 dark:border-sky-500/25',
                          'bg-purple-50 dark:bg-purple-500/10 text-purple-700 dark:text-purple-300 border-purple-200/70 dark:border-purple-500/25',
                          'bg-teal-50 dark:bg-teal-500/10 text-teal-700 dark:text-teal-300 border-teal-200/70 dark:border-teal-500/25'];

            $renderBox = function ($summary, $title, $accent, $catColors) {
                $pretty = \Carbon\Carbon::parse($summary['date'])->format('d · M · Y');
                return compact('summary', 'title', 'accent', 'catColors', 'pretty');
            };
        @endphp

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
            @foreach([
                ['data' => $summaryToday, 'title' => __('Today'), 'accent' => 'from-indigo-500 to-sky-500', 'pill' => 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-300 border-indigo-500/20'],
                ['data' => $summaryPrev,  'title' => __('Previous Day'), 'accent' => 'from-slate-500 to-slate-700', 'pill' => 'bg-slate-500/10 text-slate-600 dark:text-slate-300 border-slate-500/20'],
            ] as $box)
                @php $s = $box['data']; $pretty = \Carbon\Carbon::parse($s['date'])->format('d · M · Y'); @endphp
                <div class="dash-card bg-white dark:bg-[#131b2e] border border-slate-200/80 dark:border-slate-800/80 shadow-xs">
                    <div class="flex items-center justify-between gap-3 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800/80">
                        <div class="flex items-center gap-3">
                            <div class="icon-squircle bg-linear-to-br {{ $box['accent'] }} text-white text-xl shadow-md">📋</div>
                            <div>
                                <h2 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2 flex-wrap">
                                    {{ $box['title'] }}
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold {{ $box['pill'] }} border">
                                        📅 {{ $pretty }}
                                    </span>
                                </h2>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Resolved tickets per technician') }}</p>
                            </div>
                        </div>
                        <span class="text-right">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ __('Day Total') }}</div>
                            <div class="text-2xl font-black text-slate-900 dark:text-white leading-none">{{ $s['day_total'] }}</div>
                        </span>
                    </div>

                    @if($s['categories']->isEmpty())
                        <div class="rounded-xl border border-dashed border-slate-200 dark:border-slate-700/60 bg-slate-50/40 dark:bg-slate-800/30 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                            {{ __('No resolved tickets on this date.') }}
                        </div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($s['categories'] as $catKey => $group)
                                @php
                                    $color = $catColors[$loop->index % count($catColors)];
                                    $catLabel = $group['label'] ?? ucwords(str_replace('_', ' ', $catKey ?: 'Uncategorized'));
                                    $catIcon = $group['icon'] ?? '';
                                @endphp
                                <div class="rounded-xl border {{ $color }} p-3">
                                    <div class="flex items-center justify-between pb-2 mb-2 border-b border-current/20">
                                        <h3 class="text-xs font-black uppercase tracking-wide truncate flex items-center gap-1.5">
                                            @if($catIcon)<span>{{ $catIcon }}</span>@endif
                                            <span>{{ __($catLabel) }}</span>
                                        </h3>
                                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-white/60 dark:bg-slate-900/40 whitespace-nowrap">
                                            {{ __('Total') }}: {{ $group['total'] }}
                                        </span>
                                    </div>
                                    <ol class="space-y-1 text-sm">
                                        @foreach($group['items'] as $i => $item)
                                            <li class="flex items-center justify-between gap-2">
                                                <span class="flex items-center gap-1.5 min-w-0">
                                                    <span class="text-[10px] font-bold opacity-70 w-4">{{ $i + 1 }}.</span>
                                                    <span class="font-semibold text-slate-800 dark:text-slate-100 truncate">{{ $item['name'] }}</span>
                                                </span>
                                                <span class="text-sm font-black tabular-nums">{{ str_pad($item['count'], 2, '0', STR_PAD_LEFT) }}</span>
                                            </li>
                                        @endforeach
                                    </ol>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach

            {{-- 3rd box: Month-to-Date (same row) --}}
            @isset($summaryMonthTotal)
            <div class="dash-card bg-white dark:bg-[#131b2e] border border-slate-200/80 dark:border-slate-800/80 shadow-xs">
                <div class="flex items-center justify-between gap-3 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800/80">
                    <div class="flex items-center gap-3">
                        <div class="icon-squircle bg-linear-to-br from-emerald-500 to-teal-600 text-white text-xl shadow-md">📅</div>
                        <div>
                            <h2 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2 flex-wrap">
                                {{ __('Month to Date') }}
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-300 border border-emerald-500/20">
                                    📅 {{ $summaryMonthLabel }}
                                </span>
                            </h2>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Total resolved tickets so far this month') }}</p>
                        </div>
                    </div>
                    <span class="text-right">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ __('Total') }}</div>
                        <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 leading-none">{{ $summaryMonthTotal }}</div>
                    </span>
                </div>

                <div class="flex flex-col items-center justify-center py-6">
                    <div class="text-6xl font-black bg-linear-to-br from-emerald-500 to-teal-600 bg-clip-text text-transparent leading-none">{{ $summaryMonthTotal }}</div>
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mt-3">{{ __('Resolved This Month') }}</div>
                    <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">{{ $summaryMonthLabel }}</div>
                </div>
            </div>
            @endisset
        </div>
    @endif

    {{-- SECTION: Today's Field Technician Teams (Full Grid matching Image Aesthetic) --}}
    @if(isset($todayTechnicianTeams))
        @php
            $catSortPriority = ['complain' => 1, 'new_connection' => 2, 'line_transfer' => 3, 'transfer' => 3];
            $sortedTodayTeams = $todayTechnicianTeams->sortBy([
                fn($a, $b) => ($b->leader_done_count ?? 0) <=> ($a->leader_done_count ?? 0),
                fn($a, $b) => ($catSortPriority[$a->category] ?? 99) <=> ($catSortPriority[$b->category] ?? 99),
                fn($a, $b) => $a->id <=> $b->id,
            ])->values();
            $topSolvedCount = (int) ($sortedTodayTeams->first()->leader_done_count ?? 0);
        @endphp
        <div x-data="{ squadFilter: 'all', showCreateTeamModal: false }" class="dash-card bg-white dark:bg-[#131b2e] border border-slate-200/80 dark:border-slate-800/80 mb-6 shadow-xs">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-5 pb-3 border-b border-slate-100 dark:border-slate-800/80">
                <div class="flex items-center gap-3">
                    <div class="icon-squircle bg-linear-to-br from-indigo-500 to-purple-600 text-white text-xl shadow-md shadow-indigo-500/20">
                        🛵
                    </div>
                    <div>
                        <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-white flex items-center gap-2.5">
                            {{ __("Today's Field Squads") }}
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                {{ $sortedTodayTeams->count() }} {{ __('Squads Active') }}
                            </span>
                        </h2>
                        <p class="text-xs text-slate-400 dark:text-slate-400">
                            {{ __('3-Person Daily Teams (1 Leader + 2 Members) for field support and new connections') }}
                        </p>
                    </div>
                </div>

                {{-- Interactive Category Filter Chips + Manage Button --}}
                <div class="flex items-center gap-2.5 flex-wrap">
                    <div class="inline-flex items-center p-1 bg-slate-100 dark:bg-slate-800/80 rounded-xl border border-slate-200/60 dark:border-slate-700/60 gap-1 text-xs">
                        <button type="button" @click="squadFilter = 'all'"
                                :class="squadFilter === 'all' ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-white shadow-2xs font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium'"
                                class="px-2.5 py-1 rounded-lg transition-all">
                            {{ __('All') }} ({{ $sortedTodayTeams->count() }})
                        </button>
                        @foreach($sortedTodayTeams->groupBy('category') as $cKey => $cTeams)
                            @php $cMeta = $cTeams->first()->getCategoryMeta(); @endphp
                            <button type="button" @click="squadFilter = '{{ $cKey }}'"
                                    :class="squadFilter === '{{ $cKey }}' ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-white shadow-2xs font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium'"
                                    class="px-2.5 py-1 rounded-lg transition-all flex items-center gap-1">
                                <span>{{ $cMeta['icon'] ?? '👷' }}</span>
                                <span>{{ $cTeams->count() }}</span>
                            </button>
                        @endforeach
                    </div>

                    <button type="button" @click="showCreateTeamModal = true"
                            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-black text-white bg-emerald-600 hover:bg-emerald-500 active:scale-95 shadow-sm shadow-emerald-600/30 transition-all flex-shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        <span>{{ __('Create Team') }}</span>
                    </button>
                    <a href="{{ route('technician-teams.index') }}"
                       class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 active:scale-95 shadow-sm shadow-indigo-600/30 transition-all flex-shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                        <span>{{ __('Manage Squads') }}</span>
                    </a>
                </div>
            </div>

            @if($sortedTodayTeams->isEmpty())
                <div class="rounded-2xl border border-dashed border-slate-200 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/30 py-10 text-center">
                    <div class="text-4xl mb-2">🛵</div>
                    <div class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">{{ __('No field squads created for today yet.') }}</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 mb-4">{{ __('Click the "Create Team" button above to assign your first squad.') }}</div>
                    <button type="button" @click="showCreateTeamModal = true"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black text-white bg-emerald-600 hover:bg-emerald-500 shadow-sm shadow-emerald-600/30">
                        <span>+</span> {{ __('Create 3-Person Technician Team') }}
                    </button>
                </div>
            @else
            <div class="dash-squads-grid">
                @foreach($sortedTodayTeams as $team)
                    @php
                        $catMeta = $team->getCategoryMeta();
                        $squadTitle = $team->team_name ?: ($catMeta['label'] ?? __('Field Squad')) . ' #' . $loop->iteration;
                        $teamDoneCount = (int) ($team->leader_done_count ?? 0);
                        $isTopPerformer = ($loop->first && $teamDoneCount > 0);
                    @endphp
                    <div x-show="squadFilter === 'all' || squadFilter === '{{ $team->category }}'"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="group relative bg-white dark:bg-[#1a233a] border {{ $isTopPerformer ? 'border-amber-400/80 dark:border-amber-500/60 ring-2 ring-amber-400/20 shadow-amber-500/10' : 'border-slate-200/90 dark:border-slate-700/70 hover:border-indigo-400 dark:hover:border-indigo-500/50' }} rounded-2xl p-4 sm:p-5 hover:shadow-lg transition-all duration-200 flex flex-col justify-between shadow-2xs">
                        <div>
                            {{-- Header: Category + Badges on Left, Area + Three Dots on Right --}}
                            <div class="flex items-start justify-between gap-2 mb-3">
                                <div class="flex items-center gap-1.5 flex-wrap min-w-0">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border {{ $catMeta['badge'] ?? 'bg-amber-500/10 text-amber-500 border-amber-500/30' }}">
                                        <span>{{ $catMeta['icon'] ?? '👷' }}</span>
                                        <span>{{ __($catMeta['label']) }}</span>
                                    </span>
                                    @if($isTopPerformer)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-black bg-gradient-to-r from-amber-500/20 to-yellow-500/20 text-amber-700 dark:text-amber-300 border border-amber-400/40 shadow-xs animate-pulse">
                                            <span>🏆</span> {{ __('Top Resolver') }}
                                        </span>
                                    @endif
                                </div>

                                <div class="flex items-center gap-1.5 flex-shrink-0">
                                    @if($team->area)
                                        <a href="{{ route('map.index', ['area' => $team->area]) }}" title="{{ __('View on Live Map') }}: {{ $team->area }}"
                                           class="text-xs font-black text-rose-600 dark:text-rose-300 flex items-center gap-1.5 bg-rose-50 dark:bg-rose-950/60 border border-rose-200/90 dark:border-rose-800/70 px-2.5 py-1 rounded-lg hover:bg-rose-100 dark:hover:bg-rose-900/60 hover:scale-105 transition-all shadow-2xs whitespace-nowrap flex-shrink-0">
                                            <span class="text-rose-500 text-xs">📍</span>
                                            <span>{{ $team->area }}</span>
                                        </a>
                                    @endif
                                    <span class="dash-dots text-slate-400 dark:text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 p-0.5 cursor-pointer">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><circle cx="4" cy="10" r="1.8"/><circle cx="10" cy="10" r="1.8"/><circle cx="16" cy="10" r="1.8"/></svg>
                                    </span>
                                </div>
                            </div>

                            <h4 class="text-base sm:text-lg font-black text-slate-900 dark:text-white mb-3 truncate flex items-center gap-1.5">
                                @if($isTopPerformer)
                                    <span class="text-amber-500">🥇</span>
                                @endif
                                <span>{{ $squadTitle }}</span>
                            </h4>

                            {{-- Squad Avatars: Member1 · Leader (center, crown) · Member2 --}}
                            @php
                                $callSvg = '<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 4V3z"/></svg>';
                                $openCount = $team->leader_open_count ?? 0;
                                $doneCount = $team->leader_done_count ?? 0;
                            @endphp
                            <div class="squad-avatars">
                                <div class="squad-avatar" title="{{ $team->member1?->name ?? __('Unassigned') }}">
                                    <div class="avatar-ring avatar-ring--member">
                                        @if($team->member1)
                                            <img src="{{ $team->member1->avatarUrl() }}" alt="{{ $team->member1->name }}" loading="lazy">
                                        @else
                                            <span class="avatar-placeholder">?</span>
                                        @endif
                                    </div>
                                    @if($team->member1?->phone)
                                        <a href="tel:{{ $team->member1->phone }}" class="avatar-call" title="{{ __('Call') }}: {{ $team->member1->phone }}">{!! $callSvg !!}</a>
                                    @endif
                                </div>

                                <div class="squad-avatar squad-avatar--leader" title="{{ __('Leader') }}: {{ $team->leader?->name ?? __('Unassigned') }}">
                                    <span class="avatar-crown" aria-hidden="true">👑</span>
                                    <div class="avatar-ring avatar-ring--leader">
                                        @if($team->leader)
                                            <img src="{{ $team->leader->avatarUrl() }}" alt="{{ $team->leader->name }}" loading="lazy">
                                        @else
                                            <span class="avatar-placeholder">?</span>
                                        @endif
                                    </div>
                                    @if($team->leader?->phone)
                                        <a href="tel:{{ $team->leader->phone }}" class="avatar-call avatar-call--leader" title="{{ __('Call Leader') }}: {{ $team->leader->phone }}">{!! $callSvg !!}</a>
                                    @endif
                                </div>

                                <div class="squad-avatar" title="{{ $team->member2?->name ?? __('Unassigned') }}">
                                    <div class="avatar-ring avatar-ring--member">
                                        @if($team->member2)
                                            <img src="{{ $team->member2->avatarUrl() }}" alt="{{ $team->member2->name }}" loading="lazy">
                                        @else
                                            <span class="avatar-placeholder">?</span>
                                        @endif
                                    </div>
                                    @if($team->member2?->phone)
                                        <a href="tel:{{ $team->member2->phone }}" class="avatar-call" title="{{ __('Call') }}: {{ $team->member2->phone }}">{!! $callSvg !!}</a>
                                    @endif
                                </div>
                            </div>

                            {{-- Leader name --}}
                            <div class="text-center mt-3 pt-3 border-t border-dashed border-slate-200 dark:border-slate-700/60">
                                <div class="inline-flex items-center gap-1 text-[11px] font-black uppercase tracking-wider text-amber-600 dark:text-amber-400">
                                    <span>👑</span> {{ __('Team Leader') }}
                                </div>
                                <div class="text-base sm:text-lg font-black text-slate-900 dark:text-white truncate mt-0.5">{{ $team->leader?->name ?? __('Unassigned') }}</div>
                            </div>

                            {{-- Leader ticket stats (single line) --}}
                            <div class="flex items-center justify-center gap-2 mt-2 font-bold">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-200/70 dark:border-indigo-500/25 text-indigo-700 dark:text-indigo-300">
                                    <span class="uppercase tracking-wider text-xs font-bold">{{ __('Active') }}</span>
                                    <span class="text-base sm:text-lg font-black">{{ $openCount }}</span>
                                </span>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200/70 dark:border-emerald-500/25 text-emerald-700 dark:text-emerald-300">
                                    <span class="uppercase tracking-wider text-xs font-bold">{{ __('Done Today') }}</span>
                                    <span class="text-base sm:text-lg font-black">{{ $doneCount }}</span>
                                </span>
                            </div>
                        </div>

                        @if($team->vehicle_no || $team->notes)
                            <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 pt-3 mt-3 border-t border-slate-100 dark:border-slate-800/80">
                                @if($team->vehicle_no)
                                    <span class="inline-flex items-center gap-1 font-semibold text-slate-700 dark:text-slate-300">
                                        <span>🛵</span> {{ $team->vehicle_no }}
                                    </span>
                                @endif
                                @if($team->notes)
                                    <span class="truncate ml-auto italic text-slate-400 dark:text-slate-400" title="{{ $team->notes }}">
                                        📝 {{ $team->notes }}
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
            @endif

            {{-- Create Team Modal (teleported to <body> so parent card can't trap it) --}}
            <template x-teleport="body">
            <div x-show="showCreateTeamModal" x-cloak x-transition.opacity.duration.150ms
                 @keydown.escape.window="showCreateTeamModal = false"
                 style="position: fixed; inset: 0; z-index: 9999;"
                 class="overflow-y-auto bg-black/60 backdrop-blur-sm flex items-start sm:items-center justify-center p-4">
                <div @click.away="showCreateTeamModal = false"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden max-h-[92vh] overflow-y-auto my-auto">
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between sticky top-0 bg-white dark:bg-slate-900 z-10">
                        <h3 class="text-base font-bold text-slate-800 dark:text-white">
                            🛵 {{ __('Create 3-Person Technician Team') }}
                        </h3>
                        <button type="button" @click="showCreateTeamModal = false" class="text-slate-400 hover:text-slate-600 text-2xl leading-none">&times;</button>
                    </div>

                    <form action="{{ route('technician-teams.store') }}" method="POST" class="p-6 space-y-4">
                        @csrf
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wide mb-1">{{ __('Duty Date') }} <span class="text-red-600 dark:text-red-400">*</span></label>
                                <input type="date" name="duty_date" value="{{ now()->toDateString() }}" required
                                       class="w-full border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wide mb-1">{{ __('Category') }} <span class="text-red-600 dark:text-red-400">*</span></label>
                                <select name="category" required
                                        class="w-full border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 font-medium">
                                    @foreach(($dashboardSquadCategories ?? []) as $catKey => $catMeta)
                                        <option value="{{ $catKey }}">{{ $catMeta['icon'] ?? '👷' }} {{ __($catMeta['label']) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wide mb-1">
                                {{ __('Team / Group Name') }}
                                <span class="text-[10px] text-slate-400 normal-case font-medium ml-1">({{ __('Auto-named if blank') }})</span>
                            </label>
                            <input type="text" name="team_name" placeholder="{{ __('e.g. Team #1') }}"
                                   class="w-full border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div class="p-3 rounded-xl bg-amber-50/60 dark:bg-amber-950/20 border border-amber-200/60 dark:border-amber-800/40">
                            <label class="block text-xs font-bold text-amber-800 dark:text-amber-300 uppercase tracking-wide mb-1.5">
                                👑 {{ __('Team Leader') }} <span class="text-red-600 dark:text-red-400">*</span>
                            </label>
                            <select name="leader_id" required
                                    class="w-full border border-amber-300 dark:border-amber-700 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-amber-500 font-medium">
                                <option value="">— {{ __('Select Team Leader') }} —</option>
                                @foreach(($dashboardEligibleStaff ?? []) as $s)
                                    <option value="{{ $s->id }}">{{ $s->isOnDuty() ? '🟢' : '⚪' }} {{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wide mb-1">👷 {{ __('Team Member 1') }}</label>
                            <select name="member_1_id" class="w-full border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500">
                                <option value="">— {{ __('Select Member 1') }} —</option>
                                @foreach(($dashboardEligibleStaff ?? []) as $s)
                                    <option value="{{ $s->id }}">{{ $s->isOnDuty() ? '🟢' : '⚪' }} {{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wide mb-1">👷 {{ __('Team Member 2') }}</label>
                            <select name="member_2_id" class="w-full border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500">
                                <option value="">— {{ __('Select Member 2') }} —</option>
                                @foreach(($dashboardEligibleStaff ?? []) as $s)
                                    <option value="{{ $s->id }}">{{ $s->isOnDuty() ? '🟢' : '⚪' }} {{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wide mb-1">📍 {{ __('Assigned Area') }}</label>
                                <input type="text" name="area" list="dash-areas-list" placeholder="{{ __('e.g. Shadhupara') }}"
                                       class="w-full border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500">
                                <datalist id="dash-areas-list">
                                    @foreach(($dashboardAreas ?? []) as $a)
                                        <option value="{{ $a }}"></option>
                                    @endforeach
                                </datalist>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wide mb-1">🛵 {{ __('Vehicle No') }}</label>
                                <input type="text" name="vehicle_no" placeholder="{{ __('e.g. Bike-01') }}"
                                       class="w-full border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wide mb-1">📝 {{ __('Notes') }}</label>
                            <textarea name="notes" rows="2" placeholder="{{ __('Optional notes') }}"
                                      class="w-full border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500"></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                            <button type="button" @click="showCreateTeamModal = false"
                                    class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">
                                {{ __('Cancel') }}
                            </button>
                            <button type="submit"
                                    class="px-5 py-2 rounded-xl text-xs font-black text-white bg-emerald-600 hover:bg-emerald-500 shadow-sm shadow-emerald-600/30">
                                ✓ {{ __('Create Team') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            </template>
        </div>
    @endif

    <style>
        :root {
            --cat-color-0: #5c67f2;
            --cat-color-1: #f97316;
            --cat-color-2: #eab308;
            --cat-color-3: #3b82f6;
            --cat-color-4: #10b981;
            --cat-color-5: #ec4899;
            --cat-color-6: #8b5cf6;
        }
        html.dark {
            --cat-color-0: #6875f5;
            --cat-color-1: #fa8c38;
            --cat-color-2: #fbbf24;
            --cat-color-3: #38bdf8;
            --cat-color-4: #34d399;
            --cat-color-5: #f472b6;
            --cat-color-6: #a78bfa;
        }

        /* ===== Squad Avatars (Today's Field Squads) ===== */
        .squad-avatars {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 8px 0 4px;
        }
        .squad-avatar {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            cursor: default;
        }
        .avatar-ring {
            border-radius: 9999px;
            padding: 0;
            background: transparent;
            box-shadow: none;
            transition: transform .2s ease;
        }
        .squad-avatar:hover .avatar-ring { transform: translateY(-2px); }
        .avatar-ring img,
        .avatar-placeholder {
            display: block;
            width: 56px;
            height: 56px;
            border-radius: 9999px;
            object-fit: cover;
            box-sizing: border-box;
        }
        html.dark .avatar-placeholder { background: #1a233a; }
        .avatar-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #94a3b8;
            font-size: 1.1rem;
            border: 1px dashed #cbd5e1;
        }
        .avatar-ring--leader { padding: 0; background: transparent; box-shadow: none; }
        .avatar-ring--leader img,
        .avatar-ring--leader .avatar-placeholder { width: 72px; height: 72px; }
        .squad-avatar--leader { margin-bottom: 2px; }
        .avatar-crown {
            position: absolute;
            top: -14px;
            left: 50%;
            transform: translateX(-50%) rotate(-6deg);
            font-size: 20px;
            filter: drop-shadow(0 2px 3px rgba(0,0,0,.25));
            z-index: 2;
            pointer-events: none;
        }
        .avatar-call {
            position: absolute;
            bottom: -4px;
            right: -4px;
            width: 22px;
            height: 22px;
            border-radius: 9999px;
            background: #10b981;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 6px rgba(16,185,129,.35);
            border: 2px solid #fff;
            opacity: 0;
            transform: scale(.6);
            transition: opacity .18s ease, transform .18s ease, background .15s ease;
        }
        html.dark .avatar-call { border-color: #1a233a; }
        .avatar-call:hover { background: #059669; }
        .avatar-call--leader { width: 26px; height: 26px; }
        .squad-avatar:hover .avatar-call { opacity: 1; transform: scale(1); }
    </style>

    {{-- CHART SCRIPTS --}}
    @if($user->isAdmin())
    <script>
    (function() {
        const isDark = document.documentElement.classList.contains('dark');
        const days = @json($chartDays);
        const created = @json($chartCreated);

        new Chart(document.getElementById('lineChart'), {
            type: 'line',
            data: {
                labels: days,
                datasets: [{
                    label: 'Tickets Created',
                    data: created,
                    borderColor: '#818cf8',
                    backgroundColor: isDark ? 'rgba(129, 140, 248, 0.14)' : 'rgba(99, 102, 241, 0.08)',
                    tension: 0.45,
                    fill: true,
                    pointBackgroundColor: '#818cf8',
                    pointBorderColor: isDark ? '#1e293b' : '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        grid: {
                            color: isDark ? 'rgba(255, 255, 255, 0.04)' : 'rgba(0, 0, 0, 0.03)'
                        },
                        ticks: {
                            color: isDark ? '#94a3b8' : '#94a3b8',
                            font: { size: 11 }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            color: isDark ? '#94a3b8' : '#94a3b8',
                            font: { size: 11 }
                        },
                        grid: {
                            color: isDark ? 'rgba(255, 255, 255, 0.04)' : 'rgba(0, 0, 0, 0.03)'
                        }
                    }
                }
            }
        });

        const categoryCanvas = document.getElementById('categoryChart');
        if (categoryCanvas) {
            const catData = @json($chartByCategory->sortDesc());
            const lightColors = ['#5c67f2', '#f97316', '#eab308', '#3b82f6', '#10b981', '#ec4899', '#8b5cf6'];
            const darkColors  = ['#6875f5', '#fa8c38', '#fbbf24', '#38bdf8', '#34d399', '#f472b6', '#a78bfa'];
            const catColors = isDark ? darkColors : lightColors;

            // Compute exact card background color so borders seamlessly blend without dark rings
            const cardEl = categoryCanvas.closest('.dash-card') || categoryCanvas.parentElement;
            const cardBg = cardEl ? window.getComputedStyle(cardEl).backgroundColor : (isDark ? '#1e293b' : '#ffffff');

            new Chart(categoryCanvas, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(catData).map(k => k.charAt(0).toUpperCase() + k.slice(1).replace(/_/g, ' ')),
                    datasets: [{
                        data: Object.values(catData),
                        backgroundColor: catColors,
                        borderWidth: 3,
                        borderColor: cardBg,
                        borderRadius: 4,
                        spacing: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '72%',
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: (ctx) => `${ctx.label}: ${ctx.parsed}` } }
                    }
                }
            });
        }
    })();
    </script>
    @elseif($user->isNoc())
    <script>
    (function() {
        const isDark = document.documentElement.classList.contains('dark');
        const days = @json($chartDays);
        const resolved = @json($chartCreated);
        new Chart(document.getElementById('nocBarChart'), {
            type: 'bar',
            data: {
                labels: days,
                datasets: [{
                    label: 'Resolutions',
                    data: resolved,
                    backgroundColor: '#818cf8',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, color: isDark ? '#64748b' : '#94a3b8' },
                        grid: { color: isDark ? 'rgba(255, 255, 255, 0.03)' : 'rgba(0, 0, 0, 0.03)' }
                    },
                    x: {
                        ticks: { color: isDark ? '#64748b' : '#94a3b8' },
                        grid: { color: isDark ? 'rgba(255, 255, 255, 0.03)' : 'rgba(0, 0, 0, 0.03)' }
                    }
                }
            }
        });
    })();
    </script>
    @endif

    {{-- CLIENT DEVICE TIME-AWARE GREETING SYNC (locale-aware) --}}
    <script>
    (function() {
        try {
            const greetings = {
                morning: @json(__('Good morning')),
                afternoon: @json(__('Good afternoon')),
                evening: @json(__('Good evening'))
            };
            const h = new Date().getHours();
            let g = greetings.evening;
            if (h >= 5 && h < 12) g = greetings.morning;
            else if (h >= 12 && h < 18) g = greetings.afternoon;
            const el = document.getElementById('dash-greeting-text');
            if (el) el.textContent = g;
        } catch (e) {}
    })();
    </script>

    {{-- LIVE STATS POLLING (Every 15s) --}}
    <script>
    (function() {
        function pollDashboardStats() {
            fetch('{{ route("dashboard") }}', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                if (!data || !data.stats) return;
                const s = data.stats;
                const setTxt = (id, val) => { const el = document.getElementById(id); if (el && val !== undefined) el.textContent = val; };
                
                // Classic IDs
                setTxt('stat-in-progress', s.in_progress);
                setTxt('stat-pending', s.pending);
                setTxt('stat-waiting', s.waiting_for_customer_feedback);
                setTxt('stat-resolved', s.resolved);
                setTxt('stat-total', s.total);
                setTxt('stat-critical', s.critical);
                setTxt('stat-overdue', s.overdue);
                setTxt('stat-avg-resolution', s.avg_resolution_time);

                // Top Stat Card IDs
                setTxt('stat-g-all', s.total);
                setTxt('stat-g-inprog', s.in_progress);
                setTxt('stat-g-pending', s.pending);
                setTxt('stat-g-waiting', s.waiting_for_customer_feedback);
                setTxt('stat-g-resolved', s.resolved);
                setTxt('stat-g-critical', s.critical);
                setTxt('stat-g-overdue', s.overdue);
                setTxt('stat-g-avg-resolution', s.avg_resolution_time);
            })
            .catch(() => {});
        }
        setInterval(pollDashboardStats, 15000);
    })();
    </script>
</x-app-layout>
