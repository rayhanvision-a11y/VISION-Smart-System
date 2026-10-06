<x-app-layout>
    @php $user = auth()->user(); @endphp

    <div x-data="{ 
        showWebhookInfo: false,
        selectedLogPayload: null,
        copied: false,
        copyUrl(url) {
            navigator.clipboard.writeText(url);
            this.copied = true;
            setTimeout(() => this.copied = false, 2500);
        }
    }">

        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-black text-slate-800 dark:text-white flex items-center gap-2.5">
                    <span>📷</span>
                    <span>{{ __('Attendance Logs & Biometric Monitoring') }}</span>
                </h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    {{ __('Real-time face recognition and access control logs from HikCentral') }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" @click="showWebhookInfo = !showWebhookInfo"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 transition-colors shadow-2xs border border-indigo-200/60 dark:border-indigo-800/60">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ __('Webhook Details') }}</span>
                </button>

                <a href="{{ route('roster.index') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors shadow-2xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>{{ __('Shift Roster') }}</span>
                </a>

                <button onclick="window.location.reload()"
                        class="p-2 rounded-xl bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 shadow-2xs"
                        title="{{ __('Refresh') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </button>
            </div>
        </div>

        {{-- Webhook Information Accordion Card --}}
        <div x-show="showWebhookInfo" x-transition class="mb-6 bg-linear-to-br from-indigo-50/80 via-white to-sky-50/80 dark:from-slate-900 dark:via-slate-900/90 dark:to-slate-900 border border-indigo-200/80 dark:border-indigo-900/50 rounded-2xl p-5 shadow-sm">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 border-b border-indigo-100 dark:border-slate-800">
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold text-lg shadow-md shadow-indigo-600/20">
                        ⚡
                    </span>
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">
                            {{ __('HikCentral OpenAPI Webhook Configuration') }}
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            {{ __('Configure this endpoint in your HikCentral Event Subscription to automatically synchronize face events.') }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/50">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        {{ __('Endpoint Online') }}
                    </span>
                    <a :href="'{{ $statusUrl }}'" target="_blank" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                        {{ __('Check Health') }} &rarr;
                    </a>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                        {{ __('Webhook URL (Post to this in HikCentral):') }}
                    </label>
                    <div class="flex items-center gap-2">
                        <input type="text" readonly value="{{ $webhookUrl }}"
                               class="flex-1 text-xs font-mono bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-800 dark:text-slate-200 select-all">
                        <button type="button" @click="copyUrl('{{ $webhookUrl }}')"
                                class="px-3.5 py-2 rounded-xl text-xs font-bold bg-indigo-600 text-white hover:bg-indigo-500 transition-colors flex items-center gap-1.5 flex-shrink-0 shadow-xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            <span x-text="copied ? '{{ __('Copied!') }}' : '{{ __('Copy') }}'"></span>
                        </button>
                    </div>
                </div>

                <div class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed bg-white/70 dark:bg-slate-800/50 p-3 rounded-xl border border-slate-200/60 dark:border-slate-700/60">
                    <p class="font-bold text-slate-800 dark:text-slate-200 mb-1">💡 {{ __('Quick Setup in HikCentral:') }}</p>
                    <ol class="list-decimal list-inside space-y-0.5">
                        <li>{{ __('Open OpenAPI Manager or /artemis-web') }}</li>
                        <li>{{ __('Go to Event Subscription > Access Control Events') }}</li>
                        <li>{{ __('Enable "Face Authentication Passed" and paste this Webhook URL') }}</li>
                    </ol>
                </div>
            </div>
        </div>

        {{-- Top KPI Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5 mb-6">
            {{-- Card 1: Total Today --}}
            <div class="dash-card bg-white dark:bg-[#131b2e] border border-slate-200/80 dark:border-slate-800/80 shadow-xs p-4 rounded-2xl flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg flex-shrink-0">
                    📷
                </div>
                <div class="min-w-0">
                    <p class="text-xl font-black text-slate-900 dark:text-white leading-none">{{ $stats['today_total'] }}</p>
                    <p class="text-[11px] font-semibold text-slate-400 mt-1 truncate">{{ __('Punches Today') }}</p>
                </div>
            </div>

            {{-- Card 2: Unique Staff --}}
            <div class="dash-card bg-white dark:bg-[#131b2e] border border-slate-200/80 dark:border-slate-800/80 shadow-xs p-4 rounded-2xl flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg flex-shrink-0">
                    👥
                </div>
                <div class="min-w-0">
                    <p class="text-xl font-black text-emerald-600 dark:text-emerald-400 leading-none">{{ $stats['today_unique_staff'] }}</p>
                    <p class="text-[11px] font-semibold text-slate-400 mt-1 truncate">{{ __('Staff Present') }}</p>
                </div>
            </div>

            {{-- Card 3: Day Shift Check-ins --}}
            <div class="dash-card bg-white dark:bg-[#131b2e] border border-slate-200/80 dark:border-slate-800/80 shadow-xs p-4 rounded-2xl flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg flex-shrink-0">
                    ☀️
                </div>
                <div class="min-w-0">
                    <p class="text-xl font-black text-amber-600 dark:text-amber-400 leading-none">{{ $stats['today_day_shift'] }}</p>
                    <p class="text-[11px] font-semibold text-slate-400 mt-1 truncate">{{ __('Day Shift') }}</p>
                </div>
            </div>

            {{-- Card 4: Night Shift Check-ins --}}
            <div class="dash-card bg-white dark:bg-[#131b2e] border border-slate-200/80 dark:border-slate-800/80 shadow-xs p-4 rounded-2xl flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center text-lg flex-shrink-0">
                    🌙
                </div>
                <div class="min-w-0">
                    <p class="text-xl font-black text-purple-600 dark:text-purple-400 leading-none">{{ $stats['today_night_shift'] }}</p>
                    <p class="text-[11px] font-semibold text-slate-400 mt-1 truncate">{{ __('Night Shift') }}</p>
                </div>
            </div>

            {{-- Card 5: Unmatched Employee IDs --}}
            <div class="dash-card bg-white dark:bg-[#131b2e] border border-slate-200/80 dark:border-slate-800/80 shadow-xs p-4 rounded-2xl flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl {{ $stats['total_unmatched'] > 0 ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-400' }} flex items-center justify-center text-lg flex-shrink-0">
                    ⚠️
                </div>
                <div class="min-w-0">
                    <p class="text-xl font-black {{ $stats['total_unmatched'] > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-800 dark:text-slate-200' }} leading-none">
                        {{ $stats['total_unmatched'] }}
                    </p>
                    <p class="text-[11px] font-semibold text-slate-400 mt-1 truncate">{{ __('Unmatched IDs') }}</p>
                </div>
            </div>
        </div>

        {{-- Filter Bar --}}
        <form method="GET" action="{{ route('attendance.index') }}" data-auto-filter
              class="bg-white dark:bg-[#131b2e] border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-4 mb-6 shadow-xs flex flex-wrap items-center gap-3">
            
            {{-- Date Filter --}}
            <div class="flex items-center gap-2">
                <label class="text-xs font-bold text-slate-500 dark:text-slate-400">{{ __('Date:') }}</label>
                <input type="date" name="date" value="{{ request('date') }}"
                       class="border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-1.5 text-xs text-slate-800 dark:text-slate-200 bg-slate-50 dark:bg-slate-800 focus:ring-2 focus:ring-indigo-500">
            </div>

            {{-- Staff Filter --}}
            <div class="flex items-center gap-2">
                <label class="text-xs font-bold text-slate-500 dark:text-slate-400">{{ __('Staff:') }}</label>
                <select name="user_id" class="border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-1.5 text-xs text-slate-800 dark:text-slate-200 bg-slate-50 dark:bg-slate-800 focus:ring-2 focus:ring-indigo-500">
                    <option value="">{{ __('All Staff') }}</option>
                    @foreach($staffUsers as $su)
                        <option value="{{ $su->id }}" {{ (string) request('user_id') === (string) $su->id ? 'selected' : '' }}>
                            {{ $su->name }} ({{ $su->office_id ? 'Office ID: ' . $su->office_id : 'ID: ' . $su->id }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Shift Filter --}}
            <div class="flex items-center gap-2">
                <label class="text-xs font-bold text-slate-500 dark:text-slate-400">{{ __('Shift:') }}</label>
                <select name="shift" class="border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-1.5 text-xs text-slate-800 dark:text-slate-200 bg-slate-50 dark:bg-slate-800 focus:ring-2 focus:ring-indigo-500">
                    <option value="">{{ __('All Shifts') }}</option>
                    <option value="day_shift" {{ request('shift') === 'day_shift' ? 'selected' : '' }}>☀️ {{ __('Day Shift') }}</option>
                    <option value="night_shift" {{ request('shift') === 'night_shift' ? 'selected' : '' }}>🌙 {{ __('Night Shift') }}</option>
                </select>
            </div>

            {{-- Match Status --}}
            <div class="flex items-center gap-2">
                <label class="text-xs font-bold text-slate-500 dark:text-slate-400">{{ __('Status:') }}</label>
                <select name="match_status" class="border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-1.5 text-xs text-slate-800 dark:text-slate-200 bg-slate-50 dark:bg-slate-800 focus:ring-2 focus:ring-indigo-500">
                    <option value="">{{ __('All Events') }}</option>
                    <option value="matched" {{ request('match_status') === 'matched' ? 'selected' : '' }}>✅ {{ __('Matched Staff') }}</option>
                    <option value="unmatched" {{ request('match_status') === 'unmatched' ? 'selected' : '' }}>⚠️ {{ __('Unmatched IDs') }}</option>
                </select>
            </div>

            {{-- Search Keyword --}}
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search by Employee ID, Name, Door...') }}"
                       class="w-full border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-1.5 text-xs text-slate-800 dark:text-slate-200 bg-slate-50 dark:bg-slate-800 focus:ring-2 focus:ring-indigo-500">
            </div>

            {{-- Submit / Clear --}}
            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold transition-colors shadow-xs">
                    {{ __('Filter') }}
                </button>
                @if(request()->anyFilled(['date', 'user_id', 'shift', 'match_status', 'search']))
                    <a href="{{ route('attendance.index') }}" class="px-3 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl text-xs font-semibold transition-colors">
                        {{ __('Reset') }}
                    </a>
                @endif
            </div>
        </form>

        {{-- Log Records Table Card --}}
        <div class="dash-card bg-white dark:bg-[#131b2e] border border-slate-200/80 dark:border-slate-800/80 rounded-2xl shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-extrabold text-slate-800 dark:text-white">
                        {{ __('Live Access Event Stream') }}
                    </h2>
                    <p class="text-[11px] text-slate-400 mt-0.5">
                        {{ __('Showing latest biometric check-in records') }} ({{ $logs->total() }} {{ __('total') }})
                    </p>
                </div>
            </div>

            @if($logs->isEmpty())
                <div class="py-16 text-center">
                    <div class="w-14 h-14 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center text-2xl mx-auto mb-3">
                        📷
                    </div>
                    <p class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ __('No attendance events recorded yet') }}</p>
                    <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                        {{ __('When staff scan their faces at the HikCentral terminal, access events will instantly appear here.') }}
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                <th class="py-3 px-4">{{ __('Event Time') }}</th>
                                <th class="py-3 px-4">{{ __('Staff Member / ID') }}</th>
                                <th class="py-3 px-4">{{ __('Door / Terminal') }}</th>
                                <th class="py-3 px-4">{{ __('Verification') }}</th>
                                <th class="py-3 px-4">{{ __('Assigned Shift') }}</th>
                                <th class="py-3 px-4">{{ __('Duty Status') }}</th>
                                <th class="py-3 px-4 text-right">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/70">
                            @foreach($logs as $log)
                                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                                    {{-- Time --}}
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <div class="font-bold text-slate-800 dark:text-slate-200">
                                            {{ $log->event_time ? $log->event_time->format('h:i:s A') : 'N/A' }}
                                        </div>
                                        <div class="text-[10px] text-slate-400">
                                            {{ $log->event_time ? $log->event_time->format('d M, Y') : '' }}
                                            ({{ $log->event_time ? $log->event_time->diffForHumans() : '' }})
                                        </div>
                                    </td>

                                    {{-- Staff Member --}}
                                    <td class="py-3.5 px-4">
                                        @if($log->user)
                                            <div class="flex items-center gap-2.5">
                                                <img src="{{ $log->user->avatarUrl() }}" alt="{{ $log->user->name }}"
                                                     class="w-8 h-8 rounded-full object-cover border border-slate-200 dark:border-slate-700 flex-shrink-0">
                                                <div class="min-w-0">
                                                    <div class="font-extrabold text-slate-900 dark:text-white truncate">
                                                        {{ $log->user->name }}
                                                    </div>
                                                    <div class="text-[10px] text-slate-400 flex items-center gap-1.5 flex-wrap">
                                                        <span class="font-mono bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 px-1.5 py-0.2 rounded font-bold">
                                                            ID: {{ $log->user->office_id ?: $log->employee_no }}
                                                        </span>
                                                        @if($log->user->team)
                                                            <span>• {{ $log->user->team }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="flex items-center gap-2">
                                                <span class="w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                                    ?
                                                </span>
                                                <div>
                                                    <div class="font-bold text-amber-700 dark:text-amber-300">
                                                        {{ $log->person_name ?: __('Unregistered Person') }}
                                                    </div>
                                                    <span class="text-[10px] font-mono text-slate-400">
                                                        {{ __('Emp ID:') }} {{ $log->employee_no }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endif
                                    </td>

                                    {{-- Door / Device --}}
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <div class="font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-1">
                                            <span>🚪</span>
                                            <span>{{ $log->door_name ?: __('Office Door') }}</span>
                                        </div>
                                        @if($log->device_name)
                                            <div class="text-[10px] text-slate-400">{{ $log->device_name }}</div>
                                        @endif
                                    </td>

                                    {{-- Verification Type --}}
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200/70 dark:border-indigo-800/50">
                                            <span>👤</span>
                                            <span>{{ __('Face Verified') }}</span>
                                        </span>
                                    </td>

                                    {{-- Shift --}}
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        @if($log->shift_assigned === 'day_shift')
                                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-600 dark:text-amber-400">
                                                <span>☀️</span>
                                                <span>{{ __('Day Shift') }}</span>
                                            </span>
                                        @elseif($log->shift_assigned === 'night_shift')
                                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-purple-600 dark:text-purple-400">
                                                <span>🌙</span>
                                                <span>{{ __('Night Shift') }}</span>
                                            </span>
                                        @else
                                            <span class="text-slate-400">{{ __('N/A') }}</span>
                                        @endif
                                    </td>

                                    {{-- Duty Status --}}
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        @if($log->user && $log->user->isOnDuty())
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                {{ __('On Duty') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-400">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                                                {{ __('Off Duty') }}
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Action --}}
                                    <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1.5">
                                            @if($log->raw_data)
                                                <button type="button" @click="selectedLogPayload = @js($log->raw_data)"
                                                        class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-slate-800 transition-colors"
                                                        title="{{ __('View Payload') }}">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                                                </button>
                                            @endif

                                            @if($user->isAdmin())
                                                <form method="POST" action="{{ route('attendance.destroy', $log) }}"
                                                      onsubmit="return confirm('{{ __('Delete this attendance record?') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-slate-800 transition-colors"
                                                            title="{{ __('Delete Record') }}">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination Links --}}
                @if($logs->hasPages())
                    <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800">
                        {{ $logs->links() }}
                    </div>
                @endif
            @endif
        </div>

        {{-- Raw Data Modal (Alpine.js) --}}
        <div x-show="selectedLogPayload" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.away="selectedLogPayload = null"
                 class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-xl w-full p-6 shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                        <span>🔍</span>
                        <span>{{ __('HikCentral Raw Event Payload') }}</span>
                    </h3>
                    <button type="button" @click="selectedLogPayload = null" class="text-slate-400 hover:text-slate-600 text-lg font-bold">
                        &times;
                    </button>
                </div>
                <div class="max-h-80 overflow-y-auto bg-slate-900 text-slate-100 p-3 rounded-xl font-mono text-[11px] leading-relaxed">
                    <pre x-text="JSON.stringify(selectedLogPayload, null, 2)"></pre>
                </div>
                <div class="mt-4 text-right">
                    <button type="button" @click="selectedLogPayload = null"
                            class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold hover:bg-slate-200 dark:hover:bg-slate-700">
                        {{ __('Close') }}
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
