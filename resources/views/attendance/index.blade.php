<x-app-layout>
    @php $user = auth()->user(); @endphp

    <div x-data="{ 
        openAttendanceTable: false,
        showWebhookInfo: false,
        selectedLogPayload: null,
        copied: false,
        testingConnection: false,
        testResult: null,
        copyUrl(url) {
            navigator.clipboard.writeText(url);
            this.copied = true;
            setTimeout(() => this.copied = false, 2500);
        },
        async testConnection() {
            this.testingConnection = true;
            this.testResult = null;
            try {
                const res = await fetch('{{ route('attendance.test-connection') }}');
                const data = await res.json();
                this.testResult = data;
            } catch (err) {
                this.testResult = {
                    success: false,
                    message: 'Network error checking device: ' + (err.message || 'Server timeout')
                };
            } finally {
                this.testingConnection = false;
            }
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
            <div class="flex items-center flex-wrap gap-2">
                {{-- Live Auto-Sync Indicator Badge --}}
                <div class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/60 shadow-2xs"
                     title="{{ __('Page automatically updates when face scans arrive') }}">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>{{ __('Live Auto-Sync') }}</span>
                </div>

                {{-- Live Test Connection Button --}}
                <button type="button" @click="testConnection()" :disabled="testingConnection"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-emerald-600 text-white hover:bg-emerald-500 disabled:opacity-60 transition-colors shadow-2xs">
                    <template x-if="!testingConnection">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </template>
                    <template x-if="testingConnection">
                        <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    </template>
                    <span x-text="testingConnection ? '{{ __('Checking...') }}' : '{{ __('Test Machine') }}'"></span>
                </button>

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
            @php
                $networkHealth = $networkHealth ?? [];
                $devIp = $networkHealth['device_ip'] ?? '172.27.1.49';
                $dnsSrvIp = $networkHealth['dns_ip'] ?? '172.30.20.50';
                $srvIp = $networkHealth['server_lan_ip'] ?? $networkHealth['server_ip'] ?? '103.31.179.118';
            @endphp
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 border-b border-indigo-100 dark:border-slate-800">
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold text-lg shadow-md shadow-indigo-600/20">
                        ⚡
                    </span>
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">
                            {{ __('Hikvision & HikCentral Biometric Integration') }}
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            {{ __('Real-time event synchronization from LAN terminal or cloud OpenAPI.') }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center flex-wrap gap-2">
                    {{-- Device LAN Status Badge --}}
                    @if(!empty($networkHealth['device_online']))
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/50">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            {{ __('Device Online') }} ({{ $devIp }})
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/50">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            {{ __('Device') }}: {{ $devIp }}
                        </span>
                    @endif

                    {{-- DNS Status Badge --}}
                    @if(!empty($networkHealth['dns_online']))
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800/50">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            DNS: {{ $dnsSrvIp }}
                        </span>
                    @endif

                    <a :href="'{{ $statusUrl }}'" target="_blank" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline inline-flex items-center gap-1">
                        {{ __('Status API') }} &rarr;
                    </a>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-1 lg:grid-cols-2 gap-4">
                {{-- LAN Webhook (Preferred for Attendance Device on same LAN) --}}
                <div class="p-3.5 bg-white/80 dark:bg-slate-800/80 rounded-xl border border-indigo-200 dark:border-indigo-900/40 shadow-2xs">
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                            <span>🏠</span>
                            <span>{{ __('LAN Webhook URL') }}</span>
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-extrabold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/80 dark:text-emerald-300">
                                {{ __('Recommended for Local Terminal') }}
                            </span>
                        </label>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="text" readonly value="{{ $lanWebhookUrl ?? $webhookUrl }}"
                               class="flex-1 text-xs font-mono bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-slate-800 dark:text-slate-200 select-all">
                        <button type="button" @click="copyUrl('{{ $lanWebhookUrl ?? $webhookUrl }}')"
                                class="px-3 py-2 rounded-lg text-xs font-bold bg-indigo-600 text-white hover:bg-indigo-500 transition-colors flex items-center gap-1.5 flex-shrink-0 shadow-xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            <span x-text="copied ? '{{ __('Copied!') }}' : '{{ __('Copy') }}'"></span>
                        </button>
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1.5">
                        {{ __('Use this directly inside your Hikvision Terminal web page (HTTP Listening) or local HikCentral.') }}
                    </p>
                </div>

                {{-- Cloud / Public Webhook --}}
                <div class="p-3.5 bg-white/80 dark:bg-slate-800/80 rounded-xl border border-slate-200 dark:border-slate-700 shadow-2xs">
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                            <span>🌐</span>
                            <span>{{ __('Cloud / Public Webhook URL') }}</span>
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                                {{ __('Internet / Ngrok') }}
                            </span>
                        </label>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="text" readonly value="{{ $cloudWebhookUrl ?? $webhookUrl }}"
                               class="flex-1 text-xs font-mono bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-slate-800 dark:text-slate-200 select-all">
                        <button type="button" @click="copyUrl('{{ $cloudWebhookUrl ?? $webhookUrl }}')"
                                class="px-3 py-2 rounded-lg text-xs font-bold bg-slate-700 text-white hover:bg-slate-600 transition-colors flex items-center gap-1.5 flex-shrink-0 shadow-xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            <span x-text="copied ? '{{ __('Copied!') }}' : '{{ __('Copy') }}'"></span>
                        </button>
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1.5">
                        {{ __('Use when HikCentral or Attendance server is in another branch or cloud over internet.') }}
                    </p>
                </div>
            </div>

            {{-- Terminal Setup Guide --}}
            <div class="mt-3.5 grid grid-cols-1 md:grid-cols-2 gap-3 text-xs bg-white/70 dark:bg-slate-800/50 p-3.5 rounded-xl border border-slate-200/60 dark:border-slate-700/60">
                <div>
                    <p class="font-bold text-slate-800 dark:text-slate-200 mb-1">
                        📷 {{ __('Method 1: Direct Hikvision Face Terminal (LAN):') }}
                    </p>
                    <ol class="list-decimal list-inside space-y-0.5 text-slate-600 dark:text-slate-400">
                        <li>{{ __('Open browser:') }} <a href="http://{{ $devIp }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 underline font-mono">http://{{ $devIp }}</a></li>
                        <li>{{ __('Go to Configuration > Network > Advanced > HTTP Listening (Alarm Host)') }}</li>
                        <li>{{ __('Set Destination IP:') }} <span class="font-mono font-bold">{{ $srvIp }}</span>, {{ __('Port:') }} <span class="font-mono font-bold">{{ request()->getPort() ?: 80 }}</span></li>
                        <li>{{ __('Set URL Path:') }} <span class="font-mono font-bold">/api/hikcentral/event</span></li>
                    </ol>
                </div>
                <div>
                    <p class="font-bold text-slate-800 dark:text-slate-200 mb-1">
                        🏢 {{ __('Method 2: HikCentral OpenAPI Subscription:') }}
                    </p>
                    <ol class="list-decimal list-inside space-y-0.5 text-slate-600 dark:text-slate-400">
                        <li>{{ __('Open OpenAPI Manager or /artemis-web') }}</li>
                        <li>{{ __('Go to Event Subscription > Access Control Events') }}</li>
                        <li>{{ __('Enable "Face Authentication Passed" event') }}</li>
                        <li>{{ __('Paste the LAN or Cloud Webhook URL above') }}</li>
                    </ol>
                </div>
            </div>
        </div>

        {{-- Live Connection Test Result Banner --}}
        <div x-show="testResult !== null" x-transition class="mb-6">
            <div :class="testResult && testResult.success ? 'bg-emerald-50 dark:bg-emerald-950/40 border-emerald-300 dark:border-emerald-800 text-emerald-900 dark:text-emerald-100' : 'bg-rose-50 dark:bg-rose-950/40 border-rose-300 dark:border-rose-800 text-rose-900 dark:text-rose-100'"
                 class="border rounded-2xl p-4 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-3">
                <div class="flex items-start gap-3">
                    <span class="text-2xl" x-text="testResult && testResult.success ? '✅' : '⚠️'"></span>
                    <div>
                        <h4 class="text-sm font-black" x-text="testResult && testResult.message"></h4>
                        <template x-if="testResult && testResult.success">
                            <div class="flex flex-wrap items-center gap-2 mt-1.5 text-xs">
                                <span class="px-2 py-0.5 rounded bg-emerald-200/80 dark:bg-emerald-900/60 font-semibold">
                                    ⏱️ Latency: <strong x-text="testResult.latency_ms + 'ms'"></strong>
                                </span>
                                <span class="px-2 py-0.5 rounded bg-emerald-200/80 dark:bg-emerald-900/60 font-semibold" x-text="'HTTP 80: ' + (testResult.port_80 ? 'Open' : 'Closed')"></span>
                                <span class="px-2 py-0.5 rounded bg-emerald-200/80 dark:bg-emerald-900/60 font-semibold" x-text="'HTTPS 443: ' + (testResult.port_443 ? 'Open' : 'Closed')"></span>
                                <span class="px-2 py-0.5 rounded bg-emerald-200/80 dark:bg-emerald-900/60 font-semibold" x-text="'DNS: ' + (testResult.dns_online ? 'Active' : 'Offline')"></span>
                            </div>
                        </template>
                    </div>
                </div>
                <div class="flex items-center gap-2 self-end md:self-center">
                    <button type="button" @click="testConnection()" :disabled="testingConnection"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 transition shadow-2xs">
                        {{ __('Retest') }}
                    </button>
                    <button type="button" @click="testResult = null"
                            class="p-1.5 rounded-xl text-slate-500 hover:bg-slate-200/60 dark:hover:bg-slate-800 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Top KPI Cards --}}
        <div id="attendance-kpi-cards" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5 mb-6">
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

            {{-- Card 3: 1st Shift Check-ins --}}
            <div class="dash-card bg-white dark:bg-[#131b2e] border border-slate-200/80 dark:border-slate-800/80 shadow-xs p-4 rounded-2xl flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg flex-shrink-0">
                    ☀️
                </div>
                <div class="min-w-0">
                    <p class="text-xl font-black text-amber-600 dark:text-amber-400 leading-none">{{ $stats['today_1st_shift'] }}</p>
                    <p class="text-[11px] font-semibold text-slate-400 mt-1 truncate">{{ __('1st Shift (9AM-6PM)') }}</p>
                </div>
            </div>

            {{-- Card 4: 2nd Shift Check-ins --}}
            <div class="dash-card bg-white dark:bg-[#131b2e] border border-slate-200/80 dark:border-slate-800/80 shadow-xs p-4 rounded-2xl flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg flex-shrink-0">
                    🌤️
                </div>
                <div class="min-w-0">
                    <p class="text-xl font-black text-indigo-600 dark:text-indigo-400 leading-none">{{ $stats['today_2nd_shift'] }}</p>
                    <p class="text-[11px] font-semibold text-slate-400 mt-1 truncate">{{ __('2nd Shift (2PM-10PM)') }}</p>
                </div>
            </div>

            {{-- Card 5: Latest Face Entry Time --}}
            <div class="dash-card bg-white dark:bg-[#131b2e] border border-indigo-200/80 dark:border-indigo-800/80 shadow-xs p-4 rounded-2xl flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg flex-shrink-0 animate-pulse">
                    ⏱️
                </div>
                <div class="min-w-0">
                    <p class="text-base sm:text-lg font-black font-mono text-indigo-600 dark:text-indigo-400 leading-none truncate">
                        {{ $stats['latest_punch_time'] ?? __('No punch yet') }}
                    </p>
                    <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 mt-1 truncate" title="{{ $stats['latest_person_name'] ?? '' }}">
                        {{ $stats['latest_person_name'] ? $stats['latest_person_name'] . ' (' . ($stats['latest_punch_ago'] ?? '') . ')' : __('Latest Face Entry') }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Filter Bar --}}
        <form method="GET" action="{{ route('attendance.index') }}" data-auto-filter
              class="bg-white dark:bg-[#131b2e] border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-4 mb-6 shadow-xs flex flex-wrap items-center gap-3">
            
            {{-- Quick Date Presets (Today & Last 7 Days strictly side-by-side) --}}
            <div class="flex items-center gap-2.5 flex-nowrap overflow-x-auto w-full pt-1 pb-[10px] mb-3.5 border-b border-slate-200/70 dark:border-slate-800/80"
                 style="display: flex !important; flex-direction: row !important; flex-wrap: nowrap !important; align-items: center !important; padding-bottom: 10px !important;">
                <span class="text-xs font-bold text-slate-400 dark:text-slate-500 flex items-center gap-1 shrink-0 mr-1"
                      style="flex-shrink: 0 !important;">
                    <span>⚡</span>
                    <span>{{ __('Range:') }}</span>
                </span>
                {{-- 1. Today First --}}
                <a href="{{ route('attendance.index', ['date' => 'today']) }}"
                   class="no-box-style shrink-0 inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer shadow-2xs whitespace-nowrap {{ $filterDate === 'today' || $filterDate === $today->toDateString() ? 'bg-indigo-600 text-white ring-2 ring-indigo-500/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 border border-slate-200/60 dark:border-slate-700/60' }}"
                   style="display: inline-flex !important; width: auto !important; max-width: max-content !important; flex-shrink: 0 !important; white-space: nowrap !important;">
                    <span>📅</span>
                    <span>{{ __('Today') }} ({{ $today->format('d M') }})</span>
                </a>
                {{-- 2. Last 7 Days Second --}}
                <a href="{{ route('attendance.index', ['date' => '7days']) }}"
                   class="no-box-style shrink-0 inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer shadow-2xs whitespace-nowrap {{ $filterDate === '7days' || $filterDate === 'all' ? 'bg-indigo-600 text-white ring-2 ring-indigo-500/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 border border-slate-200/60 dark:border-slate-700/60' }}"
                   style="display: inline-flex !important; width: auto !important; max-width: max-content !important; flex-shrink: 0 !important; white-space: nowrap !important;">
                    <span>⚡</span>
                    <span>{{ __('Last 7 Days') }} ({{ number_format($totalAllLogs ?? 0) }})</span>
                </a>
                @if(!empty($latestLogDate) && $latestLogDate !== $today->toDateString())
                    <a href="{{ route('attendance.index', ['date' => $latestLogDate]) }}"
                       class="no-box-style shrink-0 inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer shadow-2xs whitespace-nowrap {{ $filterDate === $latestLogDate ? 'bg-emerald-600 text-white ring-2 ring-emerald-500/30' : 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/80 hover:bg-emerald-100 dark:hover:bg-emerald-900/60' }}">
                        <span>📌</span>
                        <span>{{ __('Latest Punch Day') }} ({{ \Carbon\Carbon::parse($latestLogDate)->format('d M, Y') }})</span>
                    </a>
                @endif
            </div>

            {{-- Date Filter (Restricted to Last 7 Days Window) --}}
            <div class="flex items-center gap-2">
                <label class="text-xs font-bold text-slate-500 dark:text-slate-400">{{ __('Date:') }}</label>
                <input type="date" name="date" min="{{ $last7StartDate }}" max="{{ $last7EndDate }}"
                       value="{{ !in_array($filterDate, ['7days', 'all', 'today']) ? $filterDate : '' }}"
                       class="border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-1.5 text-xs text-slate-800 dark:text-slate-200 bg-slate-50 dark:bg-slate-800 focus:ring-2 focus:ring-indigo-500"
                       title="{{ __('Only dates within the last 7 days are valid') }}">
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
                    <option value="1st_shift" {{ in_array(request('shift'), ['1st_shift', 'day_shift']) ? 'selected' : '' }}>☀️ {{ __('1st Shift (9:00 AM - 6:00 PM)') }}</option>
                    <option value="2nd_shift" {{ in_array(request('shift'), ['2nd_shift', 'night_shift']) ? 'selected' : '' }}>🌤️ {{ __('2nd Shift (2:00 PM - 10:00 PM)') }}</option>
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

        {{-- Daily Staff Summary: 1 Entry & Last Out --}}
        @if(isset($staffSummaries) && $staffSummaries->isNotEmpty())
            <div id="attendance-summary-card" class="dash-card bg-white dark:bg-[#131b2e] border border-slate-200/80 dark:border-slate-800/80 rounded-2xl shadow-xs overflow-hidden mb-8 mt-2">
                <div class="px-6 sm:px-8 py-6 sm:py-7 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between flex-wrap gap-2 bg-indigo-50/30 dark:bg-indigo-950/20"
                     style="padding-top: 24px !important; padding-bottom: 24px !important; padding-left: 28px !important; padding-right: 28px !important;">
                    <div>
                        <h2 class="text-sm sm:text-base font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
                            <span>👥</span>
                            <span>{{ __('Daily Staff Attendance (1 Entry & Last Out)') }}</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-indigo-100 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300">
                                {{ $staffSummaries->count() }} {{ __('Staff Active') }}
                            </span>
                        </h2>
                        <p class="text-[11px] sm:text-xs text-slate-400 mt-1">
                            @if($filterDate === '7days')
                                {{ __('First In (Entry) & Last Out (Exit) summary for Last 7 Days (:start - :end)', [
                                    'start' => !empty($last7StartDate) ? \Carbon\Carbon::parse($last7StartDate)->format('d M') : '',
                                    'end' => !empty($last7EndDate) ? \Carbon\Carbon::parse($last7EndDate)->format('d M, Y') : ''
                                ]) }}
                            @elseif($filterDate === 'all')
                                {{ __('First In (Entry) & Last Out (Exit) summary for :date', ['date' => !empty($statsDate) ? \Carbon\Carbon::parse($statsDate)->format('d M, Y') : __('Selected Date')]) }}
                            @elseif($filterDate === 'today')
                                {{ __('First In (Entry) & Last Out (Exit) count for Today (:date)', ['date' => $today->format('d M, Y')]) }}
                            @else
                                {{ __('First In (Entry) & Last Out (Exit) count for :date', ['date' => ($filterDate && !in_array($filterDate, ['all', '7days', 'today'])) ? \Carbon\Carbon::parse($filterDate)->format('d M, Y') : __('Selected Date')]) }}
                            @endif
                        </p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                <th class="py-3.5 px-6">{{ __('Staff Member / ID') }}</th>
                                <th class="py-3.5 px-6">{{ __('Shift') }}</th>
                                <th class="py-3.5 px-6 text-emerald-700 dark:text-emerald-400 font-extrabold">📥 {{ __('1st Entry (In)') }}</th>
                                <th class="py-3.5 px-6 text-rose-700 dark:text-rose-400 font-extrabold">📤 {{ __('Last Exit (Out)') }}</th>
                                <th class="py-3.5 px-6 text-center">{{ __('Total Scans') }}</th>
                                <th class="py-3.5 px-6">{{ __('Duty Status') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/70">
                            @foreach($staffSummaries as $summary)
                                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                                    {{-- Staff --}}
                                    <td class="py-3.5 px-6">
                                        <div class="flex items-center gap-2.5">
                                            @if($summary['user'])
                                                <img src="{{ $summary['user']->avatarUrl() }}" alt="{{ $summary['user']->name }}"
                                                     class="w-8 h-8 rounded-full object-cover border border-slate-200 dark:border-slate-700 flex-shrink-0">
                                                <div>
                                                    <div class="font-extrabold text-slate-900 dark:text-white truncate">
                                                        {{ $summary['user']->name }}
                                                    </div>
                                                    <div class="text-[10px] text-slate-400 font-mono">
                                                        ID: {{ $summary['user']->office_id ?: $summary['user']->id }}
                                                        @if($summary['user']->team) • {{ $summary['user']->team }} @endif
                                                    </div>
                                                </div>
                                            @else
                                                <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 font-bold flex items-center justify-center text-xs flex-shrink-0">
                                                    {{ strtoupper(substr($summary['person_name'] ?? 'ID', 0, 2)) }}
                                                </div>
                                                <div>
                                                    <div class="font-extrabold text-slate-900 dark:text-white truncate">
                                                        {{ $summary['person_name'] ?: ('Employee ID: ' . $summary['employee_no']) }}
                                                    </div>
                                                    <div class="text-[10px] text-slate-400 font-mono flex items-center gap-1">
                                                        <span>ID: {{ $summary['employee_no'] }}</span>
                                                        <span class="px-1.5 py-0.2 rounded text-[9px] font-semibold bg-amber-100 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300">
                                                            {{ __('Device ID') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Shift --}}
                                    <td class="py-3.5 px-6 whitespace-nowrap">
                                        @if(in_array($summary['shift'], ['1st_shift', 'day_shift']))
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200/70 dark:border-amber-800/50">
                                                <span>☀️</span>
                                                <span>{{ __('1st Shift (9:00 AM - 6:00 PM)') }}</span>
                                            </span>
                                        @elseif(in_array($summary['shift'], ['2nd_shift', 'night_shift']))
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200/70 dark:border-indigo-800/50">
                                                <span>🌤️</span>
                                                <span>{{ __('2nd Shift (2:00 PM - 10:00 PM)') }}</span>
                                            </span>
                                        @else
                                            <span class="text-slate-400 text-xs">{{ __('N/A') }}</span>
                                        @endif
                                    </td>

                                    {{-- 1st Entry (In) --}}
                                    <td class="py-3.5 px-6 whitespace-nowrap">
                                        @if($summary['first_in'])
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 font-mono font-bold text-xs border border-emerald-200/60 dark:border-emerald-800/50">
                                                <span>📥</span>
                                                <span>{{ $summary['first_in']->timezone(config('app.timezone', 'Asia/Dhaka'))->format('h:i:s A') }}</span>
                                            </span>
                                        @else
                                            <span class="text-slate-400">--</span>
                                        @endif
                                    </td>

                                    {{-- Last Exit (Out) --}}
                                    <td class="py-3.5 px-6 whitespace-nowrap">
                                        @if($summary['last_out'])
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 font-mono font-bold text-xs border border-rose-200/60 dark:border-rose-800/50">
                                                <span>📤</span>
                                                <span>{{ $summary['last_out']->timezone(config('app.timezone', 'Asia/Dhaka'))->format('h:i:s A') }}</span>
                                            </span>
                                        @elseif($summary['is_duty_complete'] || $summary['is_out'])
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 font-bold text-xs border border-blue-200/60">
                                                <span>✓</span>
                                                <span>{{ __('Duty Complete') }}</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400 text-xs font-semibold">
                                                <span>⏳</span>
                                                <span>{{ __('Active / Still Working') }}</span>
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Total Scans --}}
                                    <td class="py-3.5 px-6 text-center whitespace-nowrap">
                                        <span class="font-mono font-extrabold text-xs px-2.5 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                            {{ $summary['total_scans'] }}
                                        </span>
                                    </td>

                                    {{-- Status --}}
                                    <td class="py-3.5 px-6 whitespace-nowrap">
                                        @if($summary['is_duty_complete'] || $summary['is_out'])
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200/60 dark:border-blue-800/50">
                                                <span>✓</span>
                                                {{ __('Duty Complete') }}
                                            </span>
                                        @elseif($summary['is_on_duty'])
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/50">
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
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Log Records Table Card (Collapsible - Initially Closed/Off with generous padding) --}}
        <div id="attendance-table-card" class="dash-card bg-white dark:bg-[#131b2e] border border-slate-200/80 dark:border-slate-800/80 rounded-2xl shadow-xs overflow-hidden transition-all duration-300 mb-8 mt-2">
            {{-- Clickable Header Toggle --}}
            <div @click="openAttendanceTable = !openAttendanceTable"
                 class="px-6 sm:px-8 lg:px-10 py-6 sm:py-7 border-b border-slate-100 dark:border-slate-800/80 flex items-center justify-between cursor-pointer select-none hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors group"
                 style="padding-top: 24px !important; padding-bottom: 24px !important; padding-left: 28px !important; padding-right: 28px !important;"
                 title="{{ __('Click to toggle attendance records table') }}">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-2xl flex-shrink-0 group-hover:scale-105 transition-transform shadow-2xs">
                        📷
                    </div>
                    <div>
                        <div class="flex items-center gap-2.5 flex-wrap">
                            <h2 class="text-base sm:text-lg font-black text-slate-800 dark:text-white">
                                @if($filterDate === '7days')
                                    {{ __('Attendance Records — Last 7 Days') }} ({{ !empty($last7StartDate) ? \Carbon\Carbon::parse($last7StartDate)->format('d M') : '' }} - {{ !empty($last7EndDate) ? \Carbon\Carbon::parse($last7EndDate)->format('d M, Y') : '' }})
                                @elseif($filterDate === 'all')
                                    {{ __('All Attendance Events (Historical & Live)') }}
                                @elseif($filterDate === $today->toDateString() || $filterDate === 'today')
                                    {{ __('Attendance Records for Today') }} ({{ $today->format('d M, Y') }})
                                @else
                                    {{ __('Attendance Records for') }} {{ ($filterDate && !in_array($filterDate, ['all', '7days', 'today'])) ? \Carbon\Carbon::parse($filterDate)->format('d M, Y') : '' }}
                                @endif
                            </h2>
                            @if($filterDate === '7days')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-indigo-100 text-indigo-800 dark:bg-indigo-950/60 dark:text-indigo-300">
                                    ⚡ {{ __('Last 7 Days') }}
                                </span>
                            @elseif($filterDate === 'all')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-indigo-100 text-indigo-800 dark:bg-indigo-950/60 dark:text-indigo-300">
                                    {{ __('All Dates') }}
                                </span>
                            @elseif($filterDate === $today->toDateString() || $filterDate === 'today')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                    {{ __('Today') }}
                                </span>
                            @elseif(!empty($latestLogDate) && $filterDate === $latestLogDate)
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                    {{ __('Latest Active Date') }}
                                </span>
                            @endif
                        </div>
                        <p class="text-[11px] sm:text-xs text-slate-400 mt-1 flex items-center gap-2 flex-wrap">
                            <span>{{ __('Showing biometric check-in stream') }} (<strong id="attendance-records-count" class="font-bold text-slate-600 dark:text-slate-300">{{ $logs->total() }}</strong> {{ __('records') }})</span>
                            <span class="text-slate-300 dark:text-slate-600">•</span>
                            <span class="font-bold text-indigo-600 dark:text-indigo-400" x-text="openAttendanceTable ? '{{ __('Click to collapse table') }}' : '{{ __('Click to open full records table') }}'"></span>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all shadow-2xs"
                          :class="openAttendanceTable ? 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-800/60' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200/60 dark:border-slate-700/60'">
                        <span x-text="openAttendanceTable ? '📂 {{ __('Open') }}' : '📁 {{ __('Closed') }}'"></span>
                    </span>
                    <button type="button" class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 flex items-center justify-center transition-transform duration-200 shadow-2xs"
                            :class="openAttendanceTable ? 'rotate-180 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400' : ''">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                </div>
            </div>

            {{-- Collapsible Body Container (Initially Off/Closed) --}}
            <div x-show="openAttendanceTable" x-collapse x-cloak id="attendance-records-body">
                @if($logs->isEmpty())
                    <div class="py-16 text-center px-6 sm:px-8">
                        <div class="w-14 h-14 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center text-2xl mx-auto mb-3">
                            📷
                        </div>
                        <p class="text-sm font-bold text-slate-700 dark:text-slate-300">
                            @if($filterDate === '7days')
                                {{ __('No attendance events found in the last 7 days.') }}
                            @elseif($filterDate === 'all')
                                {{ __('No attendance events found matching your filter criteria.') }}
                            @else
                                {{ __('No attendance events for :date', ['date' => ($filterDate && !in_array($filterDate, ['all', '7days', 'today'])) ? \Carbon\Carbon::parse($filterDate)->format('d M, Y') : $today->format('d M, Y')]) }}
                            @endif
                        </p>
                        <p class="text-xs text-slate-400 mt-1 max-w-md mx-auto">
                            @if(!empty($totalAllLogs) && $totalAllLogs > 0 && !empty($latestLogDate))
                                {{ __('There are no records for this date, but :total records exist in the system (Latest recorded on :latest).', [
                                    'total' => number_format($totalAllLogs),
                                    'latest' => \Carbon\Carbon::parse($latestLogDate)->format('d M, Y')
                                ]) }}
                            @else
                                {{ __('When staff scan their faces at the HikCentral terminal, access events will appear here.') }}
                            @endif
                        </p>

                        @if(!empty($totalAllLogs) && $totalAllLogs > 0 && !empty($latestLogDate))
                            <div class="mt-4 flex items-center justify-center gap-2.5 flex-wrap">
                                @if($filterDate !== $latestLogDate)
                                    <a href="{{ route('attendance.index', ['date' => $latestLogDate]) }}"
                                       class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold transition-all shadow-xs inline-flex items-center gap-1.5 cursor-pointer">
                                        <span>⚡</span>
                                        <span>{{ __('View Latest Records (:date)', ['date' => \Carbon\Carbon::parse($latestLogDate)->format('d M, Y')]) }}</span>
                                    </a>
                                @endif
                                <a href="{{ route('attendance.index', ['date' => 'all']) }}"
                                   class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-bold transition-all inline-flex items-center gap-1.5 cursor-pointer">
                                    <span>🌐</span>
                                    <span>{{ __('View All :count Records', ['count' => number_format($totalAllLogs)]) }}</span>
                                </a>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                    <th class="py-3.5 px-6 min-w-[150px]">
                                        <div class="flex items-center gap-1.5 text-slate-700 dark:text-slate-200">
                                            <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span class="font-extrabold uppercase tracking-wide text-[11px]">{{ __('Face Entry Time') }}</span>
                                        </div>
                                    </th>
                                    <th class="py-3.5 px-6">{{ __('Staff Member / ID') }}</th>
                                    <th class="py-3.5 px-6">{{ __('Door / Terminal') }}</th>
                                    <th class="py-3.5 px-6">{{ __('Verification') }}</th>
                                    <th class="py-3.5 px-6">{{ __('Assigned Shift') }}</th>
                                    <th class="py-3.5 px-6">{{ __('Duty Status') }}</th>
                                    <th class="py-3.5 px-6 text-right">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/70">
                                @foreach($logs as $log)
                                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                                        {{-- Face Entry Time --}}
                                        <td class="py-3.5 px-6 whitespace-nowrap">
                                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-indigo-50/90 dark:bg-indigo-950/70 border border-indigo-200/80 dark:border-indigo-800/60 shadow-2xs">
                                                <svg class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400 flex-shrink-0 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span class="font-black font-mono text-sm tracking-tight text-indigo-950 dark:text-indigo-200">
                                                    {{ $log->event_time ? $log->event_time->timezone(config('app.timezone', 'Asia/Dhaka'))->format('h:i:s A') : 'N/A' }}
                                                </span>
                                            </div>
                                            <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-1.5 font-medium pl-1">
                                                <span>📅 {{ $log->event_time ? $log->event_time->timezone(config('app.timezone', 'Asia/Dhaka'))->format('d M, Y') : '' }}</span>
                                                <span class="text-slate-300 dark:text-slate-600">•</span>
                                                <span class="text-indigo-600 dark:text-indigo-400 font-bold">{{ $log->event_time ? $log->event_time->diffForHumans() : '' }}</span>
                                            </div>
                                        </td>

                                        {{-- Staff Member --}}
                                        <td class="py-3.5 px-6">
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
                                                            <span class="text-slate-300 dark:text-slate-600">•</span>
                                                            <span class="text-slate-600 dark:text-slate-300 font-semibold">Entry: {{ $log->event_time ? $log->event_time->format('h:i:s A') : '' }}</span>
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
                                        <td class="py-3.5 px-6 whitespace-nowrap">
                                            <div class="font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-1">
                                                 <span>🚪</span>
                                                 <span>{{ $log->door_name ?: __('Office Door') }}</span>
                                            </div>
                                            @if($log->device_name)
                                                <div class="text-[10px] text-slate-400">{{ $log->device_name }}</div>
                                            @endif
                                        </td>

                                        {{-- Verification Type --}}
                                        <td class="py-3.5 px-6 whitespace-nowrap">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200/70 dark:border-indigo-800/50">
                                                <span>👤</span>
                                                <span>{{ __('Face Verified') }}</span>
                                            </span>
                                            <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5 pl-1 font-mono">
                                                {{ $log->event_time ? $log->event_time->timezone(config('app.timezone', 'Asia/Dhaka'))->format('h:i:s A') : '' }}
                                            </div>
                                        </td>

                                        {{-- Shift --}}
                                        <td class="py-3.5 px-6 whitespace-nowrap">
                                            @if(in_array($log->shift_assigned, ['1st_shift', 'day_shift']))
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200/70 dark:border-amber-800/50">
                                                    <span>☀️</span>
                                                    <span>{{ __('1st Shift (9:00 AM - 6:00 PM)') }}</span>
                                                </span>
                                            @elseif(in_array($log->shift_assigned, ['2nd_shift', 'night_shift']))
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200/70 dark:border-indigo-800/50">
                                                    <span>🌤️</span>
                                                    <span>{{ __('2nd Shift (2:00 PM - 10:00 PM)') }}</span>
                                                </span>
                                            @else
                                                <span class="text-slate-400">{{ __('N/A') }}</span>
                                            @endif
                                        </td>

                                        {{-- Duty Status --}}
                                        <td class="py-3.5 px-6 whitespace-nowrap">
                                            @if($log->event_type === 'out' || ($log->user && $log->user->current_shift === 'off_duty'))
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200/60 dark:border-blue-800/50">
                                                <span>✓</span>
                                                {{ __('Duty Complete') }}
                                            </span>
                                        @elseif($log->user ? $log->user->isOnDuty() : true)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/50">
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

                                    {{-- Action Buttons (Clearly Clickable) --}}
                                    <td class="py-3.5 px-6 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1.5">
                                            @if($log->raw_data)
                                                <button type="button" @click.stop="selectedLogPayload = @js($log->raw_data)"
                                                        class="w-8 h-8 rounded-lg inline-flex items-center justify-center bg-slate-100 hover:bg-indigo-50 dark:bg-slate-800 dark:hover:bg-indigo-950/60 text-slate-500 hover:text-indigo-600 dark:text-slate-400 dark:hover:text-indigo-400 border border-slate-200/80 dark:border-slate-700/80 transition-all cursor-pointer shadow-2xs"
                                                        title="{{ __('View Payload') }}">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                                                </button>
                                            @endif

                                            @if($user->isAdmin())
                                                <form method="POST" action="{{ route('attendance.destroy', $log) }}"
                                                      class="inline"
                                                      onsubmit="return confirm('{{ __('Delete this attendance record?') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="w-8 h-8 rounded-lg inline-flex items-center justify-center bg-slate-100 hover:bg-rose-50 dark:bg-slate-800 dark:hover:bg-rose-950/60 text-slate-500 hover:text-rose-600 dark:text-slate-400 dark:hover:text-rose-400 border border-slate-200/80 dark:border-slate-700/80 transition-all cursor-pointer shadow-2xs"
                                                            title="{{ __('Delete Record') }}">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
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
                    <div class="px-6 sm:px-8 py-4 border-t border-slate-100 dark:border-slate-800">
                        {{ $logs->links() }}
                    </div>
                @endif
            @endif
        </div>
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

    {{-- Live Real-time Polling Script (Auto-Updates Table & KPI Cards without page reload) --}}
    <script>
    (function () {
        let isPolling = false;

        function pollAttendance() {
            if (isPolling) return;
            // Don't poll if browser tab is not active / hidden
            if (document.hidden) return;

            isPolling = true;

            fetch(window.location.href, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) throw new Error('Network error');
                return response.text();
            })
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                const newKpi = doc.getElementById('attendance-kpi-cards');
                const currentKpi = document.getElementById('attendance-kpi-cards');
                if (newKpi && currentKpi && newKpi.innerHTML.trim() !== currentKpi.innerHTML.trim()) {
                    currentKpi.innerHTML = newKpi.innerHTML;
                }

                const newSummary = doc.getElementById('attendance-summary-card');
                const currentSummary = document.getElementById('attendance-summary-card');
                if (newSummary && currentSummary && newSummary.innerHTML.trim() !== currentSummary.innerHTML.trim()) {
                    currentSummary.innerHTML = newSummary.innerHTML;
                } else if (newSummary && !currentSummary) {
                    const tableCard = document.getElementById('attendance-table-card');
                    if (tableCard) {
                        tableCard.parentNode.insertBefore(newSummary, tableCard);
                    }
                }

                // Update records count badge in header
                const newCount = doc.getElementById('attendance-records-count');
                const currentCount = document.getElementById('attendance-records-count');
                if (newCount && currentCount && newCount.innerHTML.trim() !== currentCount.innerHTML.trim()) {
                    currentCount.innerHTML = newCount.innerHTML;
                }

                // Update inner collapsible table body without destroying accordion state
                const newBody = doc.getElementById('attendance-records-body');
                const currentBody = document.getElementById('attendance-records-body');
                if (newBody && currentBody && newBody.innerHTML.trim() !== currentBody.innerHTML.trim()) {
                    currentBody.innerHTML = newBody.innerHTML;
                    if (window.Alpine) {
                        window.Alpine.initTree(currentBody);
                    }
                }
            })
            .catch(() => {})
            .finally(() => {
                isPolling = false;
            });
        }

        // Auto poll every 4 seconds so punches appear in real-time without reloading
        setInterval(pollAttendance, 4000);
    })();
    </script>
</x-app-layout>
