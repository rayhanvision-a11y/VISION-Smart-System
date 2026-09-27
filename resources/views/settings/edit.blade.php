<x-app-layout>
    <div class="w-full space-y-4 max-w-7xl mx-auto" x-data="{ 
        activeTab: new URLSearchParams(window.location.search).get('tab') || 'notice',
        showDataHub: true,
        setTab(tab) {
            this.activeTab = tab;
            const url = new URL(window.location);
            url.searchParams.set('tab', tab);
            window.history.replaceState({}, '', url);
        }
    }">
        {{-- Compact Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-3 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-100 dark:border-indigo-800/60 flex items-center justify-center text-indigo-600 dark:text-indigo-400 shrink-0">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-base font-bold text-slate-900 dark:text-white tracking-tight leading-tight">{{ __('System & Brand Settings') }}</h1>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Company branding, header ticker notices, master data lists, and database backups.') }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                @if(auth()->user()->isSuperAdminOnly())
                <a href="{{ route('settings.backup.export') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 text-xs font-semibold transition-colors shadow-xs">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    <span>{{ __('Export JSON') }}</span>
                </a>
                @endif
                <button type="button" @click="showDataHub = !showDataHub"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 text-xs font-semibold transition-colors shadow-xs"
                        :title="showDataHub ? 'Hide Data Hub' : 'Show Data Hub'">
                    <span>🗂️</span>
                    <span x-text="showDataHub ? '{{ __('Hide Data Hub') }}' : '{{ __('Show Data Hub') }}'"></span>
                </button>
            </div>
        </div>

        {{-- 🗂️ Data Management Hub — Sleek Compact Toolbar Grid --}}
        <div x-show="showDataHub" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
             class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xs p-2.5">
            <div class="flex items-center justify-between mb-1.5 px-0.5">
                <span class="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                    <span>🗂️</span> {{ __('Data Management Quick Access') }}
                </span>
                <span class="text-[10px] text-slate-400">{{ __('Master tables & lookup records') }}</span>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(100px, 1fr)); gap: 0.375rem;">
                <a href="{{ route('ticket-categories.index') }}"
                   class="group flex flex-col p-1.5 rounded-lg border border-indigo-100 dark:border-indigo-950/80 bg-indigo-50/50 dark:bg-indigo-950/20 hover:bg-indigo-100/70 dark:hover:bg-indigo-950/50 transition-colors text-center">
                    <span class="text-xs">🏷️</span>
                    <span class="text-[11px] font-bold text-slate-800 dark:text-slate-200 group-hover:text-indigo-600 truncate mt-0.5">{{ __('Categories') }}</span>
                    <span class="text-[9px] text-slate-400 truncate">{{ \App\Models\TicketCategory::count() }}</span>
                </a>

                <a href="{{ route('areas.index') }}"
                   class="group flex flex-col p-1.5 rounded-lg border border-emerald-100 dark:border-emerald-950/80 bg-emerald-50/50 dark:bg-emerald-950/20 hover:bg-emerald-100/70 dark:hover:bg-emerald-950/50 transition-colors text-center">
                    <span class="text-xs">📍</span>
                    <span class="text-[11px] font-bold text-slate-800 dark:text-slate-200 group-hover:text-emerald-600 truncate mt-0.5">{{ __('Areas') }}</span>
                    <span class="text-[9px] text-slate-400 truncate">@if(\Schema::hasTable('areas')){{ \App\Models\Area::count() }}@else 0 @endif</span>
                </a>

                <a href="{{ route('labels.index') }}"
                   class="group flex flex-col p-1.5 rounded-lg border border-amber-100 dark:border-amber-950/80 bg-amber-50/50 dark:bg-amber-950/20 hover:bg-amber-100/70 dark:hover:bg-amber-950/50 transition-colors text-center">
                    <span class="text-xs">🔖</span>
                    <span class="text-[11px] font-bold text-slate-800 dark:text-slate-200 group-hover:text-amber-600 truncate mt-0.5">{{ __('Labels') }}</span>
                    <span class="text-[9px] text-slate-400 truncate">{{ \App\Models\Label::count() }}</span>
                </a>

                <a href="{{ route('pop-offices.index') }}"
                   class="group flex flex-col p-1.5 rounded-lg border border-sky-100 dark:border-sky-950/80 bg-sky-50/50 dark:bg-sky-950/20 hover:bg-sky-100/70 dark:hover:bg-sky-950/50 transition-colors text-center">
                    <span class="text-xs">🏢</span>
                    <span class="text-[11px] font-bold text-slate-800 dark:text-slate-200 group-hover:text-sky-600 truncate mt-0.5">{{ __('POPs') }}</span>
                    <span class="text-[9px] text-slate-400 truncate">{{ \App\Models\PopOffice::count() }}</span>
                </a>

                <a href="{{ route('sla-policies.index') }}"
                   class="group flex flex-col p-1.5 rounded-lg border border-rose-100 dark:border-rose-950/80 bg-rose-50/50 dark:bg-rose-950/20 hover:bg-rose-100/70 dark:hover:bg-rose-950/50 transition-colors text-center">
                    <span class="text-xs">⏱️</span>
                    <span class="text-[11px] font-bold text-slate-800 dark:text-slate-200 group-hover:text-rose-600 truncate mt-0.5">{{ __('SLAs') }}</span>
                    <span class="text-[9px] text-slate-400 truncate">{{ \App\Models\SlaPolicy::count() }}</span>
                </a>

                <a href="{{ route('kb-categories.index') }}"
                   class="group flex flex-col p-1.5 rounded-lg border border-purple-100 dark:border-purple-950/80 bg-purple-50/50 dark:bg-purple-950/20 hover:bg-purple-100/70 dark:hover:bg-purple-950/50 transition-colors text-center">
                    <span class="text-xs">📚</span>
                    <span class="text-[11px] font-bold text-slate-800 dark:text-slate-200 group-hover:text-purple-600 truncate mt-0.5">{{ __('KB Cat') }}</span>
                    <span class="text-[9px] text-slate-400 truncate">{{ __('Articles') }}</span>
                </a>

                <a href="{{ route('users.index') }}"
                   class="group flex flex-col p-1.5 rounded-lg border border-cyan-100 dark:border-cyan-950/80 bg-cyan-50/50 dark:bg-cyan-950/20 hover:bg-cyan-100/70 dark:hover:bg-cyan-950/50 transition-colors text-center">
                    <span class="text-xs">👥</span>
                    <span class="text-[11px] font-bold text-slate-800 dark:text-slate-200 group-hover:text-cyan-600 truncate mt-0.5">{{ __('Users') }}</span>
                    <span class="text-[9px] text-slate-400 truncate">{{ \App\Models\User::count() }}</span>
                </a>

                <a href="{{ route('teams.index') }}"
                   class="group flex flex-col p-1.5 rounded-lg border border-teal-100 dark:border-teal-950/80 bg-teal-50/50 dark:bg-teal-950/20 hover:bg-teal-100/70 dark:hover:bg-teal-950/50 transition-colors text-center">
                    <span class="text-xs">🏷️</span>
                    <span class="text-[11px] font-bold text-slate-800 dark:text-slate-200 group-hover:text-teal-600 truncate mt-0.5">{{ __('Teams') }}</span>
                    <span class="text-[9px] text-slate-400 truncate">@if(\Schema::hasTable('teams')){{ \App\Models\Team::count() }}@else 0 @endif</span>
                </a>

                <a href="{{ route('canned-responses.index') }}"
                   class="group flex flex-col p-1.5 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors text-center">
                    <span class="text-xs">💬</span>
                    <span class="text-[11px] font-bold text-slate-800 dark:text-slate-200 group-hover:text-slate-900 truncate mt-0.5">{{ __('Canned') }}</span>
                    <span class="text-[9px] text-slate-400 truncate">{{ __('Replies') }}</span>
                </a>
            </div>
        </div>

        {{-- Alerts --}}
        @if(session('status'))
            <div class="px-3.5 py-2.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-semibold flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            </div>
        @endif

        @if(session('success'))
            <div class="px-3.5 py-2.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-semibold flex items-center gap-2 shadow-xs">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="px-3.5 py-2.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-xs font-semibold flex items-center gap-2 shadow-xs">
                <svg class="w-4 h-4 text-rose-600 dark:text-rose-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        {{-- Compact Tab Navigation Bar --}}
        <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 rounded-xl p-1 shadow-xs flex items-center gap-1 overflow-x-auto scrollbar-none">
            {{-- Notice Ticker Tab --}}
            <button type="button" @click="setTab('notice')"
                    :class="activeTab === 'notice' 
                        ? 'bg-indigo-600 text-white shadow-xs font-bold' 
                        : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 font-semibold'"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs whitespace-nowrap transition-all cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                </svg>
                <span>{{ __('Header Notice Ticker') }}</span>
                @php
                    $anyNoticeActive = (($noticeMasterActive ?? '1') == '1') && ((($noticeActive ?? '0') == '1') || (($noticeResellerActive ?? '0') == '1') || (($noticeNocActive ?? '0') == '1'));
                @endphp
                @if($anyNoticeActive)
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                @endif
            </button>

            {{-- Branding & Logo Tab --}}
            <button type="button" @click="setTab('branding')"
                    :class="activeTab === 'branding' 
                        ? 'bg-indigo-600 text-white shadow-xs font-bold' 
                        : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 font-semibold'"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs whitespace-nowrap transition-all cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>{{ __('Logo & Favicon') }}</span>
            </button>

            {{-- Theme & Appearance Tab (Super Admin) --}}
            @if(auth()->user()->isSuperAdminOnly())
            <button type="button" @click="setTab('theme')"
                    :class="activeTab === 'theme' 
                        ? 'bg-indigo-600 text-white shadow-xs font-bold' 
                        : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 font-semibold'"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs whitespace-nowrap transition-all cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4 4 4 0 014-4c.48 0 .935.085 1.356.241A6.974 6.974 0 019 11a7 7 0 017-7c1.378 0 2.652.4 3.732 1.085A4 4 0 0121 9a4 4 0 01-4 4c-.48 0-.935-.085-1.356-.241A6.974 6.974 0 0115 15a7 7 0 01-7 7z"/>
                </svg>
                <span>{{ __('Colors & Theme') }}</span>
            </button>
            @endif

            {{-- Ticket Categories Tab --}}
            <button type="button" @click="setTab('categories')"
                    :class="activeTab === 'categories' 
                        ? 'bg-indigo-600 text-white shadow-xs font-bold' 
                        : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 font-semibold'"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs whitespace-nowrap transition-all cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                <span>{{ __('Ticket Categories') }}</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300"
                      :class="activeTab === 'categories' ? 'bg-white/25 text-white' : ''">
                    {{ count($categories ?? []) }}
                </span>
            </button>

            {{-- Database Backup Tab (Admin/Super Admin) --}}
            @if(auth()->user()->isAdmin())
            <button type="button" @click="setTab('backup')"
                    :class="activeTab === 'backup' 
                        ? 'bg-indigo-600 text-white shadow-xs font-bold' 
                        : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 font-semibold'"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs whitespace-nowrap transition-all cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                </svg>
                <span>{{ __('Database Backups') }}</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300"
                      :class="activeTab === 'backup' ? 'bg-white/25 text-white' : ''">
                    {{ count($backups ?? []) }}
                </span>
            </button>
            @endif

            {{-- Google Sheet Sync Tab (Admin) --}}
            @if(auth()->user()->isAdmin())
            <button type="button" @click="setTab('googlesheet')"
                    :class="activeTab === 'googlesheet' 
                        ? 'bg-emerald-600 text-white shadow-xs font-bold' 
                        : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 font-semibold'"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs whitespace-nowrap transition-all cursor-pointer">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 14H6v-2h6v2zm0-4H6v-2h6v2zm0-4H6V7h6v2zm6 8h-4v-2h4v2zm0-4h-4v-2h4v2zm0-4h-4V7h4v2z"/>
                </svg>
                <span>{{ __('Google Sheet Sync') }}</span>
                @if(($googleSheetSyncEnabled ?? '0') === '1')
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                @endif
            </button>
            @endif
        </div>

        {{-- Switch styling --}}
        <style>
        .notice-switch-btn {
            position: relative;
            display: inline-flex;
            align-items: center;
            width: 40px;
            height: 22px;
            border-radius: 9999px;
            background-color: #cbd5e1;
            cursor: pointer;
            border: none;
            padding: 0;
            outline: none;
            transition: background-color 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            flex-shrink: 0;
            vertical-align: middle;
        }
        .dark .notice-switch-btn {
            background-color: #334155;
        }
        .notice-switch-btn.is-active.is-active-master {
            background-color: #4f46e5 !important;
        }
        .notice-switch-btn.is-active.is-active-global {
            background-color: #10b981 !important;
        }
        .notice-switch-btn.is-active.is-active-reseller {
            background-color: #d97706 !important;
        }
        .notice-switch-btn.is-active.is-active-noc {
            background-color: #6366f1 !important;
        }
        .notice-switch-knob {
            position: absolute;
            top: 2px;
            left: 2px;
            width: 18px;
            height: 18px;
            border-radius: 9999px;
            background-color: #ffffff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.25);
            transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            display: block;
            pointer-events: none;
        }
        .notice-switch-btn.is-active .notice-switch-knob {
            transform: translateX(18px);
        }
        </style>

        {{-- ================= TAB 1: NOTICE TICKER (STREAMLINED & COMPACT) ================= --}}
        <div x-show="activeTab === 'notice'" x-cloak class="space-y-4"
             x-data="{
                 noticeChannel: 'global',
                 masterActive: {{ (($noticeMasterActive ?? '1') == '1') ? 'true' : 'false' }},
                 globalActive: {{ (($noticeActive ?? '0') == '1') ? 'true' : 'false' }},
                 resellerActive: {{ (($noticeResellerActive ?? '0') == '1') ? 'true' : 'false' }},
                 nocActive: {{ (($noticeNocActive ?? '0') == '1') ? 'true' : 'false' }},
                 async toggleNotice(channel) {
                     let newVal;
                     if (channel === 'master') {
                         this.masterActive = !this.masterActive;
                         newVal = this.masterActive;
                     } else if (channel === 'global') {
                         this.globalActive = !this.globalActive;
                         newVal = this.globalActive;
                     } else if (channel === 'reseller') {
                         this.resellerActive = !this.resellerActive;
                         newVal = this.resellerActive;
                     } else if (channel === 'noc') {
                         this.nocActive = !this.nocActive;
                         newVal = this.nocActive;
                     }

                     try {
                         const res = await fetch('{{ route('settings.notice.toggle') }}', {
                             method: 'POST',
                             headers: {
                                 'Content-Type': 'application/json',
                                 'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                 'Accept': 'application/json'
                             },
                             body: JSON.stringify({
                                 channel: channel,
                                 active: newVal
                             })
                         });
                         const data = await res.json();
                         if (res.ok) {
                             if (window.showToastNotification) {
                                 window.showToastNotification(
                                     newVal ? '⚡ {{ __('Notice Activated') }}' : '⏸️ {{ __('Notice Deactivated') }}',
                                     data.message || '{{ __('Notice status updated successfully!') }}',
                                     ''
                                 );
                             }
                         } else {
                             if (channel === 'master') this.masterActive = !this.masterActive;
                             else if (channel === 'global') this.globalActive = !this.globalActive;
                             else if (channel === 'reseller') this.resellerActive = !this.resellerActive;
                             else if (channel === 'noc') this.nocActive = !this.nocActive;

                             if (window.showToastNotification) {
                                 window.showToastNotification('⚠️ Error', data.message || 'Failed to update notice status', '');
                             }
                         }
                     } catch (err) {
                         console.error('Failed to toggle notice:', err);
                         if (channel === 'master') this.masterActive = !this.masterActive;
                         else if (channel === 'global') this.globalActive = !this.globalActive;
                         else if (channel === 'reseller') this.resellerActive = !this.resellerActive;
                         else if (channel === 'noc') this.nocActive = !this.nocActive;
                     }
                 }
             }">

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden">
                {{-- Compact Header with Master Power Switch inlined --}}
                <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-850 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <span class="px-2 py-0.5 rounded-md text-[11px] font-black bg-red-600 text-white tracking-wider uppercase shrink-0">
                            📢 TICKER
                        </span>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900 dark:text-white leading-tight">{{ __('Header Notice Ticker') }}</h2>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ __('Broadcast alerts across Global, Reseller, and NOC audiences.') }}</p>
                        </div>
                    </div>

                    {{-- Master Switch in Header --}}
                    <div class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg border transition-colors shrink-0"
                         :class="masterActive ? 'bg-indigo-50/70 dark:bg-indigo-950/40 border-indigo-200 dark:border-indigo-800' : 'bg-rose-50/70 dark:bg-rose-950/40 border-rose-200 dark:border-rose-800'">
                        <div class="text-right">
                            <span class="text-[11px] font-bold block leading-none"
                                  :class="masterActive ? 'text-indigo-900 dark:text-indigo-300' : 'text-rose-900 dark:text-rose-300'">
                                {{ __('Master Switch') }}
                            </span>
                            <span class="text-[10px] font-semibold block leading-none mt-0.5"
                                  :class="masterActive ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'"
                                  x-text="masterActive ? '{{ __('ONLINE') }}' : '{{ __('DISABLED') }}'"></span>
                        </div>
                        <button type="button"
                                @click="toggleNotice('master')"
                                class="notice-switch-btn"
                                :class="{ 'is-active is-active-master': masterActive }"
                                role="switch"
                                :aria-checked="masterActive ? 'true' : 'false'">
                            <span class="notice-switch-knob"></span>
                        </button>
                    </div>
                </div>

                {{-- Compact Sub-Navigation Bar for Channels --}}
                <div class="px-4 py-2 bg-slate-100/70 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-1.5">
                        <button type="button" @click="noticeChannel = 'global'"
                                :class="noticeChannel === 'global' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 font-semibold'"
                                class="px-3 py-1.5 rounded-lg text-xs transition-all cursor-pointer flex items-center gap-1.5">
                            <span>🌐 {{ __('Global') }}</span>
                            <span class="w-2 h-2 rounded-full" :class="globalActive ? 'bg-emerald-500 animate-pulse' : 'bg-slate-300 dark:bg-slate-600'"></span>
                        </button>

                        <button type="button" @click="noticeChannel = 'reseller'"
                                :class="noticeChannel === 'reseller' ? 'bg-amber-500 text-white shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 font-semibold'"
                                class="px-3 py-1.5 rounded-lg text-xs transition-all cursor-pointer flex items-center gap-1.5">
                            <span>🤝 {{ __('Resellers') }}</span>
                            <span class="w-2 h-2 rounded-full" :class="resellerActive ? 'bg-amber-300 animate-pulse' : 'bg-slate-300 dark:bg-slate-600'"></span>
                        </button>

                        <button type="button" @click="noticeChannel = 'noc'"
                                :class="noticeChannel === 'noc' ? 'bg-indigo-600 text-white shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 font-semibold'"
                                class="px-3 py-1.5 rounded-lg text-xs transition-all cursor-pointer flex items-center gap-1.5">
                            <span>🛠️ {{ __('NOC Team') }}</span>
                            <span class="w-2 h-2 rounded-full" :class="nocActive ? 'bg-indigo-300 animate-pulse' : 'bg-slate-300 dark:bg-slate-600'"></span>
                        </button>
                    </div>

                    <div class="flex items-center gap-2 text-[11px] text-slate-500 dark:text-slate-400">
                        <span class="font-medium">{{ __('Active status:') }}</span>
                        <span class="inline-flex items-center gap-1 font-bold" :class="globalActive ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400'">
                            <span class="w-1.5 h-1.5 rounded-full" :class="globalActive ? 'bg-emerald-500' : 'bg-slate-400'"></span> Global
                        </span>
                        <span class="inline-flex items-center gap-1 font-bold" :class="resellerActive ? 'text-amber-600 dark:text-amber-400' : 'text-slate-400'">
                            <span class="w-1.5 h-1.5 rounded-full" :class="resellerActive ? 'bg-amber-500' : 'bg-slate-400'"></span> Reseller
                        </span>
                        <span class="inline-flex items-center gap-1 font-bold" :class="nocActive ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400'">
                            <span class="w-1.5 h-1.5 rounded-full" :class="nocActive ? 'bg-indigo-500' : 'bg-slate-400'"></span> NOC
                        </span>
                    </div>
                </div>

                <form method="POST" action="{{ route('settings.notice.update') }}" class="p-4 space-y-4">
                    @csrf
                    <input type="hidden" name="notice_master_active" :value="masterActive ? '1' : '0'">

                    {{-- ================= CHANNEL 1: GLOBAL ================= --}}
                    <div x-show="noticeChannel === 'global'" class="space-y-3.5">
                        {{-- Controls Bar (Enable + Theme + Speed in one line) --}}
                        <div class="p-2.5 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700/80 flex flex-col md:flex-row md:items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <button type="button"
                                        @click="toggleNotice('global')"
                                        class="notice-switch-btn"
                                        :class="{ 'is-active is-active-global': globalActive }"
                                        role="switch"
                                        :aria-checked="globalActive ? 'true' : 'false'">
                                    <span class="notice-switch-knob"></span>
                                </button>
                                <input type="hidden" name="notice_active" :value="globalActive ? '1' : '0'">
                                <div>
                                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200 block leading-tight">{{ __('Enable Global Notice') }}</span>
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400 block">{{ __('Shown to all users when active.') }}</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <div class="flex items-center gap-1.5">
                                    <label class="text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase">{{ __('Theme:') }}</label>
                                    <select name="notice_theme" class="px-2.5 py-1 text-xs font-semibold border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 dark:text-slate-100">
                                        <option value="danger"  {{ ($noticeTheme ?? 'danger') === 'danger' ? 'selected' : '' }}>🔴 Red</option>
                                        <option value="warning" {{ ($noticeTheme ?? '') === 'warning' ? 'selected' : '' }}>🟡 Amber</option>
                                        <option value="info"    {{ ($noticeTheme ?? '') === 'info' ? 'selected' : '' }}>🔵 Blue</option>
                                        <option value="success" {{ ($noticeTheme ?? '') === 'success' ? 'selected' : '' }}>🟢 Green</option>
                                        <option value="indigo"  {{ ($noticeTheme ?? '') === 'indigo' ? 'selected' : '' }}>🟣 Indigo</option>
                                    </select>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <label class="text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase">{{ __('Speed:') }}</label>
                                    <select name="notice_speed" class="px-2.5 py-1 text-xs font-semibold border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 dark:text-slate-100">
                                        <option value="4" {{ ($noticeSpeed ?? 8) == 4 ? 'selected' : '' }}>Slow</option>
                                        <option value="8" {{ ($noticeSpeed ?? 8) == 8 ? 'selected' : '' }}>Normal</option>
                                        <option value="12" {{ ($noticeSpeed ?? 8) == 12 ? 'selected' : '' }}>Fast</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Compact Bilingual Cards --}}
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                            {{-- English --}}
                            <div class="p-3 bg-slate-50/70 dark:bg-slate-800/40 rounded-xl border border-slate-200 dark:border-slate-700/80 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                                        <span>🇬🇧</span> {{ __('English Notice') }}
                                    </span>
                                    <span class="text-[10px] text-slate-400">English Language</span>
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    <div class="col-span-1">
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-0.5">{{ __('Badge') }}</label>
                                        <input type="text" name="notice_badge_en" value="{{ old('notice_badge_en', $noticeBadgeEn ?? 'GLOBAL NOTICE') }}" maxlength="30"
                                               class="w-full px-2.5 py-1.5 text-xs font-bold uppercase border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 dark:text-slate-100">
                                    </div>
                                    <div class="col-span-2">
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-0.5">{{ __('Notice Message') }}</label>
                                        <textarea name="notice_text_en" rows="2" placeholder="{{ __('Notice text in English...') }}"
                                                  class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 dark:text-slate-100 leading-snug">{{ old('notice_text_en', $noticeTextEn ?? '') }}</textarea>
                                    </div>
                                </div>
                            </div>

                            {{-- Bangla --}}
                            <div class="p-3 bg-emerald-50/30 dark:bg-emerald-950/20 rounded-xl border border-emerald-200/60 dark:border-emerald-900/40 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-emerald-800 dark:text-emerald-300 flex items-center gap-1.5">
                                        <span>🇧🇩</span> {{ __('Bangla Notice') }}
                                    </span>
                                    <span class="text-[10px] text-emerald-600/70 dark:text-emerald-400/60">বাংলা ভাষা</span>
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    <div class="col-span-1">
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-0.5">{{ __('ব্যাজ') }}</label>
                                        <input type="text" name="notice_badge_bn" value="{{ old('notice_badge_bn', $noticeBadgeBn ?? 'সাধারণ নোটিশ') }}" maxlength="30"
                                               class="w-full px-2.5 py-1.5 text-xs font-bold border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 dark:text-slate-100">
                                    </div>
                                    <div class="col-span-2">
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-0.5">{{ __('নোটিশ বার্তা') }}</label>
                                        <textarea name="notice_text_bn" rows="2" placeholder="{{ __('বাংলায় নোটিশ লিখুন...') }}"
                                                  class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 dark:text-slate-100 leading-snug">{{ old('notice_text_bn', $noticeTextBn ?? '') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ================= CHANNEL 2: RESELLER ================= --}}
                    <div x-show="noticeChannel === 'reseller'" class="space-y-3.5">
                        <div class="p-2.5 bg-amber-50/60 dark:bg-amber-950/30 rounded-xl border border-amber-200/80 dark:border-amber-900/60 flex flex-col md:flex-row md:items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <button type="button"
                                        @click="toggleNotice('reseller')"
                                        class="notice-switch-btn"
                                        :class="{ 'is-active is-active-reseller': resellerActive }"
                                        role="switch"
                                        :aria-checked="resellerActive ? 'true' : 'false'">
                                    <span class="notice-switch-knob"></span>
                                </button>
                                <input type="hidden" name="notice_reseller_active" :value="resellerActive ? '1' : '0'">
                                <div>
                                    <span class="text-xs font-bold text-amber-900 dark:text-amber-200 block leading-tight">{{ __('Enable Reseller Exclusive Notice') }}</span>
                                    <span class="text-[10px] text-amber-700/80 dark:text-amber-400 block">{{ __('Shown to Resellers & POP Managers only.') }}</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <div class="flex items-center gap-1.5">
                                    <label class="text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase">{{ __('Theme:') }}</label>
                                    <select name="notice_reseller_theme" class="px-2.5 py-1 text-xs font-semibold border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 dark:text-slate-100">
                                        <option value="warning" {{ ($noticeResellerTheme ?? 'warning') === 'warning' ? 'selected' : '' }}>🟡 Amber</option>
                                        <option value="danger"  {{ ($noticeResellerTheme ?? '') === 'danger' ? 'selected' : '' }}>🔴 Red</option>
                                        <option value="info"    {{ ($noticeResellerTheme ?? '') === 'info' ? 'selected' : '' }}>🔵 Blue</option>
                                        <option value="success" {{ ($noticeResellerTheme ?? '') === 'success' ? 'selected' : '' }}>🟢 Green</option>
                                        <option value="indigo"  {{ ($noticeResellerTheme ?? '') === 'indigo' ? 'selected' : '' }}>🟣 Indigo</option>
                                    </select>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <label class="text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase">{{ __('Speed:') }}</label>
                                    <select name="notice_reseller_speed" class="px-2.5 py-1 text-xs font-semibold border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 dark:text-slate-100">
                                        <option value="4" {{ ($noticeResellerSpeed ?? 8) == 4 ? 'selected' : '' }}>Slow</option>
                                        <option value="8" {{ ($noticeResellerSpeed ?? 8) == 8 ? 'selected' : '' }}>Normal</option>
                                        <option value="12" {{ ($noticeResellerSpeed ?? 8) == 12 ? 'selected' : '' }}>Fast</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                            {{-- English --}}
                            <div class="p-3 bg-slate-50/70 dark:bg-slate-800/40 rounded-xl border border-slate-200 dark:border-slate-700/80 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                                        <span>🇬🇧</span> {{ __('English (Reseller)') }}
                                    </span>
                                    <span class="text-[10px] text-slate-400">English Language</span>
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    <div class="col-span-1">
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-0.5">{{ __('Badge') }}</label>
                                        <input type="text" name="notice_reseller_badge_en" value="{{ old('notice_reseller_badge_en', $noticeResellerBadgeEn ?? 'RESELLER ALERT') }}" maxlength="30"
                                               class="w-full px-2.5 py-1.5 text-xs font-bold uppercase border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 dark:text-slate-100">
                                    </div>
                                    <div class="col-span-2">
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-0.5">{{ __('Notice Message') }}</label>
                                        <textarea name="notice_reseller_text_en" rows="2" placeholder="{{ __('Reseller notice in English...') }}"
                                                  class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 dark:text-slate-100 leading-snug">{{ old('notice_reseller_text_en', $noticeResellerTextEn ?? '') }}</textarea>
                                    </div>
                                </div>
                            </div>

                            {{-- Bangla --}}
                            <div class="p-3 bg-amber-50/40 dark:bg-amber-950/20 rounded-xl border border-amber-200/70 dark:border-amber-900/50 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-amber-800 dark:text-amber-300 flex items-center gap-1.5">
                                        <span>🇧🇩</span> {{ __('Bangla (Reseller)') }}
                                    </span>
                                    <span class="text-[10px] text-amber-700/70 dark:text-amber-400/60">বাংলা ভাষা</span>
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    <div class="col-span-1">
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-0.5">{{ __('ব্যাজ') }}</label>
                                        <input type="text" name="notice_reseller_badge_bn" value="{{ old('notice_reseller_badge_bn', $noticeResellerBadgeBn ?? 'রিসেলার নোটিশ') }}" maxlength="30"
                                               class="w-full px-2.5 py-1.5 text-xs font-bold border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 dark:text-slate-100">
                                    </div>
                                    <div class="col-span-2">
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-0.5">{{ __('নোটিশ বার্তা') }}</label>
                                        <textarea name="notice_reseller_text_bn" rows="2" placeholder="{{ __('বাংলায় রিসেলার নোটিশ লিখুন...') }}"
                                                  class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 dark:text-slate-100 leading-snug">{{ old('notice_reseller_text_bn', $noticeResellerTextBn ?? '') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ================= CHANNEL 3: NOC ================= --}}
                    <div x-show="noticeChannel === 'noc'" class="space-y-3.5">
                        <div class="p-2.5 bg-indigo-50/60 dark:bg-indigo-950/30 rounded-xl border border-indigo-200/80 dark:border-indigo-900/60 flex flex-col md:flex-row md:items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <button type="button"
                                        @click="toggleNotice('noc')"
                                        class="notice-switch-btn"
                                        :class="{ 'is-active is-active-noc': nocActive }"
                                        role="switch"
                                        :aria-checked="nocActive ? 'true' : 'false'">
                                    <span class="notice-switch-knob"></span>
                                </button>
                                <input type="hidden" name="notice_noc_active" :value="nocActive ? '1' : '0'">
                                <div>
                                    <span class="text-xs font-bold text-indigo-900 dark:text-indigo-200 block leading-tight">{{ __('Enable NOC Exclusive Notice') }}</span>
                                    <span class="text-[10px] text-indigo-700/80 dark:text-indigo-400 block">{{ __('Shown to NOC Engineers only.') }}</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <div class="flex items-center gap-1.5">
                                    <label class="text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase">{{ __('Theme:') }}</label>
                                    <select name="notice_noc_theme" class="px-2.5 py-1 text-xs font-semibold border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 dark:text-slate-100">
                                        <option value="indigo"  {{ ($noticeNocTheme ?? 'indigo') === 'indigo' ? 'selected' : '' }}>🟣 Indigo</option>
                                        <option value="danger"  {{ ($noticeNocTheme ?? '') === 'danger' ? 'selected' : '' }}>🔴 Red</option>
                                        <option value="warning" {{ ($noticeNocTheme ?? '') === 'warning' ? 'selected' : '' }}>🟡 Amber</option>
                                        <option value="info"    {{ ($noticeNocTheme ?? '') === 'info' ? 'selected' : '' }}>🔵 Blue</option>
                                        <option value="success" {{ ($noticeNocTheme ?? '') === 'success' ? 'selected' : '' }}>🟢 Green</option>
                                    </select>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <label class="text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase">{{ __('Speed:') }}</label>
                                    <select name="notice_noc_speed" class="px-2.5 py-1 text-xs font-semibold border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 dark:text-slate-100">
                                        <option value="4" {{ ($noticeNocSpeed ?? 8) == 4 ? 'selected' : '' }}>Slow</option>
                                        <option value="8" {{ ($noticeNocSpeed ?? 8) == 8 ? 'selected' : '' }}>Normal</option>
                                        <option value="12" {{ ($noticeNocSpeed ?? 8) == 12 ? 'selected' : '' }}>Fast</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                            {{-- English --}}
                            <div class="p-3 bg-slate-50/70 dark:bg-slate-800/40 rounded-xl border border-slate-200 dark:border-slate-700/80 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                                        <span>🇬🇧</span> {{ __('English (NOC)') }}
                                    </span>
                                    <span class="text-[10px] text-slate-400">English Language</span>
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    <div class="col-span-1">
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-0.5">{{ __('Badge') }}</label>
                                        <input type="text" name="notice_noc_badge_en" value="{{ old('notice_noc_badge_en', $noticeNocBadgeEn ?? 'NOC DISPATCH') }}" maxlength="30"
                                               class="w-full px-2.5 py-1.5 text-xs font-bold uppercase border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 dark:text-slate-100">
                                    </div>
                                    <div class="col-span-2">
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-0.5">{{ __('Notice Message') }}</label>
                                        <textarea name="notice_noc_text_en" rows="2" placeholder="{{ __('NOC notice in English...') }}"
                                                  class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 dark:text-slate-100 leading-snug">{{ old('notice_noc_text_en', $noticeNocTextEn ?? '') }}</textarea>
                                    </div>
                                </div>
                            </div>

                            {{-- Bangla --}}
                            <div class="p-3 bg-indigo-50/40 dark:bg-indigo-950/20 rounded-xl border border-indigo-200/70 dark:border-indigo-900/50 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-indigo-800 dark:text-indigo-300 flex items-center gap-1.5">
                                        <span>🇧🇩</span> {{ __('Bangla (NOC)') }}
                                    </span>
                                    <span class="text-[10px] text-indigo-700/70 dark:text-indigo-400/60">বাংলা ভাষা</span>
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    <div class="col-span-1">
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-0.5">{{ __('ব্যাজ') }}</label>
                                        <input type="text" name="notice_noc_badge_bn" value="{{ old('notice_noc_badge_bn', $noticeNocBadgeBn ?? 'এনওসি নোটিশ') }}" maxlength="30"
                                               class="w-full px-2.5 py-1.5 text-xs font-bold border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 dark:text-slate-100">
                                    </div>
                                    <div class="col-span-2">
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-0.5">{{ __('নোটিশ বার্তা') }}</label>
                                        <textarea name="notice_noc_text_bn" rows="2" placeholder="{{ __('বাংলায় এনওসি নোটিশ লিখুন...') }}"
                                                  class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 dark:text-slate-100 leading-snug">{{ old('notice_noc_text_bn', $noticeNocTextBn ?? '') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Compact Save Footer --}}
                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-3">
                        <span class="text-[11px] text-slate-400">
                            💡 {{ __('All 3 channels are saved together in one click.') }}
                        </span>
                        <button type="submit" class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white px-4 py-2 rounded-xl text-xs font-bold transition-all shadow-xs cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('Save All Notices') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ================= TAB 2: BRANDING (LOGO & FAVICON COMPACT CARDS) ================= --}}
        <div x-show="activeTab === 'branding'" x-cloak class="space-y-4">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 1rem;">
                {{-- Compact Logo Card --}}
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden flex flex-col justify-between">
                    <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-100 dark:border-indigo-800/60 flex items-center justify-center text-indigo-600 dark:text-indigo-400 shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </span>
                            <div>
                                <h3 class="text-xs font-bold text-slate-800 dark:text-white leading-tight">{{ __('Company Brand Logo') }}</h3>
                                <p class="text-[10px] text-slate-400 leading-tight">{{ __('Displayed on header, sidebar & login') }}</p>
                            </div>
                        </div>
                        @if($logoPath)
                        <button type="submit" form="remove-logo-form" class="text-[11px] font-semibold text-rose-600 hover:text-rose-800 dark:text-rose-400 hover:underline">
                            {{ __('Remove') }}
                        </button>
                        @endif
                    </div>

                    <div class="p-3.5 space-y-3">
                        {{-- Side-by-side: Preview + Upload Form --}}
                        <div class="flex items-center gap-3">
                            {{-- Compact Preview Box --}}
                            <div class="w-28 h-20 rounded-lg border border-dashed border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-850 p-1.5 flex items-center justify-center shrink-0 overflow-hidden">
                                @if($logoPath)
                                    <img src="{{ asset('storage/' . $logoPath) }}" alt="Logo" class="max-h-16 max-w-full object-contain">
                                @else
                                    <div class="text-center text-slate-400">
                                        <svg class="w-6 h-6 mx-auto stroke-current opacity-40" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span class="text-[9px] block">{{ __('No Logo') }}</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Upload Form --}}
                            <form method="POST" action="{{ route('settings.logo.update') }}" enctype="multipart/form-data" class="flex-1 space-y-2">
                                @csrf
                                <div>
                                    <input type="file" name="logo" accept="image/*" required
                                           class="block w-full text-[11px] text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 rounded-lg cursor-pointer bg-slate-50 dark:bg-slate-800 focus:outline-none file:mr-2 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-[11px] file:font-bold file:bg-indigo-600 file:text-white hover:file:bg-indigo-700">
                                    @error('logo')<p class="text-rose-500 text-[10px] mt-0.5">{{ $message }}</p>@enderror
                                    <p class="text-[10px] text-slate-400 mt-1">{{ __('PNG, JPG, WEBP, SVG (Max 2MB)') }}</p>
                                </div>
                                <button type="submit" class="inline-flex items-center gap-1 bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition-all shadow-xs cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                    <span>{{ __('Upload Logo') }}</span>
                                </button>
                            </form>
                        </div>

                        @if($logoPath)
                        <form id="remove-logo-form" method="POST" action="{{ route('settings.logo.destroy') }}" class="hidden" onsubmit="return confirm('{{ __('Remove active logo?') }}')">
                            @csrf
                            @method('DELETE')
                        </form>
                        @endif
                    </div>
                </div>

                {{-- Compact Favicon Card --}}
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden flex flex-col justify-between">
                    <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-100 dark:border-emerald-800/60 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                            </span>
                            <div>
                                <h3 class="text-xs font-bold text-slate-800 dark:text-white leading-tight">{{ __('Browser Favicon') }}</h3>
                                <p class="text-[10px] text-slate-400 leading-tight">{{ __('Browser tab icon & bookmarks') }}</p>
                            </div>
                        </div>
                        @if($faviconPath)
                        <button type="submit" form="remove-favicon-form" class="text-[11px] font-semibold text-rose-600 hover:text-rose-800 dark:text-rose-400 hover:underline">
                            {{ __('Remove') }}
                        </button>
                        @endif
                    </div>

                    <div class="p-3.5 space-y-3">
                        <div class="flex items-center gap-3">
                            {{-- Compact Favicon Preview Box --}}
                            <div class="w-28 h-20 rounded-lg border border-dashed border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-850 p-1.5 flex items-center justify-center shrink-0">
                                @if($faviconPath)
                                    <div class="flex flex-col items-center">
                                        <img src="{{ asset('storage/' . $faviconPath) }}" alt="Favicon" class="w-7 h-7 object-contain">
                                        <span class="text-[9px] text-slate-400 mt-1 truncate max-w-[90px]">{{ config('app.name', 'ISP Ticket') }}</span>
                                    </div>
                                @else
                                    <div class="text-center text-slate-400">
                                        <svg class="w-6 h-6 mx-auto stroke-current opacity-40" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                                        <span class="text-[9px] block">{{ __('Default') }}</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Favicon Upload Form --}}
                            <form method="POST" action="{{ route('settings.favicon.update') }}" enctype="multipart/form-data" class="flex-1 space-y-2">
                                @csrf
                                <div>
                                    <input type="file" name="favicon" accept="image/*,.ico" required
                                           class="block w-full text-[11px] text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 rounded-lg cursor-pointer bg-slate-50 dark:bg-slate-800 focus:outline-none file:mr-2 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-[11px] file:font-bold file:bg-emerald-600 file:text-white hover:file:bg-emerald-700">
                                    @error('favicon')<p class="text-rose-500 text-[10px] mt-0.5">{{ $message }}</p>@enderror
                                    <p class="text-[10px] text-slate-400 mt-1">{{ __('Square PNG, ICO (Max 512KB)') }}</p>
                                </div>
                                <button type="submit" class="inline-flex items-center gap-1 bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition-all shadow-xs cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                    <span>{{ __('Upload Favicon') }}</span>
                                </button>
                            </form>
                        </div>

                        @if($faviconPath)
                        <form id="remove-favicon-form" method="POST" action="{{ route('settings.favicon.destroy') }}" class="hidden" onsubmit="return confirm('{{ __('Remove active favicon?') }}')">
                            @csrf
                            @method('DELETE')
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= TAB 3: THEME & BRAND COLORS (COMPACT) ================= --}}
        @if(auth()->user()->isSuperAdminOnly())
        <div x-show="activeTab === 'theme'" x-cloak class="space-y-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-100 dark:border-indigo-800/60 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4 4 4 0 014-4c.48 0 .935.085 1.356.241A6.974 6.974 0 019 11a7 7 0 017-7c1.378 0 2.652.4 3.732 1.085A4 4 0 0121 9a4 4 0 01-4 4c-.48 0-.935-.085-1.356-.241A6.974 6.974 0 0115 15a7 7 0 01-7 7z"/></svg>
                        </span>
                        <div>
                            <h2 class="text-xs font-bold text-slate-900 dark:text-white leading-tight">{{ __('Brand Colors & Palette') }}</h2>
                            <p class="text-[10px] text-slate-400 leading-tight">{{ __('Primary accent for active links & buttons; secondary for status highlights.') }}</p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('settings.theme.update') }}" id="form-theme" class="p-3.5 space-y-3.5">
                    @csrf

                    {{-- Compact Curated Presets --}}
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                            {{ __('Curated Color Presets') }}
                        </label>
                        @php
                        $presets = [
                            ['label' => 'Indigo Prime', 'primary' => '#4f46e5', 'secondary' => '#10b981'],
                            ['label' => 'Royal Violet', 'primary' => '#7c3aed', 'secondary' => '#06b6d4'],
                            ['label' => 'Ocean Blue',   'primary' => '#2563eb', 'secondary' => '#10b981'],
                            ['label' => 'Sky Amber',    'primary' => '#0284c7', 'secondary' => '#f59e0b'],
                            ['label' => 'Emerald Teal', 'primary' => '#0d9488', 'secondary' => '#8b5cf6'],
                            ['label' => 'Ruby Rose',    'primary' => '#e11d48', 'secondary' => '#f59e0b'],
                            ['label' => 'Sunset Orange','primary' => '#ea580c', 'secondary' => '#3b82f6'],
                            ['label' => 'Deep Slate',   'primary' => '#475569', 'secondary' => '#10b981'],
                        ];
                        @endphp
                        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-2" id="theme-presets">
                            @foreach($presets as $preset)
                            <button type="button"
                                    class="theme-preset-btn flex items-center justify-between p-2 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800 hover:border-indigo-400 transition-all text-[11px] font-semibold text-slate-700 dark:text-slate-200 cursor-pointer"
                                    data-primary="{{ $preset['primary'] }}" data-secondary="{{ $preset['secondary'] }}"
                                    title="{{ $preset['label'] }}">
                                <span class="truncate">{{ $preset['label'] }}</span>
                                <div class="flex items-center -space-x-1 shrink-0 ml-1">
                                    <span class="w-3 h-3 rounded-full border border-white dark:border-slate-900" style="background-color: {{ $preset['primary'] }}"></span>
                                    <span class="w-3 h-3 rounded-full border border-white dark:border-slate-900" style="background-color: {{ $preset['secondary'] }}"></span>
                                </div>
                            </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Compact Color Pickers (Side by Side) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="p-2.5 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-850">
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">{{ __('Primary Color') }}</label>
                            <div class="flex items-center gap-2">
                                <input type="color" id="primary-color-picker" value="{{ $themePrimary }}"
                                       class="w-9 h-8 rounded-lg border border-slate-200 dark:border-slate-700 cursor-pointer bg-transparent p-0.5">
                                <input type="text" id="primary-color-hex" name="primary_color" value="{{ $themePrimary }}" maxlength="7"
                                       class="flex-1 px-2.5 py-1 text-xs font-mono font-bold uppercase border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 dark:text-slate-100"
                                       oninput="if(/^#[0-9a-fA-F]{6}$/.test(this.value)){document.getElementById('primary-color-picker').value=this.value;updateThemePreview()}">
                            </div>
                            <div id="primary-preview-bar" class="h-1 rounded-full mt-2 transition-all" style="background-color: {{ $themePrimary }};"></div>
                        </div>

                        <div class="p-2.5 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-850">
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">{{ __('Secondary Accent Color') }}</label>
                            <div class="flex items-center gap-2">
                                <input type="color" id="secondary-color-picker" value="{{ $themeSecondary }}"
                                       class="w-9 h-8 rounded-lg border border-slate-200 dark:border-slate-700 cursor-pointer bg-transparent p-0.5">
                                <input type="text" id="secondary-color-hex" name="secondary_color" value="{{ $themeSecondary }}" maxlength="7"
                                       class="flex-1 px-2.5 py-1 text-xs font-mono font-bold uppercase border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 dark:text-slate-100"
                                       oninput="if(/^#[0-9a-fA-F]{6}$/.test(this.value)){document.getElementById('secondary-color-picker').value=this.value;updateThemePreview()}">
                            </div>
                            <div id="secondary-preview-bar" class="h-1 rounded-full mt-2 transition-all" style="background-color: {{ $themeSecondary }};"></div>
                        </div>
                    </div>

                    {{-- Compact Live Interactive Preview Bar --}}
                    <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200 dark:border-slate-800 space-y-2">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">{{ __('Real-time Live Elements Preview') }}</span>
                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button" id="prev-btn-primary" class="px-3 py-1 rounded-lg text-xs font-bold text-white shadow-xs" style="background-color: {{ $themePrimary }};">Primary Button</button>
                            <button type="button" id="prev-btn-secondary" class="px-3 py-1 rounded-lg text-xs font-bold text-white shadow-xs" style="background-color: {{ $themeSecondary }};">Secondary Button</button>
                            <span id="prev-badge-primary" class="px-2.5 py-0.5 rounded-full text-[11px] font-bold text-white" style="background-color: {{ $themePrimary }};">Badge</span>
                            <span id="prev-link" class="text-xs font-bold cursor-pointer" style="color: {{ $themePrimary }};">Active Link &rarr;</span>
                        </div>
                        <div id="prev-bar" class="h-1 rounded-full w-full" style="background: linear-gradient(90deg, {{ $themePrimary }}, {{ $themeSecondary }});"></div>
                    </div>

                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <span class="text-[11px] text-slate-400">{{ __('Applies site-wide immediately.') }}</span>
                        <button type="submit" class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-1.5 rounded-lg text-xs font-bold transition-all shadow-xs cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>{{ __('Save Colors') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
        function updateThemePreview() {
            const p = document.getElementById('primary-color-hex')?.value;
            const s = document.getElementById('secondary-color-hex')?.value;
            const validHex = /^#[0-9a-fA-F]{6}$/;
            if (validHex.test(p)) {
                if(document.getElementById('primary-preview-bar')) document.getElementById('primary-preview-bar').style.backgroundColor = p;
                if(document.getElementById('prev-btn-primary')) document.getElementById('prev-btn-primary').style.backgroundColor = p;
                if(document.getElementById('prev-badge-primary')) document.getElementById('prev-badge-primary').style.backgroundColor = p;
                if(document.getElementById('prev-link')) document.getElementById('prev-link').style.color = p;
            }
            if (validHex.test(s)) {
                if(document.getElementById('secondary-preview-bar')) document.getElementById('secondary-preview-bar').style.backgroundColor = s;
                if(document.getElementById('prev-btn-secondary')) document.getElementById('prev-btn-secondary').style.backgroundColor = s;
            }
            if (validHex.test(p) && validHex.test(s)) {
                if(document.getElementById('prev-bar')) document.getElementById('prev-bar').style.background = `linear-gradient(90deg, ${p}, ${s})`;
            }
        }
        document.getElementById('primary-color-picker')?.addEventListener('input', function() {
            document.getElementById('primary-color-hex').value = this.value;
            updateThemePreview();
        });
        document.getElementById('secondary-color-picker')?.addEventListener('input', function() {
            document.getElementById('secondary-color-hex').value = this.value;
            updateThemePreview();
        });
        document.querySelectorAll('.theme-preset-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const p = this.dataset.primary;
                const s = this.dataset.secondary;
                document.getElementById('primary-color-picker').value = p;
                document.getElementById('primary-color-hex').value = p;
                document.getElementById('secondary-color-picker').value = s;
                document.getElementById('secondary-color-hex').value = s;
                updateThemePreview();
                document.querySelectorAll('.theme-preset-btn').forEach(b => b.classList.remove('ring-2', 'ring-indigo-600'));
                this.classList.add('ring-2', 'ring-indigo-600');
            });
        });
        </script>
        @endif

        {{-- ================= TAB 4: TICKET CATEGORIES (COMPACT GRID) ================= --}}
        <div x-show="activeTab === 'categories'" x-cloak class="space-y-4" x-data="{ showQuickAddCat: false }">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-100 dark:border-indigo-800/60 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        </span>
                        <div>
                            <h2 class="text-xs font-bold text-slate-900 dark:text-white leading-tight">{{ __('Support Ticket Categories') }}</h2>
                            <p class="text-[10px] text-slate-400 leading-tight">{{ __('Used for routing, assignment scoping, and filtering.') }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button @click="showQuickAddCat = true" type="button" 
                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg transition-all shadow-xs cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>{{ __('Add Category') }}</span>
                        </button>
                        <a href="{{ route('ticket-categories.index') }}" 
                           class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-lg transition-all">
                            <span>{{ __('Manage All') }}</span>
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>

                {{-- Categories Compact Grid --}}
                <div class="p-3.5">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 0.625rem;">
                        @forelse($categories ?? [] as $cat)
                        <div class="p-2.5 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/40 hover:border-indigo-300 dark:hover:border-indigo-800 transition-all flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between gap-1 mb-1">
                                    <div class="flex items-center gap-1.5 min-w-0">
                                        <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $cat->color }}"></span>
                                        <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate">{{ $cat->name }}</h4>
                                    </div>
                                    <span class="text-[9px] font-mono px-1.5 py-0.5 rounded bg-white dark:bg-slate-700 text-slate-500 dark:text-slate-300 border border-slate-200 dark:border-slate-600 shrink-0">
                                        {{ $cat->slug }}
                                    </span>
                                </div>
                                <p class="text-[10px] text-slate-500 dark:text-slate-400 line-clamp-1">
                                    {{ $cat->description ?: __('No description.') }}
                                </p>
                            </div>
                            <div class="mt-2 pt-1.5 border-t border-slate-200/60 dark:border-slate-700/60 flex items-center justify-between text-[10px]">
                                <span class="text-slate-400">#{{ $cat->id }}</span>
                                <a href="{{ route('ticket-categories.index') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline font-semibold">
                                    {{ __('Edit') }} &rarr;
                                </a>
                            </div>
                        </div>
                        @empty
                        <div class="col-span-full py-8 text-center text-slate-400">
                            <p class="text-xs font-semibold">{{ __('No ticket categories found.') }}</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Quick Add Category Modal --}}
            <div x-show="showQuickAddCat" x-cloak
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
                <div class="bg-white dark:bg-slate-900 rounded-xl max-w-sm w-full border border-slate-200 dark:border-slate-800 shadow-2xl p-4"
                     @click.away="showQuickAddCat = false">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 mb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-md bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 flex items-center justify-center">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            </span>
                            <h3 class="text-xs font-bold text-slate-800 dark:text-slate-100">{{ __('Add Ticket Category') }}</h3>
                        </div>
                        <button @click="showQuickAddCat = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('ticket-categories.store') }}">
                        @csrf
                        <div class="space-y-3 text-left">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1 uppercase tracking-wider">{{ __('Name') }} <span class="text-rose-500">*</span></label>
                                <input type="text" name="name" required placeholder="e.g. Fiber Cut / Link Down"
                                       class="w-full border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1 uppercase tracking-wider">{{ __('Accent Color') }}</label>
                                <div class="flex items-center gap-2">
                                    <input type="color" name="color" value="#4f46e5"
                                           class="h-8 w-12 border border-slate-200 dark:border-slate-700 rounded-lg p-0.5 bg-slate-50 dark:bg-slate-800 cursor-pointer">
                                    <span class="text-[10px] text-slate-400">{{ __('Category badge chip color') }}</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1 uppercase tracking-wider">{{ __('Description') }}</label>
                                <textarea name="description" rows="2" placeholder="Brief description..."
                                          class="w-full border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 dark:text-white"></textarea>
                            </div>
                        </div>

                        <div class="mt-4 flex justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                            <button type="button" @click="showQuickAddCat = false" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 cursor-pointer">
                                {{ __('Cancel') }}
                            </button>
                            <button type="submit" class="px-4 py-1.5 rounded-lg text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs cursor-pointer">
                                {{ __('Save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ================= TAB 5: DATABASE BACKUP & RESTORE (COMPACT) ================= --}}
        @if(auth()->user()->isAdmin())
        <div x-show="activeTab === 'backup'" x-cloak class="space-y-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-100 dark:border-emerald-800/60 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                        </span>
                        <div>
                            <h2 class="text-xs font-bold text-slate-900 dark:text-white leading-tight">{{ __('Database Backups & Auto-Retention') }}</h2>
                            <p class="text-[10px] text-slate-400 leading-tight">{{ __('Automatic daily cron backup runs at 11:59 PM. Safely keeps up to 30 days.') }}</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('settings.backup.create') }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-bold rounded-lg transition-all shadow-xs cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <span>{{ __('Create Backup Now') }}</span>
                        </button>
                    </form>
                </div>

                {{-- Backups Table (Compact) --}}
                <div class="p-3.5 space-y-2.5">
                    <div class="overflow-x-auto border border-slate-200 dark:border-slate-800 rounded-lg">
                        <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                            <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-500 dark:text-slate-400 uppercase font-bold border-b border-slate-200 dark:border-slate-800 text-[10px]">
                                <tr>
                                    <th class="px-3 py-2">#</th>
                                    <th class="px-3 py-2">{{ __('File') }}</th>
                                    <th class="px-3 py-2">{{ __('Type') }}</th>
                                    <th class="px-3 py-2">{{ __('Size') }}</th>
                                    <th class="px-3 py-2">{{ __('Created') }}</th>
                                    <th class="px-3 py-2 text-right">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @forelse($backups ?? [] as $index => $b)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors">
                                    <td class="px-3 py-2 font-mono text-slate-400 text-[11px]">{{ $index + 1 }}</td>
                                    <td class="px-3 py-2">
                                        <div class="flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            <span class="font-mono font-bold text-slate-800 dark:text-slate-200 text-xs">{{ $b['filename'] }}</span>
                                            @if($index === 0)
                                            <span class="px-1.5 py-0.2 rounded text-[9px] bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 font-extrabold">{{ __('Latest') }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-3 py-2">
                                        @if(($b['type'] ?? 'manual') === 'auto')
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300">
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Auto
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800 text-amber-700 dark:text-amber-300">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Manual
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 font-semibold text-slate-500 dark:text-slate-400 text-xs">{{ $b['human_size'] }}</td>
                                    <td class="px-3 py-2 text-slate-500 dark:text-slate-400 text-[11px]">
                                        <span>{{ $b['created_at']->format('d M Y, h:i A') }}</span>
                                        <span class="text-[9px] text-slate-400 ml-1">({{ $b['created_at']->diffForHumans() }})</span>
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        <div class="inline-flex items-center gap-1.5 justify-end">
                                            {{-- Download --}}
                                            <a href="{{ route('settings.backup.download', $b['filename']) }}"
                                               class="px-2 py-1 bg-indigo-50 dark:bg-indigo-950/60 hover:bg-indigo-100 text-indigo-600 dark:text-indigo-400 rounded font-bold text-[10px] transition-colors inline-flex items-center gap-1"
                                               title="{{ __('Download') }}">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                                <span>{{ __('Download') }}</span>
                                            </a>

                                            {{-- Restore --}}
                                            <form method="POST" action="{{ route('settings.backup.restore', $b['filename']) }}"
                                                  onsubmit="return confirm('⚠️ {{ __('Restore database from :file? All current ticket and system records will be overwritten.', ['file' => $b['filename']]) }}')">
                                                @csrf
                                                <button type="submit"
                                                        class="px-2 py-1 bg-amber-50 dark:bg-amber-950/60 hover:bg-amber-100 text-amber-700 dark:text-amber-400 rounded font-bold text-[10px] transition-colors inline-flex items-center gap-1 cursor-pointer"
                                                        title="{{ __('Restore') }}">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                    <span>{{ __('Restore') }}</span>
                                                </button>
                                            </form>

                                            {{-- Delete --}}
                                            <form method="POST" action="{{ route('settings.backup.destroy', $b['filename']) }}"
                                                  onsubmit="return confirm('{{ __('Permanently delete this backup file?') }}')">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                        class="p-1 text-rose-500 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/50 rounded font-bold text-xs transition-colors cursor-pointer"
                                                        title="{{ __('Delete') }}">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-3 py-8 text-center text-slate-400">
                                        <p class="font-semibold">{{ __('No backups found.') }}</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="px-3 py-2 rounded-lg bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 text-[10px] text-slate-500 dark:text-slate-400 flex items-center gap-2">
                        <span>🛡️</span>
                        <span><strong>{{ __('Auto Retention (FIFO):') }}</strong> {{ __('Keeps up to 30 daily archives automatically, pruning the oldest.') }}</span>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- 📊 Google Sheet Auto Sync Tab (Admin) --}}
        @if(auth()->user()->isAdmin())
        <div x-show="activeTab === 'googlesheet'" x-cloak class="space-y-4" x-data="{ copied: false }">
            {{-- Top Header Card --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-xs">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 14H6v-2h6v2zm0-4H6v-2h6v2zm0-4H6V7h6v2zm6 8h-4v-2h4v2zm0-4h-4v-2h4v2zm0-4h-4V7h4v2z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-sm font-bold text-slate-800 dark:text-white">{{ __('Google Sheet Auto Sync') }}</h2>
                                @if(($googleSheetSyncEnabled ?? '0') === '1')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/80 dark:text-emerald-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        {{ __('Active') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                                        {{ __('Disabled') }}
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                {{ __('Automatically record tickets into your Complaint Tracking Sheet (2026) without manual entry.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Webhook Configuration Form --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40 flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider flex items-center gap-2">
                        <span>⚙️</span> {{ __('Webhook Connection Settings') }}
                    </h3>
                </div>

                <div class="p-4 sm:p-5 space-y-4">
                    <form method="POST" action="{{ route('settings.googlesheet.update') }}" class="space-y-4">
                        @csrf

                        {{-- Toggle Enable/Disable --}}
                        <div class="flex items-center justify-between p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/20"
                             x-data="{ enabled: {{ ($googleSheetSyncEnabled ?? '0') === '1' ? 'true' : 'false' }} }">
                            <div>
                                <label @click="enabled = !enabled" class="text-xs font-bold text-slate-800 dark:text-slate-200 cursor-pointer">
                                    {{ __('Enable Google Sheet Sync') }}
                                </label>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                    {{ __('When enabled, all newly created tickets and status updates will automatically sync to your Google Sheet.') }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="hidden" name="google_sheet_sync_enabled" :value="enabled ? '1' : '0'">
                                <button type="button" @click="enabled = !enabled"
                                        class="w-11 h-6 rounded-full transition-colors relative cursor-pointer focus:outline-none shrink-0"
                                        :class="enabled ? 'bg-emerald-600' : 'bg-slate-300 dark:bg-slate-700'"
                                        role="switch" :aria-checked="enabled ? 'true' : 'false'">
                                    <span class="inline-block w-5 h-5 rounded-full bg-white shadow-sm transform transition-transform duration-200 absolute top-0.5 left-0.5"
                                          :class="enabled ? 'translate-x-5' : 'translate-x-0'"></span>
                                </button>
                            </div>
                        </div>

                        {{-- Webhook URL Input --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                {{ __('Google Apps Script Web App URL') }}
                            </label>
                            <input type="url" name="google_sheet_webhook_url" value="{{ old('google_sheet_webhook_url', $googleSheetWebhookUrl ?? '') }}"
                                   placeholder="https://script.google.com/macros/s/AKfycbx.../exec"
                                   class="w-full border border-slate-200 dark:border-slate-700 rounded-lg px-3.5 py-2 text-xs font-mono text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 bg-white dark:bg-slate-800">
                            
                            @if(!empty($googleSheetWebhookUrl) && str_contains($googleSheetWebhookUrl, 'docs.google.com/spreadsheets'))
                            <div class="mt-2.5 p-3 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-600 dark:text-amber-400 text-xs flex items-start gap-2.5">
                                <span class="text-base shrink-0">⚠️</span>
                                <div class="space-y-1">
                                    <p class="font-bold text-amber-700 dark:text-amber-300">{{ __('ভুল লিঙ্ক পেস্ট করা হয়েছে (এটি ভিউ লিঙ্ক)!') }}</p>
                                    <p class="text-[11px] leading-relaxed text-slate-600 dark:text-slate-300">
                                        {{ __('আপনি সরাসরি Google Spreadsheet এর ব্রাউজার লিঙ্ক দিয়েছেন। গুগল শিট সরাসরি ব্রাউজার লিঙ্কে ডাটা রিসিভ করতে পারে না। এর জন্য আপনার শিটের Extensions > Apps Script এ গিয়ে নিচের কোডটি পেস্ট করে Deploy > New deployment > Web app থেকে প্রাপ্ত Web App URL-টি (https://script.google.com/macros/s/.../exec) এখানে দিন।') }}
                                    </p>
                                </div>
                            </div>
                            @endif

                            <p class="text-[11px] text-slate-400 mt-1">
                                {{ __('Deploy your Google Apps Script as a Web App (Access: Anyone) and paste the deployed URL here (Must start with https://script.google.com/macros/s/...).') }}
                            </p>
                        </div>

                        <div class="flex items-center gap-3 pt-2">
                            <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-colors shadow-xs cursor-pointer">
                                💾 {{ __('Save Settings') }}
                            </button>
                        </div>
                    </form>

                    {{-- Test Connection Button --}}
                    @if(!empty($googleSheetWebhookUrl))
                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <div>
                            <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ __('Verify Webhook Connection') }}</span>
                            <p class="text-[11px] text-slate-400">{{ __('Send a test ping to your Google Sheet webhook to confirm connectivity.') }}</p>
                        </div>
                        <form method="POST" action="{{ route('settings.googlesheet.test') }}">
                            @csrf
                            <button type="submit" class="px-3.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 text-xs font-semibold transition-colors cursor-pointer flex items-center gap-1.5">
                                <span>⚡</span> {{ __('Test Connection') }}
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>

            {{-- 16-Column Structure Guide Card --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40 flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider flex items-center gap-2">
                        <span>📋</span> {{ __('Sheet Column Mapping (Complaint Tracking Sheet)') }}
                    </h3>
                    <span class="text-[10px] text-slate-400">{{ __('Columns A to P (16 Columns)') }}</span>
                </div>
                <div class="p-4 overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 text-[11px] font-bold text-slate-500 uppercase tracking-wider bg-slate-50 dark:bg-slate-800/50">
                                <th class="p-2">Col</th>
                                <th class="p-2">Header Name</th>
                                <th class="p-2">Data Synced From ISP System</th>
                                <th class="p-2">Example Value</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-[11px]">
                            <tr><td class="p-2 font-mono font-bold text-indigo-600">A</td><td class="p-2 font-semibold">Master SL</td><td class="p-2 text-slate-500">Auto calculated increment (+1)</td><td class="p-2 font-mono text-slate-600 dark:text-slate-300">54</td></tr>
                            <tr><td class="p-2 font-mono font-bold text-indigo-600">B</td><td class="p-2 font-semibold">Daily SL</td><td class="p-2 text-slate-500">Daily ticket serial number (+1)</td><td class="p-2 font-mono text-slate-600 dark:text-slate-300">3</td></tr>
                            <tr><td class="p-2 font-mono font-bold text-indigo-600">C</td><td class="p-2 font-semibold">Date</td><td class="p-2 text-slate-500">Ticket creation date</td><td class="p-2 font-mono text-slate-600 dark:text-slate-300">27 Sep, 26</td></tr>
                            <tr><td class="p-2 font-mono font-bold text-indigo-600">D</td><td class="p-2 font-semibold">User Entry Time</td><td class="p-2 text-slate-500">Entry time (12-hour format)</td><td class="p-2 font-mono text-slate-600 dark:text-slate-300">01:08 PM</td></tr>
                            <tr><td class="p-2 font-mono font-bold text-indigo-600">E</td><td class="p-2 font-semibold">Complaint Source</td><td class="p-2 text-slate-500">Source: Phone, Office, Online, WhatsApp</td><td class="p-2 font-mono text-slate-600 dark:text-slate-300">Phone</td></tr>
                            <tr><td class="p-2 font-mono font-bold text-indigo-600">F</td><td class="p-2 font-semibold">ID</td><td class="p-2 text-slate-500">Client ID (or Ticket Key fallback)</td><td class="p-2 font-mono text-slate-600 dark:text-slate-300">15642</td></tr>
                            <tr><td class="p-2 font-mono font-bold text-indigo-600">G</td><td class="p-2 font-semibold">Name</td><td class="p-2 text-slate-500">Client Name (or Ticket Title)</td><td class="p-2 font-mono text-slate-600 dark:text-slate-300">Md. Mikdad Hossain</td></tr>
                            <tr><td class="p-2 font-mono font-bold text-indigo-600">H</td><td class="p-2 font-semibold">Address</td><td class="p-2 text-slate-500">Ticket Area</td><td class="p-2 font-mono text-slate-600 dark:text-slate-300">Rampura</td></tr>
                            <tr><td class="p-2 font-mono font-bold text-indigo-600">I</td><td class="p-2 font-semibold">Type</td><td class="p-2 text-slate-500">Ticket Category Name</td><td class="p-2 font-mono text-slate-600 dark:text-slate-300">Net Off (ONU Optical Power Los)</td></tr>
                            <tr><td class="p-2 font-mono font-bold text-indigo-600">J</td><td class="p-2 font-semibold">Received By</td><td class="p-2 text-slate-500">Name of person who created ticket</td><td class="p-2 font-mono text-slate-600 dark:text-slate-300">Rayhan</td></tr>
                            <tr><td class="p-2 font-mono font-bold text-indigo-600">K</td><td class="p-2 font-semibold">Forwarded To</td><td class="p-2 text-slate-500">Forwarded department or team</td><td class="p-2 font-mono text-slate-600 dark:text-slate-300">NOC Team</td></tr>
                            <tr><td class="p-2 font-mono font-bold text-indigo-600">L</td><td class="p-2 font-semibold">ONU Power Check IT Team</td><td class="p-2 text-slate-500">ONU Optical Power dBm</td><td class="p-2 font-mono text-slate-600 dark:text-slate-300">-23.56</td></tr>
                            <tr><td class="p-2 font-mono font-bold text-indigo-600">M</td><td class="p-2 font-semibold">Assigned To (Technician)</td><td class="p-2 text-slate-500">Assigned technician name</td><td class="p-2 font-mono text-slate-600 dark:text-slate-300">Hazrot</td></tr>
                            <tr><td class="p-2 font-mono font-bold text-indigo-600">N</td><td class="p-2 font-semibold">Current Status</td><td class="p-2 text-slate-500">Status (Assigned / Pending / Solved)</td><td class="p-2 font-mono text-slate-600 dark:text-slate-300">Solved</td></tr>
                            <tr><td class="p-2 font-mono font-bold text-indigo-600">O</td><td class="p-2 font-semibold">Feedback Received</td><td class="p-2 text-slate-500">Customer feedback if received</td><td class="p-2 font-mono text-slate-600 dark:text-slate-300">Good</td></tr>
                            <tr><td class="p-2 font-mono font-bold text-indigo-600">P</td><td class="p-2 font-semibold">Remarks</td><td class="p-2 text-slate-500">Ticket description or update remarks</td><td class="p-2 font-mono text-slate-600 dark:text-slate-300">Resolved fiber cut</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Setup Instructions & Apps Script Code Box --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40 flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider flex items-center gap-2">
                        <span>📝</span> {{ __('Google Apps Script Code (Copy & Paste)') }}
                    </h3>
                    <button type="button" @click="navigator.clipboard.writeText(document.getElementById('gas-script-box').innerText); copied = true; setTimeout(() => copied = false, 2500)"
                            class="px-2.5 py-1 rounded bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:hover:bg-indigo-900/60 text-indigo-600 dark:text-indigo-400 text-xs font-bold flex items-center gap-1 transition-colors cursor-pointer">
                        <span x-show="!copied">📋 {{ __('Copy Script') }}</span>
                        <span x-show="copied" class="text-emerald-600">✓ {{ __('Copied!') }}</span>
                    </button>
                </div>

                <div class="p-4 space-y-3">
                    <div class="space-y-1 text-xs text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/40 p-3 rounded-lg border border-slate-200 dark:border-slate-700">
                        <p class="font-bold text-slate-800 dark:text-white mb-1">🚀 4-Step Setup Guide:</p>
                        <p>1. Open your <strong>Complaint Tracking Sheet (2026)</strong> in Google Sheets.</p>
                        <p>2. In the top menu, click <strong>Extensions</strong> &rarr; <strong>Apps Script</strong>.</p>
                        <p>3. Delete any default code inside <code class="font-mono bg-white dark:bg-slate-900 px-1 py-0.5 rounded border border-slate-200 dark:border-slate-700">Code.gs</code>, paste the script below, and click <strong>Save 💾</strong>.</p>
                        <p>4. Click <strong>Deploy</strong> &rarr; <strong>New deployment</strong>:
                            <br>&bull; Select type: <strong>Web app</strong>
                            <br>&bull; Description: <code>Ticket Sync Webhook</code>
                            <br>&bull; Execute as: <strong>Me</strong>
                            <br>&bull; Who has access: <strong>Anyone</strong>
                            <br>&bull; Click <strong>Deploy</strong>, copy the <strong>Web App URL</strong>, and paste it into the Webhook URL field above!
                        </p>
                    </div>

                    <pre id="gas-script-box" class="p-3.5 bg-slate-900 text-slate-100 rounded-lg text-[11px] font-mono leading-relaxed overflow-x-auto max-h-96 selection:bg-indigo-500">/**
 * ISP Ticket System — Google Sheet Auto Sync Webhook
 * Sheet: Complaint Tracking Sheet (2026)
 * Supports monthly tabs (e.g. September, August) and columns A to P.
 */

function doPost(e) {
  var lock = LockService.getScriptLock();
  lock.tryLock(10000);
  
  try {
    var rawData = e.postData ? e.postData.contents : null;
    if (!rawData) {
      return ContentService.createTextOutput(JSON.stringify({ status: 'error', message: 'No payload received' }))
        .setMimeType(ContentService.MimeType.JSON);
    }
    
    var data = JSON.parse(rawData);
    var ss = SpreadsheetApp.getActiveSpreadsheet();
    
    // Connection test action
    if (data.action === 'test') {
      return ContentService.createTextOutput(JSON.stringify({
        status: 'success',
        message: 'Connected to spreadsheet &quot;' + ss.getName() + '&quot; successfully!'
      })).setMimeType(ContentService.MimeType.JSON);
    }
    
    // Determine Sheet Tab (e.g., &quot;September&quot;, &quot;October&quot;, or provided sheet_name)
    var sheetName = data.sheet_name;
    var sheet = null;
    if (sheetName) {
      sheet = ss.getSheetByName(sheetName);
    }
    if (!sheet) {
      var monthNames = [&quot;January&quot;, &quot;February&quot;, &quot;March&quot;, &quot;April&quot;, &quot;May&quot;, &quot;June&quot;, &quot;July&quot;, &quot;August&quot;, &quot;September&quot;, &quot;October&quot;, &quot;November&quot;, &quot;December&quot;];
      var currentMonth = monthNames[new Date().getMonth()];
      sheet = ss.getSheetByName(currentMonth);
    }
    if (!sheet) {
      sheet = ss.getSheets()[0]; // Fallback to first sheet
    }
    
    // UPDATE TICKET ACTION (When status, assignee, or remarks change)
    if (data.action === 'update') {
      var ticketId = String(data.id || data.client_id || data.ticket_key || '').trim();
      var foundRow = -1;
      var sheetsToSearch = [sheet];
      
      var allSheets = ss.getSheets();
      for (var s = 0; s &lt; allSheets.length; s++) {
        if (allSheets[s].getName() !== sheet.getName()) {
          sheetsToSearch.push(allSheets[s]);
        }
      }
      
      for (var i = 0; i &lt; sheetsToSearch.length; i++) {
        var curSheet = sheetsToSearch[i];
        var lastR = curSheet.getLastRow();
        if (lastR &lt; 2) continue;
        
        var idValues = curSheet.getRange(2, 6, lastR - 1, 1).getValues(); // Column F is ID
        for (var r = idValues.length - 1; r &gt;= 0; r--) {
          var cellVal = String(idValues[r][0]).trim();
          if (cellVal &amp;&amp; (cellVal === ticketId || cellVal === String(data.ticket_key || '').trim())) {
            foundRow = r + 2;
            sheet = curSheet;
            break;
          }
        }
        if (foundRow &gt; 0) break;
      }
      
      if (foundRow &gt; 0) {
        if (data.assigned_to) {
          sheet.getRange(foundRow, 13).setValue(data.assigned_to); // Col M: Assigned To (Technician)
        }
        if (data.status || data.current_status) {
          sheet.getRange(foundRow, 14).setValue(data.status || data.current_status); // Col N: Current Status
        }
        if (data.remarks) {
          var currentRemarks = sheet.getRange(foundRow, 16).getValue();
          sheet.getRange(foundRow, 16).setValue(data.remarks + (currentRemarks ? ' | ' + currentRemarks : '')); // Col P: Remarks
        }
        return ContentService.createTextOutput(JSON.stringify({
          status: 'success',
          message: 'Row ' + foundRow + ' updated in ' + sheet.getName(),
          row: foundRow
        })).setMimeType(ContentService.MimeType.JSON);
      }
    }
    
    // CREATE TICKET ACTION
    var lastRow = sheet.getLastRow();
    var masterSl = 1;
    var dailySl = 1;
    var targetDate = data.date || Utilities.formatDate(new Date(), &quot;Asia/Dhaka&quot;, &quot;dd MMM, yy&quot;);
    var targetTime = data.time || Utilities.formatDate(new Date(), &quot;Asia/Dhaka&quot;, &quot;hh:mm a&quot;);
    
    if (lastRow &gt;= 2) {
      // Find last Master SL from Col A
      var lastMasterVal = sheet.getRange(lastRow, 1).getValue();
      if (!isNaN(parseInt(lastMasterVal))) {
        masterSl = parseInt(lastMasterVal) + 1;
      } else {
        masterSl = lastRow;
      }
      
      // Calculate Daily SL from Col B &amp; Date from Col C
      var lastDateVal = String(sheet.getRange(lastRow, 3).getValue()).trim();
      var lastDailyVal = sheet.getRange(lastRow, 2).getValue();
      if (lastDateVal === targetDate &amp;&amp; !isNaN(parseInt(lastDailyVal))) {
        dailySl = parseInt(lastDailyVal) + 1;
      } else {
        dailySl = 1;
      }
    }
    
    var newRow = [
      masterSl,                                        // Col A (1): Master SL
      dailySl,                                         // Col B (2): Daily SL
      targetDate,                                      // Col C (3): Date
      targetTime,                                      // Col D (4): User Entry Time
      data.complaint_source || 'Phone',                // Col E (5): Complaint Source
      data.id || data.client_id || data.ticket_key || '', // Col F (6): ID
      data.name || data.client_name || data.title || '', // Col G (7): Name
      data.address || data.area || 'N/A',              // Col H (8): Address
      data.type || data.category || '',                // Col I (9): Type
      data.received_by || '',                          // Col J (10): Received By
      data.forwarded_to || '',                         // Col K (11): Forwarded To
      data.onu_power || '',                            // Col L (12): ONU Power Check IT Team
      data.assigned_to || 'Unassigned',                // Col M (13): Assigned To (Technician)
      data.status || data.current_status || 'Pending', // Col N (14): Current Status
      data.feedback || '',                             // Col O (15): Feedback Received
      data.remarks || ''                               // Col P (16): Remarks
    ];
    
    sheet.appendRow(newRow);
    
    return ContentService.createTextOutput(JSON.stringify({
      status: 'success',
      message: 'Ticket recorded successfully',
      sheet: sheet.getName(),
      row: sheet.getLastRow(),
      master_sl: masterSl,
      daily_sl: dailySl
    })).setMimeType(ContentService.MimeType.JSON);
    
  } catch (error) {
    return ContentService.createTextOutput(JSON.stringify({
      status: 'error',
      message: error.toString()
    })).setMimeType(ContentService.MimeType.JSON);
  } finally {
    lock.releaseLock();
  }
}

function doGet(e) {
  return ContentService.createTextOutput(&quot;ISP Ticket Google Sheet Sync Webhook is running active!&quot;);
}</pre>
                </div>
            </div>
        </div>
        @endif
    </div>
</x-app-layout>
