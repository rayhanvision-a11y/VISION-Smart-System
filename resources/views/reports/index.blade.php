<x-app-layout>
@php
    $periodLabels = [
        'today'   => __('Today'),
        'week'    => __('This Week'),
        'month'   => __('This Month'),
        '3months' => __('Last 3 Months'),
        '6months' => __('Last 6 Months'),
        'year'    => __('Last 1 Year'),
        'custom'  => __('Custom Range'),
    ];
    $members = $tab === 'team' ? $teamMembers : $resellers;
    $resRate = $periodStats['total'] > 0
        ? round(($periodStats['resolved_all_time'] ?? $periodStats['resolved']) / $periodStats['total'] * 100)
        : 0;
@endphp

<style>
    /* ── Self-contained resilient styling for Reports Dashboard ── */
    .rpt-wrapper {
        width: 100%;
        display: flex;
        flex-direction: column;
        gap: 20px;
        padding-bottom: 48px;
    }
    .rpt-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        transition: all 0.2s ease;
    }
    .dark .rpt-card {
        background: #1e293b;
        border-color: #334155;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.2);
    }
    .rpt-card-header {
        padding: 16px 20px;
        border-bottom: 1px solid #f1f5f9;
        background: rgba(248, 250, 252, 0.6);
        border-top-left-radius: 16px;
        border-top-right-radius: 16px;
    }
    .dark .rpt-card-header {
        border-bottom-color: #334155;
        background: rgba(15, 23, 42, 0.4);
    }
    .rpt-inner-card {
        background: #f8fafc;
        border: 1px solid #f1f5f9;
        border-radius: 12px;
    }
    .dark .rpt-inner-card {
        background: #0f172a;
        border-color: #334155;
    }

    /* ── Scoped Gap and Layout Fallbacks for Reports Page ── */
    .rpt-col-stack {
        display: flex !important;
        flex-direction: column !important;
        gap: 20px !important;
    }
    .rpt-wrapper .gap-1 { gap: 4px !important; }
    .rpt-wrapper .gap-1\.5 { gap: 6px !important; }
    .rpt-wrapper .gap-2 { gap: 8px !important; }
    .rpt-wrapper .gap-2\.5 { gap: 10px !important; }
    .rpt-wrapper .gap-3 { gap: 12px !important; }
    .rpt-wrapper .gap-3\.5 { gap: 14px !important; }
    .rpt-wrapper .gap-4 { gap: 16px !important; }
    .rpt-wrapper .gap-5 { gap: 20px !important; }
    .rpt-wrapper .gap-6 { gap: 24px !important; }

    /* Inset fallback scoped */
    .rpt-wrapper .inset-0 {
        top: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        left: 0 !important;
    }

    /* ── KPI 5-Cards Grid with Guaranteed Spacing ── */
    .rpt-kpi-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }
    @media (min-width: 640px) {
        .rpt-kpi-grid {
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }
    }
    @media (min-width: 1200px) {
        .rpt-kpi-grid {
            grid-template-columns: repeat(5, 1fr);
            gap: 18px;
        }
    }

    /* ── Master-Detail 2-Column Grid with Guaranteed Spacing ── */
    .rpt-dashboard-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 24px;
        align-items: start;
    }
    @media (min-width: 1024px) {
        .rpt-dashboard-grid {
            grid-template-columns: 7fr 5fr;
            gap: 24px;
        }
        .rpt-dashboard-grid.drilldown-active {
            grid-template-columns: 5fr 7fr;
            gap: 24px;
        }
    }

    /* ── SVG Gauge Container & Progress Ring ── */
    .rpt-gauge-wrap {
        position: relative;
        width: 60px;
        height: 60px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .rpt-gauge-svg {
        width: 60px;
        height: 60px;
        transform: rotate(-90deg);
        display: block;
    }
    .rpt-gauge-track {
        stroke: #e2e8f0;
    }
    .dark .rpt-gauge-track {
        stroke: #334155;
    }
    .rpt-gauge-center {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        pointer-events: none;
    }

    /* ── 8-Metric Drilldown Grid ── */
    .rpt-metrics-grid {
        display: grid !important;
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 16px !important;
        row-gap: 16px !important;
    }
    @media (min-width: 640px) {
        .rpt-metrics-grid {
            grid-template-columns: repeat(4, 1fr) !important;
            gap: 16px !important;
            row-gap: 16px !important;
        }
    }
    .rpt-metric-tile {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 14px 10px;
        text-align: center;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .dark .rpt-metric-tile {
        background: #1e293b;
        border-color: #334155;
    }
    .rpt-metric-tile:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }
</style>

<div class="rpt-wrapper">

    {{-- ══════════════════════════════════════════════════════════════
         1. TOP EXECUTIVE HEADER & UNIFIED CONTROL BAR
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="rpt-card p-4 lg:p-5">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            
            {{-- Title & Scope Info --}}
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-purple-600 flex items-center justify-center text-white shadow-md shadow-indigo-500/20 flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h1 class="text-lg lg:text-xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">
                            {{ __('Graphical Analytics & Insights') }}
                        </h1>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 border border-indigo-200/50 dark:border-indigo-500/20">
                            {{ $tab === 'team' ? __('Team Performance') : __('Reseller Performance') }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 flex flex-wrap items-center gap-2">
                        <span>{{ __('Real-time Ticket Performance & Distribution Metrics') }}</span>
                        <span class="text-slate-300 dark:text-slate-600">•</span>
                        <span class="inline-flex items-center gap-1 font-medium text-slate-600 dark:text-slate-300">
                            <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} &mdash; {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}
                        </span>
                    </p>
                </div>
            </div>

            {{-- Actions & Filter Form --}}
            <div class="flex flex-wrap items-center gap-2.5">

                {{-- 1. Team / Reseller Dropdown --}}
                <div x-data="{ open: false }" class="relative" @click.outside="open = false">
                    <button type="button" @click="open = !open"
                            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-100 border border-slate-200 dark:border-slate-700 shadow-sm transition-all cursor-pointer">
                        @if($tab === 'team')
                            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>{{ __('Team') }}</span>
                        @else
                            <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <span>{{ __('Reseller') }}</span>
                        @endif
                        <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <div x-show="open" x-cloak
                         class="absolute left-0 lg:right-0 lg:left-auto top-full mt-2 w-48 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl z-50 overflow-hidden py-1">
                        <a href="{{ route('reports.index', array_merge(request()->except('person_id'), ['tab' => 'team'])) }}"
                           class="w-full flex items-center justify-between px-3.5 py-2.5 text-xs font-semibold text-left transition-colors {{ $tab === 'team' ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700' }}">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                {{ __('Team Members') }}
                            </span>
                            @if($tab === 'team')
                                <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            @endif
                        </a>
                        <a href="{{ route('reports.index', array_merge(request()->except('person_id'), ['tab' => 'reseller'])) }}"
                           class="w-full flex items-center justify-between px-3.5 py-2.5 text-xs font-semibold text-left transition-colors {{ $tab === 'reseller' ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700' }}">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                {{ __('Resellers') }}
                            </span>
                            @if($tab === 'reseller')
                                <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            @endif
                        </a>
                    </div>
                </div>

                {{-- 2. Member / Person Dropdown (Shows team/reseller member list; click opens single report) --}}
                <div x-data="{ open: false, search: '' }" class="relative" @click.outside="open = false">
                    <button type="button" @click="open = !open"
                            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-100 border border-slate-200 dark:border-slate-700 shadow-sm transition-all cursor-pointer">
                        @if($selectedPerson)
                            <img src="{{ $selectedPerson['avatarUrl'] }}" class="w-4 h-4 rounded-full object-cover">
                            <span class="max-w-[130px] truncate">{{ $selectedPerson['name'] }}</span>
                        @else
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span>{{ $tab === 'team' ? __('All Team') : __('All Resellers') }}</span>
                        @endif
                        <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <div x-show="open" x-cloak
                         class="absolute left-0 top-full mt-2 w-64 max-h-80 overflow-y-auto bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl z-50 py-1">
                        
                        {{-- Search Filter if multiple members --}}
                        @if($members->count() > 5)
                        <div class="p-2 border-b border-slate-100 dark:border-slate-700">
                            <input type="text" x-model="search" placeholder="{{ __('Filter name...') }}"
                                   class="w-full text-xs px-2.5 py-1 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:ring-1 focus:ring-indigo-500">
                        </div>
                        @endif

                        {{-- Option: All --}}
                        <a href="{{ route('reports.index', array_merge(request()->except('person_id'), ['tab' => $tab])) }}"
                           class="w-full flex items-center justify-between px-3.5 py-2 text-xs font-bold text-left transition-colors border-b border-slate-100 dark:border-slate-700 {{ !$selectedPerson ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700' }}">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>{{ $tab === 'team' ? __('All Team Members') : __('All Resellers') }}</span>
                            </span>
                            @if(!$selectedPerson)
                                <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            @endif
                        </a>

                        {{-- Member Rows --}}
                        @foreach($members as $m)
                        @php
                            $isCur = $personId == $m['id'];
                        @endphp
                        <a href="{{ route('reports.index', array_merge(request()->query(), ['tab' => $tab, 'person_id' => $m['id']])) }}"
                           x-show="!search || '{{ strtolower($m['name']) }}'.includes(search.toLowerCase())"
                           class="w-full flex items-center justify-between px-3.5 py-2 text-xs font-medium text-left transition-colors {{ $isCur ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 font-bold' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700' }}">
                            <div class="flex items-center gap-2 min-w-0">
                                <img src="{{ $m['avatarUrl'] }}" class="w-5 h-5 rounded-full object-cover flex-shrink-0">
                                <span class="truncate">{{ $m['name'] }}</span>
                            </div>
                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 font-bold">
                                {{ $m['resolved'] }} ✅
                            </span>
                        </a>
                        @endforeach
                    </div>
                </div>

                {{-- 3. Period Dropdown (Custom Alpine Dropdown - Clean, No Overlapping Browser Icons!) --}}
                <div x-data="{ open: false }" class="relative" @click.outside="open = false">
                    <button type="button" @click="open = !open"
                            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-100 border border-slate-200 dark:border-slate-700 shadow-sm transition-all cursor-pointer">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ $periodLabels[$period] ?? __('Custom') }}</span>
                        <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <div x-show="open" x-cloak
                         class="absolute left-0 lg:right-0 lg:left-auto top-full mt-2 w-48 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl z-50 overflow-hidden py-1">
                        @foreach($periodLabels as $key => $label)
                            @if($key === 'custom')
                                <button type="button"
                                        @click="open = false; toggleCustomDates('custom');"
                                        class="w-full flex items-center justify-between px-3.5 py-2 text-xs font-semibold text-left transition-colors {{ $period === 'custom' ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 font-bold' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700' }}">
                                    <span>{{ $label }}</span>
                                    @if($period === 'custom')
                                        <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    @endif
                                </button>
                            @else
                                <a href="{{ route('reports.index', array_merge(request()->query(), ['period' => $key])) }}"
                                   class="w-full flex items-center justify-between px-3.5 py-2 text-xs font-semibold text-left transition-colors {{ $period === $key ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 font-bold' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700' }}">
                                    <span>{{ $label }}</span>
                                    @if($period === $key)
                                        <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    @endif
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>

                {{-- 4. Custom Date Range Inputs (Visible when Custom is picked) --}}
                <form method="GET" action="{{ route('reports.index') }}" id="customDateRow" class="{{ $period === 'custom' ? 'flex' : 'hidden' }} items-center gap-2 bg-slate-50 dark:bg-slate-800/80 p-1 rounded-xl border border-slate-200 dark:border-slate-700">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <input type="hidden" name="period" value="custom">
                    @if($personId)<input type="hidden" name="person_id" value="{{ $personId }}">@endif
                    <input type="date" name="date_from" value="{{ $dateFrom }}"
                           class="border border-slate-200 dark:border-slate-600 rounded-lg px-2 py-1 text-xs bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200">
                    <span class="text-xs text-slate-400 font-semibold">&mdash;</span>
                    <input type="date" name="date_to" value="{{ $dateTo }}"
                           class="border border-slate-200 dark:border-slate-600 rounded-lg px-2 py-1 text-xs bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200">
                    <button type="submit" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-600 text-white hover:bg-indigo-700 transition-colors cursor-pointer">
                        {{ __('Apply') }}
                    </button>
                </form>

                {{-- 5. Export Buttons --}}
                <div class="flex items-center gap-2 pl-2 border-l border-slate-200 dark:border-slate-700">
                    <a href="{{ route('reports.excel', array_merge(request()->query(), ['period' => $period, 'date_from' => $dateFrom, 'date_to' => $dateTo])) }}"
                       title="{{ __('Export CSV') }}"
                       class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>{{ __('CSV') }}</span>
                    </a>
                    <a href="{{ route('reports.pdf', array_merge(request()->query(), ['period' => $period, 'date_from' => $dateFrom, 'date_to' => $dateTo])) }}"
                       title="{{ __('Export PDF') }}"
                       class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 shadow-sm transition-all">
                        <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        <span>{{ __('PDF') }}</span>
                    </a>
                </div>
            </div>

        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════
         2. SLEEK KPI METRIC CARDS (5-COLUMNS WITH GUARANTEED GAPS)
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="rpt-kpi-grid">
        
        {{-- Card 1: Total Tickets --}}
        <div class="rpt-card p-4 flex flex-col justify-between" style="border-top: 3px solid #6366f1;">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold tracking-wider text-slate-500 dark:text-slate-400 uppercase">{{ __('Total Tickets') }}</span>
                <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl lg:text-3xl font-black text-slate-900 dark:text-slate-100 tracking-tight">{{ number_format($periodStats['total']) }}</span>
                @if(isset($trend['total']))
                    <span class="inline-flex items-center text-[10px] font-bold px-1.5 py-0.5 rounded {{ $trend['total'] >= 0 ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600' : 'bg-rose-50 dark:bg-rose-950/40 text-rose-600' }}">
                        {{ $trend['total'] >= 0 ? '↑' : '↓' }} {{ abs($trend['total']) }}%
                    </span>
                @endif
            </div>
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1 truncate">{{ __('All tickets in catalog') }}</p>
        </div>

        {{-- Card 2: In Progress --}}
        <div class="rpt-card p-4 flex flex-col justify-between" style="border-top: 3px solid #3b82f6;">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold tracking-wider text-slate-500 dark:text-slate-400 uppercase">{{ __('In Progress') }}</span>
                <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-950/50 flex items-center justify-center text-blue-600 dark:text-blue-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl lg:text-3xl font-black text-blue-600 dark:text-blue-400 tracking-tight">{{ number_format($periodStats['in_progress']) }}</span>
                <span class="text-[11px] font-semibold text-slate-400 dark:text-slate-500">
                    {{ $periodStats['total'] > 0 ? round(($periodStats['in_progress'] / $periodStats['total']) * 100) : 0 }}%
                </span>
            </div>
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1 flex items-center gap-1.5 truncate">
                <span class="status-dot-inprogress"></span>
                <span>{{ __('Active resolution queue') }}</span>
            </p>
        </div>

        {{-- Card 3: Pending --}}
        <div class="rpt-card p-4 flex flex-col justify-between" style="border-top: 3px solid #f59e0b;">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold tracking-wider text-slate-500 dark:text-slate-400 uppercase">{{ __('Pending') }}</span>
                <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-950/50 flex items-center justify-center text-amber-600 dark:text-amber-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl lg:text-3xl font-black text-amber-600 dark:text-amber-400 tracking-tight">{{ number_format($periodStats['pending']) }}</span>
                <span class="text-[11px] font-semibold text-slate-400 dark:text-slate-500">
                    {{ $periodStats['total'] > 0 ? round(($periodStats['pending'] / $periodStats['total']) * 100) : 0 }}%
                </span>
            </div>
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1 flex items-center gap-1.5 truncate">
                <span class="status-dot-pending"></span>
                <span>{{ __('Awaiting triage / assignment') }}</span>
            </p>
        </div>

        {{-- Card 4: Waiting Feedback --}}
        <div class="rpt-card p-4 flex flex-col justify-between" style="border-top: 3px solid #a855f7;">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold tracking-wider text-slate-500 dark:text-slate-400 uppercase">{{ __('Waiting') }}</span>
                <div class="w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-950/50 flex items-center justify-center text-purple-600 dark:text-purple-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl lg:text-3xl font-black text-purple-600 dark:text-purple-400 tracking-tight">{{ number_format($periodStats['waiting_for_customer_feedback']) }}</span>
                <span class="text-[11px] font-semibold text-slate-400 dark:text-slate-500">
                    {{ $periodStats['total'] > 0 ? round(($periodStats['waiting_for_customer_feedback'] / $periodStats['total']) * 100) : 0 }}%
                </span>
            </div>
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1 truncate">{{ __('Customer reply awaited') }}</p>
        </div>

        {{-- Card 5: Resolved --}}
        <div class="rpt-card p-4 flex flex-col justify-between" style="border-top: 3px solid #10b981;">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold tracking-wider text-slate-500 dark:text-slate-400 uppercase">{{ __('Resolved') }}</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl lg:text-3xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight">{{ number_format($periodStats['resolved_all_time'] ?? $periodStats['resolved']) }}</span>
                <span class="inline-flex items-center text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600">
                    {{ $resRate }}%
                </span>
            </div>
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1 truncate">{{ __('Resolution completion rate') }}</p>
        </div>

    </div>

    {{-- ══════════════════════════════════════════════════════════════
         3. MASTER-DETAIL DUAL-PANEL DASHBOARD (SIDE-BY-SIDE WITH GAPS)
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="rpt-dashboard-grid {{ $selectedPerson ? 'drilldown-active' : '' }}">

        {{-- ── LEFT PANEL: LEADERBOARD TABLE ── --}}
        <div class="rpt-card overflow-hidden">
            
            {{-- Leaderboard Header --}}
            <div class="rpt-card-header flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-sm font-extrabold text-slate-900 dark:text-slate-100">
                            {{ $tab === 'team' ? __('Team Leaderboard & Rankings') : __('Reseller Rankings') }}
                        </h2>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400">
                            {{ $members->count() }}
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">
                        {{ __('Click any row to drill down into detailed analytics') }}
                    </p>
                </div>

                {{-- Live Search Box --}}
                <div class="relative w-full sm:w-52">
                    <input type="text" id="leaderboardSearch" placeholder="{{ __('Search name...') }}"
                           oninput="filterLeaderboard(this.value)"
                           class="w-full text-xs pl-8 pr-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>

            {{-- Leaderboard Table Body --}}
            @if($members->isEmpty())
                <div class="p-12 text-center">
                    <svg class="w-10 h-10 text-slate-300 dark:text-slate-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ __('No records found for this period.') }}</p>
                </div>
            @else
                <div class="overflow-x-auto max-h-[720px] overflow-y-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="sticky top-0 bg-slate-50 dark:bg-slate-800/90 border-b border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider text-[10px] z-10">
                            <tr>
                                <th class="px-4 py-3 w-12 text-center">#</th>
                                <th class="px-4 py-3">{{ __('Member') }}</th>
                                <th class="px-3 py-3 text-center">{{ __('Resolved') }}</th>
                                @if(!$selectedPerson)
                                    <th class="px-3 py-3 text-center">{{ $tab === 'team' ? __('Assigned / Total') : __('Created') }}</th>
                                    <th class="px-3 py-3 text-center">{{ __('Avg Time') }}</th>
                                    <th class="px-4 py-3 min-w-[130px]">{{ __('Rate') }}</th>
                                @endif
                                <th class="px-3 py-3 w-8"></th>
                            </tr>
                        </thead>
                        <tbody id="leaderboardList" class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($members->sortByDesc('resolved')->values() as $idx => $m)
                            @php
                                $isSelected = $personId == $m['id'];
                                $rate       = $m['rate'];
                                $barClr     = $rate >= 70 ? 'bg-emerald-500' : ($rate >= 40 ? 'bg-amber-500' : 'bg-rose-500');
                                $medals     = ['🥇','🥈','🥉'];
                                $rowUrl = route('reports.index', array_merge(request()->except('person_id'), [
                                    'tab'       => $tab,
                                    'person_id' => $isSelected ? null : $m['id'],
                                ]));
                            @endphp
                            <tr data-name="{{ strtolower($m['name']) }}"
                                onclick="window.location='{{ $rowUrl }}'"
                                class="cursor-pointer transition-colors group {{ $isSelected ? 'bg-indigo-50/80 dark:bg-indigo-950/40 font-semibold' : 'hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                                
                                {{-- Rank --}}
                                <td class="px-4 py-3.5 text-center font-bold text-slate-700 dark:text-slate-300">
                                    @if($idx < 3)
                                        <span class="text-base">{{ $medals[$idx] }}</span>
                                    @else
                                        <span class="text-[11px] text-slate-400">#{{ $idx + 1 }}</span>
                                    @endif
                                </td>

                                {{-- Member Info --}}
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="relative flex-shrink-0">
                                            <img src="{{ $m['avatarUrl'] }}" alt="{{ $m['name'] }}"
                                                 class="w-9 h-9 rounded-full object-cover border {{ $isSelected ? 'border-indigo-500 ring-2 ring-indigo-500/20' : 'border-slate-200 dark:border-slate-700' }}">
                                            @if(($m['in_progress'] ?? 0) > 0)
                                                <span class="absolute -top-1 -right-1 w-4 h-4 bg-blue-600 rounded-full text-white text-[8px] font-bold flex items-center justify-center">
                                                    {{ $m['in_progress'] }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <p class="truncate text-xs font-bold {{ $isSelected ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-900 dark:text-slate-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400' }}">
                                                {{ $m['name'] }}
                                            </p>
                                            <p class="text-[10px] text-slate-400 dark:text-slate-500 truncate mt-0.5">
                                                💬 {{ $m['comments'] }} • 👍 {{ $m['reactions'] }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Resolved Count --}}
                                <td class="px-3 py-3.5 text-center">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[11px] font-extrabold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200/50 dark:border-emerald-500/20">
                                        {{ $m['resolved'] }}
                                    </span>
                                </td>

                                @if(!$selectedPerson)
                                    {{-- Assigned / Created --}}
                                    <td class="px-3 py-3.5 text-center text-[11px] font-semibold text-slate-600 dark:text-slate-300">
                                        @if($tab === 'team')
                                            <span>{{ $m['assigned'] }} <span class="text-slate-400 text-[10px]">/ {{ $m['created'] }}</span></span>
                                        @else
                                            <span>{{ $m['created'] }}</span>
                                        @endif
                                    </td>

                                    {{-- Avg Time --}}
                                    <td class="px-3 py-3.5 text-center text-[11px] text-slate-500 dark:text-slate-400">
                                        {{ $m['avg_resolution_time'] ?? ($m['avg_time'] ?? '—') }}
                                    </td>

                                    {{-- Resolution Rate Bar --}}
                                    <td class="px-4 py-3.5">
                                        <div class="flex items-center gap-2.5">
                                            <div class="flex-1 bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden">
                                                <div class="{{ $barClr }} h-2 rounded-full transition-all duration-500" style="width: {{ min(100, $rate) }}%"></div>
                                            </div>
                                            <span class="text-[11px] font-bold text-slate-700 dark:text-slate-300 w-8 text-right">{{ $rate }}%</span>
                                        </div>
                                    </td>
                                @endif

                                {{-- Arrow Indicator --}}
                                <td class="px-3 py-3.5 text-right text-slate-300 dark:text-slate-600 group-hover:text-indigo-500 transition-colors">
                                    <svg class="w-4 h-4 {{ $isSelected ? 'text-indigo-600 rotate-90 lg:rotate-0' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

        </div>

        {{-- ── RIGHT PANEL: ANALYTICS & DRILL-DOWN VIEW (STACKED WITH GAPS) ── --}}
        <div class="rpt-col-stack">
            
            @if(!$selectedPerson)
                {{-- ══ DEFAULT OVERVIEW CHARTS ══ --}}

                {{-- Chart 1: Ticket Trend Bar Chart --}}
                <div class="rpt-card p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                            <h3 class="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wide">
                                {{ __('Ticket Volume Trend') }}
                            </h3>
                        </div>
                        <span class="text-[11px] font-semibold text-slate-400 dark:text-slate-500">
                            {{ $periodLabels[$period] ?? __('Custom') }}
                        </span>
                    </div>
                    <div class="relative h-56 w-full">
                        <canvas id="ticketTrendChart"></canvas>
                    </div>
                </div>

                {{-- Chart 2: Status Distribution Donut --}}
                <div class="rpt-card p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            <h3 class="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wide">
                                {{ __('Status Distribution') }}
                            </h3>
                        </div>
                        <span class="text-[11px] font-semibold text-slate-400 dark:text-slate-500">
                            {{ __('All tickets') }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 items-center">
                        <div class="relative h-48 w-full flex items-center justify-center">
                            <canvas id="statusDonutChart"></canvas>
                            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                                <span class="text-2xl font-black text-slate-900 dark:text-slate-100">{{ $periodStats['total'] }}</span>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Total') }}</span>
                            </div>
                        </div>

                        {{-- Legend List --}}
                        <div class="space-y-2.5 text-xs">
                            <div class="flex items-center justify-between p-2.5 rounded-xl rpt-inner-card">
                                <span class="flex items-center gap-2 text-slate-600 dark:text-slate-300 font-medium">
                                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                                    {{ __('In Progress') }}
                                </span>
                                <span class="font-bold text-slate-800 dark:text-slate-100">{{ $periodStats['in_progress'] }}</span>
                            </div>
                            <div class="flex items-center justify-between p-2.5 rounded-xl rpt-inner-card">
                                <span class="flex items-center gap-2 text-slate-600 dark:text-slate-300 font-medium">
                                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                                    {{ __('Pending') }}
                                </span>
                                <span class="font-bold text-slate-800 dark:text-slate-100">{{ $periodStats['pending'] }}</span>
                            </div>
                            <div class="flex items-center justify-between p-2.5 rounded-xl rpt-inner-card">
                                <span class="flex items-center gap-2 text-slate-600 dark:text-slate-300 font-medium">
                                    <span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span>
                                    {{ __('Waiting') }}
                                </span>
                                <span class="font-bold text-slate-800 dark:text-slate-100">{{ $periodStats['waiting_for_customer_feedback'] }}</span>
                            </div>
                            <div class="flex items-center justify-between p-2.5 rounded-xl rpt-inner-card">
                                <span class="flex items-center gap-2 text-slate-600 dark:text-slate-300 font-medium">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                    {{ __('Resolved') }}
                                </span>
                                <span class="font-bold text-slate-800 dark:text-slate-100">{{ $periodStats['resolved_all_time'] ?? $periodStats['resolved'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Chart 3: Efficiency Summary --}}
                <div class="rpt-card p-4 flex items-center justify-between" style="border-left: 4px solid #6366f1;">
                    <div class="flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ __('Average Resolution Time') }}</p>
                            <p class="text-lg font-black text-indigo-600 dark:text-indigo-400 mt-0.5">{{ $periodStats['avg_resolution_time'] ?: __('N/A') }}</p>
                        </div>
                    </div>
                    <span class="text-xs text-slate-400 font-medium">{{ __('Efficiency index') }}</span>
                </div>

            @else
                {{-- ══ DRILL-DOWN VIEW: SELECTED MEMBER PROFILE ══ --}}

                {{-- Person Hero Header --}}
                <div class="rpt-card p-5">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="flex items-center gap-3.5">
                            <img src="{{ $selectedPerson['avatarUrl'] }}" alt="{{ $selectedPerson['name'] }}"
                                 class="w-14 h-14 rounded-2xl object-cover border-2 border-indigo-500 shadow-md flex-shrink-0">
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100">{{ $selectedPerson['name'] }}</h3>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 border border-indigo-200/50 dark:border-indigo-500/20">
                                        {{ str_replace('_', ' ', $selectedPerson['role']) }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                    {{ $selectedPerson['email'] }} @if(!empty($selectedPerson['phone'])) • {{ $selectedPerson['phone'] }} @endif
                                </p>
                                <div class="flex items-center gap-2 mt-2.5">
                                    <a href="{{ route('reports.excel', array_merge(request()->query(), ['person_id' => $selectedPerson['id']])) }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white transition-colors shadow-sm">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        {{ __('CSV') }}
                                    </a>
                                    <a href="{{ route('reports.pdf', array_merge(request()->query(), ['person_id' => $selectedPerson['id']])) }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white transition-colors shadow-sm">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                        {{ __('PDF') }}
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- Radial Resolution Gauge + Close Button --}}
                        <div class="flex items-center gap-4 self-end sm:self-center">
                            <div class="text-center">
                                <div class="rpt-gauge-wrap">
                                    <svg viewBox="0 0 36 36" class="rpt-gauge-svg">
                                        <circle cx="18" cy="18" r="15.9" fill="none" class="rpt-gauge-track" stroke-width="3.2"/>
                                        <circle cx="18" cy="18" r="15.9" fill="none"
                                                stroke="{{ $selectedPerson['rate'] >= 70 ? '#10b981' : ($selectedPerson['rate'] >= 40 ? '#f59e0b' : '#ef4444') }}"
                                                stroke-width="3.2"
                                                stroke-dasharray="{{ min(100, $selectedPerson['rate']) }}, 100"
                                                @if($selectedPerson['rate'] > 0) stroke-linecap="round" @endif />
                                    </svg>
                                    <div class="rpt-gauge-center">
                                        <span class="text-xs font-black text-slate-800 dark:text-slate-100">{{ $selectedPerson['rate'] }}%</span>
                                    </div>
                                </div>
                                <span class="text-[10px] font-bold text-slate-400 block mt-1">{{ __('Resolution') }}</span>
                            </div>

                            <a href="{{ route('reports.index', array_merge(request()->except('person_id'), ['tab' => $tab])) }}"
                               title="{{ __('Close Drilldown') }}"
                               class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-500 hover:text-slate-900 dark:hover:text-white flex items-center justify-center transition-colors cursor-pointer flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- 8 Metric Tiles Grid with Guaranteed Gap --}}
                @php
                    $personMetrics = [
                        ['label' => __('Created'),     'val' => $selectedPerson['created'],     'icon' => '🎫'],
                        ['label' => __('Assigned'),    'val' => $selectedPerson['assigned'],    'icon' => '📌'],
                        ['label' => __('Transferred'), 'val' => $selectedPerson['transferred'], 'icon' => '🔄'],
                        ['label' => __('Comments'),    'val' => $selectedPerson['comments'],    'icon' => '💬'],
                        ['label' => __('Reactions'),   'val' => $selectedPerson['reactions'],   'icon' => '👍'],
                        ['label' => __('Resolved'),    'val' => $selectedPerson['resolved'],    'icon' => '✅'],
                        ['label' => __('In Progress'), 'val' => $selectedPerson['in_progress'], 'icon' => '⚡'],
                        ['label' => __('Pending'),     'val' => $selectedPerson['pending'],     'icon' => '⏳'],
                    ];
                @endphp
                <div class="rpt-metrics-grid">
                    @foreach($personMetrics as $pm)
                    <div class="rpt-metric-tile">
                        <div class="text-base">{{ $pm['icon'] }}</div>
                        <div class="text-xl font-black text-slate-900 dark:text-slate-100 mt-1">{{ $pm['val'] }}</div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-0.5">{{ $pm['label'] }}</div>
                    </div>
                    @endforeach
                </div>

                {{-- Person Activity Trend Chart --}}
                <div class="rpt-card p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider flex items-center gap-1.5">
                            <span>📊</span>
                            <span>{{ __('Ticket Activity Trend') }}</span>
                        </h4>
                        <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                            {{ $personTickets->count() }} {{ __('tickets') }}
                        </span>
                    </div>
                    <div class="relative h-48 w-full">
                        <canvas id="personChart"></canvas>
                    </div>
                </div>

                {{-- Person Recent Tickets Ledger --}}
                <div class="rpt-card overflow-hidden">
                    <div class="rpt-card-header flex items-center justify-between">
                        <span class="text-xs font-extrabold text-slate-900 dark:text-slate-100">{{ __('Tickets in this period') }}</span>
                        <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400">
                            {{ $personTickets->count() }}
                        </span>
                    </div>

                    @if($personTickets->isEmpty())
                        <div class="p-8 text-center text-xs font-medium text-slate-400">{{ __('No tickets found for this period.') }}</div>
                    @else
                        <div class="overflow-x-auto max-h-80 overflow-y-auto">
                            <table class="w-full text-left text-xs border-collapse">
                                <thead class="sticky top-0 bg-slate-50 dark:bg-slate-800/90 text-[10px] text-slate-400 uppercase font-bold tracking-wider border-b border-slate-200 dark:border-slate-700">
                                    <tr>
                                        <th class="px-3.5 py-2.5">#</th>
                                        <th class="px-3.5 py-2.5">{{ __('Title') }}</th>
                                        <th class="px-3.5 py-2.5">{{ __('Status') }}</th>
                                        <th class="px-3.5 py-2.5 text-right">{{ __('Date') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                    @foreach($personTickets as $pt)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                        <td class="px-3.5 py-2.5 font-mono text-[11px] text-slate-500">
                                            <a href="{{ route('tickets.show', $pt->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline font-bold">
                                                #{{ $pt->id }}
                                            </a>
                                        </td>
                                        <td class="px-3.5 py-2.5 font-medium text-slate-800 dark:text-slate-200 max-w-[200px] truncate">
                                            <a href="{{ route('tickets.show', $pt->id) }}" class="hover:text-indigo-600 transition-colors">
                                                {{ $pt->title }}
                                            </a>
                                        </td>
                                        <td class="px-3.5 py-2.5">
                                            <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold
                                                {{ $pt->status === 'resolved' ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400' :
                                                   ($pt->status === 'in_progress' ? 'bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400' :
                                                   ($pt->status === 'pending' ? 'bg-amber-50 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400' : 'bg-purple-50 text-purple-600 dark:bg-purple-950/50 dark:text-purple-400')) }}">
                                                {{ ucfirst(str_replace('_', ' ', $pt->status)) }}
                                            </span>
                                        </td>
                                        <td class="px-3.5 py-2.5 text-right text-[10px] text-slate-400 whitespace-nowrap">
                                            {{ $pt->created_at->format('d M, H:i') }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

            @endif

        </div>

    </div>

</div>

{{-- ══════════════════════════════════════════════════════════════
     SCRIPTS & CHART.JS CONFIGURATION
═══════════════════════════════════════════════════════════════ --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script>
function toggleCustomDates(val) {
    const row = document.getElementById('customDateRow');
    if (!row) return;
    if (val === 'custom') {
        row.classList.remove('hidden');
        row.classList.add('flex');
    } else {
        row.classList.add('hidden');
        row.classList.remove('flex');
    }
}

function filterLeaderboard(query) {
    const q = query.toLowerCase().trim();
    const rows = document.querySelectorAll('#leaderboardList tr');
    rows.forEach(row => {
        const name = row.getAttribute('data-name') || '';
        if (name.includes(q)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const isDark = document.documentElement.classList.contains('dark');
    const gridColor = isDark ? 'rgba(51, 65, 85, 0.4)' : 'rgba(241, 245, 249, 0.9)';
    const tickColor = isDark ? '#94a3b8' : '#64748b';
    const tooltipBg = isDark ? '#0f172a' : '#ffffff';
    const tooltipText = isDark ? '#f8fafc' : '#0f172a';
    const tooltipBorder = isDark ? '#334155' : '#e2e8f0';

    @if(!$selectedPerson)
        // ── 1. Ticket Trend Chart ──────────────────────────────────────
        const trendCtx = document.getElementById('ticketTrendChart');
        if (trendCtx) {
            new Chart(trendCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: @json($chartDays),
                    datasets: [{
                        label: @js(__('Tickets')),
                        data: @json($chartCounts),
                        backgroundColor: 'rgba(99, 102, 241, 0.75)',
                        hoverBackgroundColor: 'rgba(79, 70, 229, 0.95)',
                        borderRadius: 6,
                        borderSkipped: false,
                        maxBarThickness: 24,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: tooltipBg,
                            titleColor: tooltipText,
                            bodyColor: tooltipText,
                            borderColor: tooltipBorder,
                            borderWidth: 1,
                            padding: 10,
                            displayColors: false,
                            cornerRadius: 8,
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { color: tickColor, font: { size: 10, weight: 600 } }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: gridColor },
                            ticks: { color: tickColor, font: { size: 10 }, precision: 0 }
                        }
                    }
                }
            });
        }

        // ── 2. Status Donut Chart ──────────────────────────────────────
        const donutCtx = document.getElementById('statusDonutChart');
        if (donutCtx) {
            new Chart(donutCtx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: [@js(__('In Progress')), @js(__('Pending')), @js(__('Waiting')), @js(__('Resolved'))],
                    datasets: [{
                        data: [
                            {{ $periodStats['in_progress'] }},
                            {{ $periodStats['pending'] }},
                            {{ $periodStats['waiting_for_customer_feedback'] }},
                            {{ $periodStats['resolved_all_time'] ?? $periodStats['resolved'] }}
                        ],
                        backgroundColor: ['#3b82f6', '#f59e0b', '#a855f7', '#10b981'],
                        borderWidth: isDark ? 2 : 1,
                        borderColor: isDark ? '#0f172a' : '#ffffff',
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '74%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: tooltipBg,
                            titleColor: tooltipText,
                            bodyColor: tooltipText,
                            borderColor: tooltipBorder,
                            borderWidth: 1,
                            padding: 8,
                            cornerRadius: 8,
                        }
                    }
                }
            });
        }

    @else
        // ── 3. Person Activity Chart ───────────────────────────────────
        const personCtx = document.getElementById('personChart');
        if (personCtx) {
            new Chart(personCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: @json($personChartDays),
                    datasets: [{
                        label: @js(__('Tickets')),
                        data: @json($personChartCounts),
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99, 102, 241, 0.12)',
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.35,
                        pointBackgroundColor: '#6366f1',
                        pointRadius: 3,
                        pointHoverRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: tooltipBg,
                            titleColor: tooltipText,
                            bodyColor: tooltipText,
                            borderColor: tooltipBorder,
                            borderWidth: 1,
                            padding: 8,
                            cornerRadius: 8,
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { color: tickColor, font: { size: 10 } }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: gridColor },
                            ticks: { color: tickColor, font: { size: 10 }, precision: 0 }
                        }
                    }
                }
            });
        }
    @endif
});
</script>
</x-app-layout>
