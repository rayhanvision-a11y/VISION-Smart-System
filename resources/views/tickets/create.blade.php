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

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('tickets.index') }}" class="text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">{{ __('Create New Ticket') }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Choose the ticket type below and fill in the required fields.') }}</p>
        </div>
    </div>

    <div x-data="{ type: '{{ $oldType }}' }" class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left: Form --}}
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">

                {{-- Tab Switcher --}}
                <div class="grid grid-cols-2 border-b border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-950/40">
                    <button type="button" @click="type = 'internal'"
                            :class="type === 'internal'
                                ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 border-b-2 border-indigo-500'
                                : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 border-b-2 border-transparent'"
                            class="flex items-center justify-center gap-2 px-4 py-3.5 text-sm font-semibold transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                        </svg>
                        {{ __('Internal Ticket') }}
                        <span x-show="type === 'internal'" class="text-[10px] font-medium px-2 py-0.5 rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300">{{ __('Team') }}</span>
                    </button>
                    <button type="button" @click="type = 'external'"
                            :class="type === 'external'
                                ? 'bg-white dark:bg-slate-900 text-emerald-600 dark:text-emerald-400 border-b-2 border-emerald-500'
                                : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 border-b-2 border-transparent'"
                            class="flex items-center justify-center gap-2 px-4 py-3.5 text-sm font-semibold transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        {{ __('External Ticket') }}
                        <span x-show="type === 'external'" class="text-[10px] font-medium px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300">{{ __('Client') }}</span>
                    </button>
                </div>

                <div class="p-6">
                    {{-- Info banner --}}
                    <div x-show="type === 'internal'" x-cloak
                         class="mb-5 flex items-start gap-3 p-3.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/30 border border-indigo-200 dark:border-indigo-800/60">
                        <svg class="w-5 h-5 text-indigo-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="text-xs text-indigo-800 dark:text-indigo-200 leading-relaxed">
                            <strong>{{ __('Internal Ticket') }}</strong> — {{ __('For team-only issues (staff, office, internal tasks). This ticket will NOT be synced to the Google Sheet.') }}
                        </div>
                    </div>
                    <div x-show="type === 'external'" x-cloak
                         class="mb-5 flex items-start gap-3 p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/60">
                        <svg class="w-5 h-5 text-emerald-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <div class="text-xs text-emerald-800 dark:text-emerald-200 leading-relaxed">
                            <strong>{{ __('External Ticket') }}</strong> — {{ __('For customer complaints. Client info fills automatically into the Google Sheet.') }}
                        </div>
                    </div>

                    <form method="POST" action="{{ route('tickets.store') }}">
                        @csrf
                        <input type="hidden" name="ticket_type" :value="type">

                        {{-- Title (both types) --}}
                        <div class="mb-5">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                {{ __('Title') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="title" value="{{ old('title') }}"
                                   placeholder="{{ __('Brief summary of the issue…') }}"
                                   class="w-full border border-slate-200 dark:border-slate-700 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 @error('title') border-red-400 bg-red-50 dark:bg-red-950/30 @enderror">
                            @error('title')<p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>@enderror
                        </div>

                        {{-- Description (both types) --}}
                        <div class="mb-5">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                {{ __('Description') }} <span class="text-red-500">*</span>
                            </label>
                            <div id="desc-editor" class="@error('description') border border-red-400 @else border border-slate-200 dark:border-slate-700 @enderror rounded-xl bg-white dark:bg-slate-800"></div>
                            <input type="hidden" name="description" id="desc-hidden">
                            @error('description')<p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>@enderror
                        </div>

                        {{-- Category + Priority (both types) --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                    {{ __('Category') }} <span x-show="type === 'external'" class="text-red-500">*</span>
                                    <span x-show="type === 'internal'" class="text-[10px] font-normal text-slate-400">({{ __('Optional') }})</span>
                                </label>
                                <select name="category"
                                        class="w-full border border-slate-200 dark:border-slate-700 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 @error('category') border-red-400 @enderror">
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
                                @error('category')<p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                    {{ __('Priority') }} <span class="text-red-500">*</span>
                                </label>
                                <select name="priority"
                                        class="w-full border border-slate-200 dark:border-slate-700 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 @error('priority') border-red-400 @enderror">
                                    <option value="low"      {{ old('priority') === 'low'                ? 'selected' : '' }}>{{ __('Low') }}</option>
                                    <option value="medium"   {{ old('priority', 'medium') === 'medium'   ? 'selected' : '' }}>{{ __('Medium') }}</option>
                                    <option value="high"     {{ old('priority') === 'high'               ? 'selected' : '' }}>{{ __('High') }}</option>
                                    <option value="critical" {{ old('priority') === 'critical'           ? 'selected' : '' }}>{{ __('Critical') }}</option>
                                </select>
                                @error('priority')<p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        {{-- Assign To (both types) --}}
                        @if(!auth()->user()->isReseller())
                        <div class="mb-5">
                            <label for="assigned_to" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                {{ __('Assign To') }}
                            </label>
                            <select name="assigned_to" id="assigned_to"
                                    class="w-full border border-slate-200 dark:border-slate-700 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 @error('assigned_to') border-red-400 @enderror">
                                <option value="">— {{ __('Leave Unassigned') }} —</option>
                                @foreach($nocUsers as $user)
                                    <option value="{{ $user->id }}" {{ (string)old('assigned_to') === (string)$user->id ? 'selected' : '' }}>
                                        {{ $user->isOnDuty() ? '🟢' : '⚪' }} {{ $user->name }} {{ $user->team ? '· ' . $user->team : '' }} ({{ ucfirst(str_replace('_', ' ', $user->role)) }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1.5">{{ $nocUsers->count() }} {{ __('assignable staff') }}</p>
                            @error('assigned_to')<p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>@enderror
                        </div>
                        @endif

                        {{-- ========== EXTERNAL-ONLY SECTION ========== --}}
                        <div x-show="type === 'external'" x-cloak x-transition.opacity>

                            {{-- Client & Tracking Info --}}
                            <div class="mb-5 p-4 rounded-xl bg-gradient-to-br from-emerald-50/60 via-white to-white dark:from-emerald-950/20 dark:via-slate-900 dark:to-slate-900 border border-emerald-200/70 dark:border-emerald-900/40">
                                <div class="flex flex-wrap items-center justify-between gap-2 mb-3.5 pb-2.5 border-b border-emerald-200/50 dark:border-emerald-900/40">
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-800 dark:text-emerald-300 flex items-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        {{ __('Client & Tracking Info') }}
                                    </h4>
                                    <label class="inline-flex items-center gap-2 cursor-pointer select-none bg-white dark:bg-slate-800 border border-emerald-300/80 dark:border-emerald-800/80 px-3 py-1.5 rounded-lg hover:bg-emerald-50 dark:hover:bg-emerald-950/30 transition">
                                        <input type="checkbox" name="sync_to_google_sheet" value="1" {{ old('sync_to_google_sheet', '1') == '1' ? 'checked' : '' }}
                                               class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300 cursor-pointer">
                                        <span class="text-xs font-bold text-emerald-800 dark:text-emerald-300 flex items-center gap-1">
                                            📊 {{ __('Sync to Google Sheet') }}
                                        </span>
                                    </label>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Client ID') }}</label>
                                        <input type="text" name="client_id" value="{{ old('client_id') }}" placeholder="15642"
                                               class="w-full border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 bg-white dark:bg-slate-800">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Client Name') }}</label>
                                        <input type="text" name="client_name" value="{{ old('client_name') }}" placeholder="Md. Mikdad Hossain"
                                               class="w-full border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 bg-white dark:bg-slate-800">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Complaint Source') }}</label>
                                        <select name="complaint_source"
                                                class="w-full border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 bg-white dark:bg-slate-800">
                                            <option value="">— {{ __('Select') }} —</option>
                                            @foreach(['Phone','Office','Online','WhatsApp','Reseller'] as $src)
                                                <option value="{{ $src }}" {{ old('complaint_source') === $src ? 'selected' : '' }}>{{ $src }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('ONU Power') }}</label>
                                        <input type="text" name="onu_power" value="{{ old('onu_power') }}" placeholder="-23.56 dBm"
                                               class="w-full border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 bg-white dark:bg-slate-800">
                                    </div>
                                </div>
                            </div>

                            {{-- Area (external only) --}}
                            <div class="mb-5" x-data="{ mode: '{{ $isCustomArea ? 'custom' : 'select' }}', value: @js($oldArea) }">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                    <span class="inline-flex items-center gap-1">📍 {{ __('Area') }}</span>
                                </label>
                                <div class="flex gap-2">
                                    <select x-show="mode === 'select'" x-model="value" name="area"
                                            class="flex-1 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 bg-slate-50 dark:bg-slate-800 @error('area') border-red-400 @enderror">
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
                                           class="flex-1 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 bg-slate-50 dark:bg-slate-800">
                                    <button type="button" @click="mode = mode === 'select' ? 'custom' : 'select'; value = ''"
                                            class="px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 whitespace-nowrap">
                                        <span x-show="mode === 'select'">+ {{ __('New') }}</span>
                                        <span x-show="mode === 'custom'">← {{ __('List') }}</span>
                                    </button>
                                </div>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1.5">
                                    {{ count($allAreas) }} {{ __('areas') }}
                                    @if(auth()->user()->isAdmin() || auth()->user()->isNoc() || auth()->user()->isSupervisorLevel())
                                        · <a href="{{ route('areas.index') }}" class="text-emerald-600 hover:underline">{{ __('Manage') }}</a>
                                    @endif
                                </p>
                                @error('area')<p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        {{-- ========== END EXTERNAL SECTION ========== --}}

                        {{-- Labels (both types) --}}
                        @if($labels->count() > 0)
                        <div class="mb-5">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Labels') }}</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($labels as $label)
                                <label class="flex items-center gap-1.5 cursor-pointer">
                                    <input type="checkbox" name="labels[]" value="{{ $label->id }}"
                                           {{ in_array($label->id, old('labels', [])) ? 'checked' : '' }}
                                           class="rounded border-slate-300 dark:border-slate-700 dark:bg-slate-800 text-indigo-600 focus:ring-indigo-500">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium text-white shadow-2xs"
                                          style="background-color: {{ $label->color }}">
                                        {{ $label->name }}
                                    </span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <div class="flex items-center gap-3 pt-4 border-t border-slate-100 dark:border-slate-800 mt-6">
                            <button type="submit"
                                    :class="type === 'internal' ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-emerald-600 hover:bg-emerald-700'"
                                    class="inline-flex items-center gap-2 text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition-colors shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span x-text="type === 'internal' ? '{{ __('Submit Internal Ticket') }}' : '{{ __('Submit External Ticket') }}'"></span>
                            </button>
                            <a href="{{ route('tickets.index') }}"
                               class="px-5 py-2.5 rounded-xl text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                                {{ __('Cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Right: Help Card --}}
        <div class="space-y-4">
            {{-- Type explainer --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-5">
                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ __('Which type?') }}
                </h3>
                <div class="space-y-3 text-xs text-slate-600 dark:text-slate-400">
                    <div :class="type === 'internal' ? 'ring-2 ring-indigo-500 bg-indigo-50/60 dark:bg-indigo-950/30' : 'bg-slate-50 dark:bg-slate-800/40'"
                         class="p-3 rounded-lg border border-slate-200/60 dark:border-slate-800 transition-all cursor-pointer"
                         @click="type = 'internal'">
                        <div class="font-semibold text-indigo-600 dark:text-indigo-400 mb-1 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                            {{ __('Internal Ticket') }}
                        </div>
                        <p class="text-[11px] leading-relaxed">{{ __('Staff, office, or team-only tasks. Minimal fields. Not synced to Google Sheet.') }}</p>
                    </div>
                    <div :class="type === 'external' ? 'ring-2 ring-emerald-500 bg-emerald-50/60 dark:bg-emerald-950/30' : 'bg-slate-50 dark:bg-slate-800/40'"
                         class="p-3 rounded-lg border border-slate-200/60 dark:border-slate-800 transition-all cursor-pointer"
                         @click="type = 'external'">
                        <div class="font-semibold text-emerald-600 dark:text-emerald-400 mb-1 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            {{ __('External Ticket') }}
                        </div>
                        <p class="text-[11px] leading-relaxed">{{ __('Customer complaints with full client tracking info. Auto-synced to Google Sheet.') }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/50 rounded-xl p-4">
                <div class="flex gap-2">
                    <svg class="w-4 h-4 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div class="text-xs text-amber-700 dark:text-amber-300">
                        <span class="font-semibold">{{ __('Priority tips:') }}</span> {{ __('Use') }} <strong>{{ __('Critical') }}</strong> {{ __('only for complete outages. For most issues,') }} <strong>{{ __('Medium') }}</strong> {{ __('or') }} <strong>{{ __('High') }}</strong> {{ __('is appropriate.') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

<style>
    [x-cloak] { display: none !important; }
    #desc-editor .ql-toolbar { border:none; border-bottom:1px solid #e2e8f0; background:#f8fafc; padding:6px 10px; border-radius:12px 12px 0 0; }
    html.dark #desc-editor .ql-toolbar { border-bottom-color:#334155; background:#0f172a; }
    html.dark #desc-editor .ql-toolbar .ql-stroke { stroke: #cbd5e1; }
    html.dark #desc-editor .ql-toolbar .ql-fill { fill: #cbd5e1; }
    html.dark #desc-editor .ql-toolbar .ql-picker { color: #cbd5e1; }
    #desc-editor .ql-container { border:none; font-family:inherit; border-radius:0 0 12px 12px; }
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
