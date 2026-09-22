<x-app-layout>
    @php
        $hour = now()->hour;
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
        $user = auth()->user();
    @endphp

    {{-- Greeting --}}
    <div class="mb-4 sm:mb-5 flex flex-wrap items-end justify-between gap-2 sm:gap-3">
        <div class="min-w-0">
            <h1 class="text-lg sm:text-2xl lg:text-3xl font-bold text-slate-800 truncate">{{ __($greeting) }}, {{ $user->name }}!</h1>
            <p class="text-slate-500 text-xs sm:text-sm mt-1">{{ __("Here's what's happening with your tickets today.") }}</p>
        </div>

        @if($user->isAdmin() && isset($resellersQuick) && $resellersQuick->isNotEmpty())
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <label for="reseller_quick" class="text-xs font-semibold text-slate-500 uppercase tracking-wide hidden sm:inline">{{ __('Reseller') }}</label>
            <select id="reseller_quick"
                    onchange="if (this.value) window.location = this.value"
                    class="text-xs sm:text-sm border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 w-full sm:w-auto">
                <option value="">{{ __('View Reseller Report') }}</option>
                @foreach($resellersQuick as $reseller)
                <option value="{{ route('reports.index', ['tab' => 'reseller', 'person_id' => $reseller->id]) }}">{{ $reseller->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
    </div>

    {{-- Global quick stats — scoped by role --}}
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
        <a href="{{ route('tickets.index') }}" class="flex items-center gap-2 sm:gap-3 bg-white border border-slate-200 rounded-xl px-2.5 sm:px-4 py-2.5 sm:py-3 hover:shadow-sm transition-shadow group">
            <div class="w-8 h-8 sm:w-9 sm:h-9 bg-indigo-100 rounded-lg flex items-center justify-center flex-shrink-0"><svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/></svg></div>
            <div><p id="stat-g-all" class="text-lg sm:text-xl font-bold text-indigo-700">{{ $globalAll }}</p><p class="text-xs text-slate-400 group-hover:text-indigo-500 transition-colors">{{ __('All Tickets') }}</p></div>
        </a>
        <a href="{{ route('tickets.index', ['status' => 'in_progress']) }}" class="flex items-center gap-2 sm:gap-3 bg-white border border-slate-200 rounded-xl px-2.5 sm:px-4 py-2.5 sm:py-3 hover:shadow-sm transition-shadow group">
            <div class="w-8 h-8 sm:w-9 sm:h-9 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0"><svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg></div>
            <div><p id="stat-g-inprog" class="text-lg sm:text-xl font-bold text-blue-600">{{ $globalInProg }}</p><p class="text-xs text-slate-400 group-hover:text-blue-500 transition-colors">{{ __('In Progress') }}</p></div>
        </a>
        <a href="{{ route('tickets.index', ['status' => 'pending']) }}" class="flex items-center gap-2 sm:gap-3 bg-white border border-slate-200 rounded-xl px-2.5 sm:px-4 py-2.5 sm:py-3 hover:shadow-sm transition-shadow group">
            <div class="w-8 h-8 sm:w-9 sm:h-9 bg-orange-100 rounded-lg flex items-center justify-center flex-shrink-0"><svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
            <div><p id="stat-g-pending" class="text-lg sm:text-xl font-bold text-orange-500">{{ $globalPending }}</p><p class="text-xs text-slate-400 group-hover:text-orange-500 transition-colors">{{ __('Pending') }}</p></div>
        </a>
        <a href="{{ route('tickets.index', ['status' => 'waiting_for_customer_feedback']) }}" class="flex items-center gap-2 sm:gap-3 bg-white border border-slate-200 rounded-xl px-2.5 sm:px-4 py-2.5 sm:py-3 hover:shadow-sm transition-shadow group">
            <div class="w-8 h-8 sm:w-9 sm:h-9 bg-violet-100 rounded-lg flex items-center justify-center flex-shrink-0"><svg class="w-4 h-4 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg></div>
            <div><p id="stat-g-waiting" class="text-lg sm:text-xl font-bold text-violet-600">{{ $globalWaiting }}</p><p class="text-xs text-slate-400 group-hover:text-violet-500 transition-colors">{{ __('Waiting') }}</p></div>
        </a>
        <a href="{{ route('tickets.index', ['status' => 'resolved']) }}" class="flex items-center gap-2 sm:gap-3 bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/80 rounded-xl px-2.5 sm:px-4 py-2.5 sm:py-3 hover:shadow-sm transition-shadow group">
            <div class="w-8 h-8 sm:w-9 sm:h-9 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center flex-shrink-0"><svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
            <div><p id="stat-g-resolved" class="text-lg sm:text-xl font-bold text-emerald-600 dark:text-emerald-400">{{ $globalResolved }}</p><p class="text-xs text-slate-400 group-hover:text-emerald-500 transition-colors">{{ __('Resolved') }}</p></div>
        </a>
    </div>

    @if($user->isAdmin())
    {{-- Admin Stat Cards — 4 steps + total + critical + overdue --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-7 gap-2.5 sm:gap-4 mb-6 sm:mb-8">

        <a href="{{ route('tickets.index', ['status' => 'in_progress']) }}"
           class="bg-white border border-slate-200 rounded-xl shadow-sm border-l-4 border-l-blue-400 p-3 sm:p-4 hover:shadow-md transition-shadow group block">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('In Progress') }}</span>
                <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </div>
            </div>
            <p id="stat-in-progress" class="text-xl sm:text-2xl lg:text-3xl font-bold text-blue-600">{{ $stats['in_progress'] }}</p>
            <p class="text-xs text-slate-400 mt-1 group-hover:text-indigo-600 transition-colors">{{ __('View all') }} →</p>
        </a>

        <a href="{{ route('tickets.index', ['status' => 'pending']) }}"
           class="bg-white border border-slate-200 rounded-xl shadow-sm border-l-4 border-l-orange-400 p-3 sm:p-4 hover:shadow-md transition-shadow group block">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('Pending') }}</span>
                <div class="w-8 h-8 bg-orange-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <p id="stat-pending" class="text-xl sm:text-2xl lg:text-3xl font-bold text-orange-500">{{ $stats['pending'] }}</p>
            <p class="text-xs text-slate-400 mt-1 group-hover:text-indigo-600 transition-colors">{{ __('View all') }} →</p>
        </a>

        <a href="{{ route('tickets.index', ['status' => 'waiting_for_customer_feedback']) }}"
           class="bg-white border border-slate-200 rounded-xl shadow-sm border-l-4 border-l-violet-400 p-3 sm:p-4 hover:shadow-md transition-shadow group block">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('Waiting') }}</span>
                <div class="w-8 h-8 bg-violet-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </div>
            </div>
            <p id="stat-waiting" class="text-xl sm:text-2xl lg:text-3xl font-bold text-violet-600">{{ $stats['waiting_for_customer_feedback'] }}</p>
            <p class="text-xs text-slate-400 mt-1 group-hover:text-indigo-600 transition-colors">{{ __('View all') }} →</p>
        </a>

        <a href="{{ route('tickets.index', ['priority' => 'critical']) }}"
           class="bg-white border border-slate-200 rounded-xl shadow-sm border-l-4 border-l-red-500 p-3 sm:p-4 hover:shadow-md transition-shadow group block">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('Critical') }}</span>
                <div class="w-8 h-8 bg-red-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
            </div>
            <p id="stat-critical" class="text-xl sm:text-2xl lg:text-3xl font-bold text-red-600">{{ $stats['critical'] }}</p>
            <p class="text-xs text-slate-400 mt-1 group-hover:text-indigo-600 transition-colors">{{ __('View all') }} →</p>
        </a>

        <a href="{{ route('tickets.index', ['status' => 'resolved']) }}"
           class="bg-white border border-slate-200 rounded-xl shadow-sm border-l-4 border-l-emerald-400 p-3 sm:p-4 hover:shadow-md transition-shadow group block">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('Resolved') }}</span>
                <div class="w-8 h-8 bg-emerald-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <p id="stat-resolved" class="text-xl sm:text-2xl lg:text-3xl font-bold text-emerald-600">{{ $stats['resolved'] }}</p>
            <p class="text-xs text-slate-400 mt-1 group-hover:text-indigo-600 transition-colors">{{ __('View all') }} →</p>
        </a>

        <a href="{{ route('tickets.index') }}"
           class="bg-white border border-slate-200 rounded-xl shadow-sm border-l-4 border-l-indigo-400 p-3 sm:p-4 hover:shadow-md transition-shadow group block">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('Total') }}</span>
                <div class="w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
            </div>
            <p id="stat-total" class="text-xl sm:text-2xl lg:text-3xl font-bold text-indigo-600">{{ $stats['total'] }}</p>
            <p class="text-xs text-slate-400 mt-1 group-hover:text-indigo-600 transition-colors">{{ __('View all') }} →</p>
        </a>

        <a href="{{ route('tickets.index', ['status' => 'overdue']) }}"
           class="bg-white border border-slate-200 rounded-xl shadow-sm border-l-4 border-l-rose-600 p-3 sm:p-4 hover:shadow-md transition-shadow group block">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('SLA Overdue') }}</span>
                <div class="w-8 h-8 bg-rose-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <p id="stat-overdue" class="text-xl sm:text-2xl lg:text-3xl font-bold text-rose-700">{{ $stats['overdue'] }}</p>
            <p class="text-xs text-slate-400 mt-1 group-hover:text-indigo-600 transition-colors">{{ __('View all') }} →</p>
        </a>

        <div class="bg-white border border-slate-200 rounded-xl shadow-sm border-l-4 border-l-teal-500 p-3 sm:p-4 block">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('Avg Resolution') }}</span>
                <div class="w-8 h-8 bg-teal-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <p id="stat-avg-resolution" class="text-xl sm:text-2xl lg:text-3xl font-bold text-teal-600">{{ $stats['avg_resolution_time'] ?? 'N/A' }}</p>
            <p class="text-xs text-slate-400 mt-1">{{ __('Mean time to resolve') }}</p>
        </div>
    </div>

    @elseif($user->isNoc())
    {{-- NOC Stat Cards — tickets assigned to me --}}
    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-3">{{ __('My Assigned Tickets') }}</p>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2.5 sm:gap-4 mb-6 sm:mb-8">
        <a href="{{ route('tickets.index', ['assigned' => 'me', 'status' => 'in_progress']) }}" class="bg-white border border-slate-200 rounded-xl shadow-sm border-l-4 border-l-blue-400 p-3 sm:p-4 hover:shadow-md transition-shadow group block">
            <div class="flex items-center justify-between mb-2"><span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('In Progress') }}</span><div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center"><svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg></div></div>
            <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-blue-600">{{ $stats['in_progress'] }}</p>
            <p class="text-xs text-slate-400 mt-1 group-hover:text-blue-500 transition-colors">{{ __('View all') }} →</p>
        </a>
        <a href="{{ route('tickets.index', ['assigned' => 'me', 'status' => 'pending']) }}" class="bg-white border border-slate-200 rounded-xl shadow-sm border-l-4 border-l-orange-400 p-3 sm:p-4 hover:shadow-md transition-shadow group block">
            <div class="flex items-center justify-between mb-2"><span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('Pending') }}</span><div class="w-8 h-8 bg-orange-50 rounded-lg flex items-center justify-center"><svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div></div>
            <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-orange-500">{{ $stats['pending'] }}</p>
            <p class="text-xs text-slate-400 mt-1 group-hover:text-orange-500 transition-colors">{{ __('View all') }} →</p>
        </a>
        <a href="{{ route('tickets.index', ['assigned' => 'me', 'status' => 'waiting_for_customer_feedback']) }}" class="bg-white border border-slate-200 rounded-xl shadow-sm border-l-4 border-l-violet-400 p-3 sm:p-4 hover:shadow-md transition-shadow group block">
            <div class="flex items-center justify-between mb-2"><span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('Waiting') }}</span><div class="w-8 h-8 bg-violet-50 rounded-lg flex items-center justify-center"><svg class="w-4 h-4 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg></div></div>
            <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-violet-600">{{ $stats['waiting_for_customer_feedback'] }}</p>
            <p class="text-xs text-slate-400 mt-1 group-hover:text-violet-500 transition-colors">{{ __('View all') }} →</p>
        </a>
        <a href="{{ route('tickets.index', ['assigned' => 'me', 'status' => 'resolved']) }}" class="bg-white border border-slate-200 rounded-xl shadow-sm border-l-4 border-l-emerald-400 p-3 sm:p-4 hover:shadow-md transition-shadow group block">
            <div class="flex items-center justify-between mb-2"><span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('Resolved') }}</span><div class="w-8 h-8 bg-emerald-50 rounded-lg flex items-center justify-center"><svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div></div>
            <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-emerald-600">{{ $stats['resolved'] }}</p>
            <p class="text-xs text-slate-400 mt-1 group-hover:text-emerald-500 transition-colors">{{ __('View all') }} →</p>
        </a>
        <a href="{{ route('tickets.index', ['assigned' => 'me']) }}" class="bg-white border border-slate-200 rounded-xl shadow-sm border-l-4 border-l-indigo-400 p-3 sm:p-4 hover:shadow-md transition-shadow group block">
            <div class="flex items-center justify-between mb-2"><span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('Total Assigned') }}</span><div class="w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center"><svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg></div></div>
            <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-indigo-600">{{ $stats['total'] }}</p>
            <p class="text-xs text-slate-400 mt-1 group-hover:text-indigo-600 transition-colors">{{ __('View all') }} →</p>
        </a>
    </div>

    @else
    {{-- Reseller Stat Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 sm:gap-4 mb-6 sm:mb-8">
        <a href="{{ route('tickets.index', ['status' => 'in_progress']) }}" class="bg-white border border-slate-200 rounded-xl shadow-sm border-l-4 border-l-blue-400 p-3 sm:p-4 hover:shadow-md transition-shadow group block">
            <div class="flex items-center justify-between mb-2"><span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('In Progress') }}</span><div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center"><svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg></div></div>
            <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-blue-600">{{ $stats['in_progress'] }}</p>
            <p class="text-xs text-slate-400 mt-1 group-hover:text-indigo-600 transition-colors">{{ __('View all') }} →</p>
        </a>
        <a href="{{ route('tickets.index', ['status' => 'pending']) }}" class="bg-white border border-slate-200 rounded-xl shadow-sm border-l-4 border-l-orange-400 p-3 sm:p-4 hover:shadow-md transition-shadow group block">
            <div class="flex items-center justify-between mb-2"><span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('Pending') }}</span><div class="w-8 h-8 bg-orange-50 rounded-lg flex items-center justify-center"><svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div></div>
            <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-orange-500">{{ $stats['pending'] }}</p>
            <p class="text-xs text-slate-400 mt-1 group-hover:text-indigo-600 transition-colors">{{ __('View all') }} →</p>
        </a>
        <a href="{{ route('tickets.index', ['status' => 'resolved']) }}" class="bg-white border border-slate-200 rounded-xl shadow-sm border-l-4 border-l-emerald-400 p-3 sm:p-4 hover:shadow-md transition-shadow group block">
            <div class="flex items-center justify-between mb-2"><span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('Resolved') }}</span><div class="w-8 h-8 bg-emerald-50 rounded-lg flex items-center justify-center"><svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div></div>
            <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-emerald-600">{{ $stats['resolved'] }}</p>
            <p class="text-xs text-slate-400 mt-1 group-hover:text-indigo-600 transition-colors">{{ __('View all') }} →</p>
        </a>
        <a href="{{ route('tickets.index') }}" class="bg-white border border-slate-200 rounded-xl shadow-sm border-l-4 border-l-indigo-400 p-3 sm:p-4 hover:shadow-md transition-shadow group block">
            <div class="flex items-center justify-between mb-2"><span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('Total') }}</span><div class="w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center"><svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg></div></div>
            <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-indigo-600">{{ $stats['total'] }}</p>
            <p class="text-xs text-slate-400 mt-1 group-hover:text-indigo-600 transition-colors">{{ __('View all') }} →</p>
        </a>
    </div>
    @endif

    {{-- Live Team Duty Stat Cards (Premium Ultra-Modern Cards) --}}
    @if(!$user->isReseller())
        @php
            $teams = \App\Models\User::TEAMS;
            $allStaff = \App\Models\User::whereNotNull('team')->get();
            $teamConfig = [
                'IT Team' => [
                    'label'       => 'IT Team',
                    'icon'        => '⚡',
                    'border'      => 'border-l-emerald-500',
                    'bg_gradient' => 'from-emerald-500/5 via-emerald-500/[0.02] to-transparent',
                    'metric_clr'  => 'text-emerald-600 dark:text-emerald-400',
                    'bar_clr'     => 'bg-emerald-500',
                ],
                'NOC team' => [
                    'label'       => 'NOC Team',
                    'icon'        => '📡',
                    'border'      => 'border-l-indigo-500',
                    'bg_gradient' => 'from-indigo-500/5 via-indigo-500/[0.02] to-transparent',
                    'metric_clr'  => 'text-indigo-600 dark:text-indigo-400',
                    'bar_clr'     => 'bg-indigo-500',
                ],
                'Call center' => [
                    'label'       => 'Call Center',
                    'icon'        => '🎧',
                    'border'      => 'border-l-cyan-500',
                    'bg_gradient' => 'from-cyan-500/5 via-cyan-500/[0.02] to-transparent',
                    'metric_clr'  => 'text-cyan-600 dark:text-cyan-400',
                    'bar_clr'     => 'bg-cyan-500',
                ],
                'Supervisor Team' => [
                    'label'       => 'Supervisor Team',
                    'icon'        => '🛡️',
                    'border'      => 'border-l-purple-500',
                    'bg_gradient' => 'from-purple-500/5 via-purple-500/[0.02] to-transparent',
                    'metric_clr'  => 'text-purple-600 dark:text-purple-400',
                    'bar_clr'     => 'bg-purple-500',
                ],
            ];
        @endphp

        <div class="mb-6 sm:mb-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
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
                            'label' => $teamKey, 'icon' => '👥', 'border' => 'border-l-indigo-500',
                            'bg_gradient' => 'from-indigo-500/5 via-transparent to-transparent',
                            'metric_clr' => 'text-indigo-600 dark:text-indigo-400', 'bar_clr' => 'bg-indigo-500'
                        ];
                    @endphp
                    <a href="{{ route('roster.index', ['team' => $teamKey]) }}"
                       class="bg-white dark:bg-slate-800/90 border border-slate-200/90 dark:border-slate-700/80 rounded-2xl shadow-sm border-l-4 {{ $cfg['border'] }} p-3 sm:p-5 sm:min-h-[135px] hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 group block relative overflow-hidden bg-gradient-to-br {{ $cfg['bg_gradient'] }}">

                        {{-- Subtle background Glow Effect --}}
                        <div class="absolute -right-6 -bottom-6 w-24 h-24 rounded-full {{ $cfg['bg_gradient'] }} blur-xl opacity-60 pointer-events-none"></div>

                        <div class="flex items-center justify-between gap-3 h-full relative z-10">
                            {{-- Left: Team Badge, Big Metric, Mini Progress & Link --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-1.5 mb-1.5">
                                    <span class="text-xs">{!! $cfg['icon'] !!}</span>
                                    <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider truncate" title="Duty &rarr; {{ $teamKey }}">
                                        Duty &rarr; {{ $teamKey }}
                                    </span>
                                </div>

                                <div class="flex items-baseline gap-2">
                                    <p class="text-2xl sm:text-3xl font-black tracking-tight {{ $cfg['metric_clr'] }}">{{ $act }}/{{ $tot }}</p>
                                    <span class="text-[10px] font-semibold text-slate-400 dark:text-slate-400">({{ $pct }}%)</span>
                                </div>

                                {{-- Progress Mini-bar --}}
                                <div class="w-24 bg-slate-100 dark:bg-slate-700/60 rounded-full h-1.5 mt-1.5 overflow-hidden">
                                    <div class="{{ $cfg['bar_clr'] }} h-1.5 rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
                                </div>

                                <p class="text-[11px] font-semibold text-slate-400 dark:text-slate-400 mt-2.5 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors flex items-center gap-1">
                                    <span>{{ __('View all') }}</span>
                                    <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                                </p>
                            </div>

                            {{-- Right: Styled Pill Badges for Shift Breakdown --}}
                            <div class="text-right text-[10px] sm:text-[11px] space-y-1.5 sm:space-y-2.5 font-medium flex-shrink-0">
                                <div class="flex items-center justify-end gap-2 px-3 py-1.5 rounded-xl bg-amber-500/10 dark:bg-amber-400/10 text-amber-700 dark:text-amber-300 border border-amber-500/20">
                                    <span>{{ $dayShift }} Day shift</span>
                                    <span class="text-xs">☀️</span>
                                </div>
                                <div class="flex items-center justify-end gap-2 px-3 py-1.5 rounded-xl bg-indigo-500/10 dark:bg-indigo-400/10 text-indigo-700 dark:text-indigo-300 border border-indigo-500/20">
                                    <span>{{ $nightShift }} Night shift</span>
                                    <span class="text-xs">🌙</span>
                                </div>
                                <div class="flex items-center justify-end gap-2 px-3 py-1.5 rounded-xl bg-violet-500/10 dark:bg-violet-400/10 text-violet-700 dark:text-violet-300 border border-violet-500/20">
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

    {{-- Admin only: Charts + Leaderboard --}}
    @if($user->isAdmin())
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-4 sm:p-5">
            <h3 class="text-sm font-semibold text-slate-700 mb-3">{{ __('Tickets Created (Last 14 Days)') }}</h3>
            <canvas id="lineChart" height="120"></canvas>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-4 sm:p-5">
            <h3 class="text-sm font-semibold text-slate-700 mb-3">{{ __('By Category') }}</h3>
            @php $catTotal = $chartByCategory->sum(); @endphp
            @if($chartByCategory->isEmpty())
            <div class="h-32 flex items-center justify-center text-sm text-slate-400">{{ __('No ticket data yet.') }}</div>
            @else
            <div class="flex flex-col sm:flex-row items-center gap-4 sm:gap-6">
                <div class="relative w-28 h-28 sm:w-32 sm:h-32 flex-shrink-0">
                    <canvas id="categoryChart" width="128" height="128"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                        <span class="text-xl font-bold text-slate-700">{{ $catTotal }}</span>
                        <span class="text-[10px] text-slate-400 uppercase tracking-wide">{{ __('Total') }}</span>
                    </div>
                </div>
                <ul class="flex-1 space-y-2 min-w-0">
                    @foreach($chartByCategory->sortDesc() as $label => $count)
                    @php $pct = $catTotal ? round($count / $catTotal * 100) : 0; @endphp
                    <li class="flex items-center gap-2 text-sm">
                        <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background-color: var(--cat-color-{{ $loop->index % 7 }})"></span>
                        <span class="text-slate-600 truncate flex-1">{{ ucfirst(str_replace('_',' ',$label)) }}</span>
                        <span class="text-slate-400 text-xs">{{ $pct }}%</span>
                        <span class="font-semibold text-slate-700 w-6 text-right">{{ $count }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
    </div>

    @if(isset($nocLeaderboard) && $nocLeaderboard->isNotEmpty())
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-4 sm:p-5 mb-6">
        <h3 class="text-sm font-semibold text-slate-700 mb-4 flex items-center justify-between">
            <span class="flex items-center gap-2">🏆 {{ __('NOC Leaderboard & Avg Resolution Time') }}</span>
            <span class="text-xs text-slate-400 font-normal">{{ __('This Month') }}</span>
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            @foreach($nocLeaderboard as $noc)
            <div class="flex items-center gap-3 p-3 bg-slate-50 border border-slate-100 rounded-xl hover:border-indigo-200 transition-colors">
                <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-700 font-bold text-xs flex items-center justify-center flex-shrink-0">
                    {{ strtoupper(substr($noc->name, 0, 2)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-semibold text-slate-800 truncate">{{ $noc->name }}</p>
                    <div class="flex items-center gap-1.5 text-[11px] text-slate-500 mt-0.5">
                        <span class="text-emerald-600 font-semibold">✓ {{ $noc->resolved_count }}</span>
                        <span class="text-slate-300">•</span>
                        <span class="text-teal-600 font-medium">⏱️ {{ $noc->avg_resolution_time }}</span>
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
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-4 sm:p-5 mb-6">
        <h3 class="text-sm font-semibold text-slate-700 mb-3">{{ __('My Resolutions (Last 7 Days)') }}</h3>
        <canvas id="nocBarChart" height="80"></canvas>
    </div>
    @endif

    @if($user->isAdmin())
    <script>
    (function() {
        const days = @json($chartDays);
        const created = @json($chartCreated);

        new Chart(document.getElementById('lineChart'), {
            type: 'line',
            data: { labels: days, datasets: [{ label: 'Tickets Created', data: created, borderColor: '#6366f1', backgroundColor: 'rgba(99,102,241,0.1)', tension: 0.4, fill: true }] },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
        });

        const categoryCanvas = document.getElementById('categoryChart');
        if (categoryCanvas) {
            const catData = @json($chartByCategory->sortDesc());
            const catColors = ['#6366f1','#f97316','#eab308','#3b82f6','#10b981','#ec4899','#8b5cf6'];
            new Chart(categoryCanvas, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(catData).map(k => k.charAt(0).toUpperCase() + k.slice(1).replace(/_/g, ' ')),
                    datasets: [{ data: Object.values(catData), backgroundColor: catColors, borderWidth: 2, borderColor: '#fff' }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: { legend: { display: false }, tooltip: { callbacks: { label: (ctx) => `${ctx.label}: ${ctx.parsed}` } } }
                }
            });
        }
    })();

    </script>
    @elseif($user->isNoc())
    <script>
    (function() {
        const days = @json($chartDays);
        const resolved = @json($chartCreated);
        new Chart(document.getElementById('nocBarChart'), {
            type: 'bar',
            data: { labels: days, datasets: [{ label: 'Resolutions', data: resolved, backgroundColor: '#6366f1' }] },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
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
