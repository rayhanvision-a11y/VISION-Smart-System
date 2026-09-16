<x-app-layout>
    @php $user = auth()->user(); @endphp

    {{-- Page Header + View Switcher --}}
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">{{ __('Board') }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Drag and drop tickets between columns') }}</p>
        </div>
        <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-800 rounded-lg p-1">
            <a href="{{ route('tickets.index') }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-white dark:hover:bg-slate-700 text-sm font-medium transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                {{ __('List') }}
            </a>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-white dark:bg-slate-700 shadow-sm text-slate-700 dark:text-slate-100 text-sm font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
                {{ __('Board') }}
            </span>
        </div>
    </div>

    {{-- Filter Bar --}}
    <form method="GET" action="{{ route('board.index') }}"
          class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm p-3 mb-5 flex flex-wrap items-center gap-3">
        <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300 cursor-pointer px-1">
            <input type="checkbox" name="my_tickets" value="1" {{ request('my_tickets') ? 'checked' : '' }}
                   class="rounded border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-indigo-500">
            {{ __('My Tickets') }}
        </label>
        @if(!$user->isReseller())
        <select name="assignee" class="border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-1.5 text-sm text-slate-700 dark:text-slate-100 bg-slate-50 dark:bg-slate-800 focus:ring-2 focus:ring-indigo-500">
            <option value="">{{ __('All Assignees') }}</option>
            @foreach($nocUsers as $noc)
            <option value="{{ $noc->id }}" {{ request('assignee') == $noc->id ? 'selected' : '' }}>{{ $noc->name }}</option>
            @endforeach
        </select>
        @endif
        <select name="priority" class="border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-1.5 text-sm text-slate-700 dark:text-slate-100 bg-slate-50 dark:bg-slate-800 focus:ring-2 focus:ring-indigo-500">
            <option value="">{{ __('All Priorities') }}</option>
            <option value="critical" {{ request('priority') === 'critical' ? 'selected' : '' }}>{{ __('Critical') }}</option>
            <option value="high"     {{ request('priority') === 'high'     ? 'selected' : '' }}>{{ __('High') }}</option>
            <option value="medium"   {{ request('priority') === 'medium'   ? 'selected' : '' }}>{{ __('Medium') }}</option>
            <option value="low"      {{ request('priority') === 'low'      ? 'selected' : '' }}>{{ __('Low') }}</option>
        </select>
        <div class="flex items-center gap-2 ml-auto">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-1.5 rounded-lg text-sm font-medium transition-colors">{{ __('Filter') }}</button>
            <a href="{{ route('board.index') }}" class="bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 px-4 py-1.5 rounded-lg text-sm font-medium transition-colors">{{ __('Clear') }}</a>
        </div>
    </form>

    {{-- Kanban Board --}}
    <div class="flex gap-4 overflow-x-auto pb-6 -mx-1 px-1" id="kanban-board" style="scrollbar-width: thin;">
        @foreach($columns as $status => $col)
        @php
            $accent = [
                'in_progress'                   => ['bar' => 'bg-blue-500',    'badge' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300',       'dot' => 'bg-blue-500'],
                'pending'                       => ['bar' => 'bg-orange-500',  'badge' => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-300', 'dot' => 'bg-orange-500'],
                'waiting_for_customer_feedback' => ['bar' => 'bg-violet-500',  'badge' => 'bg-violet-100 text-violet-700 dark:bg-violet-500/20 dark:text-violet-300', 'dot' => 'bg-violet-500'],
                'resolved'                      => ['bar' => 'bg-emerald-500','badge' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300', 'dot' => 'bg-emerald-500'],
            ][$status] ?? ['bar' => 'bg-slate-400', 'badge' => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300', 'dot' => 'bg-slate-400'];
        @endphp
        <div class="flex-shrink-0 w-80 flex flex-col bg-slate-100/70 dark:bg-slate-900/60 border border-slate-200/70 dark:border-slate-800 rounded-2xl overflow-hidden">
            {{-- Column accent bar --}}
            <div class="h-1 {{ $accent['bar'] }}"></div>

            {{-- Column header --}}
            <div class="flex items-center justify-between px-3.5 pt-3 pb-3">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-2 h-2 rounded-full {{ $accent['dot'] }} flex-shrink-0"></span>
                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-200 truncate">{{ $col['label'] }}</span>
                </div>
                <span class="flex-shrink-0 min-w-[1.5rem] text-center px-2 py-0.5 rounded-full text-xs font-bold {{ $accent['badge'] }}">
                    {{ $grouped[$status]->count() }}
                </span>
            </div>

            {{-- Card list --}}
            <div class="flex-1 space-y-2.5 min-h-24 px-2.5 pb-3 kanban-column"
                 data-status="{{ $status }}"
                 id="col-{{ $status }}">
                @forelse($grouped[$status] as $ticket)
                @php
                    $isOverdue = $ticket->due_at && $ticket->due_at->isPast() && $ticket->status !== 'resolved';
                    $priorityBorder = match($ticket->priority) {
                        'critical' => 'border-l-red-500',
                        'high'     => 'border-l-orange-500',
                        'medium'   => 'border-l-amber-500',
                        'low'      => 'border-l-slate-300 dark:border-l-slate-600',
                        default    => 'border-l-slate-300 dark:border-l-slate-600',
                    };
                    $priorityBadge = match($ticket->priority) {
                        'critical' => 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-300',
                        'high'     => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-300',
                        'medium'   => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300',
                        'low'      => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
                        default    => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
                    };
                @endphp
                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 border-l-4 {{ $priorityBorder }} rounded-xl shadow-sm p-3 cursor-{{ $user->isReseller() ? 'default' : 'grab' }} hover:shadow-md hover:-translate-y-0.5 transition-all kanban-card"
                     data-ticket-id="{{ $ticket->id }}"
                     data-status="{{ $status }}">
                    {{-- Top row: key + priority --}}
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <a href="{{ route('tickets.show', $ticket) }}"
                           class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-mono truncate">
                            {{ $ticket->ticket_key ?? '#'.$ticket->id }}
                        </a>
                        <span class="flex-shrink-0 px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide {{ $priorityBadge }}">{{ $ticket->priority }}</span>
                    </div>

                    {{-- Title --}}
                    <a href="{{ route('tickets.show', $ticket) }}"
                       class="block text-sm font-medium text-slate-800 dark:text-slate-100 hover:text-indigo-600 dark:hover:text-indigo-400 leading-snug mb-2.5 line-clamp-2">
                        {{ $ticket->title }}
                    </a>

                    {{-- Labels --}}
                    @if($ticket->labels->count())
                    <div class="flex flex-wrap gap-1 mb-2.5">
                        @foreach($ticket->labels as $label)
                        <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-medium text-white"
                              style="background-color: {{ $label->color }}">{{ $label->name }}</span>
                        @endforeach
                    </div>
                    @endif

                    {{-- Bottom row: category + due + assignee --}}
                    <div class="flex items-center justify-between gap-2 pt-2 border-t border-slate-100 dark:border-slate-700">
                        <div class="flex items-center gap-1.5 min-w-0">
                            <span class="text-[10px] px-1.5 py-0.5 bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-300 rounded font-medium truncate">
                                {{ ucfirst(str_replace('_',' ',$ticket->category)) }}
                            </span>
                            @if($isOverdue)
                            <span class="text-[10px] font-bold text-red-600 dark:text-red-400 flex-shrink-0">{{ __('OVERDUE') }}</span>
                            @elseif($ticket->due_at)
                            <span class="text-[10px] text-slate-400 dark:text-slate-500 flex-shrink-0">{{ $ticket->due_at->format('d M') }}</span>
                            @endif
                        </div>
                        @if($ticket->assignee)
                        <img src="{{ $ticket->assignee->avatarUrl() }}" alt="{{ $ticket->assignee->name }}"
                             class="w-6 h-6 rounded-full object-cover flex-shrink-0 ring-2 ring-white dark:ring-slate-800" title="{{ $ticket->assignee->name }}">
                        @else
                        <div class="w-6 h-6 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-slate-400 dark:text-slate-500 text-xs flex-shrink-0">?</div>
                        @endif
                    </div>
                </div>
                @empty
                <div class="py-10 flex flex-col items-center justify-center text-center gap-1.5 opacity-60">
                    <svg class="w-7 h-7 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                    <span class="text-xs text-slate-400 dark:text-slate-500">{{ __('No tickets') }}</span>
                </div>
                @endforelse
            </div>
        </div>
        @endforeach
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
    const isReseller = {{ $user->isReseller() ? 'true' : 'false' }};
    const isNoc      = {{ $user->isNoc() ? 'true' : 'false' }};
    const myId       = {{ $user->id }};
    const moveUrl    = '{{ route("board.move") }}';
    const csrfToken  = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    if (!isReseller) {
        document.querySelectorAll('.kanban-column').forEach(col => {
            Sortable.create(col, {
                group: 'kanban',
                animation: 150,
                ghostClass: 'opacity-50',
                onEnd(evt) {
                    const card      = evt.item;
                    const ticketId  = card.dataset.ticketId;
                    const newStatus = evt.to.dataset.status;
                    const oldStatus = card.dataset.status;

                    // NOC can only drag their own tickets
                    if (isNoc) {
                        // We'll trust the server to enforce — just submit
                    }

                    if (newStatus === oldStatus) return;

                    card.dataset.status = newStatus;

                    fetch(moveUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ ticket_id: ticketId, new_status: newStatus }),
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (!data.success) {
                            // Revert
                            const origCol = document.getElementById('col-' + oldStatus);
                            if (origCol) origCol.appendChild(card);
                            card.dataset.status = oldStatus;
                        } else {
                            // Update column counts
                            updateCounts();
                        }
                    })
                    .catch(() => {
                        const origCol = document.getElementById('col-' + oldStatus);
                        if (origCol) origCol.appendChild(card);
                    });
                }
            });
        });
    }

    function updateCounts() {
        document.querySelectorAll('.kanban-column').forEach(col => {
            const status = col.dataset.status;
            const count  = col.querySelectorAll('.kanban-card').length;
            const badge  = col.closest('.rounded-2xl')?.querySelector('.rounded-full');
            if (badge) badge.textContent = count;
        });
    }

    // Live background polling for Kanban Board
    let isDraggingCard = false;
    document.addEventListener('dragstart', () => isDraggingCard = true);
    document.addEventListener('dragend', () => setTimeout(() => isDraggingCard = false, 1000));

    (function() {
        function pollBoard() {
            if (isDraggingCard) return;
            fetch(window.location.href, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.text())
            .then(html => {
                if (isDraggingCard) return;
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                const newBoard = doc.getElementById('kanban-board');
                const currentBoard = document.getElementById('kanban-board');

                if (newBoard && currentBoard && newBoard.innerHTML.trim() !== currentBoard.innerHTML.trim()) {
                    currentBoard.innerHTML = newBoard.innerHTML;
                    if (typeof initKanbanDrag === 'function') initKanbanDrag();
                }
            })
            .catch(() => {});
        }

        setInterval(pollBoard, 10000);
    })();
    </script>
</x-app-layout>
