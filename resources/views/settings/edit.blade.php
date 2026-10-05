<x-app-layout>
    <div class="w-full space-y-4" x-data="{ 
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

        {{-- 🗂️ Data Management Hub — Grouped Sections for Clarity --}}
        <div x-show="showDataHub" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
             class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xs p-4 space-y-4">

            {{-- Section 1: People & Teams --}}
            <div>
                <div class="flex items-center gap-2 mb-2 pb-1.5 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-sm">👥</span>
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300">{{ __('People & Teams') }}</h3>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2.5">
                    <a href="{{ route('users.index') }}"
                       class="group relative flex items-center gap-2.5 p-3 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200/90 dark:border-slate-700/80 shadow-sm dark:shadow-lg dark:shadow-black/60 hover:border-transparent hover:bg-gradient-to-r hover:from-cyan-500 hover:to-blue-600 hover:shadow-xl hover:shadow-cyan-500/35 hover:-translate-y-0.5 active:scale-95 transition-all duration-200 cursor-pointer overflow-hidden">
                        <span class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-700/80 group-hover:bg-white/20 flex items-center justify-center text-lg flex-shrink-0 shadow-xs group-hover:scale-110 group-hover:rotate-3 transition-transform duration-200">👥</span>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-bold text-slate-800 dark:text-slate-100 group-hover:text-white truncate transition-colors">{{ __('Users') }}</div>
                            <div class="text-[10px] text-slate-500 dark:text-slate-400 group-hover:text-white/80 transition-colors">{{ \App\Models\User::count() }} {{ __('users') }}</div>
                        </div>
                    </a>
                    <a href="{{ route('teams.index') }}"
                       class="group relative flex items-center gap-2.5 p-3 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200/90 dark:border-slate-700/80 shadow-sm dark:shadow-lg dark:shadow-black/60 hover:border-transparent hover:bg-gradient-to-r hover:from-teal-500 hover:to-emerald-600 hover:shadow-xl hover:shadow-teal-500/35 hover:-translate-y-0.5 active:scale-95 transition-all duration-200 cursor-pointer overflow-hidden">
                        <span class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-700/80 group-hover:bg-white/20 flex items-center justify-center text-lg flex-shrink-0 shadow-xs group-hover:scale-110 group-hover:rotate-3 transition-transform duration-200">🏷️</span>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-bold text-slate-800 dark:text-slate-100 group-hover:text-white truncate transition-colors">{{ __('Team Tags') }}</div>
                            <div class="text-[10px] text-slate-500 dark:text-slate-400 group-hover:text-white/80 transition-colors">@if(\Schema::hasTable('teams')){{ \App\Models\Team::count() }}@else 0 @endif {{ __('teams') }}</div>
                        </div>
                    </a>
                    <a href="{{ route('technician-teams.index') }}"
                       class="group relative flex items-center gap-2.5 p-3 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200/90 dark:border-slate-700/80 shadow-sm dark:shadow-lg dark:shadow-black/60 hover:border-transparent hover:bg-gradient-to-r hover:from-indigo-500 hover:to-purple-600 hover:shadow-xl hover:shadow-indigo-500/35 hover:-translate-y-0.5 active:scale-95 transition-all duration-200 cursor-pointer overflow-hidden">
                        <span class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-700/80 group-hover:bg-white/20 flex items-center justify-center text-lg flex-shrink-0 shadow-xs group-hover:scale-110 group-hover:rotate-3 transition-transform duration-200">🛵</span>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-bold text-slate-800 dark:text-slate-100 group-hover:text-white truncate transition-colors">{{ __('Technician Teams') }}</div>
                            <div class="text-[10px] text-slate-500 dark:text-slate-400 group-hover:text-white/80 transition-colors">{{ __('Daily Squads') }}</div>
                        </div>
                    </a>
                    <a href="{{ route('squad-categories.index') }}"
                       class="group relative flex items-center gap-2.5 p-3 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200/90 dark:border-slate-700/80 shadow-sm dark:shadow-lg dark:shadow-black/60 hover:border-transparent hover:bg-gradient-to-r hover:from-amber-500 hover:to-orange-600 hover:shadow-xl hover:shadow-orange-500/35 hover:-translate-y-0.5 active:scale-95 transition-all duration-200 cursor-pointer overflow-hidden">
                        <span class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-700/80 group-hover:bg-white/20 flex items-center justify-center text-lg flex-shrink-0 shadow-xs group-hover:scale-110 group-hover:rotate-3 transition-transform duration-200">🚐</span>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-bold text-slate-800 dark:text-slate-100 group-hover:text-white truncate transition-colors">{{ __('Squad Categories') }}</div>
                            <div class="text-[10px] text-slate-500 dark:text-slate-400 group-hover:text-white/80 transition-colors">@if(\Schema::hasTable('squad_categories')){{ \App\Models\SquadCategory::count() }}@else 0 @endif {{ __('types') }}</div>
                        </div>
                    </a>
                    <a href="{{ route('roster.index') }}"
                       class="group relative flex items-center gap-2.5 p-3 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200/90 dark:border-slate-700/80 shadow-sm dark:shadow-lg dark:shadow-black/60 hover:border-transparent hover:bg-gradient-to-r hover:from-purple-500 hover:to-pink-600 hover:shadow-xl hover:shadow-purple-500/35 hover:-translate-y-0.5 active:scale-95 transition-all duration-200 cursor-pointer overflow-hidden">
                        <span class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-700/80 group-hover:bg-white/20 flex items-center justify-center text-lg flex-shrink-0 shadow-xs group-hover:scale-110 group-hover:rotate-3 transition-transform duration-200">📅</span>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-bold text-slate-800 dark:text-slate-100 group-hover:text-white truncate transition-colors">{{ __('Shift Roster') }}</div>
                            <div class="text-[10px] text-slate-500 dark:text-slate-400 group-hover:text-white/80 transition-colors">{{ __('Schedule') }}</div>
                        </div>
                    </a>
                </div>
            </div>

            {{-- Section 2: Ticket System --}}
            <div>
                <div class="flex items-center gap-2 mb-2 pb-1.5 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-sm">🎫</span>
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300">{{ __('Ticket System') }}</h3>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2.5">
                    <a href="{{ route('ticket-categories.index') }}"
                       class="group relative flex items-center gap-2.5 p-3 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200/90 dark:border-slate-700/80 shadow-sm dark:shadow-lg dark:shadow-black/60 hover:border-transparent hover:bg-gradient-to-r hover:from-indigo-500 hover:to-blue-600 hover:shadow-xl hover:shadow-indigo-500/35 hover:-translate-y-0.5 active:scale-95 transition-all duration-200 cursor-pointer overflow-hidden">
                        <span class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-700/80 group-hover:bg-white/20 flex items-center justify-center text-lg flex-shrink-0 shadow-xs group-hover:scale-110 group-hover:rotate-3 transition-transform duration-200">🏷️</span>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-bold text-slate-800 dark:text-slate-100 group-hover:text-white truncate transition-colors">{{ __('Categories') }}</div>
                            <div class="text-[10px] text-slate-500 dark:text-slate-400 group-hover:text-white/80 transition-colors">{{ \App\Models\TicketCategory::count() }} {{ __('types') }}</div>
                        </div>
                    </a>
                    <a href="{{ route('labels.index') }}"
                       class="group relative flex items-center gap-2.5 p-3 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200/90 dark:border-slate-700/80 shadow-sm dark:shadow-lg dark:shadow-black/60 hover:border-transparent hover:bg-gradient-to-r hover:from-amber-500 hover:to-rose-600 hover:shadow-xl hover:shadow-amber-500/35 hover:-translate-y-0.5 active:scale-95 transition-all duration-200 cursor-pointer overflow-hidden">
                        <span class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-700/80 group-hover:bg-white/20 flex items-center justify-center text-lg flex-shrink-0 shadow-xs group-hover:scale-110 group-hover:rotate-3 transition-transform duration-200">🔖</span>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-bold text-slate-800 dark:text-slate-100 group-hover:text-white truncate transition-colors">{{ __('Labels') }}</div>
                            <div class="text-[10px] text-slate-500 dark:text-slate-400 group-hover:text-white/80 transition-colors">{{ \App\Models\Label::count() }} {{ __('labels') }}</div>
                        </div>
                    </a>
                    <a href="{{ route('sla-policies.index') }}"
                       class="group relative flex items-center gap-2.5 p-3 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200/90 dark:border-slate-700/80 shadow-sm dark:shadow-lg dark:shadow-black/60 hover:border-transparent hover:bg-gradient-to-r hover:from-rose-500 hover:to-red-600 hover:shadow-xl hover:shadow-rose-500/35 hover:-translate-y-0.5 active:scale-95 transition-all duration-200 cursor-pointer overflow-hidden">
                        <span class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-700/80 group-hover:bg-white/20 flex items-center justify-center text-lg flex-shrink-0 shadow-xs group-hover:scale-110 group-hover:rotate-3 transition-transform duration-200">⏱️</span>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-bold text-slate-800 dark:text-slate-100 group-hover:text-white truncate transition-colors">{{ __('SLA Policies') }}</div>
                            <div class="text-[10px] text-slate-500 dark:text-slate-400 group-hover:text-white/80 transition-colors">{{ \App\Models\SlaPolicy::count() }} {{ __('policies') }}</div>
                        </div>
                    </a>
                    <a href="{{ route('canned-responses.index') }}"
                       class="group relative flex items-center gap-2.5 p-3 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200/90 dark:border-slate-700/80 shadow-sm dark:shadow-lg dark:shadow-black/60 hover:border-transparent hover:bg-gradient-to-r hover:from-sky-500 hover:to-cyan-600 hover:shadow-xl hover:shadow-sky-500/35 hover:-translate-y-0.5 active:scale-95 transition-all duration-200 cursor-pointer overflow-hidden">
                        <span class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-700/80 group-hover:bg-white/20 flex items-center justify-center text-lg flex-shrink-0 shadow-xs group-hover:scale-110 group-hover:rotate-3 transition-transform duration-200">💬</span>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-bold text-slate-800 dark:text-slate-100 group-hover:text-white truncate transition-colors">{{ __('Canned Replies') }}</div>
                            <div class="text-[10px] text-slate-500 dark:text-slate-400 group-hover:text-white/80 transition-colors">{{ __('Templates') }}</div>
                        </div>
                    </a>
                    <a href="{{ route('activity-logs.index') }}"
                       class="group relative flex items-center gap-2.5 p-3 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200/90 dark:border-slate-700/80 shadow-sm dark:shadow-lg dark:shadow-black/60 hover:border-transparent hover:bg-gradient-to-r hover:from-pink-500 hover:to-rose-600 hover:shadow-xl hover:shadow-pink-500/35 hover:-translate-y-0.5 active:scale-95 transition-all duration-200 cursor-pointer overflow-hidden">
                        <span class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-700/80 group-hover:bg-white/20 flex items-center justify-center text-lg flex-shrink-0 shadow-xs group-hover:scale-110 group-hover:rotate-3 transition-transform duration-200">📋</span>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-bold text-slate-800 dark:text-slate-100 group-hover:text-white truncate transition-colors">{{ __('Activity Log') }}</div>
                            <div class="text-[10px] text-slate-500 dark:text-slate-400 group-hover:text-white/80 transition-colors">{{ __('Audit Trail') }}</div>
                        </div>
                    </a>
                </div>
            </div>

            {{-- Section 3: Locations & Knowledge --}}
            <div>
                <div class="flex items-center gap-2 mb-2 pb-1.5 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-sm">🌍</span>
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300">{{ __('Locations & Knowledge') }}</h3>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2.5">
                    <a href="{{ route('areas.index') }}"
                       class="group relative flex items-center gap-2.5 p-3 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200/90 dark:border-slate-700/80 shadow-sm dark:shadow-lg dark:shadow-black/60 hover:border-transparent hover:bg-gradient-to-r hover:from-emerald-500 hover:to-teal-600 hover:shadow-xl hover:shadow-emerald-500/35 hover:-translate-y-0.5 active:scale-95 transition-all duration-200 cursor-pointer overflow-hidden">
                        <span class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-700/80 group-hover:bg-white/20 flex items-center justify-center text-lg flex-shrink-0 shadow-xs group-hover:scale-110 group-hover:rotate-3 transition-transform duration-200">📍</span>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-bold text-slate-800 dark:text-slate-100 group-hover:text-white truncate transition-colors">{{ __('Areas') }}</div>
                            <div class="text-[10px] text-slate-500 dark:text-slate-400 group-hover:text-white/80 transition-colors">@if(\Schema::hasTable('areas')){{ \App\Models\Area::count() }}@else 0 @endif {{ __('zones') }}</div>
                        </div>
                    </a>
                    <a href="{{ route('pop-offices.index') }}"
                       class="group relative flex items-center gap-2.5 p-3 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200/90 dark:border-slate-700/80 shadow-sm dark:shadow-lg dark:shadow-black/60 hover:border-transparent hover:bg-gradient-to-r hover:from-sky-500 hover:to-indigo-600 hover:shadow-xl hover:shadow-sky-500/35 hover:-translate-y-0.5 active:scale-95 transition-all duration-200 cursor-pointer overflow-hidden">
                        <span class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-700/80 group-hover:bg-white/20 flex items-center justify-center text-lg flex-shrink-0 shadow-xs group-hover:scale-110 group-hover:rotate-3 transition-transform duration-200">🏢</span>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-bold text-slate-800 dark:text-slate-100 group-hover:text-white truncate transition-colors">{{ __('POP Offices') }}</div>
                            <div class="text-[10px] text-slate-500 dark:text-slate-400 group-hover:text-white/80 transition-colors">{{ \App\Models\PopOffice::count() }} {{ __('offices') }}</div>
                        </div>
                    </a>
                    <a href="{{ route('kb-categories.index') }}"
                       class="group relative flex items-center gap-2.5 p-3 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200/90 dark:border-slate-700/80 shadow-sm dark:shadow-lg dark:shadow-black/60 hover:border-transparent hover:bg-gradient-to-r hover:from-violet-500 hover:to-purple-600 hover:shadow-xl hover:shadow-violet-500/35 hover:-translate-y-0.5 active:scale-95 transition-all duration-200 cursor-pointer overflow-hidden">
                        <span class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-700/80 group-hover:bg-white/20 flex items-center justify-center text-lg flex-shrink-0 shadow-xs group-hover:scale-110 group-hover:rotate-3 transition-transform duration-200">📚</span>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-bold text-slate-800 dark:text-slate-100 group-hover:text-white truncate transition-colors">{{ __('KB Categories') }}</div>
                            <div class="text-[10px] text-slate-500 dark:text-slate-400 group-hover:text-white/80 transition-colors">{{ __('Articles') }}</div>
                        </div>
                    </a>
                    <a href="{{ route('map.index') }}"
                       class="group relative flex items-center gap-2.5 p-3 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200/90 dark:border-slate-700/80 shadow-sm dark:shadow-lg dark:shadow-black/60 hover:border-transparent hover:bg-gradient-to-r hover:from-blue-500 hover:to-indigo-600 hover:shadow-xl hover:shadow-blue-500/35 hover:-translate-y-0.5 active:scale-95 transition-all duration-200 cursor-pointer overflow-hidden">
                        <span class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-700/80 group-hover:bg-white/20 flex items-center justify-center text-lg flex-shrink-0 shadow-xs group-hover:scale-110 group-hover:rotate-3 transition-transform duration-200">🗺️</span>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-bold text-slate-800 dark:text-slate-100 group-hover:text-white truncate transition-colors">{{ __('Live Staff Map') }}</div>
                            <div class="text-[10px] text-slate-500 dark:text-slate-400 group-hover:text-white/80 transition-colors">{{ __('GPS Tracking') }}</div>
                        </div>
                    </a>
                </div>
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
                <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/60 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
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
                            <span>🌐 {{ __('Global Notice (For Everyone)') }}</span>
                            <span class="w-2 h-2 rounded-full" :class="globalActive ? 'bg-emerald-500 animate-pulse' : 'bg-slate-300 dark:bg-slate-600'"></span>
                        </button>

                        <button type="button" @click="noticeChannel = 'reseller'"
                                :class="noticeChannel === 'reseller' ? 'bg-amber-500 text-white shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 font-semibold'"
                                class="px-3 py-1.5 rounded-lg text-xs transition-all cursor-pointer flex items-center gap-1.5">
                            <span>🤝 {{ __('Reseller Notice (For Resellers Only)') }}</span>
                            <span class="w-2 h-2 rounded-full" :class="resellerActive ? 'bg-amber-300 animate-pulse' : 'bg-slate-300 dark:bg-slate-600'"></span>
                        </button>

                        <button type="button" @click="noticeChannel = 'noc'"
                                :class="noticeChannel === 'noc' ? 'bg-indigo-600 text-white shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 font-semibold'"
                                class="px-3 py-1.5 rounded-lg text-xs transition-all cursor-pointer flex items-center gap-1.5">
                            <span>🛠️ {{ __('NOC Notice (For NOC Team Only)') }}</span>
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

                        {{-- Spacious Bilingual Cards --}}
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            {{-- English --}}
                            <div class="p-4 sm:p-5 bg-slate-50/80 dark:bg-slate-800/50 rounded-2xl border border-slate-200 dark:border-slate-700/80 space-y-3.5 shadow-xs">
                                <div class="flex items-center justify-between pb-1 border-b border-slate-200/60 dark:border-slate-700/60">
                                    <span class="text-sm font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                                        <span class="text-base">🇬🇧</span> {{ __('English Notice') }}
                                    </span>
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded-md bg-slate-200/70 dark:bg-slate-700 text-slate-600 dark:text-slate-300">English Language</span>
                                </div>
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-1.5">{{ __('Badge') }}</label>
                                        <input type="text" name="notice_badge_en" value="{{ old('notice_badge_en', $noticeBadgeEn ?? 'GLOBAL NOTICE') }}" maxlength="30"
                                               class="w-full px-3.5 py-2.5 text-sm font-bold uppercase border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-xs">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-1.5">{{ __('Notice Message') }}</label>
                                        <textarea name="notice_text_en" rows="5" placeholder="{{ __('Notice text in English...') }}"
                                                  class="w-full px-3.5 py-3 text-sm border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-800 dark:text-slate-100 leading-relaxed min-h-[130px] focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-xs resize-y">{{ old('notice_text_en', $noticeTextEn ?? '') }}</textarea>
                                    </div>
                                </div>
                            </div>

                            {{-- Bangla --}}
                            <div class="p-4 sm:p-5 bg-emerald-50/40 dark:bg-emerald-950/20 rounded-2xl border border-emerald-200/70 dark:border-emerald-900/50 space-y-3.5 shadow-xs">
                                <div class="flex items-center justify-between pb-1 border-b border-emerald-200/60 dark:border-emerald-900/60">
                                    <span class="text-sm font-bold text-emerald-800 dark:text-emerald-300 flex items-center gap-2">
                                        <span class="text-base">🇧🇩</span> {{ __('Bangla Notice') }}
                                    </span>
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded-md bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300">বাংলা ভাষা</span>
                                </div>
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-bold text-emerald-800 dark:text-emerald-300 uppercase tracking-wider mb-1.5">{{ __('ব্যাজ') }}</label>
                                        <input type="text" name="notice_badge_bn" value="{{ old('notice_badge_bn', $noticeBadgeBn ?? 'সাধারণ নোটিশ') }}" maxlength="30"
                                               class="w-full px-3.5 py-2.5 text-sm font-bold border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all shadow-xs">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-emerald-800 dark:text-emerald-300 uppercase tracking-wider mb-1.5">{{ __('নোটিশ বার্তা') }}</label>
                                        <textarea name="notice_text_bn" rows="5" placeholder="{{ __('বাংলায় নোটিশ লিখুন...') }}"
                                                  class="w-full px-3.5 py-3 text-sm border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-800 dark:text-slate-100 leading-relaxed min-h-[130px] focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all shadow-xs resize-y">{{ old('notice_text_bn', $noticeTextBn ?? '') }}</textarea>
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

                        {{-- Spacious Bilingual Cards --}}
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            {{-- English --}}
                            <div class="p-4 sm:p-5 bg-slate-50/80 dark:bg-slate-800/50 rounded-2xl border border-slate-200 dark:border-slate-700/80 space-y-3.5 shadow-xs">
                                <div class="flex items-center justify-between pb-1 border-b border-slate-200/60 dark:border-slate-700/60">
                                    <span class="text-sm font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                                        <span class="text-base">🇬🇧</span> {{ __('English (Reseller)') }}
                                    </span>
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded-md bg-slate-200/70 dark:bg-slate-700 text-slate-600 dark:text-slate-300">English Language</span>
                                </div>
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-1.5">{{ __('Badge') }}</label>
                                        <input type="text" name="notice_reseller_badge_en" value="{{ old('notice_reseller_badge_en', $noticeResellerBadgeEn ?? 'RESELLER ALERT') }}" maxlength="30"
                                               class="w-full px-3.5 py-2.5 text-sm font-bold uppercase border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all shadow-xs">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-1.5">{{ __('Notice Message') }}</label>
                                        <textarea name="notice_reseller_text_en" rows="5" placeholder="{{ __('Reseller notice in English...') }}"
                                                  class="w-full px-3.5 py-3 text-sm border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-800 dark:text-slate-100 leading-relaxed min-h-[130px] focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all shadow-xs resize-y">{{ old('notice_reseller_text_en', $noticeResellerTextEn ?? '') }}</textarea>
                                    </div>
                                </div>
                            </div>

                            {{-- Bangla --}}
                            <div class="p-4 sm:p-5 bg-amber-50/40 dark:bg-amber-950/20 rounded-2xl border border-amber-200/70 dark:border-amber-900/50 space-y-3.5 shadow-xs">
                                <div class="flex items-center justify-between pb-1 border-b border-amber-200/60 dark:border-amber-900/60">
                                    <span class="text-sm font-bold text-amber-800 dark:text-amber-300 flex items-center gap-2">
                                        <span class="text-base">🇧🇩</span> {{ __('Bangla (Reseller)') }}
                                    </span>
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded-md bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300">বাংলা ভাষা</span>
                                </div>
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-bold text-amber-800 dark:text-amber-300 uppercase tracking-wider mb-1.5">{{ __('ব্যাজ') }}</label>
                                        <input type="text" name="notice_reseller_badge_bn" value="{{ old('notice_reseller_badge_bn', $noticeResellerBadgeBn ?? 'রিসেলার নোটিশ') }}" maxlength="30"
                                               class="w-full px-3.5 py-2.5 text-sm font-bold border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all shadow-xs">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-amber-800 dark:text-amber-300 uppercase tracking-wider mb-1.5">{{ __('নোটিশ বার্তা') }}</label>
                                        <textarea name="notice_reseller_text_bn" rows="5" placeholder="{{ __('বাংলায় রিসেলার নোটিশ লিখুন...') }}"
                                                  class="w-full px-3.5 py-3 text-sm border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-800 dark:text-slate-100 leading-relaxed min-h-[130px] focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all shadow-xs resize-y">{{ old('notice_reseller_text_bn', $noticeResellerTextBn ?? '') }}</textarea>
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

                        {{-- Spacious Bilingual Cards --}}
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            {{-- English --}}
                            <div class="p-4 sm:p-5 bg-slate-50/80 dark:bg-slate-800/50 rounded-2xl border border-slate-200 dark:border-slate-700/80 space-y-3.5 shadow-xs">
                                <div class="flex items-center justify-between pb-1 border-b border-slate-200/60 dark:border-slate-700/60">
                                    <span class="text-sm font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                                        <span class="text-base">🇬🇧</span> {{ __('English (NOC)') }}
                                    </span>
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded-md bg-slate-200/70 dark:bg-slate-700 text-slate-600 dark:text-slate-300">English Language</span>
                                </div>
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-1.5">{{ __('Badge') }}</label>
                                        <input type="text" name="notice_noc_badge_en" value="{{ old('notice_noc_badge_en', $noticeNocBadgeEn ?? 'NOC DISPATCH') }}" maxlength="30"
                                               class="w-full px-3.5 py-2.5 text-sm font-bold uppercase border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-xs">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-1.5">{{ __('Notice Message') }}</label>
                                        <textarea name="notice_noc_text_en" rows="5" placeholder="{{ __('NOC notice in English...') }}"
                                                  class="w-full px-3.5 py-3 text-sm border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-800 dark:text-slate-100 leading-relaxed min-h-[130px] focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-xs resize-y">{{ old('notice_noc_text_en', $noticeNocTextEn ?? '') }}</textarea>
                                    </div>
                                </div>
                            </div>

                            {{-- Bangla --}}
                            <div class="p-4 sm:p-5 bg-indigo-50/40 dark:bg-indigo-950/20 rounded-2xl border border-indigo-200/70 dark:border-indigo-900/50 space-y-3.5 shadow-xs">
                                <div class="flex items-center justify-between pb-1 border-b border-indigo-200/60 dark:border-indigo-900/60">
                                    <span class="text-sm font-bold text-indigo-800 dark:text-indigo-300 flex items-center gap-2">
                                        <span class="text-base">🇧🇩</span> {{ __('Bangla (NOC)') }}
                                    </span>
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded-md bg-indigo-100 dark:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300">বাংলা ভাষা</span>
                                </div>
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-bold text-indigo-800 dark:text-indigo-300 uppercase tracking-wider mb-1.5">{{ __('ব্যাজ') }}</label>
                                        <input type="text" name="notice_noc_badge_bn" value="{{ old('notice_noc_badge_bn', $noticeNocBadgeBn ?? 'এনওসি নোটিশ') }}" maxlength="30"
                                               class="w-full px-3.5 py-2.5 text-sm font-bold border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-xs">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-indigo-800 dark:text-indigo-300 uppercase tracking-wider mb-1.5">{{ __('নোটিশ বার্তা') }}</label>
                                        <textarea name="notice_noc_text_bn" rows="5" placeholder="{{ __('বাংলায় এনওসি নোটিশ লিখুন...') }}"
                                                  class="w-full px-3.5 py-3 text-sm border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-800 dark:text-slate-100 leading-relaxed min-h-[130px] focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-xs resize-y">{{ old('notice_noc_text_bn', $noticeNocTextBn ?? '') }}</textarea>
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
                            <div class="w-28 h-20 rounded-lg border border-dashed border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 p-1.5 flex items-center justify-center shrink-0 overflow-hidden">
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
                            <div class="w-28 h-20 rounded-lg border border-dashed border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 p-1.5 flex items-center justify-center shrink-0">
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
                        <div class="p-2.5 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/60">
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

                        <div class="p-2.5 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/60">
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
                    <span class="text-[10px] text-slate-400">{{ __('Columns A to Q (17 Columns)') }}</span>
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
                            <tr><td class="p-2 font-mono font-bold text-indigo-600">A</td><td class="p-2 font-semibold">Master SL</td><td class="p-2 text-slate-500">Auto calculated increment (+1)</td><td class="p-2 font-mono text-slate-600 dark:text-slate-300">76</td></tr>
                            <tr><td class="p-2 font-mono font-bold text-indigo-600">B</td><td class="p-2 font-semibold">Daily SL</td><td class="p-2 text-slate-500">Daily ticket serial number (+1)</td><td class="p-2 font-mono text-slate-600 dark:text-slate-300">1</td></tr>
                            <tr><td class="p-2 font-mono font-bold text-indigo-600">C</td><td class="p-2 font-semibold">Date</td><td class="p-2 text-slate-500">Ticket creation date</td><td class="p-2 font-mono text-slate-600 dark:text-slate-300">27 Sep, 26</td></tr>
                            <tr><td class="p-2 font-mono font-bold text-indigo-600">D</td><td class="p-2 font-semibold">User Entry Time</td><td class="p-2 text-slate-500">Entry time (12-hour format)</td><td class="p-2 font-mono text-slate-600 dark:text-slate-300">01:08 PM</td></tr>
                            <tr><td class="p-2 font-mono font-bold text-indigo-600">E</td><td class="p-2 font-semibold">Complaint Source</td><td class="p-2 text-slate-500">Source: Phone, Office, Online, WhatsApp</td><td class="p-2 font-mono text-slate-600 dark:text-slate-300">Phone</td></tr>
                            <tr class="bg-indigo-50/70 dark:bg-indigo-950/40"><td class="p-2 font-mono font-bold text-indigo-600">F</td><td class="p-2 font-bold text-indigo-700 dark:text-indigo-300">ID (Website Ticket ID)</td><td class="p-2 text-indigo-600 dark:text-indigo-400 font-medium">Website Ticket Key / ID (Matches Column F)</td><td class="p-2 font-mono font-bold text-indigo-700 dark:text-indigo-300">260927009</td></tr>
                            <tr><td class="p-2 font-mono font-bold text-indigo-600">G</td><td class="p-2 font-semibold">Client ID</td><td class="p-2 text-slate-500">Customer's Account / Client ID</td><td class="p-2 font-mono text-slate-600 dark:text-slate-300">154662</td></tr>
                            <tr><td class="p-2 font-mono font-bold text-indigo-600">H</td><td class="p-2 font-semibold">Name</td><td class="p-2 text-slate-500">Client Name</td><td class="p-2 font-mono text-slate-600 dark:text-slate-300">Md. Rabby</td></tr>
                            <tr><td class="p-2 font-mono font-bold text-indigo-600">I</td><td class="p-2 font-semibold">Address</td><td class="p-2 text-slate-500">Area / Address</td><td class="p-2 font-mono text-slate-600 dark:text-slate-300">Shadhupara</td></tr>
                            <tr><td class="p-2 font-mono font-bold text-indigo-600">J</td><td class="p-2 font-semibold">Type</td><td class="p-2 text-slate-500">Ticket Category Name (Dropdown)</td><td class="p-2 font-mono text-slate-600 dark:text-slate-300">Router re-configure</td></tr>
                            <tr><td class="p-2 font-mono font-bold text-indigo-600">K</td><td class="p-2 font-semibold">Received By</td><td class="p-2 text-slate-500">Creator / Received By</td><td class="p-2 font-mono text-slate-600 dark:text-slate-300">Rayhan</td></tr>
                            <tr><td class="p-2 font-mono font-bold text-indigo-600">L</td><td class="p-2 font-semibold">Forwarded To</td><td class="p-2 text-slate-500">Forwarded department or team</td><td class="p-2 font-mono text-slate-600 dark:text-slate-300">NOC Team</td></tr>
                            <tr><td class="p-2 font-mono font-bold text-indigo-600">M</td><td class="p-2 font-semibold">ONU Power Check IT Team</td><td class="p-2 text-slate-500">ONU Optical Power dBm</td><td class="p-2 font-mono text-slate-600 dark:text-slate-300">-23.56</td></tr>
                            <tr><td class="p-2 font-mono font-bold text-indigo-600">N</td><td class="p-2 font-semibold">Assigned To (Technician)</td><td class="p-2 text-slate-500">Assigned technician name</td><td class="p-2 font-mono text-slate-600 dark:text-slate-300">Rayhan Rabby</td></tr>
                            <tr class="bg-emerald-50/70 dark:bg-emerald-950/40"><td class="p-2 font-mono font-bold text-emerald-600">O</td><td class="p-2 font-bold text-emerald-700 dark:text-emerald-300">Current Status</td><td class="p-2 text-emerald-600 dark:text-emerald-400 font-medium">Live Status (Assigned / Pending / Processing / Solved)</td><td class="p-2 font-mono font-bold text-emerald-700 dark:text-emerald-300">Solved</td></tr>
                            <tr><td class="p-2 font-mono font-bold text-indigo-600">P</td><td class="p-2 font-semibold">Feedback Received!</td><td class="p-2 text-slate-500">Feedback status or solver info</td><td class="p-2 font-mono text-slate-600 dark:text-slate-300">Not Yet</td></tr>
                            <tr><td class="p-2 font-mono font-bold text-indigo-600">Q</td><td class="p-2 font-semibold">Remarks</td><td class="p-2 text-slate-500">Ticket description or resolution remarks</td><td class="p-2 font-mono text-slate-600 dark:text-slate-300">Resolved fiber cut</td></tr>
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
                        <p>3. Delete any previous code inside <code class="font-mono bg-white dark:bg-slate-900 px-1 py-0.5 rounded border border-slate-200 dark:border-slate-700">Code.gs</code>, paste the complete script below, and click <strong>Save 💾</strong>.</p>
                        <p>4. Click <strong>Deploy</strong> &rarr; <strong>Manage deployments</strong> &rarr; <strong>Edit</strong> &rarr; <strong>New version</strong> (or <strong>New deployment</strong> > Web app):
                            <br>&bull; Select type: <strong>Web app</strong>
                            <br>&bull; Execute as: <strong>Me</strong>
                            <br>&bull; Who has access: <strong>Anyone</strong>
                            <br>&bull; Click <strong>Deploy</strong>, copy the <strong>Web App URL</strong>, and paste it into the Webhook URL field above!
                        </p>
                    </div>

                    <pre id="gas-script-box" class="p-3.5 bg-slate-900 text-slate-100 rounded-lg text-[11px] font-mono leading-relaxed overflow-x-auto max-h-96 selection:bg-indigo-500">/**
 * COMPLAINT TRACKER 2026 - PRODUCTION VERSION 5.0 (WITH REALTIME WEB & APP SYNC)
 *
 * Structure:
 *   Dashboard
 *   Template
 *   January ... December (created only when needed)
 *
 * Column Layout (17 Columns):
 *   A: Master SL | B: Daily SL | C: Date | D: Time | E: Source
 *   F: ID (Website Ticket ID: 260927009)
 *   G: Name | H: Address | I: Contact / Phone | J: Type (Complaint Type)
 *   K: Received By | L: Forwarded To | M: ONU Power Check IT Team
 *   N: Assigned To (Technician) | O: Current Status (Assigned / Pending / Processing / Solved)
 *   P: Feedback Received | Q: Remarks
 */

const CT = Object.freeze({
  VERSION: '5.0.0',

  DASHBOARD_SHEET: 'Dashboard',
  TEMPLATE_SHEET: 'Template',

  HEADER_ROWS: 2,
  DATA_START_ROW: 3,
  DATA_COLUMNS: 17,       // A:Q (17 Columns)
  ROWS_PER_DAY: 100,      // Daily prepared fresh rows block

  MASTER_SERIAL_COLUMN: 1, // A: Master SL
  DAILY_SERIAL_COLUMN: 2,  // B: Daily SL
  DATE_COLUMN: 3,          // C: Date
  TIME_COLUMN: 4,          // D: Time
  SOURCE_COLUMN: 5,        // E: Source
  TICKET_KEY_COLUMN: 6,    // F: Website Ticket ID (e.g. 260927009)
  CLIENT_ID_COLUMN: 7,     // G: Client ID (Customer ID e.g. 154662)
  NAME_COLUMN: 8,          // H: Name (Client Name)
  ADDRESS_COLUMN: 9,       // I: Address (Area / Address)
  TYPE_COLUMN: 10,         // J: Type (Complaint Type)
  CREATOR_COLUMN: 11,      // K: Received By
  FORWARDED_COLUMN: 12,    // L: Forwarded To
  ONU_POWER_COLUMN: 13,    // M: ONU Power Check IT Team
  ASSIGNED_COLUMN: 14,     // N: Assigned To (Technician)
  STATUS_COLUMN: 15,       // O: Current Status (Assigned / Pending / Processing / Solved)
  FEEDBACK_COLUMN: 16,     // P: Feedback Received (Not Yet)
  SOLVED_TIME_COLUMN: 16,  // P
  REMARKS_COLUMN: 17,      // Q: Remarks

  SOLVED_STATUS: 'solved',
  DATE_FORMAT: 'd mmm, yy',
  TIME_FORMAT: 'hh:mm AM/PM',

  PROP_SPREADSHEET_ID: 'CT_V3_SPREADSHEET_ID',
  PROP_LAST_PREPARED_DATE: 'CT_V3_LAST_PREPARED_DATE',
  PROP_PREPARED_SHEET_ID: 'CT_V3_PREPARED_SHEET_ID',
  PROP_PREPARED_ROW_COUNT: 'CT_V3_PREPARED_ROW_COUNT'
});

const CT_MONTHS = Object.freeze([
  'January', 'February', 'March', 'April', 'May', 'June',
  'July', 'August', 'September', 'October', 'November', 'December'
]);

const CT_ROW_COLORS = Object.freeze([
  '#FFE5CC', // Light Orange
  '#E2EFDA', // Light Green
  '#FFF2CC', // Light Yellow
  '#DDEBF7', // Light Blue
  '#FCE4D6'  // Light Peach
]);

/**
 * WEBHOOK ENDPOINTS (doGet & doPost)
 */
function doGet(e) {
  return ContentService.createTextOutput(JSON.stringify({
    status: 'success',
    message: 'Complaint Tracker Webhook is online and active! (v' + CT.VERSION + ')'
  })).setMimeType(ContentService.MimeType.JSON);
}

function doPost(e) {
  var lock = LockService.getDocumentLock();
  lock.waitLock(30000);

  try {
    if (!e || !e.postData || !e.postData.contents) {
      return ContentService.createTextOutput(JSON.stringify({
        status: 'error',
        message: 'No POST data received.'
      })).setMimeType(ContentService.MimeType.JSON);
    }

    var payload;
    try {
      payload = JSON.parse(e.postData.contents);
    } catch (parseErr) {
      return ContentService.createTextOutput(JSON.stringify({
        status: 'error',
        message: 'Invalid JSON payload: ' + parseErr.message
      })).setMimeType(ContentService.MimeType.JSON);
    }

    var action = String(payload.action || 'create').toLowerCase();
    var ss = getSpreadsheet_();
    var timezone = ss.getSpreadsheetTimeZone();
    var now = new Date();

    if (action === 'test') {
      return ContentService.createTextOutput(JSON.stringify({
        status: 'success',
        message: 'Connected to spreadsheet "' + ss.getName() + '" successfully!',
        version: CT.VERSION,
        spreadsheet: ss.getName()
      })).setMimeType(ContentService.MimeType.JSON);
    }

    if (action === 'create') {
      return handleWebhookCreate_(ss, payload, now, timezone);
    }

    if (action === 'update' || action === 'status_change') {
      return handleWebhookUpdate_(ss, payload, now, timezone);
    }

    return ContentService.createTextOutput(JSON.stringify({
      status: 'error',
      message: 'Unknown action: ' + action
    })).setMimeType(ContentService.MimeType.JSON);

  } catch (err) {
    return ContentService.createTextOutput(JSON.stringify({
      status: 'error',
      message: err.toString(),
      stack: err.stack
    })).setMimeType(ContentService.MimeType.JSON);
  } finally {
    lock.releaseLock();
  }
}

/**
 * Safely set value on cell, clearing validations if value is rejected.
 */
function safeSetCellValue_(sheet, row, col, val) {
  if (val === undefined || val === null) return;
  var cell = sheet.getRange(row, col);
  try {
    cell.setValue(val);
  } catch (err) {
    try {
      cell.clearDataValidations();
      cell.setValue(val);
    } catch (e2) {}
  }
}

/**
 * Webhook handler to insert a new ticket record into the month sheet.
 */
function handleWebhookCreate_(ss, payload, now, timezone) {
  var monthName = payload.sheet_name || getCurrentMonthName_(ss);
  var monthSheet = getOrCreateMonthSheet_(ss, monthName);
  var templateSheet = ss.getSheetByName(CT.TEMPLATE_SHEET);

  // Find first blank row among prepared rows
  var targetRow = -1;
  var lastRow = monthSheet.getLastRow();

  if (lastRow >= CT.DATA_START_ROW) {
    var checkCount = lastRow - CT.DATA_START_ROW + 1;
    var currentValues = monthSheet.getRange(CT.DATA_START_ROW, 1, checkCount, CT.DATA_COLUMNS).getValues();

    for (var i = 0; i < currentValues.length; i++) {
      if (!hasMeaningfulRecordData_(currentValues[i])) {
        targetRow = CT.DATA_START_ROW + i;
        break;
      }
    }
  }

  // If all rows have data or none prepared, insert a new row
  if (targetRow === -1) {
    targetRow = Math.max(CT.DATA_START_ROW, lastRow + 1);
    monthSheet.insertRowBefore(targetRow);
    if (templateSheet) {
      applyTemplateRowToRange_(templateSheet, monthSheet, targetRow, 1);
    }
  }

  // Clear data validations so custom technician names / categories don't fail
  try {
    monthSheet.getRange(targetRow, 1, 1, CT.DATA_COLUMNS).clearDataValidations();
  } catch (eValid) {}

  var ticketKey = String(payload.ticket_key || payload.ticket_id || payload.id || '').trim();
  var customerClientId = String(payload.client_id || payload.customer_id || payload.contact || '').trim();
  var clientName = String(payload.client_name || payload.name || '').trim();
  var complaintSource = String(payload.complaint_source || 'Phone').trim();
  var address = String(payload.address || payload.area || '').trim();
  var complaintType = String(payload.type || payload.category || '').trim();
  var createdBy = String(payload.received_by || payload.created_by || 'Rayhan').trim();
  var forwardedTo = String(payload.forwarded_to || '').trim();
  var onuPower = String(payload.onu_power || '').trim();
  var assignedTo = String(payload.assigned_to || '').trim();
  var status = String(payload.status || payload.current_status || 'Assigned').trim();
  var feedback = String(payload.feedback || 'Not Yet').trim();
  var remarks = String(payload.remarks || payload.description || '').trim();

  // Write 17 columns: A to Q (Col F = Ticket ID, Col O = Current Status)
  var rowValues = [
    '',             // Col A (1): Master SL (computed by updateSerialsAndColors)
    '',             // Col B (2): Daily SL (computed by updateSerialsAndColors)
    now,            // Col C (3): Date
    now,            // Col D (4): User Entry Time
    complaintSource,// Col E (5): Complaint Source (Phone)
    ticketKey,      // Col F (6): Website Ticket ID (e.g. 260927009)
    customerClientId, // Col G (7): Client ID (e.g. 154662)
    clientName,     // Col H (8): Name (e.g. Md. Rabby)
    address,        // Col I (9): Address (e.g. Shadhupara)
    complaintType,  // Col J (10): Type (e.g. Router re-configure)
    createdBy,      // Col K (11): Received By (e.g. Rayhan)
    forwardedTo,    // Col L (12): Forwarded To
    onuPower,       // Col M (13): ONU Power Check IT Team
    assignedTo,     // Col N (14): Assigned To (Technician) (e.g. Rayhan Rabby)
    status,         // Col O (15): Current Status (Assigned / Pending / Processing / Solved)
    feedback,       // Col P (16): Feedback Received! (Not Yet)
    remarks         // Col Q (17): Remarks
  ];

  for (var c = 0; c < rowValues.length; c++) {
    safeSetCellValue_(monthSheet, targetRow, c + 1, rowValues[c]);
  }

  monthSheet.getRange(targetRow, CT.DATE_COLUMN).setNumberFormat(CT.DATE_FORMAT);
  monthSheet.getRange(targetRow, CT.TIME_COLUMN).setNumberFormat(CT.TIME_FORMAT);

  // Recalculate serials, date grouping colors, and graying out of solved rows
  try {
    updateSerialsAndColors(monthSheet);
    refreshDashboardData_(ss);
  } catch (eRef) {}

  return ContentService.createTextOutput(JSON.stringify({
    status: 'success',
    message: 'Ticket #' + ticketKey + ' synced to ' + monthName + ' at Row ' + targetRow,
    sheet: monthName,
    row: targetRow,
    ticket_key: ticketKey
  })).setMimeType(ContentService.MimeType.JSON);
}

/**
 * Webhook handler to update an existing ticket (Status, Assignee, Remarks, Power).
 */
function handleWebhookUpdate_(ss, payload, now, timezone) {
  var ticketKey = String(payload.ticket_key || payload.ticket_id || payload.id || payload.client_id || '').trim();
  var newStatus = payload.status ? String(payload.status).trim() : (payload.current_status ? String(payload.current_status).trim() : null);
  var newAssigned = payload.assigned_to ? String(payload.assigned_to).trim() : null;
  var newRemarks = payload.remarks ? String(payload.remarks).trim() : null;
  var newPower = payload.onu_power ? String(payload.onu_power).trim() : null;
  var newForwarded = payload.forwarded_to ? String(payload.forwarded_to).trim() : null;

  var monthSheets = getMonthSheets_(ss);

  for (var s = 0; s < monthSheets.length; s++) {
    var sheet = monthSheets[s];
    var lastRow = sheet.getLastRow();
    if (lastRow < CT.DATA_START_ROW) continue;

    var rowCount = lastRow - CT.DATA_START_ROW + 1;
    var values = sheet.getRange(CT.DATA_START_ROW, 1, rowCount, CT.DATA_COLUMNS).getValues();

    // Dynamically identify column indices from header row if available
    var idCol = CT.TICKET_KEY_COLUMN;       // default Col F (6)
    var statusCol = CT.STATUS_COLUMN;       // default Col O (15)
    var assignedCol = CT.ASSIGNED_COLUMN;   // default Col N (14)
    var powerCol = CT.ONU_POWER_COLUMN;     // default Col M (13)
    var forwardedCol = CT.FORWARDED_COLUMN; // default Col L (12)
    var remarksCol = CT.REMARKS_COLUMN;     // default Col Q (17)

    try {
      var headerRowVals = sheet.getRange(CT.HEADER_ROWS, 1, 1, Math.min(sheet.getLastColumn(), 20)).getDisplayValues()[0];
      for (var h = 0; h < headerRowVals.length; h++) {
        var hTitle = String(headerRowVals[h] || '').toLowerCase();
        if (hTitle.includes('status')) statusCol = h + 1;
        else if (hTitle.includes('assigned') || hTitle.includes('technician')) assignedCol = h + 1;
        else if (hTitle.includes('power') || hTitle.includes('onu')) powerCol = h + 1;
        else if (hTitle.includes('forwarded')) forwardedCol = h + 1;
        else if (hTitle.includes('remarks')) remarksCol = h + 1;
        else if (hTitle === 'id' || hTitle.includes('ticket')) idCol = h + 1;
      }
    } catch (eH) {}

    for (var i = 0; i < values.length; i++) {
      var rowNumber = CT.DATA_START_ROW + i;
      var rowTicketKey = String(values[i][idCol - 1] || values[i][CT.TICKET_KEY_COLUMN - 1] || '').trim(); // Col F (6)
      var fallbackKey = String(values[i][16] || '').trim(); // Fallback Col Q (17)

      // Match by Ticket Key in Col F (e.g. 260927009) or Col Q
      var isMatch = ticketKey && (rowTicketKey === ticketKey || fallbackKey === ticketKey);

      if (isMatch) {
        try {
          sheet.getRange(rowNumber, 1, 1, CT.DATA_COLUMNS).clearDataValidations();
        } catch (eValid) {}

        if (newStatus) {
          safeSetCellValue_(sheet, rowNumber, statusCol, newStatus);
        }

        if (newAssigned !== null && newAssigned !== '') {
          safeSetCellValue_(sheet, rowNumber, assignedCol, newAssigned);
        }

        if (newRemarks !== null && newRemarks !== '') {
          var curRemarks = String(sheet.getRange(rowNumber, remarksCol).getValue() || '');
          if (!curRemarks.includes(newRemarks)) {
            var combined = curRemarks ? curRemarks + ' | ' + newRemarks : newRemarks;
            safeSetCellValue_(sheet, rowNumber, remarksCol, combined);
          }
        }

        if (newPower !== null && newPower !== '') {
          safeSetCellValue_(sheet, rowNumber, powerCol, newPower);
        }

        if (newForwarded !== null && newForwarded !== '') {
          safeSetCellValue_(sheet, rowNumber, forwardedCol, newForwarded);
        }

        // Re-apply serials and colors
        try {
          updateSerialsAndColors(sheet);
          refreshDashboardData_(ss);
        } catch (eRef) {}

        return ContentService.createTextOutput(JSON.stringify({
          status: 'success',
          message: 'Ticket #' + ticketKey + ' updated in ' + sheet.getName() + ' at row ' + rowNumber,
          sheet: sheet.getName(),
          row: rowNumber,
          ticket_key: ticketKey,
          status: newStatus
        })).setMimeType(ContentService.MimeType.JSON);
      }
    }
  }

  return ContentService.createTextOutput(JSON.stringify({
    status: 'not_found',
    message: 'Ticket #' + ticketKey + ' not found in any monthly sheet tab.'
  })).setMimeType(ContentService.MimeType.JSON);
}

/**
 * ONE-TIME SETUP
 */
function setupComplaintTracker() {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  if (!ss) throw new Error('Open the spreadsheet and run setupComplaintTracker() again.');

  const props = PropertiesService.getDocumentProperties();
  props.setProperty(CT.PROP_SPREADSHEET_ID, ss.getId());

  const currentMonthName = getCurrentMonthName_(ss);
  const legacySheet = findLegacyComplaintSheet_(ss);
  let currentMonthSheet = ss.getSheetByName(currentMonthName);

  if (
    currentMonthSheet &amp;&amp;
    legacySheet &amp;&amp;
    !sheetHasMeaningfulRecords_(currentMonthSheet) &amp;&amp;
    sheetHasMeaningfulRecords_(legacySheet)
  ) {
    ss.deleteSheet(currentMonthSheet);
    legacySheet.setName(currentMonthName);
    currentMonthSheet = legacySheet;
  }

  if (!currentMonthSheet &amp;&amp; legacySheet) {
    legacySheet.setName(currentMonthName);
    currentMonthSheet = legacySheet;
  }

  ensureDashboardSheet_(ss);
  ensureTemplateSheet_(ss, currentMonthSheet || legacySheet);

  if (!currentMonthSheet) {
    currentMonthSheet = getOrCreateMonthSheet_(ss, currentMonthName);
  }

  currentMonthSheet.setFrozenRows(CT.HEADER_ROWS);

  removeLegacyPlaceholderRows_(currentMonthSheet);

  clearPreparedState_();
  setupDailyTrigger();
  prepareNewDayRows_(true);
  refreshAllMonthlySheets();
  generateDashboard();

  ss.setActiveSheet(currentMonthSheet);
  ss.toast(
    'Setup complete. Current month: ' + currentMonthName +
    '. Fresh rows are ready at Row 3.',
    'Complaint Tracker',
    8
  );
}

/** Spreadsheet menu. */
function onOpen() {
  SpreadsheetApp.getUi()
    .createMenu('Sheet Settings')
    .addItem('Run / Repair Setup', 'setupComplaintTracker')
    .addSeparator()
    .addItem(&quot;Prepare Today's Rows&quot;, 'createNewDayRows')
    .addItem(&quot;Recreate Today's Fresh Rows&quot;, 'recreateTodayBlankRows')
    .addItem('Refresh Monthly Sheets', 'refreshAllMonthlySheets')
    .addItem('Refresh Dashboard', 'generateDashboard')
    .addItem('Sync Template from Current Sheet', 'syncTemplateFromCurrentSheet')
    .addSeparator()
    .addItem('Install Daily Trigger', 'setupDailyTrigger')
    .addItem('Health Check', 'runTrackerHealthCheck')
    .addToUi();
}

/**
 * Installs one daily trigger around 12:05 AM in the spreadsheet timezone.
 */
function setupDailyTrigger() {
  const ss = getSpreadsheet_();
  const timezone = ss.getSpreadsheetTimeZone();

  ScriptApp.getProjectTriggers().forEach(function(trigger) {
    const handler = trigger.getHandlerFunction();
    if (handler === 'createNewDayRows' || handler === 'handleComplaintEdit') {
      ScriptApp.deleteTrigger(trigger);
    }
  });

  ScriptApp.newTrigger('createNewDayRows')
    .timeBased()
    .atHour(0)
    .nearMinute(5)
    .everyDays(1)
    .inTimezone(timezone)
    .create();
}

/** Daily trigger entry point. */
function createNewDayRows() {
  prepareNewDayRows_(false);
}

/** Manual force refresh for today's fresh block. */
function recreateTodayBlankRows() {
  prepareNewDayRows_(true);
}

/**
 * Inserts actual whole rows at Row 3.
 */
function prepareNewDayRows_(force) {
  const lock = LockService.getDocumentLock();
  lock.waitLock(30000);

  try {
    const ss = getSpreadsheet_();
    const props = PropertiesService.getDocumentProperties();
    const timezone = ss.getSpreadsheetTimeZone();
    const now = new Date();
    const todayKey = Utilities.formatDate(now, timezone, 'yyyy-MM-dd');

    if (!force &amp;&amp; props.getProperty(CT.PROP_LAST_PREPARED_DATE) === todayKey) {
      return;
    }

    removeUnusedRowsFromPreviousPreparedBlock_(ss);

    const monthName = getMonthNameInTimezone_(now, timezone);
    const monthSheet = getOrCreateMonthSheet_(ss, monthName);
    const templateSheet = ss.getSheetByName(CT.TEMPLATE_SHEET);

    insertActualFreshRows_(monthSheet, templateSheet);

    props.setProperties({
      [CT.PROP_LAST_PREPARED_DATE]: todayKey,
      [CT.PROP_PREPARED_SHEET_ID]: String(monthSheet.getSheetId()),
      [CT.PROP_PREPARED_ROW_COUNT]: String(CT.ROWS_PER_DAY)
    }, false);

    updateSerialsAndColors(monthSheet);
    initializeDashboardDates_(ss);
    refreshDashboardData_(ss);

    ss.toast(
      CT.ROWS_PER_DAY + ' fresh rows created in ' + monthName + ' at Row 3.',
      'Complaint Tracker',
      6
    );
  } finally {
    lock.releaseLock();
  }
}

/**
 * Migration cleanup for placeholder rows.
 */
function removeLegacyPlaceholderRows_(sheet) {
  const lastRow = sheet.getLastRow();
  if (lastRow &lt; CT.DATA_START_ROW) return;

  const rowCount = lastRow - CT.DATA_START_ROW + 1;
  const values = sheet
    .getRange(CT.DATA_START_ROW, 1, rowCount, CT.DATA_COLUMNS)
    .getValues();

  for (let i = values.length - 1; i &gt;= 0; i--) {
    if (!hasMeaningfulRecordData_(values[i])) {
      sheet.deleteRow(CT.DATA_START_ROW + i);
    }
  }
}

/**
 * Removes only unused rows from the exact block prepared by the previous run.
 */
function removeUnusedRowsFromPreviousPreparedBlock_(ss) {
  const props = PropertiesService.getDocumentProperties();
  const preparedSheetId = Number(props.getProperty(CT.PROP_PREPARED_SHEET_ID));
  const preparedRowCount = Number(props.getProperty(CT.PROP_PREPARED_ROW_COUNT));

  if (!preparedSheetId || !preparedRowCount) return;

  const sheet = ss.getSheets().find(function(item) {
    return item.getSheetId() === preparedSheetId;
  });

  if (!sheet || sheet.getMaxRows() &lt; CT.DATA_START_ROW) {
    clearPreparedState_();
    return;
  }

  const availableRows = sheet.getMaxRows() - CT.DATA_START_ROW + 1;
  const rowsToInspect = Math.min(preparedRowCount, availableRows);
  if (rowsToInspect &lt;= 0) {
    clearPreparedState_();
    return;
  }

  const values = sheet
    .getRange(CT.DATA_START_ROW, 1, rowsToInspect, CT.DATA_COLUMNS)
    .getValues();

  for (let i = values.length - 1; i &gt;= 0; i--) {
    if (!hasMeaningfulRecordData_(values[i])) {
      sheet.deleteRow(CT.DATA_START_ROW + i);
    }
  }

  clearPreparedState_();
}

/**
 * Inserts actual sheet rows.
 */
function insertActualFreshRows_(sheet, templateSheet) {
  if (!templateSheet) {
    throw new Error('Template sheet is missing. Run setupComplaintTracker().');
  }

  if (sheet.getMaxRows() &lt; CT.DATA_START_ROW) {
    sheet.insertRowsAfter(CT.HEADER_ROWS, CT.ROWS_PER_DAY);
  } else {
    sheet.insertRowsBefore(CT.DATA_START_ROW, CT.ROWS_PER_DAY);
  }

  applyTemplateRowToRange_(
    templateSheet,
    sheet,
    CT.DATA_START_ROW,
    CT.ROWS_PER_DAY
  );

  sheet
    .getRange(CT.DATA_START_ROW, CT.DATE_COLUMN, CT.ROWS_PER_DAY, 1)
    .setNumberFormat(CT.DATE_FORMAT);

  sheet
    .getRange(CT.DATA_START_ROW, CT.TIME_COLUMN, CT.ROWS_PER_DAY, 1)
    .setNumberFormat(CT.TIME_FORMAT);
}

/**
 * Applies the Template sample row to one or more target rows.
 */
function applyTemplateRowToRange_(templateSheet, targetSheet, startRow, rowCount) {
  const source = templateSheet.getRange(
    CT.DATA_START_ROW,
    1,
    1,
    CT.DATA_COLUMNS
  );

  const target = targetSheet.getRange(
    startRow,
    1,
    rowCount,
    CT.DATA_COLUMNS
  );

  source.copyTo(target, SpreadsheetApp.CopyPasteType.PASTE_FORMAT, false);

  const validationRow = source.getDataValidations()[0];
  target.setDataValidations(repeatTemplateRow_(validationRow, rowCount));

  target.setFontFamilies(
    repeatTemplateRow_(source.getFontFamilies()[0], rowCount)
  );
  target.setFontSizes(
    repeatTemplateRow_(source.getFontSizes()[0], rowCount)
  );
  target.setFontWeights(
    repeatTemplateRow_(source.getFontWeights()[0], rowCount)
  );
  target.setFontStyles(
    repeatTemplateRow_(source.getFontStyles()[0], rowCount)
  );

  const sourceRowHeight = templateSheet.getRowHeight(CT.DATA_START_ROW);
  targetSheet.setRowHeights(startRow, rowCount, sourceRowHeight);

  target.clearContent();
  target.clearNote();
}

function repeatTemplateRow_(rowValues, rowCount) {
  return Array.from({ length: rowCount }, function() {
    return rowValues.slice();
  });
}

/**
 * SIMPLE ON-EDIT TRIGGER
 */
function onEdit(e) {
  if (!e || !e.range) return;

  const ss = e.source;
  const sheet = e.range.getSheet();
  const sheetName = sheet.getName();

  if (sheetName === CT.DASHBOARD_SHEET) {
    if (rangeContainsCell_(e.range, 2, 4) || rangeContainsCell_(e.range, 2, 6)) {
      refreshDashboardData_(ss);
    }
    return;
  }

  if (!isMonthSheetName_(sheetName)) return;
  if (e.range.getLastRow() &lt; CT.DATA_START_ROW) return;
  if (e.range.getColumn() &gt; CT.DATA_COLUMNS) return;

  const lock = LockService.getDocumentLock();
  if (!lock.tryLock(5000)) return;

  try {
    processEditedRows_(ss, sheet, e.range);

    if (
      e.range.getNumRows() === 1 &amp;&amp;
      e.range.getNumColumns() === 1 &amp;&amp;
      e.range.getColumn() === CT.CLIENT_ID_COLUMN &amp;&amp;
      e.value
    ) {
      warnAboutDuplicateComplaint_(ss, sheet, e.range.getRow(), e.value);
    }

    updateSerialsAndColors(sheet);
    refreshDashboardData_(ss);
  } finally {
    lock.releaseLock();
  }
}

/**
 * Fills Date and Entry Time when a user starts entering data in E:Q.
 */
function processEditedRows_(ss, sheet, editedRange) {
  const firstRow = Math.max(CT.DATA_START_ROW, editedRange.getRow());
  const lastRow = editedRange.getLastRow();
  const rowCount = lastRow - firstRow + 1;
  if (rowCount &lt;= 0) return;

  const recordValues = sheet
    .getRange(firstRow, 5, rowCount, CT.DATA_COLUMNS - 4) // E:Q
    .getValues();

  const metaRange = sheet.getRange(firstRow, 1, rowCount, 4); // A:D
  const metaValues = metaRange.getValues();

  const now = new Date();
  const currentMonthName = getCurrentMonthName_(ss);
  const isCurrentMonth = sheet.getName() === currentMonthName;
  const template = ss.getSheetByName(CT.TEMPLATE_SHEET);
  const rowsResetToBlank = [];

  for (let i = 0; i &lt; rowCount; i++) {
    const hasData = recordValues[i].some(function(value) {
      return !isBlankValue_(value);
    });

    if (!hasData) {
      metaValues[i] = ['', '', '', ''];
      rowsResetToBlank.push(firstRow + i);
      continue;
    }

    if (isCurrentMonth) {
      if (isBlankValue_(metaValues[i][2])) metaValues[i][2] = now;
      if (isBlankValue_(metaValues[i][3])) metaValues[i][3] = now;
    }
  }

  metaRange.setValues(metaValues);

  sheet
    .getRange(firstRow, CT.DATE_COLUMN, rowCount, 1)
    .setNumberFormat(CT.DATE_FORMAT);

  sheet
    .getRange(firstRow, CT.TIME_COLUMN, rowCount, 1)
    .setNumberFormat(CT.TIME_FORMAT);

  if (template) {
    rowsResetToBlank.forEach(function(rowNumber) {
      applyTemplateRowToRange_(template, sheet, rowNumber, 1);
    });
  }
}

/**
 * Serial and color rules:
 * - Only rows containing data in E:Q receive serials and date colors.
 * - Solved rows become gray.
 */
function updateSerialsAndColors(optionalSheet) {
  const ss = getSpreadsheet_();
  let sheet = optionalSheet;

  if (!sheet) {
    sheet = ss.getActiveSheet();
    if (!isMonthSheetName_(sheet.getName())) return;
  }

  const lastRow = sheet.getLastRow();
  if (lastRow &lt; CT.DATA_START_ROW) return;

  const rowCount = lastRow - CT.DATA_START_ROW + 1;
  const dataRange = sheet.getRange(
    CT.DATA_START_ROW,
    1,
    rowCount,
    CT.DATA_COLUMNS
  );

  const values = dataRange.getValues();
  const displayDates = sheet
    .getRange(CT.DATA_START_ROW, CT.DATE_COLUMN, rowCount, 1)
    .getDisplayValues();

  const serials = Array.from({ length: rowCount }, function() {
    return ['', ''];
  });

  const backgrounds = dataRange.getBackgrounds();
  const fontColors = dataRange.getFontColors();
  const fontLines = dataRange.getFontLines();
  const timezone = ss.getSpreadsheetTimeZone();

  let masterSerial = 1;
  let dailySerial = 1;
  let previousDateKey = '';
  let colorIndex = -1;

  for (let i = 0; i &lt; values.length; i++) {
    if (!hasMeaningfulRecordData_(values[i])) continue;

    const date = parseSheetDate_(values[i][CT.DATE_COLUMN - 1], displayDates[i][0]);
    if (!date) continue;

    const dateKey = Utilities.formatDate(date, timezone, 'yyyy-MM-dd');

    if (dateKey !== previousDateKey) {
      dailySerial = 1;
      colorIndex = (colorIndex + 1) % CT_ROW_COLORS.length;
      previousDateKey = dateKey;
    }

    serials[i] = [masterSerial, dailySerial];

    const status = String(values[i][CT.STATUS_COLUMN - 1] || '')
      .trim()
      .toLowerCase();

    const solved = status === CT.SOLVED_STATUS || status === 'closed';
    const rowBackground = solved ? '#DDDDDD' : CT_ROW_COLORS[colorIndex];
    const rowFontColor = solved ? '#999999' : '#000000';

    backgrounds[i] = Array(CT.DATA_COLUMNS).fill(rowBackground);
    fontColors[i] = Array(CT.DATA_COLUMNS).fill(rowFontColor);
    fontLines[i] = Array(CT.DATA_COLUMNS).fill('none');

    masterSerial++;
    dailySerial++;
  }

  sheet
    .getRange(CT.DATA_START_ROW, 1, rowCount, 2)
    .setValues(serials);

  dataRange.setBackgrounds(backgrounds);
  dataRange.setFontColors(fontColors);
  dataRange.setFontLines(fontLines);
}

/** Refreshes serials/colors for every month and updates Dashboard. */
function refreshAllMonthlySheets() {
  const ss = getSpreadsheet_();
  getMonthSheets_(ss).forEach(function(sheet) {
    updateSerialsAndColors(sheet);
  });
  refreshDashboardData_(ss);
}

/** Duplicate Client ID check across all month tabs. */
function warnAboutDuplicateComplaint_(ss, currentSheet, currentRow, clientId) {
  const normalizedId = String(clientId || '').trim();
  if (!normalizedId) return;

  const monthSheets = getMonthSheets_(ss);

  for (let s = 0; s &lt; monthSheets.length; s++) {
    const sheet = monthSheets[s];
    const lastRow = sheet.getLastRow();
    if (lastRow &lt; CT.DATA_START_ROW) continue;

    const rowCount = lastRow - CT.DATA_START_ROW + 1;
    const values = sheet
      .getRange(CT.DATA_START_ROW, 1, rowCount, CT.DATA_COLUMNS)
      .getValues();

    for (let i = 0; i &lt; values.length; i++) {
      const rowNumber = CT.DATA_START_ROW + i;

      if (
        sheet.getSheetId() === currentSheet.getSheetId() &amp;&amp;
        rowNumber === currentRow
      ) {
        continue;
      }

      if (!hasMeaningfulRecordData_(values[i])) continue;

      const existingId = String(values[i][CT.CLIENT_ID_COLUMN - 1] || '').trim();
      if (existingId !== normalizedId) continue;

      const status = String(values[i][CT.STATUS_COLUMN - 1] || '').trim();
      if (status.toLowerCase() === CT.SOLVED_STATUS || status.toLowerCase() === 'closed') continue;

      const foundRange = sheet.getRange(rowNumber, 1, 1, CT.DATA_COLUMNS);
      foundRange.setBackground('#ff9999');
      foundRange.setNote(
        'Duplicate open complaint detected for Client ID ' + normalizedId +
        '. Existing record: ' + sheet.getName() + ', Row ' + rowNumber + '.'
      );

      ss.toast(
        'Duplicate open complaint found in ' + sheet.getName() +
        ', Row ' + rowNumber + '. Status: ' + (status || 'Blank'),
        'Duplicate Complaint',
        10
      );
      return;
    }
  }
}

/** Full manual Dashboard refresh, including chart rebuild. */
function generateDashboard() {
  const ss = getSpreadsheet_();
  initializeDashboardDates_(ss);
  refreshDashboardData_(ss);
  rebuildDashboardCharts_(ss);
  ss.toast('Dashboard refreshed.', 'Complaint Tracker', 4);
}

/** Reads the selected date range and rebuilds Dashboard numbers. */
function refreshDashboardData_(ss) {
  const dashboard = ss.getSheetByName(CT.DASHBOARD_SHEET);
  if (!dashboard) return;

  const startCell = dashboard.getRange('D2');
  const endCell = dashboard.getRange('F2');
  const startValue = startCell.getValue();
  const endValue = endCell.getValue();

  if (!(startValue instanceof Date) || isNaN(startValue.getTime())) return;
  if (!(endValue instanceof Date) || isNaN(endValue.getTime())) return;

  let rangeStart = new Date(startValue);
  rangeStart.setHours(0, 0, 0, 0);

  let rangeEnd = new Date(endValue);
  rangeEnd.setHours(23, 59, 59, 999);

  if (rangeStart.getTime() &gt; rangeEnd.getTime()) {
    const swap = rangeStart;
    rangeStart = rangeEnd;
    rangeEnd = swap;
  }

  const typeSummary = {};
  const statusSummary = {};
  let totalComplaints = 0;
  let statusCount = 0;

  const relevantMonths = getRelevantMonthNames_(rangeStart, rangeEnd);

  relevantMonths.forEach(function(monthName) {
    const sheet = ss.getSheetByName(monthName);
    if (!sheet) return;

    const lastRow = sheet.getLastRow();
    if (lastRow &lt; CT.DATA_START_ROW) return;

    const rowCount = lastRow - CT.DATA_START_ROW + 1;
    const values = sheet
      .getRange(CT.DATA_START_ROW, 1, rowCount, CT.DATA_COLUMNS)
      .getValues();
    const displayDates = sheet
      .getRange(CT.DATA_START_ROW, CT.DATE_COLUMN, rowCount, 1)
      .getDisplayValues();

    for (let i = 0; i &lt; values.length; i++) {
      if (!hasMeaningfulRecordData_(values[i])) continue;

      const rowDate = parseSheetDate_(values[i][CT.DATE_COLUMN - 1], displayDates[i][0]);
      if (!rowDate) continue;
      if (rowDate.getTime() &lt; rangeStart.getTime()) continue;
      if (rowDate.getTime() &gt; rangeEnd.getTime()) continue;

      totalComplaints++;

      const type = String(values[i][CT.TYPE_COLUMN - 1] || '').trim();
      const status = String(values[i][CT.STATUS_COLUMN - 1] || '').trim();

      if (type) typeSummary[type] = (typeSummary[type] || 0) + 1;

      if (status) {
        statusSummary[status] = (statusSummary[status] || 0) + 1;
        statusCount++;
      }
    }
  });

  if (totalComplaints &gt; statusCount) {
    statusSummary.Blank = totalComplaints - statusCount;
  }

  const sortedTypes = Object.keys(typeSummary)
    .map(function(name) {
      return { name: name, count: typeSummary[name] };
    })
    .sort(function(a, b) {
      return b.count - a.count || a.name.localeCompare(b.name);
    });

  const sortedStatuses = Object.keys(statusSummary)
    .map(function(name) {
      return { name: name, count: statusSummary[name] };
    })
    .sort(function(a, b) {
      return b.count - a.count || a.name.localeCompare(b.name);
    });

  dashboard.getRange('R1:W200').clearContent().clearFormat();
  dashboard.getRange('A5:D50').clearContent();

  dashboard.getRange('C2').setValue('From');
  dashboard.getRange('E2').setValue('To');
  startCell.setNumberFormat('dd mmm, yy');
  endCell.setNumberFormat('dd mmm, yy');

  dashboard.getRange('A5').setValue('Total Complaints');
  dashboard.getRange('B5').setValue(totalComplaints).setNumberFormat('0');

  dashboard.getRange('A7:B7').setValues([['Complaint Type', 'Count']]);
  dashboard.getRange('C7:D7').setValues([['Status', 'Count']]);

  if (sortedTypes.length) {
    dashboard
      .getRange(8, 1, sortedTypes.length, 2)
      .setValues(sortedTypes.map(function(item) {
        return [item.name, item.count];
      }));
    dashboard.getRange(8, 2, sortedTypes.length, 1).setNumberFormat('0');
  }

  if (sortedStatuses.length) {
    dashboard
      .getRange(8, 3, sortedStatuses.length, 2)
      .setValues(sortedStatuses.map(function(item) {
        return [item.name, item.count];
      }));
    dashboard.getRange(8, 4, sortedStatuses.length, 1).setNumberFormat('0');
  }
}

/** Creates two charts on the Dashboard using stable ranges. */
function rebuildDashboardCharts_(ss) {
  const dashboard = ss.getSheetByName(CT.DASHBOARD_SHEET);
  if (!dashboard) return;

  dashboard.getCharts().forEach(function(chart) {
    dashboard.removeChart(chart);
  });

  const typeChart = dashboard.newChart()
    .setChartType(Charts.ChartType.PIE)
    .addRange(dashboard.getRange('A7:B50'))
    .setPosition(4, 6, 0, 0)
    .setOption('title', 'Complaint Type Breakdown')
    .build();

  const statusChart = dashboard.newChart()
    .setChartType(Charts.ChartType.PIE)
    .addRange(dashboard.getRange('C7:D50'))
    .setPosition(20, 6, 0, 0)
    .setOption('title', 'Status Breakdown')
    .build();

  dashboard.insertChart(typeChart);
  dashboard.insertChart(statusChart);
}

/** Creates Dashboard automatically when it does not exist. */
function ensureDashboardSheet_(ss) {
  let dashboard = ss.getSheetByName(CT.DASHBOARD_SHEET);
  if (!dashboard) {
    dashboard = ss.insertSheet(CT.DASHBOARD_SHEET);
  }

  initializeDashboardDates_(ss);
  return dashboard;
}

/**
 * Creates or rebuilds Template from the current complaint sheet.
 */
function ensureTemplateSheet_(ss, sourceSheet) {
  if (!sourceSheet) {
    sourceSheet = ss.getSheetByName(getCurrentMonthName_(ss));
  }

  if (!sourceSheet) {
    sourceSheet = findLegacyComplaintSheet_(ss);
  }

  if (!sourceSheet) {
    const existingTemplate = ss.getSheetByName(CT.TEMPLATE_SHEET);
    if (existingTemplate) {
      ensureTemplateHasSampleRow_(existingTemplate);
      return existingTemplate;
    }

    throw new Error(
      'No complaint sheet was detected. Open the complaint-data tab and run setupComplaintTracker() again.'
    );
  }

  return rebuildTemplateSheet_(ss, sourceSheet);
}

/** Manual menu action after changing dropdowns, fonts or row styling. */
function syncTemplateFromCurrentSheet() {
  const ss = getSpreadsheet_();
  let sourceSheet = ss.getActiveSheet();

  if (!isMonthSheetName_(sourceSheet.getName())) {
    sourceSheet = ss.getSheetByName(getCurrentMonthName_(ss));
  }

  if (!sourceSheet) {
    const monthSheets = getMonthSheets_(ss);
    sourceSheet = monthSheets.length ? monthSheets[0] : null;
  }

  if (!sourceSheet) {
    throw new Error('Open a monthly complaint tab, then run this action again.');
  }

  rebuildTemplateSheet_(ss, sourceSheet);
  ss.toast(
    'Template synced from ' + sourceSheet.getName() +
    '. New rows will keep its dropdowns, font and row style.',
    'Complaint Tracker',
    7
  );
}

function rebuildTemplateSheet_(ss, sourceSheet) {
  const sampleSourceRow = findBestTemplateSourceRow_(sourceSheet);
  let oldTemplate = ss.getSheetByName(CT.TEMPLATE_SHEET);

  if (oldTemplate) {
    oldTemplate.showSheet();
    ss.deleteSheet(oldTemplate);
  }

  const template = sourceSheet.copyTo(ss).setName(CT.TEMPLATE_SHEET);

  template.getCharts().forEach(function(chart) {
    template.removeChart(chart);
  });

  if (template.getMaxRows() &lt; CT.DATA_START_ROW) {
    template.insertRowsAfter(
      template.getMaxRows(),
      CT.DATA_START_ROW - template.getMaxRows()
    );
  }

  if (template.getMaxColumns() &lt; CT.DATA_COLUMNS) {
    template.insertColumnsAfter(
      template.getMaxColumns(),
      CT.DATA_COLUMNS - template.getMaxColumns()
    );
  }

  // Ensure Column Q has a header label in row 2
  var colQHeader = template.getRange(CT.HEADER_ROWS, CT.TICKET_KEY_COLUMN).getValue();
  if (!colQHeader) {
    template.getRange(CT.HEADER_ROWS, CT.TICKET_KEY_COLUMN).setValue('Ticket ID / Key');
  }

  sourceSheet
    .getRange(sampleSourceRow, 1, 1, CT.DATA_COLUMNS)
    .copyTo(
      template.getRange(CT.DATA_START_ROW, 1, 1, CT.DATA_COLUMNS),
      SpreadsheetApp.CopyPasteType.PASTE_NORMAL,
      false
    );

  ensureTemplateHasSampleRow_(template);
  template.hideSheet();
  return template;
}

function findBestTemplateSourceRow_(sheet) {
  const availableRows = sheet.getMaxRows() - CT.DATA_START_ROW + 1;
  if (availableRows &lt;= 0) return CT.DATA_START_ROW;

  const rowsToInspect = Math.min(availableRows, 250);
  const validations = sheet
    .getRange(CT.DATA_START_ROW, 1, rowsToInspect, CT.DATA_COLUMNS)
    .getDataValidations();

  let bestRow = CT.DATA_START_ROW;
  let highestValidationCount = -1;

  for (let i = 0; i &lt; validations.length; i++) {
    const count = validations[i].reduce(function(total, rule) {
      return total + (rule ? 1 : 0);
    }, 0);

    if (count &gt; highestValidationCount) {
      highestValidationCount = count;
      bestRow = CT.DATA_START_ROW + i;
    }
  }

  return bestRow;
}

function ensureTemplateHasSampleRow_(template) {
  if (template.getMaxRows() &lt; CT.DATA_START_ROW) {
    template.insertRowsAfter(
      template.getMaxRows(),
      CT.DATA_START_ROW - template.getMaxRows()
    );
  }

  if (template.getMaxRows() &gt; CT.DATA_START_ROW) {
    template.deleteRows(
      CT.DATA_START_ROW + 1,
      template.getMaxRows() - CT.DATA_START_ROW
    );
  }

  const sample = template.getRange(
    CT.DATA_START_ROW,
    1,
    1,
    CT.DATA_COLUMNS
  );

  sample.clearContent();
  sample.clearNote();
  template.setFrozenRows(CT.HEADER_ROWS);
}

/** Creates a missing month sheet from Template. */
function getOrCreateMonthSheet_(ss, monthName) {
  let sheet = ss.getSheetByName(monthName);
  if (sheet) {
    if (sheet.getMaxColumns() &lt; CT.DATA_COLUMNS) {
      sheet.insertColumnsAfter(sheet.getMaxColumns(), CT.DATA_COLUMNS - sheet.getMaxColumns());
      sheet.getRange(CT.HEADER_ROWS, CT.TICKET_KEY_COLUMN).setValue('Ticket ID / Key');
    }
    return sheet;
  }

  const template = ss.getSheetByName(CT.TEMPLATE_SHEET);
  if (!template) throw new Error('Template sheet is missing. Run setupComplaintTracker().');

  sheet = template.copyTo(ss).setName(monthName);
  sheet.showSheet();
  sheet.setFrozenRows(CT.HEADER_ROWS);

  if (sheet.getMaxColumns() &lt; CT.DATA_COLUMNS) {
    sheet.insertColumnsAfter(sheet.getMaxColumns(), CT.DATA_COLUMNS - sheet.getMaxColumns());
  }
  sheet.getRange(CT.HEADER_ROWS, CT.TICKET_KEY_COLUMN).setValue('Ticket ID / Key');

  if (sheet.getMaxRows() &gt;= CT.DATA_START_ROW) {
    sheet.deleteRows(
      CT.DATA_START_ROW,
      sheet.getMaxRows() - CT.HEADER_ROWS
    );
  }

  return sheet;
}

function findLegacyComplaintSheet_(ss) {
  const active = ss.getActiveSheet();

  if (
    active &amp;&amp;
    !isReservedSheetName_(active.getName()) &amp;&amp;
    !isMonthSheetName_(active.getName()) &amp;&amp;
    looksLikeComplaintSheet_(active)
  ) {
    return active;
  }

  const candidates = ss.getSheets().filter(function(sheet) {
    return (
      !isReservedSheetName_(sheet.getName()) &amp;&amp;
      !isMonthSheetName_(sheet.getName()) &amp;&amp;
      looksLikeComplaintSheet_(sheet)
    );
  });

  return candidates.length ? candidates[0] : null;
}

function looksLikeComplaintSheet_(sheet) {
  if (sheet.getMaxRows() &lt; CT.HEADER_ROWS) return false;
  if (sheet.getMaxColumns() &lt; 16) return false;

  const headers = sheet
    .getRange(CT.HEADER_ROWS, 1, 1, Math.min(sheet.getMaxColumns(), CT.DATA_COLUMNS))
    .getDisplayValues()[0]
    .map(function(value) {
      return String(value || '').toLowerCase().replace(/\s+/g, ' ').trim();
    });

  let score = 0;
  if (headers[CT.DATE_COLUMN - 1] &amp;&amp; headers[CT.DATE_COLUMN - 1].includes('date')) score++;
  if (headers[CT.TIME_COLUMN - 1] &amp;&amp; headers[CT.TIME_COLUMN - 1].includes('time')) score++;
  if (headers[CT.CLIENT_ID_COLUMN - 1] &amp;&amp; (headers[CT.CLIENT_ID_COLUMN - 1] === 'id' || headers[CT.CLIENT_ID_COLUMN - 1].includes('client'))) score++;
  if (headers[CT.NAME_COLUMN - 1] &amp;&amp; headers[CT.NAME_COLUMN - 1].includes('name')) score++;
  if (headers[CT.TYPE_COLUMN - 1] &amp;&amp; headers[CT.TYPE_COLUMN - 1].includes('type')) score++;
  if (headers[CT.STATUS_COLUMN - 1] &amp;&amp; headers[CT.STATUS_COLUMN - 1].includes('status')) score++;

  return score &gt;= 3;
}

function sheetHasMeaningfulRecords_(sheet) {
  const lastRow = sheet.getLastRow();
  if (lastRow &lt; CT.DATA_START_ROW) return false;

  const rowCount = lastRow - CT.DATA_START_ROW + 1;
  const values = sheet
    .getRange(CT.DATA_START_ROW, 1, rowCount, CT.DATA_COLUMNS)
    .getValues();

  return values.some(function(row) {
    return hasMeaningfulRecordData_(row);
  });
}

function initializeDashboardDates_(ss) {
  const dashboard = ss.getSheetByName(CT.DASHBOARD_SHEET);
  if (!dashboard) return;

  const now = new Date();
  const startCell = dashboard.getRange('D2');
  const endCell = dashboard.getRange('F2');

  if (!(startCell.getValue() instanceof Date)) {
    startCell.setValue(new Date(now.getFullYear(), now.getMonth(), 1));
  }

  if (!(endCell.getValue() instanceof Date)) {
    endCell.setValue(now);
  }

  dashboard.getRange('C2').setValue('From');
  dashboard.getRange('E2').setValue('To');
  startCell.setNumberFormat('dd mmm, yy');
  endCell.setNumberFormat('dd mmm, yy');
}

function runTrackerHealthCheck() {
  const ss = getSpreadsheet_();
  const props = PropertiesService.getDocumentProperties();
  const currentMonth = getCurrentMonthName_(ss);
  const triggerCount = ScriptApp.getProjectTriggers().filter(function(trigger) {
    return trigger.getHandlerFunction() === 'createNewDayRows';
  }).length;

  const report = [
    'Version: ' + CT.VERSION,
    'Spreadsheet: ' + ss.getName(),
    'Current month tab: ' + (ss.getSheetByName(currentMonth) ? 'OK' : 'MISSING'),
    'Template tab: ' + (ss.getSheetByName(CT.TEMPLATE_SHEET) ? 'OK' : 'MISSING'),
    'Dashboard tab: ' + (ss.getSheetByName(CT.DASHBOARD_SHEET) ? 'OK' : 'MISSING'),
    'Daily trigger: ' + (triggerCount === 1 ? 'OK' : 'CHECK (' + triggerCount + ')'),
    'Last prepared date: ' + (props.getProperty(CT.PROP_LAST_PREPARED_DATE) || 'Not prepared')
  ].join('\n');

  SpreadsheetApp.getUi().alert('Complaint Tracker Health Check', report, SpreadsheetApp.getUi().ButtonSet.OK);
}

function getSpreadsheet_() {
  const active = SpreadsheetApp.getActiveSpreadsheet();
  if (active) return active;

  const id = PropertiesService
    .getDocumentProperties()
    .getProperty(CT.PROP_SPREADSHEET_ID);

  if (!id) {
    throw new Error('Spreadsheet ID is not saved. Run setupComplaintTracker() once manually.');
  }

  return SpreadsheetApp.openById(id);
}

function getCurrentMonthName_(ss) {
  return getMonthNameInTimezone_(new Date(), ss.getSpreadsheetTimeZone());
}

function getMonthNameInTimezone_(date, timezone) {
  const monthNumber = Number(Utilities.formatDate(date, timezone, 'M'));
  return CT_MONTHS[monthNumber - 1];
}

function getMonthSheets_(ss) {
  return ss.getSheets().filter(function(sheet) {
    return isMonthSheetName_(sheet.getName());
  });
}

function isMonthSheetName_(name) {
  return CT_MONTHS.includes(name);
}

function isReservedSheetName_(name) {
  return name === CT.DASHBOARD_SHEET || name === CT.TEMPLATE_SHEET;
}

function getRelevantMonthNames_(startDate, endDate) {
  const result = [];
  const seen = {};
  const cursor = new Date(startDate.getFullYear(), startDate.getMonth(), 1);
  const finalMonth = new Date(endDate.getFullYear(), endDate.getMonth(), 1);

  let guard = 0;
  while (cursor.getTime() &lt;= finalMonth.getTime() &amp;&amp; guard &lt; 24) {
    const monthName = CT_MONTHS[cursor.getMonth()];
    if (!seen[monthName]) {
      result.push(monthName);
      seen[monthName] = true;
    }
    cursor.setMonth(cursor.getMonth() + 1);
    guard++;
  }

  return result;
}

/**
 * A row is a complaint record only when E:Q contains at least one value.
 */
function hasMeaningfulRecordData_(rowValues) {
  return rowValues.slice(4, CT.DATA_COLUMNS).some(function(value) {
    return !isBlankValue_(value);
  });
}

function isBlankValue_(value) {
  return value === '' || value === null || typeof value === 'undefined';
}

function parseSheetDate_(rawValue, displayValue) {
  if (rawValue instanceof Date &amp;&amp; !isNaN(rawValue.getTime())) {
    return new Date(rawValue);
  }

  const text = String(rawValue || displayValue || '').trim();
  if (!text) return null;

  const direct = new Date(text);
  if (!isNaN(direct.getTime())) return direct;

  const match = text.match(/^(\d{1,2})\s+([A-Za-z]{3,9}),?\s+(\d{2}|\d{4})$/);
  if (!match) return null;

  const shortMonths = [
    'jan', 'feb', 'mar', 'apr', 'may', 'jun',
    'jul', 'aug', 'sep', 'oct', 'nov', 'dec'
  ];

  const day = Number(match[1]);
  const monthIndex = shortMonths.indexOf(match[2].slice(0, 3).toLowerCase());
  const suppliedYear = Number(match[3]);
  const year = suppliedYear &lt; 100 ? 2000 + suppliedYear : suppliedYear;

  if (monthIndex &lt; 0) return null;

  const parsed = new Date(year, monthIndex, day);
  return isNaN(parsed.getTime()) ? null : parsed;
}

function rangeContainsCell_(range, row, column) {
  return (
    row &gt;= range.getRow() &amp;&amp;
    row &lt;= range.getLastRow() &amp;&amp;
    column &gt;= range.getColumn() &amp;&amp;
    column &lt;= range.getLastColumn()
  );
}

function clearPreparedState_() {
  const props = PropertiesService.getDocumentProperties();
  props.deleteProperty(CT.PROP_LAST_PREPARED_DATE);
  props.deleteProperty(CT.PROP_PREPARED_SHEET_ID);
  props.deleteProperty(CT.PROP_PREPARED_ROW_COUNT);
}</pre>
                </div>
            </div>
        </div>
        @endif
    </div>
</x-app-layout>
