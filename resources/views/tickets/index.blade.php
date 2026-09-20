<x-app-layout>
    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">
                {{ auth()->user()->isReseller() ? __('My Tickets') : __('All Tickets') }}
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                {{ trans_choice(':count ticket found|:count tickets found', $tickets->total(), ['count' => $tickets->total()]) }}
            </p>
        </div>
        <div class="flex items-center gap-3">
            {{-- View Switcher --}}
            <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-800 rounded-lg p-1">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-white dark:bg-slate-700 shadow-sm text-slate-700 dark:text-slate-100 text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    {{ __('List') }}
                </span>
                <a href="{{ route('board.index') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-white dark:hover:bg-slate-700 text-sm font-medium transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
                    {{ __('Board') }}
                </a>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-slate-400 dark:text-slate-500 text-sm cursor-not-allowed" title="{{ __('Coming soon') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    {{ __('Calendar') }}*
                </span>
            </div>
            <a href="{{ route('tickets.create') }}"
               class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('New Ticket') }}
            </a>
        </div>
    </div>

    {{-- Filter Bar --}}
    <form method="GET" action="{{ route('tickets.index') }}"
          class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm p-4 mb-6">
        <div class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-32">
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">{{ __('Status') }}</label>
                <select name="status" class="w-full border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-700 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-slate-50 dark:bg-slate-800">
                    <option value="">{{ __('All Statuses') }}</option>
                    <option value="in_progress"                   {{ request('status') === 'in_progress'                   ? 'selected' : '' }}>{{ __('In Progress') }}</option>
                    <option value="pending"                       {{ request('status') === 'pending'                       ? 'selected' : '' }}>{{ __('Pending') }}</option>
                    <option value="waiting_for_customer_feedback" {{ request('status') === 'waiting_for_customer_feedback' ? 'selected' : '' }}>{{ __('Waiting for Customer Feedback') }}</option>
                    <option value="resolved"                      {{ request('status') === 'resolved'                      ? 'selected' : '' }}>{{ __('Resolved') }}</option>
                    <option value="overdue"                       {{ request('status') === 'overdue'                       ? 'selected' : '' }}>{{ __('Overdue') }}</option>
                </select>
            </div>

            <div class="flex-1 min-w-32">
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">{{ __('Priority') }}</label>
                <select name="priority" class="w-full border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-700 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-slate-50 dark:bg-slate-800">
                    <option value="">{{ __('All Priorities') }}</option>
                    <option value="low"      {{ request('priority') === 'low'      ? 'selected' : '' }}>{{ __('Low') }}</option>
                    <option value="medium"   {{ request('priority') === 'medium'   ? 'selected' : '' }}>{{ __('Medium') }}</option>
                    <option value="high"     {{ request('priority') === 'high'     ? 'selected' : '' }}>{{ __('High') }}</option>
                    <option value="critical" {{ request('priority') === 'critical' ? 'selected' : '' }}>{{ __('Critical') }}</option>
                </select>
            </div>

            <div class="flex-1 min-w-32">
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">{{ __('Category') }}</label>
                <select name="category" class="w-full border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-700 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-slate-50 dark:bg-slate-800">
                    <option value="">{{ __('All Categories') }}</option>
                    @if(isset($categories) && $categories->count() > 0)
                        @foreach($categories as $cat)
                            <option value="{{ $cat->slug }}" {{ request('category') === $cat->slug ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    @else
                        <option value="line_fault"     {{ request('category') === 'line_fault'     ? 'selected' : '' }}>{{ __('Line Fault') }}</option>
                        <option value="router_issue"   {{ request('category') === 'router_issue'   ? 'selected' : '' }}>{{ __('Router Issue') }}</option>
                        <option value="new_connection" {{ request('category') === 'new_connection' ? 'selected' : '' }}>{{ __('New Connection') }}</option>
                        <option value="billing"        {{ request('category') === 'billing'        ? 'selected' : '' }}>{{ __('Billing') }}</option>
                        <option value="other"          {{ request('category') === 'other'          ? 'selected' : '' }}>{{ __('Other') }}</option>
                    @endif
                </select>
            </div>

            @if(!auth()->user()->isReseller())
            <div class="flex-1 min-w-32">
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">{{ __('Assignment') }}</label>
                <select name="assigned" class="w-full border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-700 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-slate-50 dark:bg-slate-800">
                    <option value="">{{ __('All Tickets') }}</option>
                    <option value="me" {{ request('assigned') === 'me' ? 'selected' : '' }}>{{ __('Assigned to Me') }}</option>
                </select>
            </div>
            @endif

            @if($allLabels->count() > 0)
            <div class="flex-1 min-w-32">
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">{{ __('Label') }}</label>
                <select name="label" class="w-full border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-700 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-slate-50 dark:bg-slate-800">
                    <option value="">{{ __('All Labels') }}</option>
                    @foreach($allLabels as $lbl)
                    <option value="{{ $lbl->id }}" {{ request('label') == $lbl->id ? 'selected' : '' }}>{{ $lbl->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="flex-1 min-w-48">
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">{{ __('Search') }}</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search by title...') }}"
                           class="w-full border border-slate-200 dark:border-slate-700 rounded-lg pl-9 pr-3 py-2 text-sm text-slate-700 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-slate-50 dark:bg-slate-800">
                    <svg class="absolute left-2.5 top-2.5 w-4 h-4 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>

            <div class="flex gap-2">
                <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    {{ __('Filter') }}
                </button>
                <a href="{{ route('tickets.index') }}"
                   class="bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    {{ __('Clear') }}
                </a>
            </div>
        </div>
    </form>

    {{-- Bulk Action Form --}}
    @if(auth()->user()->isAdmin())
    <div x-data="{ selectedIds: [], showBulk: false }" class="mb-4">
        <div x-show="showBulk" x-cloak class="bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-200 dark:border-indigo-500/30 rounded-xl p-3 mb-3 flex items-center gap-3 flex-wrap">
            <span class="text-sm font-medium text-indigo-700 dark:text-indigo-300" x-text="selectedIds.length + ' selected'"></span>
            <form method="POST" action="{{ route('tickets.bulk-action') }}" class="flex items-center gap-2 flex-wrap">
                @csrf
                <template x-for="id in selectedIds" :key="id">
                    <input type="hidden" name="ticket_ids[]" :value="id">
                </template>
                <select name="action" class="border border-indigo-200 dark:border-indigo-500/30 rounded-lg px-2 py-1.5 text-sm text-slate-700 dark:text-slate-100 bg-white dark:bg-slate-800" id="bulk-action-select">
                    <option value="close">{{ __('Close Tickets') }}</option>
                    <option value="assign">{{ __('Assign to NOC') }}</option>
                    <option value="status">{{ __('Change Status') }}</option>
                    @if(auth()->user()->isSuperAdmin())
                    <option value="delete">🗑 {{ __('Delete Permanently') }}</option>
                    @endif
                </select>
                <select name="assigned_to" class="border border-indigo-200 dark:border-indigo-500/30 rounded-lg px-2 py-1.5 text-sm text-slate-700 dark:text-slate-100 bg-white dark:bg-slate-800 hidden" id="bulk-assign-select">
                    @foreach(\App\Models\User::where('role','noc')->get() as $noc)
                    <option value="{{ $noc->id }}">{{ $noc->name }}</option>
                    @endforeach
                </select>
                <select name="bulk_status" class="border border-indigo-200 dark:border-indigo-500/30 rounded-lg px-2 py-1.5 text-sm text-slate-700 dark:text-slate-100 bg-white dark:bg-slate-800 hidden" id="bulk-status-select">
                    @foreach(['in_progress','pending','waiting_for_customer_feedback','resolved'] as $s)
                    <option value="{{ $s }}">{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-lg text-sm font-semibold transition-colors">{{ __('Apply') }}</button>
            </form>
        </div>

        {{-- Tickets Table --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
            @if($tickets->isEmpty())
            <div class="py-16 text-center">
                <p class="text-slate-500 dark:text-slate-400 font-medium">{{ __('No tickets found') }}</p>
                <p class="text-slate-400 dark:text-slate-500 text-sm mt-1">{{ __('Try adjusting your filters or create a new ticket.') }}</p>
                <a href="{{ route('tickets.create') }}" class="inline-block mt-4 text-sm text-indigo-600 hover:text-indigo-700 font-medium">+ {{ __('Create new ticket') }}</a>
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm" style="table-layout:fixed">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800 border-b border-slate-100 dark:border-slate-700">
                            <th class="w-9 px-2 py-3 text-center text-xs font-semibold text-slate-400 dark:text-slate-500" style="width:36px"></th>
                            <th class="px-4 py-3" style="width:40px">
                                <input type="checkbox" class="rounded border-slate-300 dark:border-slate-600"
                                       @change="selectedIds = $event.target.checked ? Array.from(document.querySelectorAll('.row-check')).map(c => c.value) : []; showBulk = selectedIds.length > 0">
                            </th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide" style="min-width:250px">{{ __('Work') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide" style="width:140px">{{ __('Assignee') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide" style="width:140px">{{ __('Reporter') }}</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide w-20">{{ __("Priority") }}</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide min-w-[130px]">{{ __("Status") }}</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide w-20">{{ __("Resolution") }}</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide w-36">{{ __('Created') }}</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide w-36">{{ __('Updated') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($tickets as $i => $ticket)
                        @php
                            $pColor = match($ticket->priority) {
                                'low'      => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
                                'medium'   => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300',
                                'high'     => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-300',
                                'critical' => 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-300',
                                default    => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
                            };
                            $sColor = match($ticket->status) {
                                'open', 'pending'                => 'bg-[#ffedd5] text-[#c2410c] border border-amber-200 dark:bg-amber-500/20 dark:text-amber-300 dark:border-amber-500/30',
                                'in_progress'                    => 'bg-[#dbeafe] text-[#1e40af] border border-blue-200 dark:bg-blue-500/20 dark:text-blue-300 dark:border-blue-500/30',
                                'waiting_for_customer_feedback'  => 'bg-[#f3e8ff] text-[#6b21a8] border border-violet-200 dark:bg-violet-500/20 dark:text-violet-300 dark:border-violet-500/30',
                                'resolved'                       => 'bg-[#d1fae5] text-[#065f46] border border-emerald-200 dark:bg-emerald-500/20 dark:text-emerald-300 dark:border-emerald-500/30',
                                'closed'                         => 'bg-[#f1f5f9] text-[#475569] border border-slate-200 dark:bg-slate-700 dark:text-slate-300 dark:border-slate-600',
                                'reopened'                       => 'bg-[#f3e8ff] text-[#6b21a8] border border-purple-200 dark:bg-purple-500/20 dark:text-purple-300 dark:border-purple-500/30',
                                default                          => 'bg-[#f1f5f9] text-[#475569] border border-slate-200 dark:bg-slate-700 dark:text-slate-300 dark:border-slate-600',
                            };
                            $prioritySquareColor = match($ticket->priority) {
                                'critical' => '#dc2626',
                                'high'     => '#ea580c',
                                'medium'   => '#d97706',
                                'low'      => '#94a3b8',
                                default    => '#94a3b8',
                            };
                            $rowBg = $i % 2 === 0 ? 'bg-white dark:bg-slate-900' : 'bg-slate-50/50 dark:bg-slate-800/70';
                            $overdue = $ticket->due_at && $ticket->due_at->isPast() && !in_array($ticket->status, ['resolved','closed']);
                            $assigneeInitials = $ticket->assignee ? strtoupper(substr($ticket->assignee->name, 0, 1)) . (strpos($ticket->assignee->name, ' ') !== false ? strtoupper(substr($ticket->assignee->name, strpos($ticket->assignee->name, ' ') + 1, 1)) : '') : '';
                            $creatorInitials  = $ticket->creator  ? strtoupper(substr($ticket->creator->name,  0, 1)) . (strpos($ticket->creator->name,  ' ') !== false ? strtoupper(substr($ticket->creator->name,  strpos($ticket->creator->name,  ' ') + 1, 1)) : '') : '';
                            $ticketKey = $ticket->ticket_key ?? '#'.$ticket->id;
                        @endphp
                        <tr class="{{ $rowBg }} hover:bg-indigo-50/30 dark:hover:bg-indigo-500/10 transition-colors cursor-pointer row-click"
                            data-href="{{ route('tickets.show', $ticket) }}"
                            data-ticket-id="{{ $ticket->id }}">
                            <td class="w-9 px-2 py-3.5 text-center cursor-grab active:cursor-grabbing drag-handle select-none text-slate-400 hover:text-indigo-600 dark:text-slate-500 dark:hover:text-indigo-400 transition-colors"
                                onclick="event.stopPropagation()"
                                title="{{ __('Drag up/down to reorder') }}">
                                <svg class="w-4 h-4 inline-block pointer-events-none" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M7 4a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0zM7 16a2 2 0 11-4 0 2 2 0 014 0zM17 4a2 2 0 11-4 0 2 2 0 014 0zM17 10a2 2 0 11-4 0 2 2 0 014 0zM17 16a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </td>
                            <td class="px-4 py-3.5" onclick="event.stopPropagation()">
                                <input type="checkbox" class="row-check rounded border-slate-300 dark:border-slate-600" value="{{ $ticket->id }}"
                                       @change="selectedIds = $event.target.checked ? [...selectedIds, $event.target.value] : selectedIds.filter(id => id !== $event.target.value); showBulk = selectedIds.length > 0">
                            </td>
                            {{-- Work column: icon + key + title --}}
                            <td class="px-5 py-3.5 max-w-xs">
                                <div class="flex items-center gap-2">
                                    <span class="w-3.5 h-3.5 rounded flex-shrink-0" style="background-color: {{ $prioritySquareColor }}"></span>
                                    <a href="{{ route('tickets.show', $ticket) }}" class="text-xs font-mono font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 flex-shrink-0">{{ $ticketKey }}</a>
                                    <a href="{{ route('tickets.show', $ticket) }}" class="font-medium text-slate-800 dark:text-slate-100 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors truncate">
                                        {{ $ticket->title }}
                                        @if($overdue)<span class="ml-1 inline-flex px-1.5 py-0.5 rounded text-xs font-bold bg-red-600 text-white">{{ __('OVERDUE') }}</span>@endif
                                    </a>
                                </div>
                                @if($ticket->labels->count())
                                <div class="flex flex-wrap gap-1 mt-1 ml-5">
                                    @foreach($ticket->labels as $label)
                                    <span class="inline-flex px-1.5 py-0.5 rounded text-xs font-medium text-white" style="background-color: {{ $label->color }}">{{ $label->name }}</span>
                                    @endforeach
                                </div>
                                @endif
                            </td>
                            {{-- Assignee --}}
                            <td class="px-4 py-3.5 whitespace-nowrap" style="width:140px">
                                @if($ticket->assignee)
                                <div class="flex items-center gap-2">
                                    <img src="{{ $ticket->assignee->avatarUrl() }}" alt="{{ $ticket->assignee->name }}" class="w-6 h-6 rounded-full object-cover flex-shrink-0">
                                    <span class="text-sm text-slate-600 dark:text-slate-300 truncate">{{ $ticket->assignee->name }}</span>
                                </div>
                                @else
                                <span class="text-slate-400 text-sm">—</span>
                                @endif
                            </td>
                            {{-- Reporter --}}
                            <td class="px-4 py-3.5 whitespace-nowrap" style="width:140px">
                                @if($ticket->creator)
                                <div class="flex items-center gap-2">
                                    <img src="{{ $ticket->creator->avatarUrl() }}" alt="{{ $ticket->creator->name }}" class="w-6 h-6 rounded-full object-cover flex-shrink-0">
                                    <span class="text-sm text-slate-600 dark:text-slate-300 truncate">{{ $ticket->creator->name }}</span>
                                </div>
                                @else
                                <span class="text-slate-400 text-sm">—</span>
                                @endif
                            </td>
                            {{-- Priority --}}
                            <td class="px-5 py-3.5">
                                <span class="text-sm text-slate-600 dark:text-slate-300">{{ ucfirst($ticket->priority) }}</span>
                            </td>
                            {{-- Status --}}
                            <td class="px-5 py-3.5" onclick="event.stopPropagation()">
                                @if(auth()->user()->isAdmin())
                                <form method="POST" action="{{ route('tickets.update', $ticket) }}" class="inline-status-form">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="priority" value="{{ $ticket->priority }}">
                                    <input type="hidden" name="assigned_to" value="{{ $ticket->assigned_to }}">
                                    <div class="relative inline-flex items-center">
                                        {{-- Visible Styled Pill Badge --}}
                                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold select-none pointer-events-none {{ $sColor }}">
                                            @if($ticket->status === 'pending' || $ticket->status === 'open')
                                                <span class="flex items-center justify-center w-4 h-4 rounded-full bg-amber-500/20 flex-shrink-0">
                                                    <span class="status-dot-pending"></span>
                                                </span>
                                            @elseif($ticket->status === 'in_progress')
                                                <span class="flex items-center justify-center w-4 h-4 rounded-full bg-blue-500/20 flex-shrink-0">
                                                    <span class="status-dot-inprogress"></span>
                                                </span>
                                            @elseif($ticket->status === 'resolved')
                                                <span class="w-2 h-2 rounded-full bg-emerald-500 flex-shrink-0"></span>
                                            @else
                                                <span class="w-2 h-2 rounded-full bg-slate-400 flex-shrink-0"></span>
                                            @endif
                                            <span class="whitespace-nowrap">{{ ucfirst(str_replace('_',' ',$ticket->status)) }}</span>
                                            <svg class="w-3.5 h-3.5 opacity-70 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </div>

                                        {{-- Invisible Native Select Overlay --}}
                                        <select name="status" onchange="this.form.submit()"
                                                class="absolute inset-0 opacity-0 w-full h-full cursor-pointer z-20">
                                            @foreach(['in_progress','pending','waiting_for_customer_feedback','resolved'] as $s)
                                            <option value="{{ $s }}" {{ $ticket->status === $s ? 'selected' : '' }} class="bg-white text-slate-800">{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </form>
                                @else
                                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold {{ $sColor }}">
                                    @if($ticket->status === 'pending' || $ticket->status === 'open')
                                        <span class="flex items-center justify-center w-4 h-4 rounded-full bg-amber-500/20 flex-shrink-0">
                                            <span class="status-dot-pending"></span>
                                        </span>
                                    @elseif($ticket->status === 'in_progress')
                                        <span class="flex items-center justify-center w-4 h-4 rounded-full bg-blue-500/20 flex-shrink-0">
                                            <span class="status-dot-inprogress"></span>
                                        </span>
                                    @else
                                        <span class="w-1.5 h-1.5 rounded-full bg-current opacity-70 flex-shrink-0"></span>
                                    @endif
                                    {{ ucfirst(str_replace('_',' ',$ticket->status)) }}
                                </span>
                                @endif
                            </td>
                            {{-- Resolution --}}
                            <td class="px-5 py-3.5">
                                <span class="text-sm {{ in_array($ticket->status, ['resolved','closed']) ? 'text-emerald-600 dark:text-emerald-400 font-medium' : 'text-slate-400 dark:text-slate-500' }}">
                                    {{ in_array($ticket->status, ['resolved','closed']) ? __('Done') : __('Unresolved') }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-slate-400 dark:text-slate-500 text-xs">{{ $ticket->created_at->format('d M Y, H:i') }}</td>
                            <td class="px-5 py-3.5 text-slate-400 dark:text-slate-500 text-xs">{{ $ticket->updated_at->format('d M Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($tickets->hasPages())
            <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-800">
                {{ $tickets->links() }}
            </div>
            @endif
            @endif
        </div>
    </div>
    @else
    {{-- Non-admin ticket table (no bulk, no checkboxes) --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
        @if($tickets->isEmpty())
        <div class="py-16 text-center">
            <p class="text-slate-500 dark:text-slate-400 font-medium">{{ __('No tickets found') }}</p>
            <a href="{{ route('tickets.create') }}" class="inline-block mt-4 text-sm text-indigo-600 hover:text-indigo-700 font-medium">+ {{ __('Create new ticket') }}</a>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800 border-b border-slate-100 dark:border-slate-700">
                        <th class="w-9 px-2 py-3 text-center text-xs font-semibold text-slate-400 dark:text-slate-500" style="width:36px"></th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">{{ __('Work') }}</th>
                        @if(!auth()->user()->isReseller())
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide" style="width:150px">{{ __('Assignee') }}</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide" style="width:150px">{{ __('Reporter') }}</th>
                        @endif
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide w-20">{{ __("Priority") }}</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide min-w-[130px]">{{ __("Status") }}</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide w-20">{{ __("Resolution") }}</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide w-36">{{ __('Created') }}</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide w-36">{{ __('Updated') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($tickets as $i => $ticket)
                    @php
                        $pColor = match($ticket->priority) { 'low' => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300', 'medium' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300', 'high' => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-300', 'critical' => 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-300', default => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300' };
                        $sColor = match($ticket->status) { 'open' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300', 'in_progress' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300', 'pending' => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-300', 'waiting_for_customer_feedback' => 'bg-violet-100 text-violet-700 dark:bg-violet-500/20 dark:text-violet-300', 'resolved' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300', 'closed' => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300', 'reopened' => 'bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-300', default => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300' };
                        $prioritySquareColor = match($ticket->priority) { 'critical' => '#dc2626', 'high' => '#ea580c', 'medium' => '#d97706', 'low' => '#94a3b8', default => '#94a3b8' };
                        $rowBg = $i % 2 === 0 ? 'bg-white dark:bg-slate-900' : 'bg-slate-50/50 dark:bg-slate-800/70';
                        $overdue = $ticket->due_at && $ticket->due_at->isPast() && !in_array($ticket->status, ['resolved','closed']);
                        $ticketKey = $ticket->ticket_key ?? '#'.$ticket->id;
                    @endphp
                    <tr class="{{ $rowBg }} hover:bg-indigo-50/30 dark:hover:bg-indigo-500/10 transition-colors cursor-pointer row-click"
                        data-href="{{ route('tickets.show', $ticket) }}"
                        data-ticket-id="{{ $ticket->id }}">
                        <td class="w-9 px-2 py-3.5 text-center cursor-grab active:cursor-grabbing drag-handle select-none text-slate-400 hover:text-indigo-600 dark:text-slate-500 dark:hover:text-indigo-400 transition-colors"
                            onclick="event.stopPropagation()"
                            title="{{ __('Drag up/down to reorder') }}">
                            <svg class="w-4 h-4 inline-block pointer-events-none" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M7 4a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0zM7 16a2 2 0 11-4 0 2 2 0 014 0zM17 4a2 2 0 11-4 0 2 2 0 014 0zM17 10a2 2 0 11-4 0 2 2 0 014 0zM17 16a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </td>
                        <td class="px-5 py-3.5 max-w-sm">
                            <div class="flex items-center gap-2">
                                <span class="w-3.5 h-3.5 rounded flex-shrink-0" style="background-color: {{ $prioritySquareColor }}"></span>
                                <a href="{{ route('tickets.show', $ticket) }}" class="text-xs font-mono font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 flex-shrink-0">{{ $ticketKey }}</a>
                                <a href="{{ route('tickets.show', $ticket) }}" class="font-medium text-slate-800 dark:text-slate-100 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors truncate">{{ $ticket->title }}@if($overdue) <span class="ml-1 inline-flex px-1.5 py-0.5 rounded text-xs font-bold bg-red-600 text-white">{{ __('OVERDUE') }}</span>@endif</a>
                            </div>
                            @if($ticket->labels->count())<div class="flex flex-wrap gap-1 mt-1 ml-5">@foreach($ticket->labels as $label)<span class="inline-flex px-1.5 py-0.5 rounded text-xs font-medium text-white" style="background-color: {{ $label->color }}">{{ $label->name }}</span>@endforeach</div>@endif
                        </td>
                        @if(!auth()->user()->isReseller())
                        <td class="px-5 py-3.5" style="width:150px">
                            @if($ticket->assignee)
                            <div class="flex items-center gap-2">
                                <img src="{{ $ticket->assignee->avatarUrl() }}" alt="{{ $ticket->assignee->name }}" class="w-6 h-6 rounded-full object-cover flex-shrink-0">
                                <span class="text-sm text-slate-600 dark:text-slate-300 truncate">{{ $ticket->assignee->name }}</span>
                            </div>
                            @else
                            <span class="text-slate-400 text-sm">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5" style="width:150px">
                            @if($ticket->creator)
                            <div class="flex items-center gap-2">
                                <img src="{{ $ticket->creator->avatarUrl() }}" alt="{{ $ticket->creator->name }}" class="w-6 h-6 rounded-full object-cover flex-shrink-0">
                                <span class="text-sm text-slate-600 dark:text-slate-300 truncate">{{ $ticket->creator->name }}</span>
                            </div>
                            @else
                            <span class="text-slate-400 text-sm">—</span>
                            @endif
                        </td>
                        @endif
                        <td class="px-5 py-3.5 text-sm text-slate-600 dark:text-slate-300">{{ ucfirst($ticket->priority) }}</td>
                        <td class="px-5 py-3.5"><span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold whitespace-nowrap {{ $sColor }}"><span class="w-1.5 h-1.5 rounded-full bg-current opacity-70 flex-shrink-0"></span>{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span></td>
                        <td class="px-5 py-3.5 text-sm {{ in_array($ticket->status, ['resolved','closed']) ? 'text-emerald-600 dark:text-emerald-400 font-medium' : 'text-slate-400 dark:text-slate-500' }}">{{ in_array($ticket->status, ['resolved','closed']) ? __('Done') : __('Unresolved') }}</td>
                        <td class="px-5 py-3.5 text-slate-400 dark:text-slate-500 text-xs">{{ $ticket->created_at->format('d M Y, H:i') }}</td>
                        <td class="px-5 py-3.5 text-slate-400 dark:text-slate-500 text-xs">{{ $ticket->updated_at->format('d M Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($tickets->hasPages())<div class="px-5 py-4 border-t border-slate-100 dark:border-slate-800">{{ $tickets->links() }}</div>@endif
        @endif
    </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
    document.getElementById('bulk-action-select')?.addEventListener('change', function() {
        document.getElementById('bulk-assign-select').classList.toggle('hidden', this.value !== 'assign');
        document.getElementById('bulk-status-select').classList.toggle('hidden', this.value !== 'status');
    });

    function bindRowClicks() {
        document.querySelectorAll('tr.row-click').forEach(row => {
            row.addEventListener('click', function(e) {
                if (e.target.closest('a, input, select, button, form, .drag-handle')) return;
                window.location = this.dataset.href;
            });
        });
    }

    function saveTicketOrder(tbody) {
        if (!tbody) return;
        const ids = Array.from(tbody.querySelectorAll('tr[data-ticket-id]')).map(tr => tr.dataset.ticketId);
        try {
            localStorage.setItem('ticket_row_order', JSON.stringify(ids));
        } catch(e) {}
    }

    function restoreTicketOrder(tbody) {
        if (!tbody) return;
        try {
            const savedOrder = JSON.parse(localStorage.getItem('ticket_row_order'));
            if (!savedOrder || !Array.isArray(savedOrder) || savedOrder.length === 0) return;

            const rowMap = new Map();
            tbody.querySelectorAll('tr[data-ticket-id]').forEach(tr => {
                rowMap.set(tr.dataset.ticketId, tr);
            });

            // Append in saved order
            savedOrder.forEach(id => {
                if (rowMap.has(id)) {
                    tbody.appendChild(rowMap.get(id));
                    rowMap.delete(id);
                }
            });
            // Append any new tickets not in saved order
            rowMap.forEach(tr => tbody.appendChild(tr));
        } catch(e) {}
    }

    let draggedRow = null;

    function initTicketsDragAndDrop() {
        document.querySelectorAll('tbody').forEach(tbody => {
            restoreTicketOrder(tbody);

            const rows = tbody.querySelectorAll('tr[data-ticket-id]');
            rows.forEach(row => {
                row.setAttribute('draggable', 'true');

                row.addEventListener('dragstart', function(e) {
                    draggedRow = this;
                    this.classList.add('opacity-30', 'bg-indigo-100', 'dark:bg-indigo-900/50');
                    e.dataTransfer.effectAllowed = 'move';
                    e.dataTransfer.setData('text/plain', this.dataset.ticketId);
                });

                row.addEventListener('dragend', function() {
                    draggedRow = null;
                    document.querySelectorAll('tr[data-ticket-id]').forEach(r => {
                        r.classList.remove('opacity-30', 'bg-indigo-100', 'dark:bg-indigo-900/50', 'border-t-2', 'border-b-2', 'border-indigo-500');
                    });
                    saveTicketOrder(this.closest('tbody'));
                });

                row.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    e.dataTransfer.dropEffect = 'move';
                    if (!draggedRow || draggedRow === this) return;

                    const rect = this.getBoundingClientRect();
                    const midpoint = rect.top + rect.height / 2;
                    if (e.clientY < midpoint) {
                        this.parentNode.insertBefore(draggedRow, this);
                    } else {
                        this.parentNode.insertBefore(draggedRow, this.nextSibling);
                    }
                });

                row.addEventListener('drop', function(e) {
                    e.preventDefault();
                    saveTicketOrder(this.closest('tbody'));
                });
            });
        });
    }

    bindRowClicks();
    initTicketsDragAndDrop();

    // Live background polling for new/updated tickets
    (function() {
        function pollTicketsIndex() {
            if (document.querySelectorAll('.row-check:checked').length > 0) return;

            fetch(window.location.href, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                const newTbody = doc.querySelector('tbody');
                const currentTbody = document.querySelector('tbody');

                if (newTbody && currentTbody && newTbody.innerHTML.trim() !== currentTbody.innerHTML.trim()) {
                    currentTbody.innerHTML = newTbody.innerHTML;
                    bindRowClicks();
                    initTicketsDragAndDrop();
                }
            })
            .catch(() => {});
        }

        setInterval(pollTicketsIndex, 8000);
    })();
    </script>
</x-app-layout>
