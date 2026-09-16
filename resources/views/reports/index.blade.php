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
    :root {
        --rpt-wrap:    #f8fafc;
        --rpt-card:    #fff;
        --rpt-chip:    #f1f5f9;
        --rpt-border:  #e2e8f0;
        --rpt-border2: #f1f5f9;
        --rpt-heading: #0f172a;
        --rpt-body:    #64748b;
        --rpt-muted:   #94a3b8;
        --rpt-faint:   #cbd5e1;
        --rpt-selected: #eef2ff;
        --rpt-hover:   #f8fafc;
    }
    .dark {
        --rpt-wrap:    #0f172a;
        --rpt-card:    #1e293b;
        --rpt-chip:    #334155;
        --rpt-border:  #334155;
        --rpt-border2: #334155;
        --rpt-heading: #f1f5f9;
        --rpt-body:    #cbd5e1;
        --rpt-muted:   #94a3b8;
        --rpt-faint:   #64748b;
        --rpt-selected: rgba(99,102,241,0.18);
        --rpt-hover:   #263449;
    }
    .rpt-card   { background:var(--rpt-card); border:1px solid var(--rpt-border); border-radius:14px; }
    .rpt-card2  { background:var(--rpt-wrap); border:1px solid var(--rpt-border); border-radius:14px; }
    .stat-num   { font-size:2.4rem; font-weight:800; line-height:1; letter-spacing:-1px; }
    .pill-btn   { border-radius:8px; font-size:0.75rem; font-weight:600; padding:5px 14px; transition:all .15s; border:1px solid transparent; cursor:pointer; }
    .pill-btn.active    { background:#6366f1; color:#fff; border-color:#6366f1; }
    .pill-btn:not(.active) { background:var(--rpt-chip); color:var(--rpt-body); border-color:var(--rpt-border); }
    .pill-btn:not(.active):hover { background:var(--rpt-border); color:var(--rpt-heading); }
    .tab-btn    { border-radius:10px; padding:8px 22px; font-size:0.85rem; font-weight:700; transition:all .15s; text-decoration:none; display:inline-block; }
    .tab-btn.active     { background:var(--rpt-card); color:#6366f1; border:1px solid #e0e7ff; box-shadow:0 1px 4px #6366f115; }
    .tab-btn:not(.active) { background:transparent; color:var(--rpt-muted); border:1px solid transparent; }
    .tab-btn:not(.active):hover { color:var(--rpt-heading); background:var(--rpt-chip); }
    .kpi-glow-blue   { box-shadow:0 2px 16px #3b82f612; }
    .kpi-glow-sky    { box-shadow:0 2px 16px #0ea5e912; }
    .kpi-glow-orange { box-shadow:0 2px 16px #f97316 12; }
    .kpi-glow-violet { box-shadow:0 2px 16px #8b5cf612; }
    .kpi-glow-green  { box-shadow:0 2px 16px #10b98112; }
</style>

{{-- ══════════════════════════════════════════
     WRAPPER
═══════════════════════════════════════════ --}}
<div style="background:var(--rpt-wrap); border-radius:20px; padding:24px;">

    {{-- ── Top Header ── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <div style="width:34px;height:34px;background:linear-gradient(135deg,#6366f1,#8b5cf6);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <svg style="width:18px;height:18px;color:#fff;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <h1 style="color:var(--rpt-heading);font-size:1.35rem;font-weight:800;letter-spacing:-0.5px;">{{ __('Graphical Analytics & Insights') }}</h1>
            </div>
            <p style="color:var(--rpt-muted);font-size:0.78rem;margin-left:42px;">
                {{ __('Real-time Ticket Performance & Team Distribution Metrics') }} &mdash;
                <span style="color:var(--rpt-faint);">{{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} — {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}</span>
            </p>
        </div>
        <div class="flex items-center gap-2">
            <div style="background:var(--rpt-card);border:1px solid var(--rpt-border);border-radius:8px;padding:6px 14px;color:var(--rpt-body);font-size:0.75rem;font-weight:600;box-shadow:0 1px 3px #0001;">
                ⏱ <span style="color:var(--rpt-heading);">{{ $periodLabels[$period] ?? __('Custom') }}</span>
            </div>
            <a href="{{ route('reports.excel', array_merge(request()->query(), ['period' => $period, 'date_from' => $dateFrom, 'date_to' => $dateTo])) }}"
               style="background:#10b981;color:#fff;border-radius:8px;padding:7px 16px;font-size:0.75rem;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:6px;box-shadow:0 2px 8px #10b98130;">
                <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                {{ __('Export CSV') }}
            </a>
            <a href="{{ route('reports.pdf', array_merge(request()->query(), ['period' => $period, 'date_from' => $dateFrom, 'date_to' => $dateTo])) }}"
               style="background:var(--rpt-card);color:var(--rpt-body);border:1px solid var(--rpt-border);border-radius:8px;padding:7px 16px;font-size:0.75rem;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
                <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                {{ __('PDF') }}
            </a>
        </div>
    </div>

    {{-- ── Tabs + Period Filters ── --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div style="background:var(--rpt-chip);border:1px solid var(--rpt-border);border-radius:12px;padding:4px;display:inline-flex;gap:4px;">
            <a href="{{ route('reports.index', array_merge(request()->except('tab','person_id'), ['tab' => 'team'])) }}"
               class="tab-btn {{ $tab==='team' ? 'active' : '' }}">👥 {{ __('Team') }}</a>
            <a href="{{ route('reports.index', array_merge(request()->except('tab','person_id'), ['tab' => 'reseller'])) }}"
               class="tab-btn {{ $tab==='reseller' ? 'active' : '' }}">🏪 {{ __('Reseller') }}</a>
        </div>

        <form method="GET" action="{{ route('reports.index') }}" class="flex flex-wrap items-center gap-3">
            <input type="hidden" name="tab" value="{{ $tab }}">
            @if($personId) <input type="hidden" name="person_id" value="{{ $personId }}"> @endif
            
            {{-- Period Dropdown Menu --}}
            <div style="position:relative; display:inline-flex; align-items:center;">
                <select name="period" id="periodDropdown" onchange="toggleCustomPeriod(this.value); this.form.submit();"
                        style="background:var(--rpt-card); border:1px solid var(--rpt-border); color:var(--rpt-heading); border-radius:10px; padding:8px 36px 8px 14px; font-size:0.82rem; font-weight:700; cursor:pointer; appearance:none; box-shadow:0 1px 3px rgba(0,0,0,0.05); transition:all 0.15s;">
                    <option value="today"   {{ $period === 'today'   ? 'selected' : '' }}>📅 {{ __('Today') }}</option>
                    <option value="week"    {{ $period === 'week'    ? 'selected' : '' }}>🗓️ {{ __('This Week') }}</option>
                    <option value="month"   {{ $period === 'month'   ? 'selected' : '' }}>📊 {{ __('This Month') }}</option>
                    <option value="3months" {{ $period === '3months' ? 'selected' : '' }}>📈 {{ __('Last 3 Months') }}</option>
                    <option value="6months" {{ $period === '6months' ? 'selected' : '' }}>📉 {{ __('Last 6 Months') }}</option>
                    <option value="year"    {{ $period === 'year'    ? 'selected' : '' }}>📆 {{ __('Last 1 Year') }}</option>
                    <option value="custom"  {{ $period === 'custom'  ? 'selected' : '' }}>⚙️ {{ __('Custom Range') }}</option>
                </select>
                <svg style="position:absolute; right:12px; pointer-events:none; width:16px; height:16px; color:var(--rpt-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>

            <div id="customRange" class="{{ $period==='custom' ? 'flex' : 'hidden' }} items-center gap-2">
                <input type="date" name="date_from" value="{{ $dateFrom }}"
                       style="background:var(--rpt-card);border:1px solid var(--rpt-border);border-radius:8px;padding:6px 10px;font-size:0.78rem;color:var(--rpt-heading);">
                <span style="color:var(--rpt-faint);">—</span>
                <input type="date" name="date_to" value="{{ $dateTo }}"
                       style="background:var(--rpt-card);border:1px solid var(--rpt-border);border-radius:8px;padding:6px 10px;font-size:0.78rem;color:var(--rpt-heading);">
                <button type="submit" class="pill-btn active">{{ __('Apply') }}</button>
            </div>
        </form>
    </div>

    {{-- ══════════════════════════════════════════
         MEMBER LIST + DRILL-DOWN
    ═══════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 gap-4 mb-5">

        {{-- ── Leaderboard Panel ── --}}
        @unless($selectedPerson)
        <div>
            <div class="rpt-card overflow-hidden" style="box-shadow:0 1px 6px #0001;">
                <div style="background:var(--rpt-wrap);border-bottom:1px solid var(--rpt-border);padding:14px 20px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:{{ $members->count() > 5 ? '10px' : '0' }};">
                        <div>
                            <p style="color:var(--rpt-heading);font-size:0.92rem;font-weight:800;">
                                {{ $tab === 'team' ? '🏆 '.__('Team Leaderboard') : '🏪 '.__('Reseller Board') }}
                            </p>
                            <p style="color:var(--rpt-muted);font-size:0.72rem;">{{ __('Click any member row to drill down into detailed analytics') }}</p>
                        </div>
                        <span style="background:#6366f122;color:#6366f1;border:1px solid #6366f144;border-radius:20px;padding:3px 14px;font-size:0.72rem;font-weight:700;flex-shrink:0;">
                            {{ $members->count() }} {{ __('members') }}
                        </span>
                    </div>
                    @if($members->count() > 5)
                    <div style="position:relative;">
                        <input type="text" id="leaderboardSearch" placeholder="{{ __('Search by name…') }}"
                               oninput="filterLeaderboard(this.value)"
                               style="width:100%;background:var(--rpt-card);border:1px solid var(--rpt-border);border-radius:8px;padding:6px 10px 6px 30px;font-size:0.78rem;color:var(--rpt-heading);">
                        <svg style="position:absolute;left:9px;top:7px;width:14px;height:14px;color:var(--rpt-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    @endif
                </div>

                @if($members->isEmpty())
                <div style="padding:40px;text-align:center;color:var(--rpt-muted);font-size:0.85rem;">{{ __('No records found.') }}</div>
                @else
                <div class="overflow-x-auto">
                    <table style="width:100%; border-collapse:collapse; text-align:left; font-size:0.82rem;">
                        <thead>
                            <tr style="background:var(--rpt-wrap); border-bottom:1px solid var(--rpt-border);">
                                <th style="padding:10px 16px; width:48px; color:var(--rpt-muted); font-size:0.68rem; font-weight:700; text-transform:uppercase;">#</th>
                                <th style="padding:10px 16px; color:var(--rpt-muted); font-size:0.68rem; font-weight:700; text-transform:uppercase;">{{ __('Member') }}</th>
                                <th style="padding:10px 16px; color:var(--rpt-muted); font-size:0.68rem; font-weight:700; text-transform:uppercase;">{{ __('Resolved') }}</th>
                                @if($tab === 'team')
                                <th style="padding:10px 16px; color:var(--rpt-muted); font-size:0.68rem; font-weight:700; text-transform:uppercase;">{{ __('Assigned / Created') }}</th>
                                @else
                                <th style="padding:10px 16px; color:var(--rpt-muted); font-size:0.68rem; font-weight:700; text-transform:uppercase;">{{ __('Created') }}</th>
                                @endif
                                <th style="padding:10px 16px; color:var(--rpt-muted); font-size:0.68rem; font-weight:700; text-transform:uppercase;">{{ __('Avg Time') }}</th>
                                <th style="padding:10px 16px; min-width:140px; color:var(--rpt-muted); font-size:0.68rem; font-weight:700; text-transform:uppercase;">{{ __('Resolution Rate') }}</th>
                                <th style="padding:10px 16px; text-align:right; width:40px;"></th>
                            </tr>
                        </thead>
                        <tbody id="leaderboardList">
                            @foreach($members->sortByDesc('resolved')->values() as $idx => $m)
                            @php
                                $isSelected = $personId == $m['id'];
                                $rate       = $m['rate'];
                                $barClr     = $rate >= 70 ? '#10b981' : ($rate >= 40 ? '#f59e0b' : '#ef4444');
                                $medals     = ['🥇','🥈','🥉'];
                                $url = route('reports.index', array_merge(request()->except('person_id'), [
                                    'tab'       => $tab,
                                    'person_id' => $isSelected ? null : $m['id'],
                                ]));
                            @endphp
                            <tr data-name="{{ strtolower($m['name']) }}" class="{{ $idx >= 10 ? 'lb-extra' : '' }}"
                                style="display:{{ $idx >= 10 ? 'none' : 'table-row' }}; border-bottom:1px solid var(--rpt-border2); cursor:pointer; transition:background .15s; {{ $isSelected ? 'background:var(--rpt-selected);' : '' }}"
                                onclick="window.location='{{ $url }}'"
                                onmouseover="this.style.background='var(--rpt-hover)'"
                                onmouseout="this.style.background='{{ $isSelected ? 'var(--rpt-selected)' : '' }}'">
                                
                                {{-- Rank --}}
                                <td style="padding:14px 16px; font-weight:700; color:var(--rpt-heading);">
                                    @if($idx < 3)
                                        <span style="font-size:1.1rem;">{{ $medals[$idx] }}</span>
                                    @else
                                        <span style="font-size:0.75rem; color:var(--rpt-muted);">#{{ $idx+1 }}</span>
                                    @endif
                                </td>

                                {{-- Member (Avatar + Name) --}}
                                <td style="padding:14px 16px;">
                                    <div style="display:flex; align-items:center; gap:12px;">
                                        <div style="position:relative; flex-shrink:0;">
                                            <img src="{{ $m['avatarUrl'] }}" style="width:38px; height:38px; border-radius:50%; object-fit:cover; border:2px solid {{ $isSelected ? '#6366f1' : 'var(--rpt-border)' }};" alt="">
                                            @if(($m['in_progress'] ?? 0) > 0)
                                            <span style="position:absolute; top:-2px; right:-2px; width:14px; height:14px; background:#6366f1; border-radius:50%; color:#fff; font-size:8px; font-weight:800; display:flex; align-items:center; justify-content:center;">{{ $m['in_progress'] }}</span>
                                            @endif
                                        </div>
                                        <div>
                                            <p style="color:{{ $isSelected ? '#6366f1' : 'var(--rpt-heading)' }}; font-weight:700; margin:0; line-height:1.2;">{{ $m['name'] }}</p>
                                            <span style="color:var(--rpt-muted); font-size:0.7rem;">💬 {{ $m['comments'] }} comments • 👍 {{ $m['reactions'] }} reactions</span>
                                        </div>
                                    </div>
                                </td>

                                {{-- Resolved --}}
                                <td style="padding:14px 16px;">
                                    <span style="background:rgba(16,185,129,0.12); color:#10b981; border-radius:6px; padding:3px 10px; font-weight:800; font-size:0.78rem; display:inline-flex; align-items:center; gap:4px;">
                                        ✅ {{ $m['resolved'] }}
                                    </span>
                                </td>

                                {{-- Assigned / Created --}}
                                <td style="padding:14px 16px; color:var(--rpt-heading); font-weight:600;">
                                    @if($tab === 'team')
                                    <span>📌 {{ $m['assigned'] }} <span style="color:var(--rpt-muted); font-size:0.7rem; font-weight:normal;">/ 🎫 {{ $m['created'] }}</span></span>
                                    @else
                                    <span>🎫 {{ $m['created'] }}</span>
                                    @endif
                                </td>

                                {{-- Avg Resolution Time --}}
                                <td style="padding:14px 16px; color:var(--rpt-body); font-size:0.75rem;">
                                    ⏱️ {{ $m['avg_resolution_time'] }}
                                </td>

                                {{-- Resolution Rate Bar --}}
                                <td style="padding:14px 16px;">
                                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:4px;">
                                        <span style="color:{{ $barClr }}; font-weight:800; font-size:0.78rem;">{{ $rate }}%</span>
                                    </div>
                                    <div style="background:var(--rpt-chip); border-radius:6px; height:6px; overflow:hidden; width:100%;">
                                        <div style="width:{{ $rate }}%; height:100%; background:{{ $barClr }}; border-radius:6px; transition:width 0.4s;"></div>
                                    </div>
                                </td>

                                {{-- Chevron --}}
                                <td style="padding:14px 16px; text-align:right;">
                                    <svg style="width:16px; height:16px; color:var(--rpt-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($members->count() > 10)
                <div style="padding:12px 20px;text-align:center;border-top:1px solid var(--rpt-border2);">
                    <button type="button" id="lbShowMoreBtn" onclick="toggleLeaderboardMore()"
                            style="background:var(--rpt-chip);color:var(--rpt-heading);border:1px solid var(--rpt-border);border-radius:8px;padding:7px 18px;font-size:0.78rem;font-weight:700;cursor:pointer;">
                        {{ __('Show :count more', ['count' => $members->count() - 10]) }}
                    </button>
                </div>
                @endif
                @endif
            </div>
        </div>
        @endunless

        {{-- ── Individual Drill-down ── --}}
        @if($selectedPerson)
        <div class="space-y-4">

            {{-- Back to leaderboard --}}
            <a href="{{ route('reports.index', array_merge(request()->except('person_id'), ['tab' => $tab])) }}"
               style="display:inline-flex;align-items:center;gap:5px;color:var(--rpt-muted);font-size:0.78rem;font-weight:600;text-decoration:none;">
                <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                {{ __('Back to') }} {{ $tab === 'team' ? __('Team Leaderboard') : __('Reseller Board') }}
            </a>

            {{-- Person Header --}}
            <div class="rpt-card p-5" style="box-shadow:0 1px 6px #0001;">
                <div style="display:flex;align-items:flex-start;gap:16px;">
                    <img src="{{ $selectedPerson['avatarUrl'] }}" style="width:64px;height:64px;border-radius:14px;object-fit:cover;border:2px solid #e0e7ff;flex-shrink:0;" alt="">
                    <div style="flex:1;min-width:0;">
                        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                            <h2 style="color:var(--rpt-heading);font-size:1.1rem;font-weight:800;">{{ $selectedPerson['name'] }}</h2>
                            @php
                                $rc2 = ['super_admin'=>['c'=>'#7c3aed'],'admin'=>['c'=>'#4f46e5'],'noc'=>['c'=>'#0891b2'],'reseller'=>['c'=>'#c2410c']][$selectedPerson['role']] ?? ['c'=>'#64748b'];
                                $rc2['bg'] = $rc2['c'] . '22';
                            @endphp
                            <span style="background:{{ $rc2['bg'] }};color:{{ $rc2['c'] }};border-radius:20px;padding:2px 12px;font-size:0.68rem;font-weight:700;letter-spacing:0.5px;">
                                {{ strtoupper(str_replace('_',' ',$selectedPerson['role'])) }}
                            </span>
                        </div>
                        @if(!empty($selectedPerson['email']))
                        <p style="color:var(--rpt-body);font-size:0.78rem;margin-top:3px;">{{ $selectedPerson['email'] }}</p>
                        @endif
                        @if(!empty($selectedPerson['phone']))
                        <p style="color:var(--rpt-muted);font-size:0.72rem;">{{ $selectedPerson['phone'] }}</p>
                        @endif
                        <div style="display:flex;align-items:center;gap:8px;margin-top:8px;">
                            <a href="{{ route('reports.excel', array_merge(request()->query(), ['person_id' => $selectedPerson['id']])) }}"
                               style="background:#10b981;color:#fff;border-radius:6px;padding:4px 10px;font-size:0.7rem;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
                                📥 {{ __('Export Person CSV') }}
                            </a>
                            <a href="{{ route('reports.pdf', array_merge(request()->query(), ['person_id' => $selectedPerson['id']])) }}"
                               style="background:#4f46e5;color:#fff;border-radius:6px;padding:4px 10px;font-size:0.7rem;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
                                📄 {{ __('Export Person PDF') }}
                            </a>
                        </div>
                    </div>
                    <div style="flex-shrink:0;text-align:center;">
                        <div style="position:relative;width:72px;height:72px;">
                            <svg viewBox="0 0 36 36" style="width:72px;height:72px;transform:rotate(-90deg);">
                                <circle cx="18" cy="18" r="15.9" fill="none" stroke="#f1f5f9" stroke-width="3"/>
                                <circle cx="18" cy="18" r="15.9" fill="none"
                                        stroke="{{ $selectedPerson['rate']>=70 ? '#10b981' : ($selectedPerson['rate']>=40 ? '#f59e0b' : '#f87171') }}"
                                        stroke-width="3"
                                        stroke-dasharray="{{ $selectedPerson['rate'] }}, 100"
                                        stroke-linecap="round"/>
                            </svg>
                            <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;">
                                <span style="color:var(--rpt-heading);font-size:0.9rem;font-weight:800;">{{ $selectedPerson['rate'] }}%</span>
                            </div>
                        </div>
                        <p style="color:var(--rpt-muted);font-size:0.65rem;margin-top:3px;" title="{{ __('Resolved ÷ :base tickets in this period', ['base' => $tab === 'team' ? __('Assigned') : __('Created')]) }}">{{ __('Resolution') }}</p>
                    </div>
                </div>
            </div>

            {{-- Stat Cards --}}
            @php
                $statCards = [
                    ['label'=>__('CREATED'),     'val'=>$selectedPerson['created'],     'icon'=>'🎫'],
                    ['label'=>__('ASSIGNED'),    'val'=>$selectedPerson['assigned'],    'icon'=>'📌'],
                    ['label'=>__('TRANSFERRED'), 'val'=>$selectedPerson['transferred'], 'icon'=>'🔄'],
                    ['label'=>__('COMMENTS'),    'val'=>$selectedPerson['comments'],    'icon'=>'💬'],
                    ['label'=>__('REACTIONS'),   'val'=>$selectedPerson['reactions'],   'icon'=>'👍'],
                    ['label'=>__('RESOLVED'),    'val'=>$selectedPerson['resolved'],    'icon'=>'✅'],
                    ['label'=>__('IN PROGRESS'), 'val'=>$selectedPerson['in_progress'], 'icon'=>'⚡'],
                    ['label'=>__('PENDING'),     'val'=>$selectedPerson['pending'],     'icon'=>'⏳'],
                ];
            @endphp
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                @foreach($statCards as $sc)
                <div style="background:var(--rpt-card);border:1px solid var(--rpt-border);border-radius:12px;padding:16px 12px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,0.03);">
                    <div style="font-size:1.1rem;opacity:0.85;">{{ $sc['icon'] }}</div>
                    <div style="color:var(--rpt-heading);font-size:1.7rem;font-weight:800;line-height:1;margin-top:6px;">{{ $sc['val'] }}</div>
                    <div style="color:var(--rpt-muted);font-size:0.65rem;font-weight:700;letter-spacing:0.5px;margin-top:6px;text-transform:uppercase;">{{ $sc['label'] }}</div>
                </div>
                @endforeach
            </div>

            {{-- Person Chart --}}
            <div class="rpt-card p-5" style="box-shadow:0 1px 6px #0001;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
                    <p style="color:var(--rpt-heading);font-size:0.85rem;font-weight:700;">📊 {{ __('Ticket Activity') }}</p>
                    <span style="background:var(--rpt-chip);color:var(--rpt-body);border-radius:6px;padding:3px 10px;font-size:0.68rem;font-weight:700;text-transform:uppercase;">{{ $personTickets->count() }} {{ __('tickets') }}</span>
                </div>
                <canvas id="personChart" height="100"></canvas>
            </div>

            {{-- Ticket Table --}}
            <div class="rpt-card overflow-hidden" style="box-shadow:0 1px 6px #0001;">
                <div style="background:var(--rpt-wrap);border-bottom:1px solid var(--rpt-border);padding:12px 20px;display:flex;align-items:center;justify-content:space-between;">
                    <p style="color:var(--rpt-heading);font-size:0.82rem;font-weight:700;">🎫 {{ __('Tickets in this period') }}</p>
                    <span style="background:var(--rpt-card);border:1px solid var(--rpt-border);color:var(--rpt-body);border-radius:20px;padding:2px 10px;font-size:0.7rem;font-weight:700;">{{ $personTickets->count() }}</span>
                </div>
                @if($personTickets->isEmpty())
                <div style="padding:32px;text-align:center;color:var(--rpt-muted);font-size:0.82rem;">{{ __('No tickets found for this period.') }}</div>
                @else
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;font-size:0.78rem;">
                        <thead>
                            <tr style="background:var(--rpt-wrap);border-bottom:1px solid var(--rpt-border);">
                                <th style="padding:10px 16px;text-align:left;color:var(--rpt-muted);font-size:0.65rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;">#</th>
                                <th style="padding:10px 16px;text-align:left;color:var(--rpt-muted);font-size:0.65rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;">{{ __('Title') }}</th>
                                <th style="padding:10px 16px;text-align:left;color:var(--rpt-muted);font-size:0.65rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;">{{ __('Status') }}</th>
                                <th style="padding:10px 16px;text-align:left;color:var(--rpt-muted);font-size:0.65rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;">{{ __('Priority') }}</th>
                                <th style="padding:10px 16px;text-align:left;color:var(--rpt-muted);font-size:0.65rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;">{{ __('Date') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($personTickets->take(20) as $t)
                            @php
                                [$sc2bg,$sc2c] = match($t->status) {
                                    'in_progress'                   => ['#f0f9ff','#0ea5e9'],
                                    'pending'                       => ['#fff7ed','#f97316'],
                                    'waiting_for_customer_feedback' => ['#f5f3ff','#8b5cf6'],
                                    'resolved'                      => ['#f0fdf4','#10b981'],
                                    default                         => ['#f1f5f9','#64748b'],
                                };
                                [$pcbg,$pcc] = match($t->priority) {
                                    'critical' => ['#fef2f2','#ef4444'],
                                    'high'     => ['#fff7ed','#f97316'],
                                    'medium'   => ['#fffbeb','#d97706'],
                                    default    => ['#f1f5f9','#64748b'],
                                };
                            @endphp
                            <tr style="border-bottom:1px solid var(--rpt-border2);cursor:pointer;transition:background .1s;"
                                onmouseover="this.style.background='var(--rpt-hover)'" onmouseout="this.style.background=''"
                                onclick="window.location='{{ route('tickets.show', $t) }}'">
                                <td style="padding:10px 16px;color:var(--rpt-faint);font-family:monospace;font-size:0.72rem;">#{{ $t->id }}</td>
                                <td style="padding:10px 16px;color:var(--rpt-heading);font-weight:600;max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $t->title }}</td>
                                <td style="padding:10px 16px;">
                                    <span style="background:{{ $sc2bg }};color:{{ $sc2c }};border-radius:20px;padding:2px 10px;font-size:0.68rem;font-weight:700;">
                                        {{ __(ucfirst(str_replace('_',' ',$t->status))) }}
                                    </span>
                                </td>
                                <td style="padding:10px 16px;">
                                    <span style="background:{{ $pcbg }};color:{{ $pcc }};border-radius:6px;padding:2px 8px;font-size:0.68rem;font-weight:700;">
                                        {{ __(ucfirst($t->priority)) }}
                                    </span>
                                </td>
                                <td style="padding:10px 16px;color:var(--rpt-muted);font-size:0.72rem;">{{ $t->created_at->format('d M Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($personTickets->count() > 20)
                <div style="padding:10px 20px;color:var(--rpt-muted);font-size:0.72rem;border-top:1px solid var(--rpt-border2);">{{ __('Showing :shown of :total tickets', ['shown' => 20, 'total' => $personTickets->count()]) }}</div>
                @endif
                @endif
            </div>

        </div>{{-- /drill-down --}}
        @endif

    </div>{{-- /grid --}}


    @unless($selectedPerson)
    {{-- ── KPI Cards ── --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-5">
        @php
        $pctOfTotal = __('% of total');
        $kpis = [
            ['label'=>__('TOTAL TICKETS'),    'val'=>$periodStats['total'],                         'sub'=>__('All cataloged tickets'),         'icon'=>'📋', 'c'=>'#6366f1', 'light'=>'#eef2ff', 'border'=>'#c7d2fe', 'trend'=>$trend['total'] ?? null],
            ['label'=>__('IN PROGRESS'),      'val'=>$periodStats['in_progress'],                   'sub'=>round($periodStats['total']>0?$periodStats['in_progress']/$periodStats['total']*100:0).$pctOfTotal, 'icon'=>'⚡', 'c'=>'#0ea5e9', 'light'=>'#f0f9ff', 'border'=>'#bae6fd', 'trend'=>null],
            ['label'=>__('PENDING'),          'val'=>$periodStats['pending'],                       'sub'=>round($periodStats['total']>0?$periodStats['pending']/$periodStats['total']*100:0).$pctOfTotal,      'icon'=>'⏳', 'c'=>'#f97316', 'light'=>'#fff7ed', 'border'=>'#fed7aa', 'trend'=>null],
            ['label'=>__('WAITING FEEDBACK'), 'val'=>$periodStats['waiting_for_customer_feedback'], 'sub'=>round($periodStats['total']>0?$periodStats['waiting_for_customer_feedback']/$periodStats['total']*100:0).$pctOfTotal, 'icon'=>'💬', 'c'=>'#8b5cf6', 'light'=>'#f5f3ff', 'border'=>'#ddd6fe', 'trend'=>null],
            ['label'=>__('RESOLVED'),         'val'=>$periodStats['resolved'],                      'sub'=>$resRate.__('% resolution rate'),    'icon'=>'✅', 'c'=>'#10b981', 'light'=>'#f0fdf4', 'border'=>'#a7f3d0', 'trend'=>$trend['resolved'] ?? null],
        ];
        @endphp
        @foreach($kpis as $k)
        <div style="background:var(--rpt-card);border:1px solid {{ $k['border'] }};border-radius:14px;padding:18px 16px;position:relative;overflow:hidden;box-shadow:0 2px 12px {{ $k['c'] }}10;">
            <div style="position:absolute;top:-12px;right:-12px;width:64px;height:64px;background:{{ $k['c'] }}22;border-radius:50%;"></div>
            <div style="display:flex;align-items:flex-start;justify-content:space-between;">
                <div style="font-size:1.5rem;margin-bottom:6px;">{{ $k['icon'] }}</div>
                @if($k['trend'] !== null)
                @php
                    $isUp = $k['trend'] >= 0;
                    $trendColor = $isUp ? '#10b981' : '#ef4444';
                    $trendBg    = $isUp ? '#10b98122' : '#ef444422';
                @endphp
                <span style="background:{{ $trendBg }};color:{{ $trendColor }};border-radius:20px;padding:2px 8px;font-size:0.65rem;font-weight:800;z-index:1;position:relative;" title="vs previous period">
                    {{ $isUp ? '▲' : '▼' }} {{ number_format(abs($k['trend']), 1) }}%
                </span>
                @endif
            </div>
            <div class="stat-num" style="color:{{ $k['c'] }};">{{ $k['val'] }}</div>
            <div style="color:var(--rpt-muted);font-size:0.65rem;font-weight:700;letter-spacing:1px;margin-top:4px;text-transform:uppercase;">{{ $k['label'] }}</div>
            <div style="color:var(--rpt-faint);font-size:0.7rem;margin-top:2px;">{{ $k['sub'] }}</div>
        </div>
        @endforeach
    </div>

    {{-- ── Charts Row ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-5">

        {{-- Trend Chart --}}
        <div class="rpt-card p-5" style="box-shadow:0 1px 6px #0001;">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p style="color:var(--rpt-heading);font-size:0.9rem;font-weight:700;">📈 {{ __('Ticket Trend') }}</p>
                    <p style="color:var(--rpt-muted);font-size:0.72rem;">{{ __('Tickets created over time') }}</p>
                </div>
                <span style="background:var(--rpt-chip);color:var(--rpt-body);border-radius:6px;padding:3px 10px;font-size:0.68rem;font-weight:700;letter-spacing:0.5px;text-transform:uppercase;">
                    {{ $periodLabels[$period] ?? '' }}
                </span>
            </div>
            <canvas id="trendChart" height="110"></canvas>
        </div>

        {{-- Status Donut --}}
        <div class="rpt-card p-5" style="box-shadow:0 1px 6px #0001;">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p style="color:var(--rpt-heading);font-size:0.9rem;font-weight:700;">🍩 {{ __('Status Distribution') }}</p>
                    <p style="color:var(--rpt-muted);font-size:0.72rem;">{{ __('Current ticket status breakdown') }}</p>
                </div>
                <span style="background:var(--rpt-chip);color:var(--rpt-body);border-radius:6px;padding:3px 10px;font-size:0.68rem;font-weight:700;text-transform:uppercase;">{{ __('BREAKDOWN') }}</span>
            </div>
            <div class="flex items-center gap-6">
                <div style="position:relative;width:140px;height:140px;flex-shrink:0;">
                    <canvas id="donutChart" width="140" height="140"></canvas>
                    <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;">
                        <span style="color:var(--rpt-heading);font-size:1.5rem;font-weight:800;">{{ $periodStats['total'] }}</span>
                        <span style="color:var(--rpt-muted);font-size:0.65rem;">{{ __('Total') }}</span>
                    </div>
                </div>
                <div style="flex:1;display:flex;flex-direction:column;gap:10px;">
                    @php
                    $donutItems = [
                        [__('In Progress'), $periodStats['in_progress'],                   '#0ea5e9','#f0f9ff'],
                        [__('Pending'),     $periodStats['pending'],                       '#f97316','#fff7ed'],
                        [__('Waiting'),     $periodStats['waiting_for_customer_feedback'], '#8b5cf6','#f5f3ff'],
                        [__('Resolved'),    $periodStats['resolved_all_time'] ?? $periodStats['resolved'], '#10b981','#f0fdf4'],
                    ];
                    @endphp
                    @foreach($donutItems as [$dlabel,$dval,$dcol,$dbg])
                    <div style="display:flex;align-items:center;gap:8px;">
                        <div style="width:10px;height:10px;border-radius:3px;background:{{ $dcol }};flex-shrink:0;"></div>
                        <span style="color:var(--rpt-body);font-size:0.75rem;flex:1;">{{ $dlabel }}</span>
                        <span style="color:var(--rpt-heading);font-size:0.82rem;font-weight:700;">{{ $dval }}</span>
                        <span style="background:{{ $dbg }};color:{{ $dcol }};border-radius:20px;padding:1px 8px;font-size:0.68rem;font-weight:700;">
                            {{ $periodStats['total']>0 ? round($dval/$periodStats['total']*100) : 0 }}%
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- ── Resolved by Agent ── --}}
    @php
        $resolvedByAgentAll = $members->where('resolved', '>', 0)->sortByDesc('resolved')->values();
        $resolvedByAgent = $resolvedByAgentAll->take(10);
    @endphp
    @if($resolvedByAgent->isNotEmpty())
    <div class="rpt-card p-5 mb-5" style="box-shadow:0 1px 6px #0001;">
        <div class="flex items-center justify-between mb-4">
            <div>
                <p style="color:var(--rpt-heading);font-size:0.9rem;font-weight:700;">✅ {{ $tab === 'team' ? __('Resolved by Agent') : __('Resolved by Reseller') }}</p>
                <p style="color:var(--rpt-muted);font-size:0.72rem;">
                    {{ __('How many tickets each person resolved') }} — {{ $periodLabels[$period] ?? '' }}
                    @if($resolvedByAgentAll->count() > 10) ({{ __('top 10 shown') }}) @endif
                </p>
            </div>
        </div>
        <div style="max-height:360px; overflow-y:auto;">
            <canvas id="resolvedByAgentChart" height="{{ max(60, $resolvedByAgent->count() * 34) }}"></canvas>
        </div>
    </div>
    @endif


    {{-- ── Key Insights Bar ── --}}
    <div style="background:var(--rpt-card);border:1px solid var(--rpt-border);border-radius:14px;padding:16px 20px;margin-top:20px;display:flex;flex-wrap:wrap;gap:20px;align-items:flex-start;box-shadow:0 1px 4px #0001;">
        <div style="display:flex;align-items:center;gap:8px;flex-basis:100%;">
            <span style="font-size:1rem;">💡</span>
            <span style="color:var(--rpt-body);font-size:0.78rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;">{{ __('Key Analytics Highlights & Insights') }}</span>
        </div>
        @php
            $topMember = $members->sortByDesc('resolved')->first();
            $resolvedAllTime = $periodStats['resolved_all_time'] ?? $periodStats['resolved'];
            $totalRes  = $periodStats['total'] > 0 ? round($resolvedAllTime/$periodStats['total']*100) : 0;
        @endphp
        <div style="display:flex;align-items:flex-start;gap:8px;min-width:200px;">
            <span style="color:#6366f1;margin-top:2px;">📌</span>
            <p style="color:var(--rpt-body);font-size:0.75rem;line-height:1.5;">
                <span style="color:var(--rpt-heading);font-weight:700;">{{ __('Resolution Rate:') }}</span>
                {{ __(':percent% of all tickets resolved (:resolved/:total)', ['percent' => $totalRes, 'resolved' => $resolvedAllTime, 'total' => $periodStats['total']]) }}
            </p>
        </div>
        @if($topMember)
        <div style="display:flex;align-items:flex-start;gap:8px;min-width:200px;">
            <span style="color:#f59e0b;margin-top:2px;">🏆</span>
            <p style="color:var(--rpt-body);font-size:0.75rem;line-height:1.5;">
                <span style="color:var(--rpt-heading);font-weight:700;">{{ __('Top Performer:') }}</span>
                {{ __(':name with :count resolved (:rate% rate)', ['name' => $topMember['name'], 'count' => $topMember['resolved'], 'rate' => $topMember['rate']]) }}
            </p>
        </div>
        @endif
        <div style="display:flex;align-items:flex-start;gap:8px;min-width:200px;">
            <span style="color:#f97316;margin-top:2px;">⚡</span>
            <p style="color:var(--rpt-body);font-size:0.75rem;line-height:1.5;">
                <span style="color:var(--rpt-heading);font-weight:700;">{{ __('Active Pipeline:') }}</span>
                {{ __(':count tickets in progress', ['count' => $periodStats['in_progress'] + $periodStats['pending'] + $periodStats['waiting_for_customer_feedback']]) }}
                &mdash; {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} — {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}
            </p>
        </div>
    </div>
    @endunless

</div>{{-- /wrapper --}}

{{-- ── Charts JS ── --}}
<script>
(function(){
    const opts = {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                mode: 'index', intersect: false,
                backgroundColor: '#fff',
                titleColor: '#64748b',
                bodyColor: '#0f172a',
                borderColor: '#e2e8f0',
                borderWidth: 1,
                padding: 10,
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { stepSize: 1, color: '#cbd5e1', font: { size: 11 } },
                grid: { color: '#f1f5f9' }
            },
            x: {
                ticks: { color: '#cbd5e1', font: { size: 10 } },
                grid: { display: false }
            }
        }
    };

@unless($selectedPerson)
    new Chart(document.getElementById('trendChart'), {
        type: 'bar',
        data: {
            labels: @json($chartDays),
            datasets: [{
                label: 'Tickets',
                data: @json($chartCounts),
                backgroundColor: 'rgba(99,102,241,0.18)',
                borderColor: '#6366f1',
                borderWidth: 1.5,
                borderRadius: 5,
            }]
        },
        options: opts
    });

    new Chart(document.getElementById('donutChart'), {
        type: 'doughnut',
        data: {
            labels: ['In Progress','Pending','Waiting','Resolved'],
            datasets: [{
                data: [
                    {{ $periodStats['in_progress'] }},
                    {{ $periodStats['pending'] }},
                    {{ $periodStats['waiting_for_customer_feedback'] }},
                    {{ $periodStats['resolved_all_time'] ?? $periodStats['resolved'] }},
                ],
                backgroundColor: ['#0ea5e9','#f97316','#8b5cf6','#10b981'],
                borderWidth: 2,
                borderColor: '#fff',
                hoverOffset: 6,
            }]
        },
        options: {
            responsive: false,
            cutout: '68%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#fff',
                    titleColor: '#64748b',
                    bodyColor: '#0f172a',
                    borderColor: '#e2e8f0',
                    borderWidth: 1,
                }
            }
        }
    });

    const resolvedChartEl = document.getElementById('resolvedByAgentChart');
    if (resolvedChartEl) {
        new Chart(resolvedChartEl, {
            type: 'bar',
            data: {
                labels: @json($resolvedByAgent->pluck('name')),
                datasets: [{
                    label: 'Resolved',
                    data: @json($resolvedByAgent->pluck('resolved')),
                    backgroundColor: '#10b981',
                    borderRadius: 5,
                    barThickness: 18,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, ticks: { stepSize: 1, color: '#94a3b8', font: { size: 11 } }, grid: { color: '#f1f5f9' } },
                    y: { ticks: { color: '#64748b', font: { size: 11 } }, grid: { display: false } }
                }
            }
        });
    }
@endunless

@if($selectedPerson)
    new Chart(document.getElementById('personChart'), {
        type: 'bar',
        data: {
            labels: @json($personChartDays),
            datasets: [{
                label: 'Tickets',
                data: @json($personChartCounts),
                backgroundColor: 'rgba(99,102,241,0.2)',
                borderColor: '#6366f1',
                borderWidth: 1.5,
                borderRadius: 5,
            }]
        },
        options: opts
    });
@endif
})();

function toggleCustomPeriod(val) {
    const el = document.getElementById('customRange');
    if (el) {
        if (val === 'custom') { el.classList.remove('hidden'); el.classList.add('flex'); }
        else { el.classList.add('hidden'); el.classList.remove('flex'); }
    }
}

// ── Leaderboard search + Show More ───────────────────────────────────────
let lbExpanded = false;

function filterLeaderboard(query) {
    const q = query.trim().toLowerCase();
    const btn = document.getElementById('lbShowMoreBtn');
    document.querySelectorAll('#leaderboardList tr[data-name]').forEach(row => {
        if (!q) {
            // No query: restore normal show-more state
            row.style.display = (row.classList.contains('lb-extra') && !lbExpanded) ? 'none' : 'table-row';
        } else {
            // Searching: ignore show-more paging, just match
            row.style.display = row.dataset.name.includes(q) ? 'table-row' : 'none';
        }
    });
    if (btn) btn.style.display = q ? 'none' : '';
}

function toggleLeaderboardMore() {
    lbExpanded = !lbExpanded;
    document.querySelectorAll('#leaderboardList tr.lb-extra').forEach(row => {
        row.style.display = lbExpanded ? 'table-row' : 'none';
    });
    const btn = document.getElementById('lbShowMoreBtn');
    if (btn) {
        const extraCount = document.querySelectorAll('#leaderboardList tr.lb-extra').length;
        const showLessLabel = @json(__('Show less'));
        const showMoreLabel = @json(__('Show :count more', ['count' => '__COUNT__']));
        btn.textContent = lbExpanded ? showLessLabel : showMoreLabel.replace('__COUNT__', extraCount);
    }
}
</script>
</x-app-layout>
