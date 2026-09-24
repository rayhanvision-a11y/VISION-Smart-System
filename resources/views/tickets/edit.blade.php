<x-app-layout>
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('tickets.show', $ticket) }}" class="text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">{{ __('Edit Ticket') }} <span class="text-slate-400 font-normal">#{{ $ticket->id }}</span></h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5 truncate max-w-md">{{ $ticket->title }}</p>
        </div>
    </div>

    <div class="max-w-lg">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-6">
            <form method="POST" action="{{ route('tickets.update', $ticket) }}">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">{{ __('Status') }}</label>
                    <select name="status" class="w-full border border-slate-200 dark:border-slate-700 rounded-lg px-3.5 py-2.5 text-sm text-slate-700 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800">
                        @foreach(['open','in_progress','resolved','closed','reopened'] as $s)
                        <option value="{{ $s }}" {{ $ticket->status === $s ? 'selected' : '' }}>{{ __(ucfirst(str_replace('_', ' ', $s))) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">{{ __('Priority') }}</label>
                    <select name="priority" class="w-full border border-slate-200 dark:border-slate-700 rounded-lg px-3.5 py-2.5 text-sm text-slate-700 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800">
                        @foreach(['low','medium','high','critical'] as $p)
                        <option value="{{ $p }}" {{ $ticket->priority === $p ? 'selected' : '' }}>{{ __(ucfirst($p)) }}</option>
                        @endforeach
                    </select>
                </div>

                @php
                    $editAssignable = $nocUsers->map(fn ($u) => [
                        'id' => $u->id,
                        'name' => $u->name,
                        'team' => $u->team,
                        'role' => $u->role,
                        'on_duty' => $u->isOnDuty(),
                    ])->values();
                    $currentAssignee = $nocUsers->firstWhere('id', (int) $ticket->assigned_to);
                @endphp
                <div class="mb-6" x-data="{
                    open: false,
                    query: '',
                    selectedId: {{ $ticket->assigned_to ? (int) $ticket->assigned_to : 'null' }},
                    selectedLabel: @js($currentAssignee ? ($currentAssignee->isOnDuty() ? '🟢 ' : '⚪ ').$currentAssignee->name.($currentAssignee->team ? ' · '.$currentAssignee->team : '') : ''),
                    users: @js($editAssignable),
                    get filtered() {
                        if (!this.query) return this.users;
                        const q = this.query.toLowerCase();
                        return this.users.filter(u => u.name.toLowerCase().includes(q) || (u.team||'').toLowerCase().includes(q) || (u.role||'').toLowerCase().includes(q));
                    },
                    pick(u) { this.selectedId = u.id; this.selectedLabel = (u.on_duty ? '🟢 ' : '⚪ ') + u.name + (u.team ? ' · ' + u.team : ''); this.open = false; this.query = ''; },
                    clear() { this.selectedId = null; this.selectedLabel = ''; this.query = ''; this.open = false; }
                }">
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">{{ __('Assign to Staff') }}</label>
                    <input type="hidden" name="assigned_to" :value="selectedId ?? ''">
                    <div @click.away="open = false" class="relative">
                        <button type="button" @click="open = !open" class="w-full flex items-center justify-between border border-slate-200 dark:border-slate-700 rounded-lg px-3.5 py-2.5 text-sm text-slate-700 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 text-left">
                            <span x-show="selectedLabel" x-text="selectedLabel" class="truncate"></span>
                            <span x-show="!selectedLabel" class="text-slate-400">{{ __('Unassigned') }}</span>
                            <span class="flex items-center gap-1">
                                <span x-show="selectedLabel" @click.stop="clear()" class="text-slate-400 hover:text-red-500 px-1">✕</span>
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </span>
                        </button>
                        <div x-show="open" x-cloak x-transition class="absolute z-30 mt-1 w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg shadow-lg max-h-72 flex flex-col">
                            <div class="p-2 border-b border-slate-100 dark:border-slate-700">
                                <input type="text" x-model="query" @keydown.enter.prevent="if (filtered.length) pick(filtered[0])" placeholder="{{ __('Search by name, team, role…') }}" class="w-full px-2.5 py-1.5 text-sm border border-slate-200 dark:border-slate-700 rounded-md bg-slate-50 dark:bg-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div class="overflow-y-auto flex-1">
                                <template x-for="u in filtered" :key="u.id">
                                    <button type="button" @click="pick(u)" class="w-full text-left px-3 py-2 text-sm hover:bg-indigo-50 dark:hover:bg-indigo-950/40 flex items-center justify-between gap-2">
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
                </div>

                <div class="mb-6">
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">📍 {{ __('Area') }}</label>
                    <input type="text" name="area" list="area-suggestions-edit" value="{{ old('area', $ticket->area) }}"
                           placeholder="{{ __('e.g. Shadhupara, Gopalpur') }}"
                           class="w-full border border-slate-200 dark:border-slate-700 rounded-lg px-3.5 py-2.5 text-sm text-slate-700 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800">
                    <datalist id="area-suggestions-edit">
                        @if(\Schema::hasTable('areas'))
                            @foreach(\App\Models\Area::where('is_active', true)->orderBy('name')->pluck('name') as $areaOption)
                                <option value="{{ $areaOption }}"></option>
                            @endforeach
                        @endif
                    </datalist>
                </div>

                <div class="flex gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <button type="submit"
                            class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ __('Save Changes') }}
                    </button>
                    <a href="{{ route('tickets.show', $ticket) }}"
                       class="px-5 py-2.5 rounded-xl text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                        {{ __('Cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
