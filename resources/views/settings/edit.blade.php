<x-app-layout>
    <div class="w-full space-y-6" x-data="{ 
        activeTab: new URLSearchParams(window.location.search).get('tab') || 'notice',
        setTab(tab) {
            this.activeTab = tab;
            const url = new URL(window.location);
            url.searchParams.set('tab', tab);
            window.history.replaceState({}, '', url);
        }
    }">
        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs">
            <div>
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-100 dark:border-indigo-800/60 flex items-center justify-center text-indigo-600 dark:text-indigo-400 shadow-xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">{{ __('System & Brand Settings') }}</h1>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Customize your company branding, header ticker notices, categories, and system backup management.') }}</p>
                    </div>
                </div>
            </div>

            {{-- Quick action links / export --}}
            <div class="flex items-center flex-wrap gap-2.5">
                @if(auth()->user()->isSuperAdminOnly())
                <a href="{{ route('settings.backup.export') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 text-xs font-semibold transition-colors shadow-xs">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    <span>{{ __('Export Settings JSON') }}</span>
                </a>
                @endif
                <a href="{{ route('ticket-categories.index') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-indigo-200 dark:border-indigo-800/80 bg-indigo-50/70 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 text-xs font-semibold transition-colors shadow-xs">
                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    <span>{{ __('Manage Categories') }} &rarr;</span>
                </a>
                <a href="{{ route('areas.index') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-emerald-200 dark:border-emerald-800/80 bg-emerald-50/70 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 text-xs font-semibold transition-colors shadow-xs">
                    <span>📍</span>
                    <span>{{ __('Manage Areas') }} &rarr;</span>
                </a>
            </div>
        </div>

        {{-- Alerts --}}
        @if(session('status'))
            <div class="px-4 py-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-semibold flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            </div>
        @endif

        @if(session('success'))
            <div class="px-4 py-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-semibold flex items-center gap-2 shadow-xs">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="px-4 py-3.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-xs font-semibold flex items-center gap-2 shadow-xs">
                <svg class="w-4 h-4 text-rose-600 dark:text-rose-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        {{-- Full Width Tab Navigation Bar --}}
        <div class="border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 rounded-2xl p-1.5 shadow-xs flex items-center gap-1.5 overflow-x-auto scrollbar-none">
            {{-- Notice Ticker Tab --}}
            <button type="button" @click="setTab('notice')"
                    :class="activeTab === 'notice' 
                        ? 'bg-indigo-600 text-white shadow-xs font-bold' 
                        : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/70 font-semibold'"
                    class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs whitespace-nowrap transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                        : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/70 font-semibold'"
                    class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs whitespace-nowrap transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>{{ __('Logo & Favicon') }}</span>
            </button>

            {{-- Theme & Appearance Tab (Super Admin) --}}
            @if(auth()->user()->isSuperAdminOnly())
            <button type="button" @click="setTab('theme')"
                    :class="activeTab === 'theme' 
                        ? 'bg-indigo-600 text-white shadow-xs font-bold' 
                        : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/70 font-semibold'"
                    class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs whitespace-nowrap transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4 4 4 0 014-4c.48 0 .935.085 1.356.241A6.974 6.974 0 019 11a7 7 0 017-7c1.378 0 2.652.4 3.732 1.085A4 4 0 0121 9a4 4 0 01-4 4c-.48 0-.935-.085-1.356-.241A6.974 6.974 0 0115 15a7 7 0 01-7 7z"/>
                </svg>
                <span>{{ __('Theme & Brand Colors') }}</span>
            </button>
            @endif

            {{-- Ticket Categories Tab --}}
            <button type="button" @click="setTab('categories')"
                    :class="activeTab === 'categories' 
                        ? 'bg-indigo-600 text-white shadow-xs font-bold' 
                        : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/70 font-semibold'"
                    class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs whitespace-nowrap transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                <span>{{ __('Ticket Categories') }}</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300"
                      :class="activeTab === 'categories' ? 'bg-white/20 text-white' : ''">
                    {{ count($categories ?? []) }}
                </span>
            </button>

            {{-- Database Backup Tab (Admin/Super Admin) --}}
            @if(auth()->user()->isAdmin())
            <button type="button" @click="setTab('backup')"
                    :class="activeTab === 'backup' 
                        ? 'bg-indigo-600 text-white shadow-xs font-bold' 
                        : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/70 font-semibold'"
                    class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs whitespace-nowrap transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                </svg>
                <span>{{ __('Database Backups') }}</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300"
                      :class="activeTab === 'backup' ? 'bg-white/20 text-white' : ''">
                    {{ count($backups ?? []) }}
                </span>
            </button>
            @endif
        </div>

        {{-- ================= TAB 1: NOTICE TICKER (ROLE-BASED CHANNELS) ================= --}}
        <div x-show="activeTab === 'notice'" x-cloak class="space-y-6"
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
                             // Revert state on server error
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

            <style>
            /* Bulletproof Notice Toggle Switches */
            .notice-switch-btn {
                position: relative;
                display: inline-flex;
                align-items: center;
                width: 48px;
                height: 26px;
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
                top: 3px;
                left: 3px;
                width: 20px;
                height: 20px;
                border-radius: 9999px;
                background-color: #ffffff;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.28);
                transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
                display: block;
                pointer-events: none;
            }
            .notice-switch-btn.is-active .notice-switch-knob {
                transform: translateX(22px);
            }
            </style>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs overflow-hidden">
                {{-- Header --}}
                <div class="p-6 border-b border-slate-100 dark:border-slate-800/80 bg-gradient-to-r from-slate-50 via-white to-slate-50 dark:from-slate-900 dark:via-slate-850 dark:to-slate-900">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <span class="px-2.5 py-1 rounded-xl text-xs font-extrabold bg-red-600 text-white shadow-xs tracking-wider uppercase flex-shrink-0 mt-0.5">
                                📢 NOTICE
                            </span>
                            <div>
                                <h2 class="text-base font-bold text-slate-900 dark:text-white">{{ __('Targeted Notice Ticker Management') }}</h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                    {{ __('Configure independent notice messages, color themes, and badges for Resellers, NOC, and all users.') }}
                                </p>
                            </div>
                        </div>

                        {{-- Quick Info Badges --}}
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-xs font-bold transition-colors"
                                  :class="globalActive ? 'bg-emerald-50 dark:bg-emerald-950/40 border-emerald-300 text-emerald-700 dark:text-emerald-400' : 'bg-slate-100 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-400'">
                                <span class="w-2 h-2 rounded-full transition-colors" :class="globalActive ? 'bg-emerald-500' : 'bg-slate-400'"></span>
                                <span>{{ __('Global Notice') }}:</span>
                                <span x-text="globalActive ? '{{ __('ON') }}' : '{{ __('OFF') }}'"></span>
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-xs font-bold transition-colors"
                                  :class="resellerActive ? 'bg-amber-50 dark:bg-amber-950/40 border-amber-300 text-amber-700 dark:text-amber-400' : 'bg-slate-100 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-400'">
                                <span class="w-2 h-2 rounded-full transition-colors" :class="resellerActive ? 'bg-amber-500' : 'bg-slate-400'"></span>
                                <span>{{ __('Reseller Notice') }}:</span>
                                <span x-text="resellerActive ? '{{ __('ON') }}' : '{{ __('OFF') }}'"></span>
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-xs font-bold transition-colors"
                                  :class="nocActive ? 'bg-indigo-50 dark:bg-indigo-950/40 border-indigo-300 text-indigo-700 dark:text-indigo-400' : 'bg-slate-100 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-400'">
                                <span class="w-2 h-2 rounded-full transition-colors" :class="nocActive ? 'bg-indigo-500' : 'bg-slate-400'"></span>
                                <span>{{ __('NOC Notice') }}:</span>
                                <span x-text="nocActive ? '{{ __('ON') }}' : '{{ __('OFF') }}'"></span>
                            </span>
                        </div>
                    </div>

                    {{-- Channel Sub-Tabs Switcher --}}
                    <div class="mt-5 flex items-center gap-2 border-t border-slate-200/60 dark:border-slate-800 pt-4">
                        <button type="button" @click="noticeChannel = 'global'"
                                :class="noticeChannel === 'global' ? 'bg-slate-900 dark:bg-white text-white dark:text-slate-900 font-bold shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-semibold hover:bg-slate-200'"
                                class="px-4 py-2 rounded-xl text-xs transition-all cursor-pointer flex items-center gap-2">
                            <span>🌐 {{ __('Global Notice (For Everyone)') }}</span>
                            <span class="w-2 h-2 rounded-full transition-colors"
                                  :class="globalActive ? 'bg-emerald-400 animate-pulse' : 'bg-slate-300 dark:bg-slate-600'"></span>
                        </button>

                        <button type="button" @click="noticeChannel = 'reseller'"
                                :class="noticeChannel === 'reseller' ? 'bg-amber-600 text-white font-bold shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-semibold hover:bg-slate-200'"
                                class="px-4 py-2 rounded-xl text-xs transition-all cursor-pointer flex items-center gap-2">
                            <span>🤝 {{ __('Reseller Notice (For Resellers Only)') }}</span>
                            <span class="w-2 h-2 rounded-full transition-colors"
                                  :class="resellerActive ? 'bg-amber-400 animate-pulse' : 'bg-slate-300 dark:bg-slate-600'"></span>
                        </button>

                        <button type="button" @click="noticeChannel = 'noc'"
                                :class="noticeChannel === 'noc' ? 'bg-indigo-600 text-white font-bold shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-semibold hover:bg-slate-200'"
                                class="px-4 py-2 rounded-xl text-xs transition-all cursor-pointer flex items-center gap-2">
                            <span>🛠️ {{ __('NOC Notice (For NOC Team Only)') }}</span>
                            <span class="w-2 h-2 rounded-full transition-colors"
                                  :class="nocActive ? 'bg-indigo-400 animate-pulse' : 'bg-slate-300 dark:bg-slate-600'"></span>
                        </button>
                    </div>
                </div>

                <form method="POST" action="{{ route('settings.notice.update') }}" class="p-6 space-y-6">
                    @csrf

                    {{-- Master Power Switch Banner --}}
                    <div class="p-4 rounded-xl border flex flex-col sm:flex-row sm:items-center justify-between gap-3 transition-colors"
                         :class="masterActive ? 'bg-indigo-50/60 dark:bg-indigo-950/20 border-indigo-200 dark:border-indigo-900/60' : 'bg-rose-50/60 dark:bg-rose-950/20 border-rose-200 dark:border-rose-900/60'">
                        <div class="space-y-0.5">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-black uppercase tracking-wider transition-colors"
                                      :class="masterActive ? 'text-indigo-900 dark:text-indigo-300' : 'text-rose-900 dark:text-rose-300'">
                                    📢 {{ __('Main Notice Ticker Master Power Switch') }}
                                </span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black transition-colors"
                                      :class="masterActive ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white'"
                                      x-text="masterActive ? '{{ __('ONLINE') }}' : '{{ __('SYSTEM DISABLED') }}'">
                                </span>
                            </div>
                            <p class="text-[11px] transition-colors"
                               :class="masterActive ? 'text-indigo-700 dark:text-indigo-400' : 'text-rose-700 dark:text-rose-400'">
                                {{ __('When turned OFF, all notices are completely disabled across the entire website for all users.') }}
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <button type="button"
                                    @click="toggleNotice('master')"
                                    class="notice-switch-btn"
                                    :class="{ 'is-active is-active-master': masterActive }"
                                    role="switch"
                                    :aria-checked="masterActive ? 'true' : 'false'">
                                <span class="notice-switch-knob"></span>
                            </button>
                            <input type="hidden" name="notice_master_active" :value="masterActive ? '1' : '0'">
                        </div>
                    </div>

                    {{-- ================= 1. GLOBAL CHANNEL ================= --}}
                    <div x-show="noticeChannel === 'global'" class="space-y-6">
                        <div class="flex items-center justify-between p-4 bg-slate-50/80 dark:bg-slate-800/40 rounded-xl border border-slate-200 dark:border-slate-700/80">
                            <div class="space-y-0.5">
                                <label class="text-xs font-bold text-slate-800 dark:text-slate-200 block">
                                    {{ __('Enable Global Notice') }}
                                </label>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400 block">
                                    {{ __('When no role-specific notice is active, this global notice will be shown to everyone.') }}
                                </span>
                            </div>
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
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">{{ __('Color Theme') }}</label>
                                <select name="notice_theme" class="w-full px-3.5 py-2 text-xs font-semibold border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 dark:text-slate-100">
                                    <option value="danger"  {{ ($noticeTheme ?? 'danger') === 'danger' ? 'selected' : '' }}>🔴 {{ __('Emergency Red') }}</option>
                                    <option value="warning" {{ ($noticeTheme ?? '') === 'warning' ? 'selected' : '' }}>🟡 {{ __('Amber Warning') }}</option>
                                    <option value="info"    {{ ($noticeTheme ?? '') === 'info' ? 'selected' : '' }}>🔵 {{ __('Sky Blue') }}</option>
                                    <option value="success" {{ ($noticeTheme ?? '') === 'success' ? 'selected' : '' }}>🟢 {{ __('Emerald Green') }}</option>
                                    <option value="indigo"  {{ ($noticeTheme ?? '') === 'indigo' ? 'selected' : '' }}>🟣 {{ __('Indigo Brand') }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">{{ __('Scroll Speed') }}</label>
                                <select name="notice_speed" class="w-full px-3.5 py-2 text-xs font-semibold border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 dark:text-slate-100">
                                    <option value="4" {{ ($noticeSpeed ?? 8) == 4 ? 'selected' : '' }}>{{ __('Slow') }} (4)</option>
                                    <option value="8" {{ ($noticeSpeed ?? 8) == 8 ? 'selected' : '' }}>{{ __('Normal') }} (8)</option>
                                    <option value="12" {{ ($noticeSpeed ?? 8) == 12 ? 'selected' : '' }}>{{ __('Fast') }} (12)</option>
                                </select>
                            </div>
                        </div>

                        {{-- Bilingual Inputs for Global --}}
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 pt-2">
                            {{-- English Box --}}
                            <div class="p-4 bg-slate-50/70 dark:bg-slate-800/30 rounded-xl border border-slate-200 dark:border-slate-700/70 space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-800 dark:text-slate-200">
                                        <span>🇬🇧</span>
                                        <span>{{ __('English Version') }}</span>
                                    </span>
                                    <span class="text-[10px] text-slate-400">{{ __('Displayed when portal language is English.') }}</span>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1">{{ __('English Badge Text') }}</label>
                                    <input type="text" name="notice_badge_en" value="{{ old('notice_badge_en', $noticeBadgeEn ?? 'GLOBAL NOTICE') }}" maxlength="30"
                                           placeholder="e.g. GLOBAL NOTICE"
                                           class="w-full px-3 py-1.5 text-xs font-bold uppercase border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 dark:text-slate-100">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1">{{ __('English Notice Text') }}</label>
                                    <textarea name="notice_text_en" rows="3" placeholder="{{ __('Enter notice in English...') }}"
                                              class="w-full px-3.5 py-2.5 text-xs border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 leading-relaxed">{{ old('notice_text_en', $noticeTextEn ?? '') }}</textarea>
                                </div>
                            </div>

                            {{-- Bangla Box --}}
                            <div class="p-4 bg-emerald-50/30 dark:bg-emerald-950/10 rounded-xl border border-emerald-200/70 dark:border-emerald-900/40 space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-800 dark:text-emerald-300">
                                        <span>🇧🇩</span>
                                        <span>{{ __('Bangla Version') }}</span>
                                    </span>
                                    <span class="text-[10px] text-emerald-600/70 dark:text-emerald-400/60">{{ __('Displayed when portal language is Bangla.') }}</span>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1">{{ __('Bangla Badge Text') }}</label>
                                    <input type="text" name="notice_badge_bn" value="{{ old('notice_badge_bn', $noticeBadgeBn ?? 'সাধারণ নোটিশ') }}" maxlength="30"
                                           placeholder="যেমন: সাধারণ নোটিশ"
                                           class="w-full px-3 py-1.5 text-xs font-bold border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 dark:text-slate-100">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1">{{ __('Bangla Notice Text') }}</label>
                                    <textarea name="notice_text_bn" rows="3" placeholder="{{ __('Enter notice in Bangla...') }}"
                                              class="w-full px-3.5 py-2.5 text-xs border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 leading-relaxed">{{ old('notice_text_bn', $noticeTextBn ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ================= 2. RESELLER CHANNEL ================= --}}
                    <div x-show="noticeChannel === 'reseller'" class="space-y-6">
                        <div class="flex items-center justify-between p-4 bg-amber-50/60 dark:bg-amber-950/20 rounded-xl border border-amber-200 dark:border-amber-900/60">
                            <div class="space-y-0.5">
                                <label class="text-xs font-bold text-amber-900 dark:text-amber-300 block">
                                    {{ __('Enable Reseller Exclusive Notice') }}
                                </label>
                                <span class="text-[11px] text-amber-700 dark:text-amber-400 block">
                                    {{ __('When enabled, Resellers and POP Managers will see this notice instead of the global notice.') }}
                                </span>
                            </div>
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
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">{{ __('Color Theme') }}</label>
                                <select name="notice_reseller_theme" class="w-full px-3.5 py-2 text-xs font-semibold border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 dark:text-slate-100">
                                    <option value="warning" {{ ($noticeResellerTheme ?? 'warning') === 'warning' ? 'selected' : '' }}>🟡 {{ __('Amber Warning') }}</option>
                                    <option value="danger"  {{ ($noticeResellerTheme ?? '') === 'danger' ? 'selected' : '' }}>🔴 {{ __('Emergency Red') }}</option>
                                    <option value="info"    {{ ($noticeResellerTheme ?? '') === 'info' ? 'selected' : '' }}>🔵 {{ __('Sky Blue') }}</option>
                                    <option value="success" {{ ($noticeResellerTheme ?? '') === 'success' ? 'selected' : '' }}>🟢 {{ __('Emerald Green') }}</option>
                                    <option value="indigo"  {{ ($noticeResellerTheme ?? '') === 'indigo' ? 'selected' : '' }}>🟣 {{ __('Indigo Brand') }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">{{ __('Scroll Speed') }}</label>
                                <select name="notice_reseller_speed" class="w-full px-3.5 py-2 text-xs font-semibold border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 dark:text-slate-100">
                                    <option value="4" {{ ($noticeResellerSpeed ?? 8) == 4 ? 'selected' : '' }}>{{ __('Slow') }} (4)</option>
                                    <option value="8" {{ ($noticeResellerSpeed ?? 8) == 8 ? 'selected' : '' }}>{{ __('Normal') }} (8)</option>
                                    <option value="12" {{ ($noticeResellerSpeed ?? 8) == 12 ? 'selected' : '' }}>{{ __('Fast') }} (12)</option>
                                </select>
                            </div>
                        </div>

                        {{-- Bilingual Inputs for Reseller --}}
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 pt-2">
                            {{-- English Box --}}
                            <div class="p-4 bg-slate-50/70 dark:bg-slate-800/30 rounded-xl border border-slate-200 dark:border-slate-700/70 space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-800 dark:text-slate-200">
                                        <span>🇬🇧</span>
                                        <span>{{ __('English Version') }}</span>
                                    </span>
                                    <span class="text-[10px] text-slate-400">{{ __('Displayed when portal language is English.') }}</span>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1">{{ __('English Badge Text') }}</label>
                                    <input type="text" name="notice_reseller_badge_en" value="{{ old('notice_reseller_badge_en', $noticeResellerBadgeEn ?? 'RESELLER ALERT') }}" maxlength="30"
                                           placeholder="e.g. RESELLER ALERT"
                                           class="w-full px-3 py-1.5 text-xs font-bold uppercase border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 dark:text-slate-100">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1">{{ __('English Notice Text') }}</label>
                                    <textarea name="notice_reseller_text_en" rows="3" placeholder="{{ __('Enter notice in English...') }}"
                                              class="w-full px-3.5 py-2.5 text-xs border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-amber-500 leading-relaxed">{{ old('notice_reseller_text_en', $noticeResellerTextEn ?? '') }}</textarea>
                                </div>
                            </div>

                            {{-- Bangla Box --}}
                            <div class="p-4 bg-amber-50/40 dark:bg-amber-950/20 rounded-xl border border-amber-200/70 dark:border-amber-900/50 space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-800 dark:text-amber-300">
                                        <span>🇧🇩</span>
                                        <span>{{ __('Bangla Version') }}</span>
                                    </span>
                                    <span class="text-[10px] text-amber-700/70 dark:text-amber-400/60">{{ __('Displayed when portal language is Bangla.') }}</span>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1">{{ __('Bangla Badge Text') }}</label>
                                    <input type="text" name="notice_reseller_badge_bn" value="{{ old('notice_reseller_badge_bn', $noticeResellerBadgeBn ?? 'রিসেলার নোটিশ') }}" maxlength="30"
                                           placeholder="যেমন: রিসেলার নোটিশ"
                                           class="w-full px-3 py-1.5 text-xs font-bold border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 dark:text-slate-100">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1">{{ __('Bangla Notice Text') }}</label>
                                    <textarea name="notice_reseller_text_bn" rows="3" placeholder="{{ __('Enter notice in Bangla...') }}"
                                              class="w-full px-3.5 py-2.5 text-xs border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-amber-500 leading-relaxed">{{ old('notice_reseller_text_bn', $noticeResellerTextBn ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ================= 3. NOC CHANNEL ================= --}}
                    <div x-show="noticeChannel === 'noc'" class="space-y-6">
                        <div class="flex items-center justify-between p-4 bg-indigo-50/60 dark:bg-indigo-950/20 rounded-xl border border-indigo-200 dark:border-indigo-900/60">
                            <div class="space-y-0.5">
                                <label class="text-xs font-bold text-indigo-900 dark:text-indigo-300 block">
                                    {{ __('Enable NOC Exclusive Notice') }}
                                </label>
                                <span class="text-[11px] text-indigo-700 dark:text-indigo-400 block">
                                    {{ __('When enabled, NOC Engineers will see this notice instead of the global notice.') }}
                                </span>
                            </div>
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
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">{{ __('Color Theme') }}</label>
                                <select name="notice_noc_theme" class="w-full px-3.5 py-2 text-xs font-semibold border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 dark:text-slate-100">
                                    <option value="indigo"  {{ ($noticeNocTheme ?? 'indigo') === 'indigo' ? 'selected' : '' }}>🟣 {{ __('Indigo Brand') }}</option>
                                    <option value="danger"  {{ ($noticeNocTheme ?? '') === 'danger' ? 'selected' : '' }}>🔴 {{ __('Emergency Red') }}</option>
                                    <option value="warning" {{ ($noticeNocTheme ?? '') === 'warning' ? 'selected' : '' }}>🟡 {{ __('Amber Warning') }}</option>
                                    <option value="info"    {{ ($noticeNocTheme ?? '') === 'info' ? 'selected' : '' }}>🔵 {{ __('Sky Blue') }}</option>
                                    <option value="success" {{ ($noticeNocTheme ?? '') === 'success' ? 'selected' : '' }}>🟢 {{ __('Emerald Green') }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">{{ __('Scroll Speed') }}</label>
                                <select name="notice_noc_speed" class="w-full px-3.5 py-2 text-xs font-semibold border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 dark:text-slate-100">
                                    <option value="4" {{ ($noticeNocSpeed ?? 8) == 4 ? 'selected' : '' }}>{{ __('Slow') }} (4)</option>
                                    <option value="8" {{ ($noticeNocSpeed ?? 8) == 8 ? 'selected' : '' }}>{{ __('Normal') }} (8)</option>
                                    <option value="12" {{ ($noticeNocSpeed ?? 8) == 12 ? 'selected' : '' }}>{{ __('Fast') }} (12)</option>
                                </select>
                            </div>
                        </div>

                        {{-- Bilingual Inputs for NOC --}}
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 pt-2">
                            {{-- English Box --}}
                            <div class="p-4 bg-slate-50/70 dark:bg-slate-800/30 rounded-xl border border-slate-200 dark:border-slate-700/70 space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-800 dark:text-slate-200">
                                        <span>🇬🇧</span>
                                        <span>{{ __('English Version') }}</span>
                                    </span>
                                    <span class="text-[10px] text-slate-400">{{ __('Displayed when portal language is English.') }}</span>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1">{{ __('English Badge Text') }}</label>
                                    <input type="text" name="notice_noc_badge_en" value="{{ old('notice_noc_badge_en', $noticeNocBadgeEn ?? 'NOC DISPATCH') }}" maxlength="30"
                                           placeholder="e.g. NOC DISPATCH"
                                           class="w-full px-3 py-1.5 text-xs font-bold uppercase border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 dark:text-slate-100">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1">{{ __('English Notice Text') }}</label>
                                    <textarea name="notice_noc_text_en" rows="3" placeholder="{{ __('Enter notice in English...') }}"
                                              class="w-full px-3.5 py-2.5 text-xs border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 leading-relaxed">{{ old('notice_noc_text_en', $noticeNocTextEn ?? '') }}</textarea>
                                </div>
                            </div>

                            {{-- Bangla Box --}}
                            <div class="p-4 bg-indigo-50/40 dark:bg-indigo-950/20 rounded-xl border border-indigo-200/70 dark:border-indigo-900/50 space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-800 dark:text-indigo-300">
                                        <span>🇧🇩</span>
                                        <span>{{ __('Bangla Version') }}</span>
                                    </span>
                                    <span class="text-[10px] text-indigo-700/70 dark:text-indigo-400/60">{{ __('Displayed when portal language is Bangla.') }}</span>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1">{{ __('Bangla Badge Text') }}</label>
                                    <input type="text" name="notice_noc_badge_bn" value="{{ old('notice_noc_badge_bn', $noticeNocBadgeBn ?? 'এনওসি নোটিশ') }}" maxlength="30"
                                           placeholder="যেমন: এনওসি নোটিশ"
                                           class="w-full px-3 py-1.5 text-xs font-bold border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 dark:text-slate-100">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1">{{ __('Bangla Notice Text') }}</label>
                                    <textarea name="notice_noc_text_bn" rows="3" placeholder="{{ __('Enter notice in Bangla...') }}"
                                              class="w-full px-3.5 py-2.5 text-xs border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 leading-relaxed">{{ old('notice_noc_text_bn', $noticeNocTextBn ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Save Button --}}
                    <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3">
                        <span class="text-xs text-slate-400">
                            💡 {{ __('All 3 channels (Global, Reseller, NOC) settings will be saved in one click.') }}
                        </span>
                        <button type="submit" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white px-6 py-2.5 rounded-xl text-xs font-bold transition-all shadow-md cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('Save All Notices') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ================= TAB 2: BRANDING (LOGO & FAVICON) ================= --}}
        <div x-show="activeTab === 'branding'" x-cloak class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Logo Card --}}
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs overflow-hidden flex flex-col">
                    <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-100 dark:border-indigo-800/60 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </span>
                            <div>
                                <h3 class="text-sm font-bold text-slate-800 dark:text-white">{{ __('Company Brand Logo') }}</h3>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ __('Displayed on the sidebar, header, and login page.') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 flex-1 flex flex-col justify-between space-y-6">
                        {{-- Preview Box --}}
                        <div>
                            <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block mb-2">{{ __('Active Logo Preview') }}</span>
                            <div class="w-full h-36 rounded-xl border-2 border-dashed border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/40 p-4 flex items-center justify-center transition-all">
                                @if($logoPath)
                                    <img src="{{ asset('storage/' . $logoPath) }}" alt="Logo" class="max-h-24 max-w-full object-contain filter drop-shadow-xs">
                                @else
                                    <div class="text-center text-slate-400 dark:text-slate-500">
                                        <svg class="w-10 h-10 mx-auto mb-1 stroke-current opacity-40" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span class="text-xs">{{ __('No logo uploaded yet') }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Upload Form --}}
                        <form method="POST" action="{{ route('settings.logo.update') }}" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">{{ __('Upload New Logo') }}</label>
                                <input type="file" name="logo" accept="image/*" required
                                       class="block w-full text-xs text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 rounded-xl cursor-pointer bg-slate-50 dark:bg-slate-800/80 focus:outline-none file:mr-3 file:py-2.5 file:px-4 file:rounded-l-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-600 file:text-white hover:file:bg-indigo-700">
                                @error('logo')<p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>@enderror
                                <p class="text-[11px] text-slate-400 mt-1.5">{{ __('Formats: PNG, JPG, WEBP, or SVG. Maximum file size: 2MB.') }}</p>
                            </div>

                            <div class="pt-2 flex items-center justify-between">
                                <button type="submit" class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl text-xs font-bold transition-all shadow-sm cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                    <span>{{ __('Upload Logo') }}</span>
                                </button>
                                @if($logoPath)
                                <button type="submit" form="remove-logo-form" class="text-xs font-semibold text-rose-600 hover:text-rose-800 dark:text-rose-400 hover:underline px-2 py-1">
                                    {{ __('Remove Custom Logo') }}
                                </button>
                                @endif
                            </div>
                        </form>

                        @if($logoPath)
                        <form id="remove-logo-form" method="POST" action="{{ route('settings.logo.destroy') }}" class="hidden" onsubmit="return confirm('{{ __('Are you sure you want to remove the current logo?') }}')">
                            @csrf
                            @method('DELETE')
                        </form>
                        @endif
                    </div>
                </div>

                {{-- Favicon Card --}}
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs overflow-hidden flex flex-col">
                    <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-100 dark:border-emerald-800/60 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                            </span>
                            <div>
                                <h3 class="text-sm font-bold text-slate-800 dark:text-white">{{ __('Browser Favicon') }}</h3>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ __('Shown in browser tabs, favorites, and shortcuts.') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 flex-1 flex flex-col justify-between space-y-6">
                        {{-- Preview Box --}}
                        <div>
                            <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block mb-2">{{ __('Active Favicon Preview') }}</span>
                            <div class="w-full h-36 rounded-xl border-2 border-dashed border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/40 p-4 flex items-center justify-center transition-all">
                                @if($faviconPath)
                                    <div class="flex items-center gap-3 p-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xs">
                                        <img src="{{ asset('storage/' . $faviconPath) }}" alt="Favicon" class="w-8 h-8 object-contain">
                                        <div class="text-left">
                                            <span class="text-xs font-bold text-slate-800 dark:text-white block">{{ config('app.name', 'ISP Ticket') }}</span>
                                            <span class="text-[10px] text-slate-400 block">{{ __('Browser Tab Icon') }}</span>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-center text-slate-400 dark:text-slate-500">
                                        <svg class="w-10 h-10 mx-auto mb-1 stroke-current opacity-40" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                                        <span class="text-xs">{{ __('Default favicon active') }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Upload Form --}}
                        <form method="POST" action="{{ route('settings.favicon.update') }}" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">{{ __('Upload New Favicon') }}</label>
                                <input type="file" name="favicon" accept="image/*,.ico" required
                                       class="block w-full text-xs text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 rounded-xl cursor-pointer bg-slate-50 dark:bg-slate-800/80 focus:outline-none file:mr-3 file:py-2.5 file:px-4 file:rounded-l-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-600 file:text-white hover:file:bg-emerald-700">
                                @error('favicon')<p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>@enderror
                                <p class="text-[11px] text-slate-400 mt-1.5">{{ __('Square image recommended (512×512 or ICO). Max 512KB.') }}</p>
                            </div>

                            <div class="pt-2 flex items-center justify-between">
                                <button type="submit" class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-xl text-xs font-bold transition-all shadow-sm cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                    <span>{{ __('Upload Favicon') }}</span>
                                </button>
                                @if($faviconPath)
                                <button type="submit" form="remove-favicon-form" class="text-xs font-semibold text-rose-600 hover:text-rose-800 dark:text-rose-400 hover:underline px-2 py-1">
                                    {{ __('Remove Custom Favicon') }}
                                </button>
                                @endif
                            </div>
                        </form>

                        @if($faviconPath)
                        <form id="remove-favicon-form" method="POST" action="{{ route('settings.favicon.destroy') }}" class="hidden" onsubmit="return confirm('{{ __('Are you sure you want to remove the current favicon?') }}')">
                            @csrf
                            @method('DELETE')
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= TAB 3: THEME & BRAND COLORS ================= --}}
        @if(auth()->user()->isSuperAdminOnly())
        <div x-show="activeTab === 'theme'" x-cloak class="space-y-6">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs overflow-hidden">
                <div class="p-6 border-b border-slate-100 dark:border-slate-800/80 bg-gradient-to-r from-slate-50 via-white to-slate-50 dark:from-slate-900 dark:via-slate-850 dark:to-slate-900">
                    <div class="flex items-center gap-3">
                        <span class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-100 dark:border-indigo-800/60 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4 4 4 0 014-4c.48 0 .935.085 1.356.241A6.974 6.974 0 019 11a7 7 0 017-7c1.378 0 2.652.4 3.732 1.085A4 4 0 0121 9a4 4 0 01-4 4c-.48 0-.935-.085-1.356-.241A6.974 6.974 0 0115 15a7 7 0 01-7 7z"/></svg>
                        </span>
                        <div>
                            <h2 class="text-base font-bold text-slate-900 dark:text-white">{{ __('Site Theme & Palette Customization') }}</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                {{ __('Set the primary brand color (used for active buttons, sidebar accents) and secondary status accents.') }}
                            </p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('settings.theme.update') }}" id="form-theme" class="p-6 space-y-6">
                    @csrf

                    {{-- Quick Palette Presets --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-3">
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
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3" id="theme-presets">
                            @foreach($presets as $preset)
                            <button type="button"
                                    class="theme-preset-btn flex items-center justify-between p-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/60 hover:border-indigo-400 dark:hover:border-indigo-600 transition-all text-xs font-semibold text-slate-700 dark:text-slate-200 cursor-pointer text-left"
                                    data-primary="{{ $preset['primary'] }}" data-secondary="{{ $preset['secondary'] }}"
                                    title="{{ $preset['label'] }}">
                                <span>{{ $preset['label'] }}</span>
                                <div class="flex items-center -space-x-1.5 flex-shrink-0">
                                    <span class="w-4 h-4 rounded-full border border-white dark:border-slate-900 shadow-xs" style="background-color: {{ $preset['primary'] }}"></span>
                                    <span class="w-4 h-4 rounded-full border border-white dark:border-slate-900 shadow-xs" style="background-color: {{ $preset['secondary'] }}"></span>
                                </div>
                            </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Custom Color Inputs --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                        <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                {{ __('Primary Color') }}
                            </label>
                            <p class="text-[11px] text-slate-400 mb-3">{{ __('Applied to active sidebar links, primary action buttons & badges.') }}</p>
                            
                            <div class="flex items-center gap-3">
                                <input type="color" id="primary-color-picker" value="{{ $themePrimary }}"
                                       class="w-12 h-11 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer bg-transparent p-1">
                                <input type="text" id="primary-color-hex" name="primary_color" value="{{ $themePrimary }}" maxlength="7"
                                       class="flex-1 px-3.5 py-2.5 text-xs font-mono font-bold uppercase border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500"
                                       oninput="if(/^#[0-9a-fA-F]{6}$/.test(this.value)){document.getElementById('primary-color-picker').value=this.value;updateThemePreview()}">
                            </div>
                            <div id="primary-preview-bar" class="h-1.5 rounded-full mt-3 transition-all" style="background-color: {{ $themePrimary }};"></div>
                        </div>

                        <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                {{ __('Secondary Accent Color') }}
                            </label>
                            <p class="text-[11px] text-slate-400 mb-3">{{ __('Applied to highlights, status chips & complementary accents.') }}</p>
                            
                            <div class="flex items-center gap-3">
                                <input type="color" id="secondary-color-picker" value="{{ $themeSecondary }}"
                                       class="w-12 h-11 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer bg-transparent p-1">
                                <input type="text" id="secondary-color-hex" name="secondary_color" value="{{ $themeSecondary }}" maxlength="7"
                                       class="flex-1 px-3.5 py-2.5 text-xs font-mono font-bold uppercase border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500"
                                       oninput="if(/^#[0-9a-fA-F]{6}$/.test(this.value)){document.getElementById('secondary-color-picker').value=this.value;updateThemePreview()}">
                            </div>
                            <div id="secondary-preview-bar" class="h-1.5 rounded-full mt-3 transition-all" style="background-color: {{ $themeSecondary }};"></div>
                        </div>
                    </div>

                    {{-- Live Component Interactive Preview --}}
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                        <div class="px-5 py-3 bg-slate-50 dark:bg-slate-800/80 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">{{ __('Live Interactive UI Preview') }}</span>
                            <span class="text-[10px] text-slate-400">{{ __('Real-time visual demonstration') }}</span>
                        </div>
                        <div class="p-6 bg-white dark:bg-slate-900 space-y-4">
                            <div class="flex flex-wrap items-center gap-3">
                                <button type="button" id="prev-btn-primary" class="px-4 py-2 rounded-xl text-xs font-bold text-white shadow-sm transition-all" style="background-color: {{ $themePrimary }};">Primary Button</button>
                                <button type="button" id="prev-btn-secondary" class="px-4 py-2 rounded-xl text-xs font-bold text-white shadow-sm transition-all" style="background-color: {{ $themeSecondary }};">Secondary Button</button>
                                <span id="prev-badge-primary" class="px-3 py-1 rounded-full text-xs font-bold text-white shadow-xs" style="background-color: {{ $themePrimary }};">Primary Badge</span>
                                <span id="prev-badge-secondary" class="px-3 py-1 rounded-full text-xs font-bold text-white shadow-xs" style="background-color: {{ $themeSecondary }};">Active Status</span>
                                <span id="prev-link" class="text-xs font-bold transition-colors cursor-pointer" style="color: {{ $themePrimary }};">Active Navigation Link &rarr;</span>
                            </div>
                            <div id="prev-bar" class="h-1.5 rounded-full w-full" style="background: linear-gradient(90deg, {{ $themePrimary }}, {{ $themeSecondary }});"></div>
                        </div>
                    </div>

                    {{-- Action Row --}}
                    <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <span class="text-xs text-slate-500 dark:text-slate-400">
                            {{ __('Changes will immediately apply site-wide upon saving.') }}
                        </span>
                        <button type="submit" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-xl text-xs font-bold transition-all shadow-md cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>{{ __('Save Theme Palette') }}</span>
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
                if(document.getElementById('prev-badge-secondary')) document.getElementById('prev-badge-secondary').style.backgroundColor = s;
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
                document.querySelectorAll('.theme-preset-btn').forEach(b => b.classList.remove('ring-2', 'ring-indigo-600', 'border-transparent'));
                this.classList.add('ring-2', 'ring-indigo-600', 'border-transparent');
            });
        });
        </script>
        @endif

        {{-- ================= TAB 4: TICKET CATEGORIES ================= --}}
        <div x-show="activeTab === 'categories'" x-cloak class="space-y-6" x-data="{ showQuickAddCat: false }">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs overflow-hidden">
                <div class="p-6 border-b border-slate-100 dark:border-slate-800/80 bg-gradient-to-r from-slate-50 via-white to-slate-50 dark:from-slate-900 dark:via-slate-850 dark:to-slate-900">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <span class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-100 dark:border-indigo-800/60 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                            </span>
                            <div>
                                <h2 class="text-base font-bold text-slate-900 dark:text-white">{{ __('Support Ticket Categories') }}</h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                    {{ __('Manage the categories used for ticket routing, assignment scoping, and filtering.') }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2.5">
                            <button @click="showQuickAddCat = true" type="button" 
                                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-xs font-bold rounded-xl transition-all shadow-sm cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>{{ __('Add Category') }}</span>
                            </button>
                            <a href="{{ route('ticket-categories.index') }}" 
                               class="inline-flex items-center gap-1 px-3.5 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-xl transition-all">
                                <span>{{ __('Full Manager') }}</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Categories Grid Table --}}
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @forelse($categories ?? [] as $cat)
                        <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/40 hover:border-slate-300 dark:hover:border-slate-700 transition-all flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between gap-2 mb-2">
                                    <div class="flex items-center gap-2">
                                        <span class="w-3.5 h-3.5 rounded-full shadow-xs flex-shrink-0" style="background-color: {{ $cat->color }}"></span>
                                        <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate">{{ $cat->name }}</h4>
                                    </div>
                                    <span class="text-[10px] font-mono font-medium px-2 py-0.5 rounded-md bg-white dark:bg-slate-700 text-slate-500 dark:text-slate-300 border border-slate-200 dark:border-slate-600">
                                        {{ $cat->slug }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 line-clamp-2">
                                    {{ $cat->description ?: __('No extra description provided.') }}
                                </p>
                            </div>
                            <div class="mt-4 pt-3 border-t border-slate-200/60 dark:border-slate-700/60 flex items-center justify-between text-[11px]">
                                <span class="text-slate-400">{{ __('ID: #') }}{{ $cat->id }}</span>
                                <a href="{{ route('ticket-categories.index') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline font-semibold">
                                    {{ __('Manage') }} &rarr;
                                </a>
                            </div>
                        </div>
                        @empty
                        <div class="col-span-full py-12 text-center text-slate-400">
                            <p class="text-sm font-semibold">{{ __('No ticket categories found.') }}</p>
                            <button @click="showQuickAddCat = true" class="mt-2 text-xs text-indigo-600 font-bold hover:underline">
                                {{ __('Create first category now') }}
                            </button>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Quick Add Category Modal --}}
            <div x-show="showQuickAddCat" x-cloak
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
                <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full border border-slate-200 dark:border-slate-800 shadow-2xl p-6"
                     @click.away="showQuickAddCat = false">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 mb-5">
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            </span>
                            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ __('Add New Ticket Category') }}</h3>
                        </div>
                        <button @click="showQuickAddCat = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('ticket-categories.store') }}">
                        @csrf
                        <div class="space-y-4 text-left">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">{{ __('Category Name') }} <span class="text-rose-500">*</span></label>
                                <input type="text" name="name" required placeholder="e.g. Fiber Cut / Link Down"
                                       class="w-full border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs bg-slate-50 dark:bg-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">{{ __('Badge Accent Color') }}</label>
                                <div class="flex items-center gap-3">
                                    <input type="color" name="color" value="#4f46e5"
                                           class="h-10 w-14 border border-slate-200 dark:border-slate-700 rounded-xl p-1 bg-slate-50 dark:bg-slate-800 cursor-pointer">
                                    <span class="text-xs text-slate-400">{{ __('Used for colored chips on tickets') }}</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">{{ __('Description') }}</label>
                                <textarea name="description" rows="2" placeholder="Brief explanation of when to pick this category..."
                                          class="w-full border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2 text-xs bg-slate-50 dark:bg-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500"></textarea>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end gap-2.5 pt-4 border-t border-slate-100 dark:border-slate-800">
                            <button type="button" @click="showQuickAddCat = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer">
                                {{ __('Cancel') }}
                            </button>
                            <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm cursor-pointer">
                                {{ __('Save Category') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ================= TAB 5: DATABASE BACKUP & RESTORE ================= --}}
        @if(auth()->user()->isAdmin())
        <div x-show="activeTab === 'backup'" x-cloak class="space-y-6">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs overflow-hidden">
                <div class="p-6 border-b border-slate-100 dark:border-slate-800/80 bg-gradient-to-r from-slate-50 via-white to-slate-50 dark:from-slate-900 dark:via-slate-850 dark:to-slate-900">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <span class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-100 dark:border-emerald-800/60 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                            </span>
                            <div>
                                <h2 class="text-base font-bold text-slate-900 dark:text-white">{{ __('Database Backup & 30-Day Auto Retention') }}</h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                    {{ __('Automatic daily cron backup runs at 11:59 PM. Safely keeps up to 30 days of archives (FIFO rotation).') }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2.5">
                            <form method="POST" action="{{ route('settings.backup.create') }}">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-bold rounded-xl transition-all shadow-sm cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    <span>{{ __('Create Instant Backup Now') }}</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Backups Table --}}
                <div class="p-6">
                    <div class="overflow-x-auto border border-slate-200 dark:border-slate-800 rounded-xl">
                        <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                            <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-500 dark:text-slate-400 uppercase font-bold border-b border-slate-200 dark:border-slate-800">
                                <tr>
                                    <th class="px-4 py-3">#</th>
                                    <th class="px-4 py-3">{{ __('Backup File') }}</th>
                                    <th class="px-4 py-3">{{ __('Type / Method') }}</th>
                                    <th class="px-4 py-3">{{ __('Size') }}</th>
                                    <th class="px-4 py-3">{{ __('Timestamp') }}</th>
                                    <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @forelse($backups ?? [] as $index => $b)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors">
                                    <td class="px-4 py-3 font-mono text-slate-400">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            <span class="font-mono font-bold text-slate-800 dark:text-slate-200">{{ $b['filename'] }}</span>
                                            @if($index === 0)
                                            <span class="px-2 py-0.5 rounded-full text-[10px] bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 font-extrabold">{{ __('Latest') }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if(($b['type'] ?? 'manual') === 'auto')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300">
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                                                <span>⚙️ {{ __('Auto (Scheduled)') }}</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800 text-amber-700 dark:text-amber-300">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                <span>👤 {{ __('Manual (Admin)') }}</span>
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 font-semibold text-slate-500 dark:text-slate-400">{{ $b['human_size'] }}</td>
                                    <td class="px-4 py-3 text-slate-500 dark:text-slate-400">
                                        <span class="font-medium">{{ $b['created_at']->format('d M Y, h:i A') }}</span>
                                        <span class="text-[10px] text-slate-400 block">({{ $b['created_at']->diffForHumans() }})</span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="inline-flex items-center gap-2 justify-end">
                                            {{-- Download --}}
                                            <a href="{{ route('settings.backup.download', $b['filename']) }}"
                                               class="px-3 py-1.5 bg-indigo-50 dark:bg-indigo-950/60 hover:bg-indigo-100 dark:hover:bg-indigo-900 text-indigo-600 dark:text-indigo-400 rounded-lg font-bold text-[11px] transition-colors inline-flex items-center gap-1"
                                               title="{{ __('Download SQL Backup') }}">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                                <span>{{ __('Download') }}</span>
                                            </a>

                                            {{-- Restore --}}
                                            <form method="POST" action="{{ route('settings.backup.restore', $b['filename']) }}"
                                                  onsubmit="return confirm('⚠️ {{ __('Are you sure you want to restore database from :file? All current ticket and system records will be overwritten.', ['file' => $b['filename']]) }}')">
                                                @csrf
                                                <button type="submit"
                                                        class="px-3 py-1.5 bg-amber-50 dark:bg-amber-950/60 hover:bg-amber-100 dark:hover:bg-amber-900 text-amber-700 dark:text-amber-400 rounded-lg font-bold text-[11px] transition-colors inline-flex items-center gap-1 cursor-pointer"
                                                        title="{{ __('Restore this Backup') }}">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                    <span>{{ __('Restore') }}</span>
                                                </button>
                                            </form>

                                            {{-- Delete --}}
                                            <form method="POST" action="{{ route('settings.backup.destroy', $b['filename']) }}"
                                                  onsubmit="return confirm('{{ __('Permanently delete this backup file?') }}')">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                        class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/50 rounded-lg font-bold text-xs transition-colors cursor-pointer"
                                                        title="{{ __('Delete Backup') }}">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-12 text-center text-slate-400">
                                        <svg class="w-10 h-10 mx-auto mb-2 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                        <p class="font-semibold">{{ __('No database backups created yet.') }}</p>
                                        <p class="text-[11px] mt-1">{{ __('Click "Create Instant Backup Now" to capture your current state.') }}</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800 text-[11px] text-slate-500 dark:text-slate-400 flex items-start gap-2.5">
                        <span class="text-base leading-none">🛡️</span>
                        <div>
                            <span class="font-bold text-slate-700 dark:text-slate-300">{{ __('Automated Retention Policy (FIFO):') }}</span>
                            {{ __('The system automatically keeps up to 30 daily backup archives. When a 31st backup is generated, the oldest backup is automatically pruned to keep your disk usage optimal.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</x-app-layout>
