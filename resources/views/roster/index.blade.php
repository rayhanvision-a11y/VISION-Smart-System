<x-app-layout>
    @php $user = auth()->user(); @endphp

    {{-- Page Header + View Switcher --}}
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">{{ __('Shift Board') }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Drag and drop staff between shift columns') }}</p>
        </div>
        <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-800 rounded-lg p-1">
            <a href="{{ route('tickets.index') }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-white dark:hover:bg-slate-700 text-sm font-medium transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                {{ __('List') }}
            </a>
            <a href="{{ route('board.index') }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-white dark:hover:bg-slate-700 text-sm font-medium transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
                {{ __('Tickets Board') }}
            </a>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-white dark:bg-slate-700 shadow-sm text-slate-700 dark:text-slate-100 text-sm font-medium">
                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ __('Shift Board') }}
            </span>
        </div>
    </div>

    {{-- Filter Bar (Identical to Board layout) --}}
    <form method="GET" action="{{ route('roster.index') }}"
          class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm p-3 mb-5 flex flex-wrap items-center gap-3">
        
        <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300 cursor-pointer px-1">
            <input type="checkbox" name="my_shift" value="1" {{ request('my_shift') ? 'checked' : '' }}
                   class="rounded border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-indigo-500">
            {{ __('My Shift') }}
        </label>

        <select name="team" class="border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-1.5 text-sm text-slate-700 dark:text-slate-100 bg-slate-50 dark:bg-slate-800 focus:ring-2 focus:ring-indigo-500">
            <option value="">{{ __('All Teams') }}</option>
            @foreach($teams as $key => $name)
            <option value="{{ $key }}" {{ $selectedTeam === $key ? 'selected' : '' }}>{{ $name }}</option>
            @endforeach
        </select>

        <select name="role" class="border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-1.5 text-sm text-slate-700 dark:text-slate-100 bg-slate-50 dark:bg-slate-800 focus:ring-2 focus:ring-indigo-500">
            <option value="">{{ __('All Roles') }}</option>
            <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>{{ __('Admin') }}</option>
            <option value="noc" {{ request('role') === 'noc' ? 'selected' : '' }}>{{ __('NOC') }}</option>
            <option value="call_center" {{ request('role') === 'call_center' ? 'selected' : '' }}>{{ __('Call Center') }}</option>
            <option value="supervisor" {{ request('role') === 'supervisor' ? 'selected' : '' }}>{{ __('Supervisor') }}</option>
        </select>

        <div class="flex-1 min-w-48">
            <div class="relative">
                <input type="text" name="search" value="{{ $search }}" placeholder="{{ __('Search staff...') }}"
                       class="w-full border border-slate-200 dark:border-slate-700 rounded-lg pl-9 pr-3 py-1.5 text-sm text-slate-700 dark:text-slate-100 bg-slate-50 dark:bg-slate-800 focus:ring-2 focus:ring-indigo-500">
                <svg class="absolute left-2.5 top-2.5 w-4 h-4 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
        </div>

        <div class="flex items-center gap-2 ml-auto">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-1.5 rounded-lg text-sm font-medium transition-colors">{{ __('Filter') }}</button>
            <a href="{{ route('roster.index') }}" class="bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 px-4 py-1.5 rounded-lg text-sm font-medium transition-colors">{{ __('Clear') }}</a>
        </div>
    </form>

    {{-- Shift Board (Identical column structure to Board) --}}
    <div class="flex gap-4 overflow-x-auto pb-6 -mx-1 px-1" id="roster-board" style="scrollbar-width: thin;">
        @foreach($shifts as $shiftKey => $shiftMeta)
        @php
            $usersInShift = $grouped[$shiftKey] ?? collect();
            $accent = match($shiftKey) {
                'unassigned'  => ['bar' => 'bg-indigo-500',  'badge' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300', 'dot' => 'bg-indigo-500'],
                'day_shift'   => ['bar' => 'bg-blue-500',    'badge' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300',       'dot' => 'bg-blue-500'],
                'night_shift' => ['bar' => 'bg-orange-500',  'badge' => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-300', 'dot' => 'bg-orange-500'],
                'day_off'     => ['bar' => 'bg-violet-500',  'badge' => 'bg-violet-100 text-violet-700 dark:bg-violet-500/20 dark:text-violet-300', 'dot' => 'bg-violet-500'],
                default       => ['bar' => 'bg-slate-400',   'badge' => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',       'dot' => 'bg-slate-400'],
            };
        @endphp
        <div class="flex-shrink-0 w-80 flex flex-col bg-slate-100/70 dark:bg-slate-900/60 border border-slate-200/70 dark:border-slate-800 rounded-2xl overflow-hidden">
            {{-- Column accent bar --}}
            <div class="h-1 {{ $accent['bar'] }}"></div>

            {{-- Column header --}}
            <div class="flex items-center justify-between px-3.5 pt-3 pb-3">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-2 h-2 rounded-full {{ $accent['dot'] }} flex-shrink-0"></span>
                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-200 truncate">{{ $shiftMeta['label'] }}</span>
                    <span class="text-[11px] text-slate-400 font-normal">({{ $shiftMeta['time'] }})</span>
                </div>
                <span class="flex-shrink-0 min-w-[1.5rem] text-center px-2 py-0.5 rounded-full text-xs font-bold {{ $accent['badge'] }}">
                    {{ $usersInShift->count() }}
                </span>
            </div>

            {{-- Card list --}}
            <div class="flex-1 space-y-2.5 min-h-24 px-2.5 pb-3 roster-column"
                 data-shift="{{ $shiftKey }}"
                 id="col-{{ $shiftKey }}">
                
                @forelse($usersInShift as $stUser)
                @php $onDuty = $stUser->isOnDuty(); @endphp
                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm p-3 cursor-grab hover:shadow-md hover:-translate-y-0.5 transition-all roster-card"
                     data-user-id="{{ $stUser->id }}"
                     data-shift="{{ $shiftKey }}">
                    
                    {{-- Top row: ID / Role + Shift Pill --}}
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 font-mono truncate">
                            USR-{{ str_pad($stUser->id, 4, '0', STR_PAD_LEFT) }}
                        </span>
                        
                        @if($onDuty)
                        <span class="flex-shrink-0 px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">
                            🟢 {{ __('ON DUTY') }}
                        </span>
                        @else
                        <span class="flex-shrink-0 px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                            ⚪ {{ __('OFF DUTY') }}
                        </span>
                        @endif
                    </div>

                    {{-- Name --}}
                    <h4 class="text-sm font-medium text-slate-800 dark:text-slate-100 leading-snug mb-1 truncate">
                        {{ $stUser->name }}
                    </h4>
                    <p class="text-xs text-slate-400 dark:text-slate-500 truncate mb-2.5">{{ $stUser->email }}</p>

                    {{-- Bottom row: Team Tag + Avatar --}}
                    <div class="flex items-center justify-between gap-2 pt-2 border-t border-slate-100 dark:border-slate-700">
                        <div class="flex items-center gap-1.5 min-w-0">
                            @if($user->isAdmin() || $user->isSupervisorLevel())
                            <select onchange="updateUserTeam({{ $stUser->id }}, this.value)"
                                    class="text-[10px] px-1.5 py-0.5 bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-300 rounded font-medium border-0 focus:ring-1 focus:ring-indigo-500">
                                <option value="">+ {{ __('Team Tag') }}</option>
                                @foreach($teams as $tKey => $tLabel)
                                <option value="{{ $tKey }}" {{ $stUser->team === $tKey ? 'selected' : '' }}>{{ $tLabel }}</option>
                                @endforeach
                            </select>
                            @else
                            <span class="text-[10px] px-1.5 py-0.5 bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-300 rounded font-medium truncate">
                                {{ $stUser->team ?? strtoupper(str_replace('_',' ',$stUser->role)) }}
                            </span>
                            @endif
                        </div>

                        <div class="relative flex-shrink-0">
                            <img src="{{ $stUser->avatarUrl() }}" alt="{{ $stUser->name }}"
                                 class="w-6 h-6 rounded-full object-cover ring-2 ring-white dark:ring-slate-800" title="{{ $stUser->name }}">
                            @if($onDuty)
                            <span class="absolute -bottom-0.5 -right-0.5 w-2 h-2 bg-emerald-500 rounded-full border border-white dark:border-slate-800"></span>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="py-10 flex flex-col items-center justify-center text-center gap-1.5 opacity-60">
                    <svg class="w-7 h-7 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/></svg>
                    <span class="text-xs text-slate-400 dark:text-slate-500">{{ __('No staff') }}</span>
                </div>
                @endforelse

            </div>
        </div>
        @endforeach
    </div>

    {{-- SortableJS Drag & Drop --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
    const updateShiftUrl = '{{ route("roster.update-shift") }}';
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    document.querySelectorAll('.roster-column').forEach(col => {
        Sortable.create(col, {
            group: 'roster-kanban',
            animation: 150,
            ghostClass: 'opacity-50',
            onEnd(evt) {
                const card = evt.item;
                const userId = card.dataset.userId;
                const newShift = evt.to.dataset.shift;
                const oldShift = card.dataset.shift;

                if (newShift === oldShift) return;

                card.dataset.shift = newShift;

                fetch(updateShiftUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ user_id: userId, shift: newShift }),
                })
                .then(r => r.json())
                .then(data => {
                    if (data.status !== 'ok') {
                        // Revert back
                        const origCol = document.getElementById('col-' + oldShift);
                        if (origCol) origCol.appendChild(card);
                        card.dataset.shift = oldShift;
                    } else {
                        updateRosterCounts();
                    }
                })
                .catch(() => {
                    const origCol = document.getElementById('col-' + oldShift);
                    if (origCol) origCol.appendChild(card);
                    card.dataset.shift = oldShift;
                });
            }
        });
    });

    function updateRosterCounts() {
        document.querySelectorAll('.roster-column').forEach(col => {
            const shift = col.dataset.shift;
            const count = col.querySelectorAll('.roster-card').length;
            const badge = col.closest('.rounded-2xl')?.querySelector('.rounded-full');
            if (badge) badge.textContent = count;
        });
    }

    function updateUserTeam(userId, teamName) {
        fetch(`/roster/users/${userId}/team`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ team: teamName })
        })
        .then(r => r.json())
        .catch(err => console.error(err));
    }
    </script>
</x-app-layout>
