<x-app-layout>
    <div class="space-y-6" x-data="ticketCalendar()">
        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2.5">
                    <span class="p-2 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200/50 dark:border-indigo-800/50">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </span>
                    {{ __('Ticket Activity Calendar') }}
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    {{ __('Track ticket volume created and resolved by date across the month') }} &bull;
                    <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $targetDate->format('F Y') }}</span>
                </p>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
                {{-- View Switcher --}}
                <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-800 rounded-lg p-1">
                    <a href="{{ route('tickets.index') }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-white dark:hover:bg-slate-700 text-sm font-medium transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        {{ __('List') }}
                    </a>
                    <a href="{{ route('board.index') }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-white dark:hover:bg-slate-700 text-sm font-medium transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
                        {{ __('Board') }}
                    </a>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-white dark:bg-slate-700 shadow-sm text-indigo-600 dark:text-indigo-400 text-sm font-semibold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        {{ __('Calendar') }}
                    </span>
                </div>

                <a href="{{ route('tickets.create') }}"
                   class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('New Ticket') }}
                </a>
            </div>
        </div>

        {{-- Monthly KPI Summary Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Card 1: Created This Month --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        {{ __('Created This Month') }}
                    </span>
                    <span class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    </span>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-2xl sm:text-3xl font-extrabold text-slate-800 dark:text-slate-100">
                        {{ number_format($monthCreatedTotal) }}
                    </span>
                    <span class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">
                        {{ __('tickets created') }}
                    </span>
                </div>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-2">
                    {{ __('Total logged in') }} {{ $targetDate->format('M Y') }}
                </p>
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-emerald-500/80"></div>
            </div>

            {{-- Card 2: Solved This Month --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        {{ __('Solved This Month') }}
                    </span>
                    <span class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </span>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-2xl sm:text-3xl font-extrabold text-slate-800 dark:text-slate-100">
                        {{ number_format($monthSolvedTotal) }}
                    </span>
                    <span class="text-xs text-indigo-600 dark:text-indigo-400 font-medium">
                        {{ __('tickets solved') }}
                    </span>
                </div>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-2">
                    {{ __('Resolved or closed in') }} {{ $targetDate->format('M Y') }}
                </p>
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-indigo-500/80"></div>
            </div>

            {{-- Card 3: Resolution Rate --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        {{ __('Resolution Rate') }}
                    </span>
                    <span class="w-9 h-9 rounded-xl bg-violet-50 dark:bg-violet-950/60 text-violet-600 dark:text-violet-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </span>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-2xl sm:text-3xl font-extrabold text-slate-800 dark:text-slate-100">
                        {{ $resolutionRate }}%
                    </span>
                    <span class="text-xs text-violet-600 dark:text-violet-400 font-medium">
                        {{ __('solved vs created') }}
                    </span>
                </div>
                <div class="mt-2 w-full bg-slate-100 dark:bg-slate-800 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-violet-500 h-1.5 rounded-full transition-all duration-500" style="width: {{ min(100, $resolutionRate) }}%"></div>
                </div>
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-violet-500/80"></div>
            </div>

            {{-- Card 4: Peak Activity Day --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        {{ __('Peak Activity Day') }}
                    </span>
                    <span class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </span>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-2xl sm:text-3xl font-extrabold text-slate-800 dark:text-slate-100">
                        {{ $peakActivityDate ?? __('None') }}
                    </span>
                    @if($peakActivityCount > 0)
                        <span class="text-xs text-amber-600 dark:text-amber-400 font-medium">
                            ({{ $peakActivityCount }} {{ __('actions') }})
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-2">
                    {{ __('Busiest single date this month') }}
                </p>
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-amber-500/80"></div>
            </div>
        </div>

        {{-- Month Navigation & Filter Controls --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
            <form method="GET" action="{{ route('tickets.calendar') }}" class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                {{-- Month / Year Navigation Buttons --}}
                <div class="flex items-center gap-2 flex-wrap">
                    {{-- Previous Month --}}
                    <a href="{{ route('tickets.calendar', array_merge(request()->except(['year', 'month']), ['year' => $prevMonth->year, 'month' => $prevMonth->month])) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-sm font-medium shadow-sm transition-all"
                       title="{{ $prevMonth->format('F Y') }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        <span class="hidden sm:inline">{{ $prevMonth->format('M') }}</span>
                    </a>

                    {{-- Current Selected Month / Today Button --}}
                    <a href="{{ route('tickets.calendar', array_merge(request()->except(['year', 'month']), ['year' => now()->year, 'month' => now()->month])) }}"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border {{ ($targetDate->year === now()->year && $targetDate->month === now()->month) ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 font-bold' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-medium' }} text-sm shadow-sm transition-all">
                        <span>{{ __('Today') }}</span>
                    </a>

                    {{-- Next Month --}}
                    <a href="{{ route('tickets.calendar', array_merge(request()->except(['year', 'month']), ['year' => $nextMonth->year, 'month' => $nextMonth->month])) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-sm font-medium shadow-sm transition-all"
                       title="{{ $nextMonth->format('F Y') }}">
                        <span class="hidden sm:inline">{{ $nextMonth->format('M') }}</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>

                    {{-- Month and Year Direct Pickers --}}
                    <div class="flex items-center gap-1.5 ml-1">
                        <select name="month" onchange="this.form.submit()"
                                class="border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm font-semibold text-slate-800 dark:text-slate-100 bg-slate-50 dark:bg-slate-800 focus:ring-2 focus:ring-indigo-500">
                            @for($m = 1; $m <= 12; $m++)
                                @php $dateObj = \Carbon\Carbon::create(null, $m, 1); @endphp
                                <option value="{{ $m }}" {{ $targetDate->month === $m ? 'selected' : '' }}>
                                    {{ $dateObj->format('F') }}
                                </option>
                            @endfor
                        </select>

                        <select name="year" onchange="this.form.submit()"
                                class="border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm font-semibold text-slate-800 dark:text-slate-100 bg-slate-50 dark:bg-slate-800 focus:ring-2 focus:ring-indigo-500">
                            @for($y = now()->year - 2; $y <= now()->year + 2; $y++)
                                <option value="{{ $y }}" {{ $targetDate->year === $y ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @endfor
                        </select>
                    </div>
                </div>

                {{-- Filters: Category, Area, Priority, Assignee --}}
                <div class="flex items-center gap-2 flex-wrap">
                    {{-- Category --}}
                    <select name="category" onchange="this.form.submit()"
                            class="border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-medium text-slate-700 dark:text-slate-200 bg-slate-50 dark:bg-slate-800 focus:ring-2 focus:ring-indigo-500">
                        <option value="">{{ __('All Categories') }}</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->slug ?? $cat->name }}" {{ request('category') === ($cat->slug ?? $cat->name) ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>

                    {{-- Area --}}
                    @if(isset($allAreas) && $allAreas->count() > 0)
                        <select name="area" onchange="this.form.submit()"
                                class="border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-medium text-slate-700 dark:text-slate-200 bg-slate-50 dark:bg-slate-800 focus:ring-2 focus:ring-indigo-500">
                            <option value="">📍 {{ __('All Areas') }}</option>
                            @foreach($allAreas as $area)
                                <option value="{{ $area }}" {{ strtolower((string)request('area')) === strtolower((string)$area) ? 'selected' : '' }}>
                                    {{ $area }}
                                </option>
                            @endforeach
                        </select>
                    @endif

                    {{-- Assignee (Admins/NOC) --}}
                    @if(isset($nocUsers) && $nocUsers->count() > 0)
                        <select name="assigned_to" onchange="this.form.submit()"
                                class="border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-medium text-slate-700 dark:text-slate-200 bg-slate-50 dark:bg-slate-800 focus:ring-2 focus:ring-indigo-500">
                            <option value="">👤 {{ __('All Staff') }}</option>
                            @foreach($nocUsers as $staff)
                                <option value="{{ $staff->id }}" {{ request('assigned_to') == $staff->id ? 'selected' : '' }}>
                                    {{ $staff->name }}
                                </option>
                            @endforeach
                        </select>
                    @endif

                    @if(request('category') || request('area') || request('assigned_to') || request('priority'))
                        <a href="{{ route('tickets.calendar', ['year' => $targetDate->year, 'month' => $targetDate->month]) }}"
                           class="text-xs text-rose-600 dark:text-rose-400 hover:underline px-2 py-1 font-medium">
                            {{ __('Clear Filters') }}
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Legend and Information Banner --}}
        <div class="flex items-center justify-between flex-wrap gap-3 px-1 text-xs text-slate-500 dark:text-slate-400">
            <div class="flex items-center gap-4 flex-wrap">
                <span class="flex items-center gap-1.5 font-medium">
                    <span class="w-3 h-3 rounded-full bg-emerald-500 inline-block shadow-sm"></span>
                    <strong class="text-slate-700 dark:text-slate-300">{{ __('Created') }} (টিকেট তৈরি)</strong>
                </span>
                <span class="flex items-center gap-1.5 font-medium">
                    <span class="w-3 h-3 rounded-full bg-indigo-600 inline-block shadow-sm"></span>
                    <strong class="text-slate-700 dark:text-slate-300">{{ __('Solved') }} (টিকেট সমাধান)</strong>
                </span>
                <span class="flex items-center gap-1.5 font-medium">
                    <span class="w-3 h-3 rounded-md ring-2 ring-indigo-500 inline-block"></span>
                    <strong class="text-slate-700 dark:text-slate-300">{{ __('Today') }} (আজকের দিন)</strong>
                </span>
            </div>
            <div class="flex items-center gap-1.5 text-slate-400 dark:text-slate-500">
                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ __('Click any date to see exact ticket details and list') }}</span>
            </div>
        </div>

        {{-- Calendar Grid --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
            {{-- Day of Week Headers (Sun - Sat) --}}
            <div class="grid grid-cols-7 border-b border-slate-200 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/50 text-center">
                @php
                    $daysOfWeek = [
                        ['en' => 'Sun', 'bn' => 'রবি', 'weekend' => false],
                        ['en' => 'Mon', 'bn' => 'সোম', 'weekend' => false],
                        ['en' => 'Tue', 'bn' => 'মঙ্গল', 'weekend' => false],
                        ['en' => 'Wed', 'bn' => 'বুধ', 'weekend' => false],
                        ['en' => 'Thu', 'bn' => 'বৃহস্পতি', 'weekend' => false],
                        ['en' => 'Fri', 'bn' => 'শুক্র', 'weekend' => true],
                        ['en' => 'Sat', 'bn' => 'শনি', 'weekend' => true],
                    ];
                @endphp
                @foreach($daysOfWeek as $dow)
                    <div class="py-3 px-1 sm:px-2 {{ $dow['weekend'] ? 'bg-amber-50/40 dark:bg-amber-950/20' : '' }}">
                        <span class="block text-xs sm:text-sm font-bold {{ $dow['weekend'] ? 'text-amber-700 dark:text-amber-400' : 'text-slate-700 dark:text-slate-200' }}">
                            {{ __($dow['en']) }}
                        </span>
                        <span class="text-[10px] text-slate-400 dark:text-slate-500 hidden sm:block">
                            {{ $dow['bn'] }}
                        </span>
                    </div>
                @endforeach
            </div>

            {{-- Calendar Weeks & Day Cells --}}
            <div class="divide-y divide-slate-200 dark:divide-slate-800">
                @foreach($calendarWeeks as $week)
                    <div class="grid grid-cols-7 divide-x divide-slate-200 dark:divide-slate-800 min-h-28 sm:min-h-32">
                        @foreach($week as $day)
                            @php
                                $isCurrent = $day['is_current_month'];
                                $isToday = $day['is_today'];
                                $hasActivity = $day['created_count'] > 0 || $day['solved_count'] > 0;
                            @endphp
                            <div @click="openDay('{{ $day['date'] }}', {{ $day['created_count'] }}, {{ $day['solved_count'] }})"
                                 class="relative p-1.5 sm:p-2.5 transition-all cursor-pointer group flex flex-col justify-between
                                        {{ !$isCurrent ? 'bg-slate-50/40 dark:bg-slate-900/40 opacity-50' : 'bg-white dark:bg-slate-900' }}
                                        {{ $isToday ? 'ring-2 ring-inset ring-indigo-500 bg-indigo-50/25 dark:bg-indigo-950/20' : 'hover:bg-slate-50 dark:hover:bg-slate-800/60' }}
                                        {{ $day['is_weekend'] && $isCurrent ? 'bg-slate-50/20 dark:bg-slate-800/10' : '' }}">

                                {{-- Date Top Row --}}
                                <div class="flex items-center justify-between mb-1.5">
                                    {{-- Day Number --}}
                                    @if($isToday)
                                        <span class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-indigo-600 text-white font-bold text-xs sm:text-sm flex items-center justify-center shadow-sm">
                                            {{ $day['day'] }}
                                        </span>
                                    @else
                                        <span class="text-xs sm:text-sm font-semibold {{ $isCurrent ? 'text-slate-800 dark:text-slate-200' : 'text-slate-400 dark:text-slate-600' }} group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                            {{ $day['day'] }}
                                        </span>
                                    @endif

                                    {{-- Today Badge / Quick inspect icon --}}
                                    @if($isToday)
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 hidden sm:inline-block">
                                            {{ __('Today') }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Metrics Badges Container --}}
                                <div class="space-y-1 sm:space-y-1.5 my-auto">
                                    {{-- Created Tickets Badge --}}
                                    @if($day['created_count'] > 0)
                                        <div class="flex items-center justify-between px-1.5 sm:px-2 py-0.5 sm:py-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/80 dark:border-emerald-800/60 text-emerald-800 dark:emerald-200 shadow-xs transition-transform group-hover:scale-[1.02]"
                                             title="{{ $day['created_count'] }} {{ __('tickets created on this date') }}">
                                            <span class="inline-flex items-center gap-1 text-[11px] sm:text-xs font-bold">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                <span class="hidden sm:inline">{{ __('Created') }}:</span>
                                                <span class="sm:hidden">+</span>
                                                <span>{{ $day['created_count'] }}</span>
                                            </span>
                                            <span class="text-[10px] font-medium text-emerald-600 dark:text-emerald-400 hidden md:inline">
                                                {{ __('new') }}
                                            </span>
                                        </div>
                                    @endif

                                    {{-- Solved Tickets Badge --}}
                                    @if($day['solved_count'] > 0)
                                        <div class="flex items-center justify-between px-1.5 sm:px-2 py-0.5 sm:py-1 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200/80 dark:border-indigo-800/60 text-indigo-800 dark:indigo-200 shadow-xs transition-transform group-hover:scale-[1.02]"
                                             title="{{ $day['solved_count'] }} {{ __('tickets solved on this date') }}">
                                            <span class="inline-flex items-center gap-1 text-[11px] sm:text-xs font-bold">
                                                <svg class="w-3 h-3 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                <span class="hidden sm:inline">{{ __('Solved') }}:</span>
                                                <span class="sm:hidden">✓</span>
                                                <span>{{ $day['solved_count'] }}</span>
                                            </span>
                                            <span class="text-[10px] font-medium text-indigo-600 dark:text-indigo-400 hidden md:inline">
                                                {{ __('done') }}
                                            </span>
                                        </div>
                                    @endif

                                    {{-- Empty Day Indicator --}}
                                    @if(!$hasActivity)
                                        <div class="text-center py-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <span class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">
                                                + {{ __('View Date') }}
                                            </span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Bottom Activity Bar --}}
                                <div class="mt-1 pt-1 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[10px] text-slate-400 dark:text-slate-500">
                                    @if($hasActivity)
                                        <span class="font-medium text-slate-600 dark:text-slate-400">
                                            {{ $day['total_activity'] }} {{ __('total') }}
                                        </span>
                                        <span class="text-indigo-600 dark:text-indigo-400 opacity-0 group-hover:opacity-100 transition-opacity font-semibold">
                                            {{ __('Details') }} &rarr;
                                        </span>
                                    @else
                                        <span class="text-slate-300 dark:text-slate-700">&mdash;</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Interactive Day Details Slide-over / Modal (Alpine.js) --}}
        <div x-show="modalOpen"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             aria-labelledby="modal-title" role="dialog" aria-modal="true">
            {{-- Backdrop --}}
            <div x-show="modalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="modalOpen = false"
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"></div>

            <div class="fixed inset-0 z-10 overflow-y-auto flex items-center justify-center p-3 sm:p-6">
                <div x-show="modalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     @click.stop
                     class="relative bg-white dark:bg-slate-900 rounded-2xl max-w-2xl w-full max-h-[85vh] flex flex-col shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">

                    {{-- Modal Header --}}
                    <div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50/60 dark:bg-slate-800/40">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="p-1.5 rounded-lg bg-indigo-100 dark:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </span>
                                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100" x-text="dayData.formatted_date || selectedDate"></h3>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                <span x-text="dayData.created_count || 0"></span> {{ __('created') }} &bull;
                                <span x-text="dayData.solved_count || 0"></span> {{ __('solved') }}
                            </p>
                        </div>

                        <button @click="modalOpen = false"
                                class="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    {{-- Modal Tabs --}}
                    <div class="px-5 pt-3 border-b border-slate-200 dark:border-slate-800 flex items-center gap-2">
                        <button @click="activeTab = 'created'"
                                :class="activeTab === 'created' ? 'border-emerald-600 text-emerald-600 dark:text-emerald-400 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 font-medium'"
                                class="py-2.5 px-3 border-b-2 text-sm flex items-center gap-1.5 transition-colors">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>{{ __('Created Tickets') }}</span>
                            <span class="px-1.5 py-0.5 rounded-full text-xs bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:emerald-300 font-bold" x-text="dayData.created_count || 0"></span>
                        </button>

                        <button @click="activeTab = 'solved'"
                                :class="activeTab === 'solved' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 font-medium'"
                                class="py-2.5 px-3 border-b-2 text-sm flex items-center gap-1.5 transition-colors">
                            <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                            <span>{{ __('Solved Tickets') }}</span>
                            <span class="px-1.5 py-0.5 rounded-full text-xs bg-indigo-100 dark:bg-indigo-950/60 text-indigo-800 dark:indigo-300 font-bold" x-text="dayData.solved_count || 0"></span>
                        </button>
                    </div>

                    {{-- Modal Body with Tickets List --}}
                    <div class="p-4 sm:p-5 overflow-y-auto flex-1 space-y-3">
                        {{-- Loading State --}}
                        <div x-show="loading" class="py-12 text-center text-slate-500 dark:text-slate-400 space-y-3">
                            <svg class="animate-spin w-8 h-8 text-indigo-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <p class="text-sm">{{ __('Loading ticket details...') }}</p>
                        </div>

                        {{-- Tab: Created Tickets --}}
                        <div x-show="!loading && activeTab === 'created'" class="space-y-2.5">
                            <template x-if="dayData.created_tickets && dayData.created_tickets.length > 0">
                                <div class="space-y-2">
                                    <template x-for="t in dayData.created_tickets" :key="t.id">
                                        <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 hover:bg-white dark:hover:bg-slate-800 hover:border-emerald-300 dark:hover:border-emerald-700 transition-all flex items-start justify-between gap-3 group">
                                            <div class="space-y-1 min-w-0">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <span class="font-mono text-xs font-bold text-slate-500 dark:text-slate-400" x-text="t.ticket_key"></span>
                                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300" x-text="t.status"></span>
                                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider"
                                                          :class="{
                                                              'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300': t.priority === 'critical',
                                                              'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300': t.priority === 'high',
                                                              'bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300': t.priority === 'medium',
                                                              'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300': t.priority === 'low'
                                                          }" x-text="t.priority"></span>
                                                    <span class="text-xs text-slate-400 dark:text-slate-500" x-text="t.time"></span>
                                                </div>
                                                <h4 class="text-sm font-semibold text-slate-800 dark:text-slate-100 line-clamp-1" x-text="t.title"></h4>
                                                <div class="flex items-center gap-3 text-xs text-slate-500 dark:text-slate-400 flex-wrap">
                                                    <span x-show="t.client_name" class="flex items-center gap-1">
                                                        <span>👤</span>
                                                        <span x-text="t.client_name"></span>
                                                    </span>
                                                    <span x-show="t.area" class="flex items-center gap-1">
                                                        <span>📍</span>
                                                        <span x-text="t.area"></span>
                                                    </span>
                                                    <span x-show="t.assignee" class="flex items-center gap-1">
                                                        <span>🔧</span>
                                                        <span x-text="t.assignee"></span>
                                                    </span>
                                                </div>
                                            </div>
                                            <a :href="t.url"
                                               class="shrink-0 inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs transition-colors">
                                                <span>{{ __('View') }}</span>
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                            </a>
                                        </div>
                                    </template>
                                </div>
                            </template>
                            <template x-if="!dayData.created_tickets || dayData.created_tickets.length === 0">
                                <div class="py-10 text-center text-slate-400 dark:text-slate-500">
                                    <svg class="w-12 h-12 mx-auto text-slate-300 dark:text-slate-700 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <p class="text-sm">{{ __('No tickets created on this date.') }}</p>
                                </div>
                            </template>
                        </div>

                        {{-- Tab: Solved Tickets --}}
                        <div x-show="!loading && activeTab === 'solved'" class="space-y-2.5">
                            <template x-if="dayData.solved_tickets && dayData.solved_tickets.length > 0">
                                <div class="space-y-2">
                                    <template x-for="t in dayData.solved_tickets" :key="t.id">
                                        <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 hover:bg-white dark:hover:bg-slate-800 hover:border-indigo-300 dark:hover:border-indigo-700 transition-all flex items-start justify-between gap-3 group">
                                            <div class="space-y-1 min-w-0">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <span class="font-mono text-xs font-bold text-slate-500 dark:text-slate-400" x-text="t.ticket_key"></span>
                                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-indigo-100 text-indigo-800 dark:bg-indigo-950/60 dark:text-indigo-300">
                                                        ✓ <span x-text="t.status"></span>
                                                    </span>
                                                    <span class="text-xs text-slate-400 dark:text-slate-500" x-text="t.time"></span>
                                                </div>
                                                <h4 class="text-sm font-semibold text-slate-800 dark:text-slate-100 line-clamp-1" x-text="t.title"></h4>
                                                <div class="flex items-center gap-3 text-xs text-slate-500 dark:text-slate-400 flex-wrap">
                                                    <span x-show="t.client_name" class="flex items-center gap-1">
                                                        <span>👤</span>
                                                        <span x-text="t.client_name"></span>
                                                    </span>
                                                    <span x-show="t.area" class="flex items-center gap-1">
                                                        <span>📍</span>
                                                        <span x-text="t.area"></span>
                                                    </span>
                                                    <span x-show="t.assignee" class="flex items-center gap-1">
                                                        <span>🔧 {{ __('Resolved by') }}:</span>
                                                        <span class="font-medium text-slate-700 dark:text-slate-300" x-text="t.assignee"></span>
                                                    </span>
                                                </div>
                                            </div>
                                            <a :href="t.url"
                                               class="shrink-0 inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-xs transition-colors">
                                                <span>{{ __('View') }}</span>
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                            </a>
                                        </div>
                                    </template>
                                </div>
                            </template>
                            <template x-if="!dayData.solved_tickets || dayData.solved_tickets.length === 0">
                                <div class="py-10 text-center text-slate-400 dark:text-slate-500">
                                    <svg class="w-12 h-12 mx-auto text-slate-300 dark:text-slate-700 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <p class="text-sm">{{ __('No tickets were solved on this date.') }}</p>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40 flex items-center justify-between flex-wrap gap-2">
                        <div class="flex items-center gap-2">
                            <a :href="dayData.view_all_created_url || '#'"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-emerald-300 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:emerald-200 text-xs font-semibold hover:bg-emerald-100 transition-colors">
                                <span>{{ __('Open Created List') }}</span>
                                <span>&rarr;</span>
                            </a>
                            <a :href="dayData.view_all_solved_url || '#'"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-indigo-300 dark:border-indigo-800 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-800 dark:indigo-200 text-xs font-semibold hover:bg-indigo-100 transition-colors">
                                <span>{{ __('Open Solved List') }}</span>
                                <span>&rarr;</span>
                            </a>
                        </div>

                        <button @click="modalOpen = false"
                                class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 text-xs font-semibold transition-colors">
                            {{ __('Close') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Alpine Data Script --}}
    <script>
        function ticketCalendar() {
            return {
                modalOpen: false,
                loading: false,
                selectedDate: '',
                activeTab: 'created',
                dayData: {
                    date: '',
                    formatted_date: '',
                    created_count: 0,
                    solved_count: 0,
                    created_tickets: [],
                    solved_tickets: [],
                    view_all_created_url: '',
                    view_all_solved_url: ''
                },
                openDay(dateStr, createdCount, solvedCount) {
                    this.selectedDate = dateStr;
                    this.modalOpen = true;
                    this.loading = true;
                    // Default to solved tab if only solved tickets exist, else created
                    if (createdCount === 0 && solvedCount > 0) {
                        this.activeTab = 'solved';
                    } else {
                        this.activeTab = 'created';
                    }

                    fetch(`{{ route('tickets.calendar.day-details') }}?date=${dateStr}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.dayData = data;
                        this.loading = false;
                    })
                    .catch(err => {
                        console.error('Error fetching calendar day details:', err);
                        this.loading = false;
                    });
                }
            };
        }
    </script>
</x-app-layout>
