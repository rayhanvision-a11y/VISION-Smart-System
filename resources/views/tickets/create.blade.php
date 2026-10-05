<x-app-layout>
    @php
        $areaMasterList = \Schema::hasTable('areas')
            ? \App\Models\Area::where('is_active', true)->orderBy('name')->pluck('name')->toArray()
            : [];
        $areaLegacyList = \Schema::hasColumn('tickets', 'area')
            ? \App\Models\Ticket::whereNotNull('area')->where('area','!=','')
                ->distinct()->orderBy('area')->limit(200)->pluck('area')
                ->diff($areaMasterList)->values()->toArray()
            : [];
        $allAreas = array_merge($areaMasterList, $areaLegacyList);
        $oldArea = old('area');
        $isCustomArea = $oldArea && ! in_array($oldArea, $allAreas, true);
        $oldType = old('ticket_type', 'external');
    @endphp

    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('tickets.index') }}"
               class="w-9 h-9 flex items-center justify-center rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-500 hover:text-indigo-600 hover:border-indigo-300 dark:hover:border-indigo-500 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-800 dark:text-slate-100 leading-tight">{{ __('Create New Ticket') }}</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">{{ __('Pick a ticket type below and complete the required fields.') }}</p>
            </div>
        </div>
    </div>

    <div x-data="{ type: '{{ $oldType }}' }" class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ==== MAIN CARD ==== --}}
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">

                {{-- Pill-style tab switcher --}}
                <div class="p-4 border-b border-slate-100 dark:border-slate-800 bg-gradient-to-br from-slate-50 to-white dark:from-slate-950/60 dark:to-slate-900">
                    <div class="grid grid-cols-2 gap-2 p-1.5 bg-slate-100/80 dark:bg-slate-800/60 rounded-xl">
                        <button type="button" @click="type = 'internal'"
                                :class="type === 'internal'
                                    ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm ring-1 ring-indigo-200 dark:ring-indigo-800'
                                    : 'text-slate-500 dark:text-slate-400 hover:text-slate-700'"
                                class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            {{ __('Internal') }}
                        </button>
                        <button type="button" @click="type = 'external'"
                                :class="type === 'external'
                                    ? 'bg-white dark:bg-slate-900 text-emerald-600 dark:text-emerald-400 shadow-sm ring-1 ring-emerald-200 dark:ring-emerald-800'
                                    : 'text-slate-500 dark:text-slate-400 hover:text-slate-700'"
                                class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            {{ __('External') }}
                        </button>
                    </div>

                    {{-- Info hint below tabs --}}
                    <p x-show="type === 'internal'" x-cloak class="text-[11px] text-slate-500 dark:text-slate-400 mt-2.5 text-center leading-relaxed">
                        <svg class="w-3 h-3 inline text-indigo-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                        {{ __('Team-only issue — will not sync to Google Sheet') }}
                    </p>
                    <p x-show="type === 'external'" x-cloak class="text-[11px] text-slate-500 dark:text-slate-400 mt-2.5 text-center leading-relaxed">
                        <svg class="w-3 h-3 inline text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        {{ __('Customer complaint — client info auto-synced to Google Sheet') }}
                    </p>
                </div>

                <div class="p-5 sm:p-6">
                    <form method="POST" action="{{ route('tickets.store') }}" class="space-y-5">
                        @csrf
                        <input type="hidden" name="ticket_type" :value="type">

                        {{-- ==== EXTERNAL — Client fields ON TOP ==== --}}
                        <div x-show="type === 'external'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-5">
                            {{-- Client ID + Client Name --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">{{ __('Client ID') }}</label>
                                    <input type="text" name="client_id" value="{{ old('client_id') }}" placeholder="e.g. 15642"
                                           class="w-full bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-800 dark:text-slate-100 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
                                </div>
                                <div>
                                    <label class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">{{ __('Client Name') }}</label>
                                    <input type="text" name="client_name" value="{{ old('client_name') }}" placeholder="e.g. Md. Mikdad Hossain"
                                           class="w-full bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-800 dark:text-slate-100 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
                                </div>
                            </div>

                            {{-- Area + Address --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div x-data="{ mode: '{{ $isCustomArea ? 'custom' : 'select' }}', value: @js($oldArea) }">
                                    <label class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">📍 {{ __('Area') }}</label>
                                    <div class="flex gap-2">
                                        <select x-show="mode === 'select'" x-model="value" name="area"
                                                class="flex-1 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
                                            <option value="">— {{ __('Select area') }} —</option>
                                            @if(!empty($areaMasterList))
                                            <optgroup label="{{ __('Managed Areas') }}">
                                                @foreach($areaMasterList as $areaOption)
                                                    <option value="{{ $areaOption }}" {{ $oldArea === $areaOption ? 'selected' : '' }}>{{ $areaOption }}</option>
                                                @endforeach
                                            </optgroup>
                                            @endif
                                            @if(!empty($areaLegacyList))
                                            <optgroup label="{{ __('Legacy Areas') }}">
                                                @foreach($areaLegacyList as $legacyArea)
                                                    <option value="{{ $legacyArea }}" {{ $oldArea === $legacyArea ? 'selected' : '' }}>{{ $legacyArea }}</option>
                                                @endforeach
                                            </optgroup>
                                            @endif
                                        </select>
                                        <input x-show="mode === 'custom'" x-model="value" name="area" type="text"
                                               placeholder="{{ __('Type new area…') }}"
                                               class="flex-1 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
                                        <button type="button" @click="mode = mode === 'select' ? 'custom' : 'select'; value = ''"
                                                class="px-3 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold text-emerald-600 hover:bg-emerald-50 whitespace-nowrap transition">
                                            <span x-show="mode === 'select'">+</span>
                                            <span x-show="mode === 'custom'">←</span>
                                        </button>
                                    </div>
                                </div>

                                <div>
                                    <label class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">🏠 {{ __('Address') }}</label>
                                    <input type="text" name="address" value="{{ old('address') }}" placeholder="{{ __('House / road / landmark…') }}"
                                           class="w-full bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-800 dark:text-slate-100 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
                                </div>
                            </div>

                            {{-- Source + ONU Power --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">📞 {{ __('Complaint Source') }}</label>
                                    <select name="complaint_source"
                                            class="w-full bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
                                        <option value="">— {{ __('Select') }} —</option>
                                        @foreach(['Phone','Office','Online','WhatsApp','Reseller'] as $src)
                                            <option value="{{ $src }}" {{ old('complaint_source') === $src ? 'selected' : '' }}>{{ $src }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">📡 {{ __('ONU Power') }}</label>
                                    <input type="text" name="onu_power" value="{{ old('onu_power') }}" placeholder="-23.56 dBm"
                                           class="w-full bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-800 dark:text-slate-100 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
                                </div>
                            </div>
                        </div>

                        {{-- SECTION: Core fields (title/desc for internal, then category/priority/assign for both) --}}
                        <div class="space-y-5">
                            {{-- Title (Internal only) --}}
                            <div x-show="type === 'internal'" x-cloak>
                                <label class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">
                                    {{ __('Title') }}
                                    <span class="text-red-600 dark:text-red-400">*</span>
                                </label>
                                <input type="text" name="title" value="{{ old('title') }}"
                                       placeholder="{{ __('Brief summary of the issue…') }}"
                                       class="w-full bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-lg px-4 py-2.5 text-sm text-slate-800 dark:text-slate-100 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition @error('title') border-red-400 ring-2 ring-red-100 @enderror">
                                @error('title')<p class="text-red-500 text-xs mt-1.5 flex items-center gap-1">⚠ {{ $message }}</p>@enderror
                            </div>

                            {{-- Description (Internal only) --}}
                            <div x-show="type === 'internal'" x-cloak>
                                <label class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">
                                    {{ __('Description') }}
                                    <span class="text-red-600 dark:text-red-400">*</span>
                                </label>
                                <div id="desc-editor" class="rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus-within:ring-2 focus-within:ring-indigo-500 focus-within:border-transparent transition @error('description') border-red-400 @enderror"></div>
                                <input type="hidden" name="description" id="desc-hidden">
                                @error('description')<p class="text-red-500 text-xs mt-1.5">⚠ {{ $message }}</p>@enderror
                            </div>

                            {{-- Category + Priority side by side --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">
                                        {{ __('Category') }}
                                        <span x-show="type === 'external'" class="text-red-600 dark:text-red-400">*</span>
                                    </label>
                                    <select name="category"
                                            class="w-full bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition @error('category') border-red-400 @enderror">
                                        <option value="">{{ __('Select category…') }}</option>
                                        @if(isset($categories) && $categories->count() > 0)
                                            @foreach($categories as $cat)
                                                <option value="{{ $cat->slug }}" {{ old('category') === $cat->slug ? 'selected' : '' }}>{{ $cat->name }}</option>
                                            @endforeach
                                        @else
                                            <option value="line_fault"     {{ old('category') === 'line_fault'     ? 'selected' : '' }}>{{ __('Line Fault') }}</option>
                                            <option value="router_issue"   {{ old('category') === 'router_issue'   ? 'selected' : '' }}>{{ __('Router Issue') }}</option>
                                            <option value="new_connection" {{ old('category') === 'new_connection' ? 'selected' : '' }}>{{ __('New Connection') }}</option>
                                            <option value="billing"        {{ old('category') === 'billing'        ? 'selected' : '' }}>{{ __('Billing') }}</option>
                                            <option value="other"          {{ old('category') === 'other'          ? 'selected' : '' }}>{{ __('Other') }}</option>
                                        @endif
                                    </select>
                                    @error('category')<p class="text-red-500 text-xs mt-1.5">⚠ {{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">
                                        {{ __('Priority') }}
                                        <span class="text-red-600 dark:text-red-400">*</span>
                                    </label>
                                    <select name="priority"
                                            class="w-full bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition @error('priority') border-red-400 @enderror">
                                        <option value="low"      {{ old('priority') === 'low'                ? 'selected' : '' }}>🟢 {{ __('Low') }}</option>
                                        <option value="medium"   {{ old('priority', 'medium') === 'medium'   ? 'selected' : '' }}>🟡 {{ __('Medium') }}</option>
                                        <option value="high"     {{ old('priority') === 'high'               ? 'selected' : '' }}>🟠 {{ __('High') }}</option>
                                        <option value="critical" {{ old('priority') === 'critical'           ? 'selected' : '' }}>🔴 {{ __('Critical') }}</option>
                                    </select>
                                    @error('priority')<p class="text-red-500 text-xs mt-1.5">⚠ {{ $message }}</p>@enderror
                                </div>
                            </div>

                            {{-- Assign To --}}
                            @if(!auth()->user()->isReseller())
                            <div>
                                <label class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">
                                    {{ __('Assign To') }}
                                    <span class="text-[10px] font-medium text-slate-400 normal-case">({{ __('Optional') }})</span>
                                </label>
                                @php
                                    $todayLeaderIds = \App\Models\DailyTechnicianTeam::whereDate('duty_date', today())->pluck('leader_id')->filter()->map(fn($i) => (int) $i)->all();
                                @endphp
                                <select name="assigned_to" id="assigned_to"
                                        class="w-full bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition @error('assigned_to') border-red-400 @enderror">
                                    <option value="">— {{ __('Leave Unassigned') }} —</option>
                                    @foreach($nocUsers as $user)
                                        <option value="{{ $user->id }}" {{ (string)old('assigned_to') === (string)$user->id ? 'selected' : '' }}>
                                            {{ in_array((int) $user->id, $todayLeaderIds, true) ? '👑 ' : '' }}{{ $user->isOnDuty() ? '🟢' : '⚪' }} {{ $user->name }}{{ $user->team ? ' · ' . $user->team : '' }} ({{ ucfirst(str_replace('_', ' ', $user->role)) }})
                                        </option>
                                    @endforeach
                                </select>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1.5">{{ $nocUsers->count() }} {{ __('assignable staff · 🟢 on-duty') }}</p>
                                @error('assigned_to')<p class="text-red-500 text-xs mt-1.5">⚠ {{ $message }}</p>@enderror
                            </div>
                            @endif
                        </div>

                        {{-- Labels --}}
                        @if($labels->count() > 0)
                        <div>
                            <label class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">🏷️ {{ __('Labels') }}</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($labels as $label)
                                <label class="flex items-center gap-1.5 cursor-pointer group">
                                    <input type="checkbox" name="labels[]" value="{{ $label->id }}"
                                           {{ in_array($label->id, old('labels', [])) ? 'checked' : '' }}
                                           class="peer sr-only">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium text-white opacity-50 peer-checked:opacity-100 peer-checked:ring-2 peer-checked:ring-offset-2 peer-checked:ring-offset-white dark:peer-checked:ring-offset-slate-900 transition"
                                          style="background-color: {{ $label->color }}; --tw-ring-color: {{ $label->color }};">
                                        {{ $label->name }}
                                    </span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        {{-- Actions --}}
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 pt-5 border-t border-slate-100 dark:border-slate-800">
                            <a href="{{ route('tickets.index') }}"
                               class="order-2 sm:order-1 text-center px-5 py-2.5 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                                {{ __('Cancel') }}
                            </a>
                            <button type="submit"
                                    :class="type === 'internal'
                                        ? 'bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500'
                                        : 'bg-emerald-600 hover:bg-emerald-700 focus:ring-emerald-500'"
                                    class="order-1 sm:order-2 inline-flex items-center justify-center gap-2 text-white px-6 py-3 rounded-lg text-sm font-bold shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span x-show="type === 'internal'">{{ __('Create Internal Ticket') }}</span>
                                <span x-show="type === 'external'">{{ __('Create External Ticket') }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ==== SIDEBAR ==== --}}
        <div class="space-y-4">
            {{-- Compare card --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm p-5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3">{{ __('Ticket Types') }}</h3>
                <div class="space-y-2.5">
                    <button type="button" @click="type = 'internal'"
                            :class="type === 'internal' ? 'ring-2 ring-indigo-500 bg-indigo-50/70 dark:bg-indigo-950/40 border-indigo-200 dark:border-indigo-800' : 'border-slate-200 dark:border-slate-700 hover:border-indigo-200 dark:hover:border-indigo-700 hover:bg-slate-50 dark:hover:bg-slate-800/50'"
                            class="w-full text-left p-3 rounded-xl border transition-all">
                        <div class="flex items-start gap-2.5">
                            <div :class="type === 'internal' ? 'bg-indigo-600 text-white' : 'bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600'"
                                 class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-semibold text-sm text-slate-800 dark:text-slate-100">{{ __('Internal Ticket') }}</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed">{{ __('Staff tasks, internal notes, team issues. Only 5 fields.') }}</div>
                            </div>
                        </div>
                    </button>

                    <button type="button" @click="type = 'external'"
                            :class="type === 'external' ? 'ring-2 ring-emerald-500 bg-emerald-50/70 dark:bg-emerald-950/40 border-emerald-200 dark:border-emerald-800' : 'border-slate-200 dark:border-slate-700 hover:border-emerald-200 dark:hover:border-emerald-700 hover:bg-slate-50 dark:hover:bg-slate-800/50'"
                            class="w-full text-left p-3 rounded-xl border transition-all">
                        <div class="flex items-start gap-2.5">
                            <div :class="type === 'external' ? 'bg-emerald-600 text-white' : 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600'"
                                 class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-semibold text-sm text-slate-800 dark:text-slate-100">{{ __('External Ticket') }}</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed">{{ __('Customer complaints. Full client tracking, auto-sync to Google Sheet.') }}</div>
                            </div>
                        </div>
                    </button>
                </div>
            </div>

            {{-- Priority guide --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm p-5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3">{{ __('Priority Guide') }}</h3>
                <div class="space-y-2 text-xs">
                    <div class="flex items-start gap-2">
                        <span class="mt-0.5">🔴</span>
                        <div><span class="font-semibold text-slate-700 dark:text-slate-200">Critical</span> <span class="text-slate-500 dark:text-slate-400">— Complete outage, multiple users affected</span></div>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="mt-0.5">🟠</span>
                        <div><span class="font-semibold text-slate-700 dark:text-slate-200">High</span> <span class="text-slate-500 dark:text-slate-400">— Single-user outage, urgent complaint</span></div>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="mt-0.5">🟡</span>
                        <div><span class="font-semibold text-slate-700 dark:text-slate-200">Medium</span> <span class="text-slate-500 dark:text-slate-400">— Normal complaints (default)</span></div>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="mt-0.5">🟢</span>
                        <div><span class="font-semibold text-slate-700 dark:text-slate-200">Low</span> <span class="text-slate-500 dark:text-slate-400">— Enquiries, non-urgent requests</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<style>
    [x-cloak] { display: none !important; }
    #desc-editor .ql-toolbar { border:none; border-bottom:1px solid #e2e8f0; background:#f8fafc; padding:6px 10px; border-radius:8px 8px 0 0; }
    html.dark #desc-editor .ql-toolbar { border-bottom-color:#334155; background:#0f172a; }
    html.dark #desc-editor .ql-toolbar .ql-stroke { stroke: #cbd5e1; }
    html.dark #desc-editor .ql-toolbar .ql-fill { fill: #cbd5e1; }
    html.dark #desc-editor .ql-toolbar .ql-picker { color: #cbd5e1; }
    #desc-editor .ql-container { border:none; font-family:inherit; border-radius:0 0 8px 8px; }
    #desc-editor .ql-editor { font-size:14px; min-height:140px; padding:12px 16px; color:#1e293b; }
    html.dark #desc-editor .ql-editor { color:#f1f5f9; }
    #desc-editor .ql-editor.ql-blank::before { color:#94a3b8; font-style:normal; }
    html.dark #desc-editor .ql-editor.ql-blank::before { color:#64748b; }
</style>

<script>
(function() {
    var timer = setInterval(function() {
        if (typeof Quill === 'undefined') return;
        clearInterval(timer);
        var descHidden = document.getElementById('desc-hidden');
        var descQuill = new Quill('#desc-editor', {
            theme: 'snow',
            placeholder: 'Describe the issue in detail...',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline'],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    ['blockquote', 'code-block', 'link', 'image'],
                    ['clean']
                ]
            }
        });
        var oldVal = @json(old('description', ''));
        if (oldVal) {
            descQuill.clipboard.dangerouslyPasteHTML(oldVal);
            descHidden.value = oldVal;
        }
        descQuill.on('text-change', function() {
            var text = descQuill.getText().trim();
            descHidden.value = text ? descQuill.root.innerHTML : '';
        });
    }, 50);
})();
</script>
</x-app-layout>
