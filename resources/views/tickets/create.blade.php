<x-app-layout>
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('tickets.index') }}" class="text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">{{ __('Create New Ticket') }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Fill in the details below to submit a support ticket.') }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left: Form --}}
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-6">
                <form method="POST" action="{{ route('tickets.store') }}">
                    @csrf

                    <div class="mb-5">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            {{ __('Title') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="title" value="{{ old('title') }}"
                               placeholder="{{ __('Brief summary of the issue...') }}"
                               class="w-full border border-slate-200 dark:border-slate-700 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 @error('title') border-red-400 bg-red-50 dark:bg-red-950/30 @enderror">
                        @error('title')
                            <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="mb-5">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            {{ __('Description') }} <span class="text-red-500">*</span>
                        </label>
                        <div id="desc-editor" class="@error('description') border border-red-400 @else border border-slate-200 dark:border-slate-700 @enderror rounded-xl bg-white dark:bg-slate-800"></div>
                        <input type="hidden" name="description" id="desc-hidden">
                        @error('description')
                            <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                {{ __('Category') }} <span class="text-red-500">*</span>
                            </label>
                            <select name="category"
                                    class="w-full border border-slate-200 dark:border-slate-700 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 @error('category') border-red-400 @enderror">
                                <option value="">{{ __('Select category...') }}</option>
                                @if(isset($categories) && $categories->count() > 0)
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->slug }}" {{ old('category') === $cat->slug ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
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
                                <option value="low"      {{ old('priority') === 'low'                        ? 'selected' : '' }}>{{ __('Low') }}</option>
                                <option value="medium"   {{ old('priority', 'medium') === 'medium'           ? 'selected' : '' }}>{{ __('Medium') }}</option>
                                <option value="high"     {{ old('priority') === 'high'                       ? 'selected' : '' }}>{{ __('High') }}</option>
                                <option value="critical" {{ old('priority') === 'critical'                   ? 'selected' : '' }}>{{ __('Critical') }}</option>
                            </select>
                            @error('priority')<p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    @php
                        $areaMasterList = \App\Models\Area::where('is_active', true)->orderBy('name')->pluck('name')->toArray();
                        $areaLegacyList = \App\Models\Ticket::whereNotNull('area')->where('area','!=','')
                            ->distinct()->orderBy('area')->limit(200)->pluck('area')
                            ->diff($areaMasterList)->values()->toArray();
                        $allAreas = array_merge($areaMasterList, $areaLegacyList);
                        $oldArea = old('area');
                        $isCustom = $oldArea && ! in_array($oldArea, $allAreas, true);
                    @endphp
                    @php
                        $assignableUsers = $nocUsers->map(fn ($u) => [
                            'id' => $u->id,
                            'name' => $u->name,
                            'team' => $u->team,
                            'role' => $u->role,
                            'on_duty' => $u->isOnDuty(),
                        ])->values();
                        $oldAssigned = old('assigned_to');
                        $preSelected = $nocUsers->firstWhere('id', (int) $oldAssigned);
                    @endphp
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                        {{-- 📍 Area (searchable dropdown + add-new toggle) --}}
                        <div x-data="{ mode: '{{ $isCustom ? 'custom' : 'select' }}', value: @js($oldArea) }">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                <span class="inline-flex items-center gap-1">📍 {{ __('Area') }}</span>
                            </label>
                            <div class="flex gap-2">
                                <select x-show="mode === 'select'" x-model="value" name="area"
                                        class="flex-1 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 @error('area') border-red-400 @enderror">
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
                                       class="flex-1 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800">
                                <button type="button" @click="mode = mode === 'select' ? 'custom' : 'select'; value = ''"
                                        class="px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 whitespace-nowrap">
                                    <span x-show="mode === 'select'">+ {{ __('New') }}</span>
                                    <span x-show="mode === 'custom'">← {{ __('List') }}</span>
                                </button>
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1.5">
                                {{ count($allAreas) }} {{ __('areas') }}
                                @if(auth()->user()->isAdmin() || auth()->user()->isNoc() || auth()->user()->isSupervisorLevel())
                                    · <a href="{{ route('areas.index') }}" class="text-indigo-600 hover:underline">{{ __('Manage') }}</a>
                                @endif
                            </p>
                            @error('area')<p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>@enderror
                        </div>

                        {{-- Assign To — searchable combobox --}}
                        @if(!auth()->user()->isReseller())
                        <div x-data="{
                            open: false,
                            query: '',
                            selectedId: {{ $oldAssigned ? (int) $oldAssigned : 'null' }},
                            selectedLabel: @js($preSelected ? ($preSelected->isOnDuty() ? '🟢 ' : '⚪ ').$preSelected->name.($preSelected->team ? ' · '.$preSelected->team : '') : ''),
                            users: @js($assignableUsers),
                            get filtered() {
                                if (!this.query) return this.users;
                                const q = this.query.toLowerCase();
                                return this.users.filter(u =>
                                    u.name.toLowerCase().includes(q) ||
                                    (u.team||'').toLowerCase().includes(q) ||
                                    (u.role||'').toLowerCase().includes(q)
                                );
                            },
                            pick(u) {
                                this.selectedId = u.id;
                                this.selectedLabel = (u.on_duty ? '🟢 ' : '⚪ ') + u.name + (u.team ? ' · ' + u.team : '');
                                this.open = false;
                                this.query = '';
                            },
                            clear() {
                                this.selectedId = null;
                                this.selectedLabel = '';
                                this.query = '';
                                this.open = false;
                            }
                        }" class="relative">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Assign To') }}</label>
                            <input type="hidden" name="assigned_to" :value="selectedId ?? ''">

                            <div @click.away="open = false">
                                <button type="button" @click="open = !open"
                                        class="w-full flex items-center justify-between border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 text-left">
                                    <span x-show="selectedLabel" x-text="selectedLabel" class="truncate"></span>
                                    <span x-show="!selectedLabel" class="text-slate-400">{{ __('Leave Unassigned') }}</span>
                                    <span class="flex items-center gap-1">
                                        <span x-show="selectedLabel" @click.stop="clear()" class="text-slate-400 hover:text-red-500 px-1">✕</span>
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </span>
                                </button>

                                <div x-show="open" x-cloak x-transition
                                     class="absolute z-30 mt-1 w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg shadow-lg max-h-72 flex flex-col">
                                    <div class="p-2 border-b border-slate-100 dark:border-slate-700">
                                        <input type="text" x-model="query" x-ref="searchInput"
                                               @keydown.enter.prevent="if (filtered.length) pick(filtered[0])"
                                               placeholder="{{ __('Search by name, team, role…') }}"
                                               class="w-full px-2.5 py-1.5 text-sm border border-slate-200 dark:border-slate-700 rounded-md bg-slate-50 dark:bg-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                                    </div>
                                    <div class="overflow-y-auto flex-1">
                                        <template x-for="u in filtered" :key="u.id">
                                            <button type="button" @click="pick(u)"
                                                    class="w-full text-left px-3 py-2 text-sm hover:bg-indigo-50 dark:hover:bg-indigo-950/40 flex items-center justify-between gap-2">
                                                <span class="flex items-center gap-2 min-w-0">
                                                    <span x-text="u.on_duty ? '🟢' : '⚪'"></span>
                                                    <span class="truncate">
                                                        <span x-text="u.name" class="font-medium text-slate-900 dark:text-white"></span>
                                                        <span x-show="u.team" class="text-slate-500 text-xs">· <span x-text="u.team"></span></span>
                                                    </span>
                                                </span>
                                                <span x-text="u.role.replaceAll('_',' ').toUpperCase()" class="text-[10px] text-slate-400 shrink-0"></span>
                                            </button>
                                        </template>
                                        <div x-show="filtered.length === 0" class="p-3 text-center text-xs text-slate-400">{{ __('No matches') }}</div>
                                    </div>
                                </div>
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1.5">{{ $nocUsers->count() }} {{ __('assignable staff') }}</p>
                        </div>
                        @endif
                    </div>

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
                                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition-colors shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ __('Submit Ticket') }}
                        </button>
                        <a href="{{ route('tickets.index') }}"
                           class="px-5 py-2.5 rounded-xl text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                            {{ __('Cancel') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Right: Help Card --}}
        <div class="space-y-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-5">
                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ __('Category Guide') }}
                </h3>
                <div class="space-y-3 text-xs text-slate-600 dark:text-slate-400">
                    <div class="flex gap-2">
                        <span class="w-2 h-2 rounded-full bg-red-400 flex-shrink-0 mt-1"></span>
                        <div><span class="font-semibold text-slate-700 dark:text-slate-300">{{ __('Line Fault') }}</span> — {{ __('No internet, slow speeds, or intermittent connectivity issues.') }}</div>
                    </div>
                    <div class="flex gap-2">
                        <span class="w-2 h-2 rounded-full bg-orange-400 flex-shrink-0 mt-1"></span>
                        <div><span class="font-semibold text-slate-700 dark:text-slate-300">{{ __('Router Issue') }}</span> — {{ __('Router configuration, Wi-Fi problems, or device not working.') }}</div>
                    </div>
                    <div class="flex gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 flex-shrink-0 mt-1"></span>
                        <div><span class="font-semibold text-slate-700 dark:text-slate-300">{{ __('New Connection') }}</span> — {{ __('Request for a new internet connection or service activation.') }}</div>
                    </div>
                    <div class="flex gap-2">
                        <span class="w-2 h-2 rounded-full bg-blue-400 flex-shrink-0 mt-1"></span>
                        <div><span class="font-semibold text-slate-700 dark:text-slate-300">{{ __('Billing') }}</span> — {{ __('Payment issues, invoice queries, or account billing questions.') }}</div>
                    </div>
                    <div class="flex gap-2">
                        <span class="w-2 h-2 rounded-full bg-slate-400 flex-shrink-0 mt-1"></span>
                        <div><span class="font-semibold text-slate-700 dark:text-slate-300">{{ __('Other') }}</span> — {{ __("Anything that doesn't fit the above categories.") }}</div>
                    </div>
                </div>
            </div>

            <div class="bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/50 rounded-xl p-4">
                <div class="flex gap-2">
                    <svg class="w-4 h-4 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div class="text-xs text-amber-700 dark:text-amber-300">
                        <span class="font-semibold">{{ __('Priority tips:') }}</span> {{ __('Use') }} <strong>{{ __('Critical') }}</strong> {{ __('only for complete outages affecting multiple users. For most issues,') }} <strong>{{ __('Medium') }}</strong> {{ __('or') }} <strong>{{ __('High') }}</strong> {{ __('is appropriate.') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

<style>
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

        // Restore old value if validation failed
        var oldVal = @json(old('description', ''));
        if (oldVal) {
            descQuill.clipboard.dangerouslyPasteHTML(oldVal);
            descHidden.value = oldVal;
        }

        // Keep hidden input in sync on every change
        descQuill.on('text-change', function() {
            var text = descQuill.getText().trim();
            descHidden.value = text ? descQuill.root.innerHTML : '';
        });

    }, 50);
})();
</script>
</x-app-layout>
