<x-app-layout>
    @php
        $hour = now()->hour;
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
        $user = auth()->user();
    @endphp

    {{-- Greeting --}}
    <div class="mb-5 sm:mb-6 flex items-end justify-between gap-3">
        <div class="min-w-0 flex-1">
            <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-slate-800 dark:text-white tracking-tight">{{ __($greeting) }}, {{ $user->name }}!</h1>
            <p class="text-slate-500 dark:text-slate-400 text-xs sm:text-sm mt-1">{{ __("Here's what's happening with your tickets today.") }}</p>
        </div>

        @if($user->isAdmin() && isset($resellersQuick) && $resellersQuick->isNotEmpty())
        <div class="flex items-center gap-2 flex-shrink-0 ml-auto">
            <span class="hidden sm:inline-flex px-2.5 py-1 rounded-md text-[11px] font-bold uppercase tracking-wider bg-slate-100 dark:bg-slate-800/90 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700/80 shadow-xs">
                {{ __('RESELLER') }}
            </span>
            <select id="reseller_quick"
                    onchange="if (this.value) window.location = this.value"
                    class="text-xs sm:text-sm bg-white dark:bg-[#131b2e] text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700/80 rounded-lg px-3 py-1.5 pr-8 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-xs cursor-pointer max-w-[180px] sm:max-w-none">
                <option value="">{{ __('View Reseller Report') }}</option>
                @foreach($resellersQuick as $reseller)
                <option value="{{ route('reports.index', ['tab' => 'reseller', 'person_id' => $reseller->id]) }}">{{ $reseller->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
    </div>

    {{-- Global quick stats — scoped by role (Row 1 - 5 mini cards) --}}
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
    @endphp
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-6">
        <a href="{{ route('tickets.index') }}" class="flex items-center gap-3 bg-white dark:bg-[#131b2e] border border-slate-200 dark:border-slate-800/90 rounded-xl px-3 sm:px-4 py-3 hover:border-indigo-300 dark:hover:border-slate-700 hover:shadow-sm transition-all group">
            <div class="w-9 h-9 bg-indigo-50 dark:bg-indigo-950/50 rounded-lg flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/></svg>
            </div>
            <div>
                <p id="stat-g-all" class="text-xl font-bold text-slate-800 dark:text-white">{{ $globalAll }}</p>
                <p class="text-xs text-slate-400 dark:text-slate-400 group-hover:text-indigo-500 transition-colors">{{ __('All Tickets') }}</p>
            </div>
        </a>
        <a href="{{ route('tickets.index', ['status' => 'in_progress']) }}" class="flex items-center gap-3 bg-white dark:bg-[#131b2e] border border-slate-200 dark:border-slate-800/90 rounded-xl px-3 sm:px-4 py-3 hover:border-blue-300 dark:hover:border-slate-700 hover:shadow-sm transition-all group">
            <div class="w-9 h-9 bg-blue-50 dark:bg-blue-950/50 rounded-lg flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </div>
            <div>
                <p id="stat-g-inprog" class="text-xl font-bold text-slate-800 dark:text-white">{{ $globalInProg }}</p>
                <p class="text-xs text-slate-400 dark:text-slate-400 group-hover:text-blue-500 transition-colors">{{ __('In Progress') }}</p>
            </div>
        </a>
        <a href="{{ route('tickets.index', ['status' => 'pending']) }}" class="flex items-center gap-3 bg-white dark:bg-[#131b2e] border border-slate-200 dark:border-slate-800/90 rounded-xl px-3 sm:px-4 py-3 hover:border-amber-300 dark:hover:border-slate-700 hover:shadow-sm transition-all group">
            <div class="w-9 h-9 bg-amber-50 dark:bg-amber-950/50 rounded-lg flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p id="stat-g-pending" class="text-xl font-bold text-slate-800 dark:text-white">{{ $globalPending }}</p>
                <p class="text-xs text-slate-400 dark:text-slate-400 group-hover:text-amber-500 transition-colors">{{ __('Pending') }}</p>
            </div>
        </a>
        <a href="{{ route('tickets.index', ['status' => 'waiting_for_customer_feedback']) }}" class="flex items-center gap-3 bg-white dark:bg-[#131b2e] border border-slate-200 dark:border-slate-800/90 rounded-xl px-3 sm:px-4 py-3 hover:border-purple-300 dark:hover:border-slate-700 hover:shadow-sm transition-all group">
            <div class="w-9 h-9 bg-purple-50 dark:bg-purple-950/50 rounded-lg flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            </div>
            <div>
                <p id="stat-g-waiting" class="text-xl font-bold text-slate-800 dark:text-white">{{ $globalWaiting }}</p>
                <p class="text-xs text-slate-400 dark:text-slate-400 group-hover:text-purple-500 transition-colors">{{ __('Waiting') }}</p>
            </div>
        </a>
        <a href="{{ route('tickets.index', ['status' => 'resolved']) }}" class="flex items-center gap-3 bg-white dark:bg-[#131b2e] border border-slate-200 dark:border-slate-800/90 rounded-xl px-3 sm:px-4 py-3 hover:border-emerald-300 dark:hover:border-slate-700 hover:shadow-sm transition-all group">
            <div class="w-9 h-9 bg-emerald-50 dark:bg-emerald-950/50 rounded-lg flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p id="stat-g-resolved" class="text-xl font-bold text-slate-800 dark:text-white">{{ $globalResolved }}</p>
                <p class="text-xs text-slate-400 dark:text-slate-400 group-hover:text-emerald-500 transition-colors">{{ __('Resolved') }}</p>
            </div>
        </a>
    </div>

    @if($user->isAdmin())
    {{-- Admin Stat Cards — 7 steps grid (Row 2) --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-7 gap-3 sm:gap-4 mb-4">

        <a href="{{ route('tickets.index', ['status' => 'in_progress']) }}"
           class="bg-white dark:bg-[#131b2e] border border-slate-200 dark:border-slate-800/90 rounded-xl p-4 hover:border-blue-400/50 dark:hover:border-slate-700 transition-all hover:shadow-sm group block">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('In Progress') }}</span>
                <div class="w-7 h-7 bg-blue-500/10 text-blue-500 dark:text-blue-400 rounded-lg flex items-center justify-center">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </div>
            </div>
            <p id="stat-in-progress" class="text-2xl sm:text-3xl font-bold text-slate-800 dark:text-white my-2">{{ $stats['in_progress'] }}</p>
            <p class="text-xs text-slate-400 dark:text-slate-500 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ __('View all') }} &rarr;</p>
        </a>

        <a href="{{ route('tickets.index', ['status' => 'pending']) }}"
           class="bg-white dark:bg-[#131b2e] border border-slate-200 dark:border-slate-800/90 rounded-xl p-4 hover:border-amber-400/50 dark:hover:border-slate-700 transition-all hover:shadow-sm group block">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Pending') }}</span>
                <div class="w-7 h-7 bg-amber-500/10 text-amber-500 dark:text-amber-400 rounded-lg flex items-center justify-center">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <p id="stat-pending" class="text-2xl sm:text-3xl font-bold text-slate-800 dark:text-white my-2">{{ $stats['pending'] }}</p>
            <p class="text-xs text-slate-400 dark:text-slate-500 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ __('View all') }} &rarr;</p>
        </a>

        <a href="{{ route('tickets.index', ['status' => 'waiting_for_customer_feedback']) }}"
           class="bg-white dark:bg-[#131b2e] border border-slate-200 dark:border-slate-800/90 rounded-xl p-4 hover:border-purple-400/50 dark:hover:border-slate-700 transition-all hover:shadow-sm group block">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Waiting') }}</span>
                <div class="w-7 h-7 bg-purple-500/10 text-purple-500 dark:text-purple-400 rounded-lg flex items-center justify-center">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </div>
            </div>
            <p id="stat-waiting" class="text-2xl sm:text-3xl font-bold text-slate-800 dark:text-white my-2">{{ $stats['waiting_for_customer_feedback'] }}</p>
            <p class="text-xs text-slate-400 dark:text-slate-500 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ __('View all') }} &rarr;</p>
        </a>

        <a href="{{ route('tickets.index', ['priority' => 'critical']) }}"
           class="bg-white dark:bg-[#131b2e] border border-slate-200 dark:border-slate-800/90 rounded-xl p-4 hover:border-red-400/50 dark:hover:border-slate-700 transition-all hover:shadow-sm group block">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Critical') }}</span>
                <div class="w-7 h-7 bg-red-500/10 text-red-500 dark:text-red-400 rounded-lg flex items-center justify-center">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
            </div>
            <p id="stat-critical" class="text-2xl sm:text-3xl font-bold text-slate-800 dark:text-white my-2">{{ $stats['critical'] }}</p>
            <p class="text-xs text-slate-400 dark:text-slate-500 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ __('View all') }} &rarr;</p>
        </a>

        <a href="{{ route('tickets.index', ['status' => 'resolved']) }}"
           class="bg-white dark:bg-[#131b2e] border border-slate-200 dark:border-slate-800/90 rounded-xl p-4 hover:border-emerald-400/50 dark:hover:border-slate-700 transition-all hover:shadow-sm group block">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Resolved') }}</span>
                <div class="w-7 h-7 bg-emerald-500/10 text-emerald-500 dark:text-emerald-400 rounded-lg flex items-center justify-center">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <p id="stat-resolved" class="text-2xl sm:text-3xl font-bold text-emerald-600 dark:text-emerald-400 my-2">{{ $stats['resolved'] }}</p>
            <p class="text-xs text-slate-400 dark:text-slate-500 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ __('View all') }} &rarr;</p>
        </a>

        <a href="{{ route('tickets.index') }}"
           class="bg-white dark:bg-[#131b2e] border border-slate-200 dark:border-slate-800/90 rounded-xl p-4 hover:border-indigo-400/50 dark:hover:border-slate-700 transition-all hover:shadow-sm group block">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Total') }}</span>
                <div class="w-7 h-7 bg-indigo-500/10 text-indigo-500 dark:text-indigo-400 rounded-lg flex items-center justify-center">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/></svg>
                </div>
            </div>
            <p id="stat-total" class="text-2xl sm:text-3xl font-bold text-indigo-600 dark:text-indigo-400 my-2">{{ $stats['total'] }}</p>
            <p class="text-xs text-slate-400 dark:text-slate-500 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ __('View all') }} &rarr;</p>
        </a>

        <a href="{{ route('tickets.index', ['status' => 'overdue']) }}"
           class="bg-white dark:bg-[#131b2e] border border-slate-200 dark:border-slate-800/90 rounded-xl p-4 hover:border-rose-400/50 dark:hover:border-slate-700 transition-all hover:shadow-sm group block">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('SLA Overdue') }}</span>
                <div class="w-7 h-7 bg-rose-500/10 text-rose-500 dark:text-rose-400 rounded-lg flex items-center justify-center">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <p id="stat-overdue" class="text-2xl sm:text-3xl font-bold text-slate-800 dark:text-white my-2">{{ $stats['overdue'] }}</p>
            <p class="text-xs text-slate-400 dark:text-slate-500 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ __('View all') }} &rarr;</p>
        </a>

    </div>

    {{-- Row 3: Standalone Avg Resolution Card --}}
    <div class="mb-6 sm:mb-8 max-w-xs">
        <div class="bg-white dark:bg-[#131b2e] border border-slate-200 dark:border-slate-800/90 rounded-xl p-4 block">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Avg Resolution') }}</span>
                <div class="w-7 h-7 bg-teal-500/10 text-teal-500 dark:text-teal-400 rounded-lg flex items-center justify-center">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <p id="stat-avg-resolution" class="text-2xl sm:text-3xl font-bold text-teal-500 dark:text-teal-400 my-2">{{ $stats['avg_resolution_time'] ?? 'N/A' }}</p>
            <p class="text-xs text-slate-400 dark:text-slate-500">{{ __('Mean time to resolve') }}</p>
        </div>
    </div>

    @elseif($user->isNoc())
    {{-- NOC Stat Cards — tickets assigned to me --}}
    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-3">{{ __('My Assigned Tickets') }}</p>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2.5 sm:gap-4 mb-6 sm:mb-8">
        <a href="{{ route('tickets.index', ['assigned' => 'me', 'status' => 'in_progress']) }}" class="bg-white dark:bg-[#131b2e] border border-slate-200 dark:border-slate-800/90 rounded-xl p-4 hover:shadow-sm transition-all group block">
            <div class="flex items-center justify-between mb-2"><span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">{{ __('In Progress') }}</span><div class="w-7 h-7 bg-blue-500/10 text-blue-400 rounded-lg flex items-center justify-center"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg></div></div>
            <p class="text-2xl sm:text-3xl font-bold text-slate-800 dark:text-white my-2">{{ $stats['in_progress'] }}</p>
            <p class="text-xs text-slate-400 dark:text-slate-500 group-hover:text-blue-500 transition-colors">{{ __('View all') }} &rarr;</p>
        </a>
        <a href="{{ route('tickets.index', ['assigned' => 'me', 'status' => 'pending']) }}" class="bg-white dark:bg-[#131b2e] border border-slate-200 dark:border-slate-800/90 rounded-xl p-4 hover:shadow-sm transition-all group block">
            <div class="flex items-center justify-between mb-2"><span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">{{ __('Pending') }}</span><div class="w-7 h-7 bg-amber-500/10 text-amber-400 rounded-lg flex items-center justify-center"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div></div>
            <p class="text-2xl sm:text-3xl font-bold text-slate-800 dark:text-white my-2">{{ $stats['pending'] }}</p>
            <p class="text-xs text-slate-400 dark:text-slate-500 group-hover:text-orange-500 transition-colors">{{ __('View all') }} &rarr;</p>
        </a>
        <a href="{{ route('tickets.index', ['assigned' => 'me', 'status' => 'waiting_for_customer_feedback']) }}" class="bg-white dark:bg-[#131b2e] border border-slate-200 dark:border-slate-800/90 rounded-xl p-4 hover:shadow-sm transition-all group block">
            <div class="flex items-center justify-between mb-2"><span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">{{ __('Waiting') }}</span><div class="w-7 h-7 bg-purple-500/10 text-purple-400 rounded-lg flex items-center justify-center"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg></div></div>
            <p class="text-2xl sm:text-3xl font-bold text-slate-800 dark:text-white my-2">{{ $stats['waiting_for_customer_feedback'] }}</p>
            <p class="text-xs text-slate-400 dark:text-slate-500 group-hover:text-violet-500 transition-colors">{{ __('View all') }} &rarr;</p>
        </a>
        <a href="{{ route('tickets.index', ['assigned' => 'me', 'status' => 'resolved']) }}" class="bg-white dark:bg-[#131b2e] border border-slate-200 dark:border-slate-800/90 rounded-xl p-4 hover:shadow-sm transition-all group block">
            <div class="flex items-center justify-between mb-2"><span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">{{ __('Resolved') }}</span><div class="w-7 h-7 bg-emerald-500/10 text-emerald-400 rounded-lg flex items-center justify-center"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div></div>
            <p class="text-2xl sm:text-3xl font-bold text-emerald-500 dark:text-emerald-400 my-2">{{ $stats['resolved'] }}</p>
            <p class="text-xs text-slate-400 dark:text-slate-500 group-hover:text-emerald-500 transition-colors">{{ __('View all') }} &rarr;</p>
        </a>
        <a href="{{ route('tickets.index', ['assigned' => 'me']) }}" class="bg-white dark:bg-[#131b2e] border border-slate-200 dark:border-slate-800/90 rounded-xl p-4 hover:shadow-sm transition-all group block">
            <div class="flex items-center justify-between mb-2"><span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">{{ __('Total Assigned') }}</span><div class="w-7 h-7 bg-indigo-500/10 text-indigo-400 rounded-lg flex items-center justify-center"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg></div></div>
            <p class="text-2xl sm:text-3xl font-bold text-indigo-500 dark:text-indigo-400 my-2">{{ $stats['total'] }}</p>
            <p class="text-xs text-slate-400 dark:text-slate-500 group-hover:text-indigo-600 transition-colors">{{ __('View all') }} &rarr;</p>
        </a>
    </div>

    @else
    {{-- Reseller Stat Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-6 sm:mb-8">
        <a href="{{ route('tickets.index', ['status' => 'in_progress']) }}" class="bg-white dark:bg-[#131b2e] border border-slate-200 dark:border-slate-800/90 rounded-xl p-4 hover:shadow-sm transition-all group block">
            <div class="flex items-center justify-between mb-2"><span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">{{ __('In Progress') }}</span><div class="w-7 h-7 bg-blue-500/10 text-blue-400 rounded-lg flex items-center justify-center"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg></div></div>
            <p class="text-2xl sm:text-3xl font-bold text-slate-800 dark:text-white my-2">{{ $stats['in_progress'] }}</p>
            <p class="text-xs text-slate-400 dark:text-slate-500 group-hover:text-indigo-600 transition-colors">{{ __('View all') }} &rarr;</p>
        </a>
        <a href="{{ route('tickets.index', ['status' => 'pending']) }}" class="bg-white dark:bg-[#131b2e] border border-slate-200 dark:border-slate-800/90 rounded-xl p-4 hover:shadow-sm transition-all group block">
            <div class="flex items-center justify-between mb-2"><span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">{{ __('Pending') }}</span><div class="w-7 h-7 bg-amber-500/10 text-amber-400 rounded-lg flex items-center justify-center"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div></div>
            <p class="text-2xl sm:text-3xl font-bold text-slate-800 dark:text-white my-2">{{ $stats['pending'] }}</p>
            <p class="text-xs text-slate-400 dark:text-slate-500 group-hover:text-indigo-600 transition-colors">{{ __('View all') }} &rarr;</p>
        </a>
        <a href="{{ route('tickets.index', ['status' => 'resolved']) }}" class="bg-white dark:bg-[#131b2e] border border-slate-200 dark:border-slate-800/90 rounded-xl p-4 hover:shadow-sm transition-all group block">
            <div class="flex items-center justify-between mb-2"><span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">{{ __('Resolved') }}</span><div class="w-7 h-7 bg-emerald-500/10 text-emerald-400 rounded-lg flex items-center justify-center"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div></div>
            <p class="text-2xl sm:text-3xl font-bold text-emerald-500 dark:text-emerald-400 my-2">{{ $stats['resolved'] }}</p>
            <p class="text-xs text-slate-400 dark:text-slate-500 group-hover:text-indigo-600 transition-colors">{{ __('View all') }} &rarr;</p>
        </a>
        <a href="{{ route('tickets.index') }}" class="bg-white dark:bg-[#131b2e] border border-slate-200 dark:border-slate-800/90 rounded-xl p-4 hover:shadow-sm transition-all group block">
            <div class="flex items-center justify-between mb-2"><span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">{{ __('Total') }}</span><div class="w-7 h-7 bg-indigo-500/10 text-indigo-400 rounded-lg flex items-center justify-center"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg></div></div>
            <p class="text-2xl sm:text-3xl font-bold text-indigo-500 dark:text-indigo-400 my-2">{{ $stats['total'] }}</p>
            <p class="text-xs text-slate-400 dark:text-slate-500 group-hover:text-indigo-600 transition-colors">{{ __('View all') }} &rarr;</p>
        </a>
    </div>
    @endif

    {{-- Live Team Duty Stat Cards (Row 4) --}}
    @if(!$user->isReseller())
        @php
            $teams = \App\Models\User::TEAMS;
            $allStaff = \App\Models\User::whereNotNull('team')->get();
            $teamConfig = [
                'IT Team' => [
                    'label'       => 'IT Team',
                    'icon'        => '⚡',
                    'metric_clr'  => 'text-emerald-600 dark:text-emerald-400',
                    'bar_clr'     => 'bg-emerald-500',
                ],
                'NOC team' => [
                    'label'       => 'NOC Team',
                    'icon'        => '📡',
                    'metric_clr'  => 'text-slate-800 dark:text-white',
                    'bar_clr'     => 'bg-indigo-500',
                ],
                'Call center' => [
                    'label'       => 'Call Center',
                    'icon'        => '🎧',
                    'metric_clr'  => 'text-slate-800 dark:text-white',
                    'bar_clr'     => 'bg-cyan-500',
                ],
                'Supervisor Team' => [
                    'label'       => 'Supervisor Team',
                    'icon'        => '🛡️',
                    'metric_clr'  => 'text-slate-800 dark:text-white',
                    'bar_clr'     => 'bg-purple-500',
                ],
            ];
        @endphp

        <style>
            .duty-grid-4 {
                display: grid !important;
                grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
            }
            @media (max-width: 640px) {
                .duty-grid-4 {
                    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                }
            }
            .shift-badge-container {
                display: flex !important;
                flex-direction: column !important;
                gap: 6px !important;
            }
            .shift-pill-badge {
                display: inline-flex !important;
                align-items: center !important;
                justify-content: flex-end !important;
                gap: 6px !important;
                padding: 3.5px 10px !important;
                border-radius: 9999px !important;
                font-size: 11px !important;
                font-weight: 600 !important;
                white-space: nowrap !important;
                box-sizing: border-box !important;
                background-color: rgba(15, 23, 42, 0.55) !important;
                transition: all 0.15s ease !important;
            }
            .shift-pill-day {
                color: #fbbf24 !important;
                border: 1px solid rgba(251, 191, 36, 0.4) !important;
            }
            .shift-pill-night {
                color: #93c5fd !important;
                border: 1px solid rgba(147, 197, 253, 0.4) !important;
            }
            .shift-pill-off {
                color: #c4b5fd !important;
                border: 1px solid rgba(196, 181, 253, 0.4) !important;
            }
            html:not(.dark) .shift-pill-badge {
                background-color: #f8fafc !important;
            }
            html:not(.dark) .shift-pill-day {
                color: #b45309 !important;
                border: 1px solid rgba(217, 119, 6, 0.35) !important;
            }
            html:not(.dark) .shift-pill-night {
                color: #2563eb !important;
                border: 1px solid rgba(37, 99, 235, 0.35) !important;
            }
            html:not(.dark) .shift-pill-off {
                color: #7c3aed !important;
                border: 1px solid rgba(124, 58, 237, 0.35) !important;
            }
        </style>
        <div class="mb-6 sm:mb-8">
            <div class="duty-grid-4 gap-2.5 sm:gap-3 lg:gap-4">
                @foreach($teams as $teamKey => $teamLabel)
                    @php
                        $tUsers     = $allStaff->filter(fn($u) => $u->team === $teamKey);
                        $tot        = $tUsers->count();
                        $act        = $tUsers->filter(fn($u) => $u->isOnDuty())->count();
                        $dayShift   = $tUsers->where('current_shift', 'day_shift')->count();
                        $nightShift = $tUsers->where('current_shift', 'night_shift')->count();
                        $dayOff     = $tUsers->where('current_shift', 'day_off')->count();
                        $pct        = $tot > 0 ? round(($act / $tot) * 100) : 0;
                        $cfg        = $teamConfig[$teamKey] ?? [
                            'label' => $teamKey, 'icon' => '👥',
                            'metric_clr' => 'text-slate-800 dark:text-white', 'bar_clr' => 'bg-indigo-500'
                        ];
                    @endphp
                    <a href="{{ route('roster.index', ['team' => $teamKey]) }}"
                       class="bg-white dark:bg-[#131b2e] border border-slate-200 dark:border-slate-800/90 rounded-2xl p-3 sm:p-4 hover:border-slate-300 dark:hover:border-slate-700 transition-all hover:shadow-sm group block relative overflow-hidden">

                        <div class="flex items-center justify-between gap-2 h-full relative z-10">
                            {{-- Left: Team Badge, Big Metric, Mini Progress & Link --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-1.5 mb-1.5">
                                    <span class="text-xs">{!! $cfg['icon'] !!}</span>
                                    <span class="text-[10px] sm:text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider truncate" title="DUTY &rarr; {{ strtoupper($teamKey) }}">
                                        DUTY &rarr; {{ strtoupper($teamKey) }}
                                    </span>
                                </div>

                                <div class="flex items-baseline gap-1.5">
                                    <p class="text-2xl sm:text-3xl font-black tracking-tight {{ $act > 0 ? 'text-emerald-500 dark:text-emerald-400' : 'text-slate-800 dark:text-white' }}">{{ $act }}/{{ $tot }}</p>
                                    <span class="text-[10px] font-semibold text-slate-400 dark:text-slate-500">({{ $pct }}%)</span>
                                </div>

                                {{-- Progress Mini-bar --}}
                                <div class="w-16 sm:w-24 bg-slate-100 dark:bg-slate-800 rounded-full h-1.5 mt-2 overflow-hidden">
                                    <div class="{{ $cfg['bar_clr'] }} h-1.5 rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
                                </div>

                                <p class="text-[10px] sm:text-[11px] font-semibold text-slate-400 dark:text-slate-500 mt-2.5 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors flex items-center gap-1">
                                    <span>{{ __('View all') }}</span>
                                    <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                                </p>
                            </div>

                            {{-- Right: Styled Pill Badges for Shift Breakdown with Gap --}}
                            <div class="text-right text-[10px] sm:text-[11px] shift-badge-container flex-shrink-0">
                                <div class="shift-pill-badge shift-pill-day">
                                    <span>{{ $dayShift }} Day shift</span>
                                    <span class="text-xs">☀️</span>
                                </div>
                                <div class="shift-pill-badge shift-pill-night">
                                    <span>{{ $nightShift }} Night shift</span>
                                    <span class="text-xs">🌙</span>
                                </div>
                                <div class="shift-pill-badge shift-pill-off">
                                    <span>{{ $dayOff }} Day off</span>
                                    <span class="text-xs">🏖️</span>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Admin only: Charts + Leaderboard (Row 5) --}}
    @if($user->isAdmin())
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white dark:bg-[#131b2e] border border-slate-200 dark:border-slate-800/90 rounded-xl p-5 shadow-xs">
            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-200 mb-4">{{ __('Tickets Created (Last 14 Days)') }}</h3>
            <canvas id="lineChart" height="120"></canvas>
        </div>
        <div class="bg-white dark:bg-[#131b2e] border border-slate-200 dark:border-slate-800/90 rounded-xl p-5 shadow-xs">
            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-200 mb-4">{{ __('By Category') }}</h3>
            @php $catTotal = $chartByCategory->sum(); @endphp
            @if($chartByCategory->isEmpty())
            <div class="h-32 flex items-center justify-center text-sm text-slate-400">{{ __('No ticket data yet.') }}</div>
            @else
            <div class="flex flex-col sm:flex-row items-center gap-6">
                <div class="relative w-32 h-32 flex-shrink-0">
                    <canvas id="categoryChart" width="128" height="128"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                        <span class="text-2xl font-bold text-slate-800 dark:text-white">{{ $catTotal }}</span>
                        <span class="text-[10px] text-slate-400 uppercase tracking-widest">{{ __('TOTAL') }}</span>
                    </div>
                </div>
                <ul class="flex-1 space-y-2.5 min-w-0">
                    @foreach($chartByCategory->sortDesc() as $label => $count)
                    @php $pct = $catTotal ? round($count / $catTotal * 100) : 0; @endphp
                    <li class="flex items-center gap-3 text-sm">
                        <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background-color: var(--cat-color-{{ $loop->index % 7 }})"></span>
                        <span class="text-slate-600 dark:text-slate-300 truncate flex-1">{{ ucfirst(str_replace('_',' ',$label)) }}</span>
                        <span class="text-slate-400 dark:text-slate-500 text-xs">{{ $pct }}%</span>
                        <span class="font-bold text-slate-700 dark:text-white w-6 text-right">{{ $count }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
    </div>

    @if(isset($nocLeaderboard) && $nocLeaderboard->isNotEmpty())
    <div class="bg-white dark:bg-[#131b2e] border border-slate-200 dark:border-slate-800/90 rounded-xl p-5 mb-6 shadow-xs">
        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-200 mb-4 flex items-center justify-between">
            <span class="flex items-center gap-2">🏆 {{ __('NOC Leaderboard & Avg Resolution Time') }}</span>
            <span class="text-xs text-slate-400 font-normal">{{ __('This Month') }}</span>
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            @foreach($nocLeaderboard as $noc)
            <div class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800 rounded-xl hover:border-indigo-400/40 transition-colors">
                <div class="w-9 h-9 rounded-full bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 font-bold text-xs flex items-center justify-center flex-shrink-0">
                    {{ strtoupper(substr($noc->name, 0, 2)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-semibold text-slate-800 dark:text-slate-200 truncate">{{ $noc->name }}</p>
                    <div class="flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                        <span class="text-emerald-600 dark:text-emerald-400 font-semibold">✓ {{ $noc->resolved_count }}</span>
                        <span class="text-slate-300 dark:text-slate-700">•</span>
                        <span class="text-teal-600 dark:text-teal-400 font-medium">⏱️ {{ $noc->avg_resolution_time }}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <style>
        :root {
            --cat-color-0:#6366f1; --cat-color-1:#f97316; --cat-color-2:#eab308;
            --cat-color-3:#3b82f6; --cat-color-4:#10b981; --cat-color-5:#ec4899; --cat-color-6:#8b5cf6;
        }
    </style>

    @elseif($user->isNoc())
    {{-- NOC: My resolutions chart --}}
    <div class="bg-white dark:bg-[#131b2e] border border-slate-200 dark:border-slate-800/90 rounded-xl p-5 mb-6 shadow-xs">
        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-200 mb-3">{{ __('My Resolutions (Last 7 Days)') }}</h3>
        <canvas id="nocBarChart" height="80"></canvas>
    </div>
    @endif

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
                    backgroundColor: isDark ? 'rgba(129, 140, 248, 0.15)' : 'rgba(99, 102, 241, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#818cf8',
                    pointRadius: 3,
                    pointHoverRadius: 5
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        grid: {
                            color: isDark ? 'rgba(255, 255, 255, 0.04)' : 'rgba(0, 0, 0, 0.04)'
                        },
                        ticks: {
                            color: isDark ? '#64748b' : '#94a3b8'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            color: isDark ? '#64748b' : '#94a3b8'
                        },
                        grid: {
                            color: isDark ? 'rgba(255, 255, 255, 0.04)' : 'rgba(0, 0, 0, 0.04)'
                        }
                    }
                }
            }
        });

        const categoryCanvas = document.getElementById('categoryChart');
        if (categoryCanvas) {
            const catData = @json($chartByCategory->sortDesc());
            const catColors = ['#6366f1','#f97316','#eab308','#3b82f6','#10b981','#ec4899','#8b5cf6'];
            new Chart(categoryCanvas, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(catData).map(k => k.charAt(0).toUpperCase() + k.slice(1).replace(/_/g, ' ')),
                    datasets: [{
                        data: Object.values(catData),
                        backgroundColor: catColors,
                        borderWidth: 2,
                        borderColor: isDark ? '#131b2e' : '#fff'
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
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, color: isDark ? '#64748b' : '#94a3b8' },
                        grid: { color: isDark ? 'rgba(255, 255, 255, 0.04)' : 'rgba(0, 0, 0, 0.04)' }
                    },
                    x: {
                        ticks: { color: isDark ? '#64748b' : '#94a3b8' },
                        grid: { color: isDark ? 'rgba(255, 255, 255, 0.04)' : 'rgba(0, 0, 0, 0.04)' }
                    }
                }
            }
        });
    })();
    </script>
    @endif

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
                setTxt('stat-in-progress', s.in_progress);
                setTxt('stat-pending', s.pending);
                setTxt('stat-waiting', s.waiting_for_customer_feedback);
                setTxt('stat-resolved', s.resolved);
                setTxt('stat-total', s.total);
                setTxt('stat-critical', s.critical);
                setTxt('stat-overdue', s.overdue);
                setTxt('stat-avg-resolution', s.avg_resolution_time);

                setTxt('stat-g-all', s.total);
                setTxt('stat-g-inprog', s.in_progress);
                setTxt('stat-g-pending', s.pending);
                setTxt('stat-g-waiting', s.waiting_for_customer_feedback);
                setTxt('stat-g-resolved', s.resolved);
            })
            .catch(() => {});
        }
        setInterval(pollDashboardStats, 15000);
    })();
    </script>
</x-app-layout>
