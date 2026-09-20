<x-app-layout>
    @php
        $user = auth()->user();
        $pColor = match($ticket->priority) {
            'low'      => 'bg-slate-100 text-slate-600',
            'medium'   => 'bg-amber-100 text-amber-700',
            'high'     => 'bg-orange-100 text-orange-700',
            'critical' => 'bg-red-100 text-red-700',
            default    => 'bg-slate-100 text-slate-600',
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
        $roleColors = [
            'super_admin' => 'bg-purple-100 text-purple-700',
            'admin'       => 'bg-red-100 text-red-700',
            'noc'         => 'bg-blue-100 text-blue-700',
            'reseller'    => 'bg-emerald-100 text-emerald-700',
        ];
        $isOverdue = $ticket->due_at && $ticket->due_at->isPast() && $ticket->status !== 'resolved';
        $ticketKey = $ticket->ticket_key ?? '#'.$ticket->id;

        // All activity merged and sorted
        $allActivity = collect();
        foreach ($ticket->messages as $m) {
            $allActivity->push(['type' => 'message', 'obj' => $m, 'at' => $m->created_at]);
        }
        foreach ($ticket->history as $h) {
            $allActivity->push(['type' => 'history', 'obj' => $h, 'at' => $h->created_at]);
        }
        if (!$user->isReseller()) {
            foreach ($ticket->notes as $n) {
                $allActivity->push(['type' => 'note', 'obj' => $n, 'at' => $n->created_at]);
            }
        }
        $allActivity = $allActivity->sortBy('at');
    @endphp

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-1.5 text-sm text-slate-400 mb-3">
        <a href="{{ route('tickets.index') }}" class="hover:text-indigo-600 transition-colors font-medium">{{ __('Tickets') }}</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="font-mono text-slate-500 font-semibold">{{ $ticketKey }}</span>
        @if($isOverdue)<span class="ml-2 inline-flex px-2 py-0.5 rounded-full text-xs font-bold bg-red-600 text-white">{{ __('OVERDUE') }}</span>@endif
    </div>

    {{-- Jira-style Title Block --}}
    <div class="mb-5" x-data="inlineTitleEdit()">
        {{-- Display mode --}}
        <div x-show="!editing" class="group flex items-start gap-2">
            <h1 class="text-2xl font-bold text-slate-800 leading-snug flex-1" x-ref="titleDisplay">{{ $ticket->title }}</h1>
            @if($user->isAdmin() || $user->isNoc())
            <button @click="startEdit()" class="opacity-0 group-hover:opacity-100 transition-opacity mt-1 p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-600 flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
            </button>
            @endif
        </div>
        {{-- Edit mode --}}
        @if($user->isAdmin() || $user->isNoc())
        <div x-show="editing" x-cloak class="flex items-center gap-2">
            <input x-ref="titleInput" x-model="title" type="text"
                   class="flex-1 text-2xl font-bold text-slate-800 border-2 border-indigo-400 rounded-lg px-3 py-1 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                   @blur="save()" @keydown.enter="save()" @keydown.escape="cancel()">
            <button @click="save()" class="text-xs bg-indigo-600 text-white px-3 py-1.5 rounded-lg hover:bg-indigo-700">{{ __('Save') }}</button>
            <button @click="cancel()" class="text-xs bg-slate-100 text-slate-600 px-3 py-1.5 rounded-lg hover:bg-slate-200">{{ __('Cancel') }}</button>
        </div>
        @endif
    </div>

    {{-- Quick action row --}}
    <div class="flex flex-wrap items-center gap-2 mb-5">
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
            {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
        </span>
        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $pColor }}">{{ ucfirst($ticket->priority) }}</span>
        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700">{{ ucfirst(str_replace('_', ' ', $ticket->category)) }}</span>
        @foreach($ticket->labels as $label)
        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold text-white" style="background-color: {{ $label->color }}">
            {{ $label->name }}
            @if($user->isAdmin() || $user->isNoc())
            <form method="POST" action="{{ route('tickets.labels.detach', [$ticket, $label]) }}" class="inline">
                @csrf @method('DELETE')
                <button type="submit" class="ml-0.5 hover:opacity-70 leading-none">&times;</button>
            </form>
            @endif
        </span>
        @endforeach
        @if(($user->isAdmin() || $user->isNoc()) && $allLabels->count() > 0)
        <form method="POST" action="{{ route('tickets.labels.attach', $ticket) }}" class="inline-flex items-center gap-1">
            @csrf
            <select name="label_id" class="border border-slate-200 rounded px-1.5 py-0.5 text-xs text-slate-700 bg-white">
                <option value="">+ {{ __('Label') }}</option>
                @foreach($allLabels as $lbl)
                    @if(!$ticket->labels->contains($lbl->id))
                    <option value="{{ $lbl->id }}">{{ $lbl->name }}</option>
                    @endif
                @endforeach
            </select>
            <button type="submit" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">{{ __('Add') }}</button>
        </form>
        @endif
        <div class="flex-1"></div>
        @if(($user->isAdmin() || $user->isNoc()) && !$ticket->isMerged())
        <a href="{{ route('tickets.merge.form', $ticket) }}" class="inline-flex items-center gap-1.5 border border-slate-200 text-slate-600 hover:bg-slate-100 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors">{{ __('Merge') }}</a>
        @endif
        @if($user->isAdmin() || $user->isNoc())
        <a href="{{ route('tickets.edit', $ticket) }}" class="inline-flex items-center gap-1.5 border border-slate-200 text-slate-600 hover:bg-slate-100 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors">{{ __('Edit') }}</a>
        @endif
        @if($user->isSuperAdmin())
        <form method="POST" action="{{ route('tickets.destroy', $ticket) }}" onsubmit="return confirm('⚠️ Are you sure you want to permanently delete this ticket ({{ $ticketKey }}) and ALL its messages, attachments, notes, and history? This CANNOT be undone!')" class="inline">
            @csrf @method('DELETE')
            <button type="submit" class="inline-flex items-center gap-1.5 border border-red-200 bg-red-50 text-red-600 hover:bg-red-600 hover:text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition-all shadow-2xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                {{ __('Delete') }}
            </button>
        </form>
        @endif
    </div>

    @if($ticket->isMerged())
    <div class="mb-4 px-4 py-3 rounded-lg bg-amber-50 border border-amber-200 text-amber-700 text-sm flex items-center gap-2">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        {{ __('This ticket was merged into') }}
        <a href="{{ route('tickets.show', $ticket->mergedInto) }}" class="font-semibold underline">{{ $ticket->mergedInto->ticket_key ?? '#'.$ticket->mergedInto->id }}</a>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- LEFT COLUMN --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Description --}}
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 bg-slate-50"><h2 class="text-sm font-semibold text-slate-600">{{ __('Description') }}</h2></div>
                <div class="p-5">
                    <div class="text-slate-700 text-sm leading-relaxed prose prose-sm max-w-none">{!! $ticket->formatted_description ?: __('No description provided.') !!}</div>
                </div>
            </div>

            {{-- Attachments --}}
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-slate-600">{{ __('Attachments') }}</h2>
                    <span class="text-xs text-slate-400">{{ $ticket->attachments->count() }}</span>
                </div>
                <div class="p-5">
                    @if($ticket->attachments->isNotEmpty())
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-4">
                        @foreach($ticket->attachments as $att)
                        <div class="flex items-center gap-2 border border-slate-200 rounded-lg px-3 py-2 text-xs">
                            <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            <a href="{{ route('tickets.attachments.download', [$ticket, $att]) }}" class="text-indigo-600 hover:text-indigo-800 font-medium truncate flex-1" title="{{ $att->original_name }}">{{ $att->original_name }}</a>
                            <span class="text-slate-400 flex-shrink-0">{{ $att->humanSize() }}</span>
                            @if($user->isAdmin() || $att->uploaded_by === $user->id)
                            <form method="POST" action="{{ route('tickets.attachments.destroy', [$ticket, $att]) }}" onsubmit="return confirm('{{ __('Delete this attachment?') }}')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-600 flex-shrink-0">&times;</button>
                            </form>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @endif
                    @if($ticket->status !== 'closed')
                    <form method="POST" action="{{ route('tickets.attachments.store', $ticket) }}" enctype="multipart/form-data" class="flex items-center gap-2">
                        @csrf
                        <input type="file" name="files[]" multiple class="text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors flex-shrink-0">{{ __('Upload') }}</button>
                    </form>
                    @error('files')<p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>@enderror
                    @error('files.0')<p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>@enderror
                    @endif
                </div>
            </div>

            {{-- CSAT rating (reseller, own resolved ticket) --}}
            @if($user->isReseller() && $ticket->created_by === $user->id && in_array($ticket->status, ['resolved','closed']))
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 bg-slate-50"><h2 class="text-sm font-semibold text-slate-600">{{ __('Rate this resolution') }}</h2></div>
                <div class="p-5">
                    @if($ticket->csat_submitted_at)
                    <div class="flex items-center gap-1 mb-1">
                        @for($i = 1; $i <= 5; $i++)
                        <svg class="w-5 h-5 {{ $i <= $ticket->csat_rating ? 'text-amber-400' : 'text-slate-200' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.957a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.368 2.447a1 1 0 00-.363 1.118l1.287 3.957c.3.922-.755 1.688-1.538 1.118l-3.367-2.447a1 1 0 00-1.176 0l-3.367 2.447c-.783.57-1.838-.196-1.538-1.118l1.286-3.957a1 1 0 00-.362-1.118L2.813 9.384c-.783-.57-.38-1.81.588-1.81h4.163a1 1 0 00.95-.69l1.286-3.957z"/></svg>
                        @endfor
                    </div>
                    @if($ticket->csat_comment)<p class="text-sm text-slate-500 italic">"{{ $ticket->csat_comment }}"</p>@endif
                    <p class="text-xs text-slate-400 mt-1">{{ __('Thanks for your feedback!') }}</p>
                    @else
                    <form method="POST" action="{{ route('tickets.csat', $ticket) }}" x-data="{ rating: 0 }">
                        @csrf
                        <div class="flex items-center gap-1 mb-3">
                            <template x-for="i in 5" :key="i">
                                <button type="button" @click="rating = i" class="focus:outline-none">
                                    <svg class="w-7 h-7 transition-colors" :class="i <= rating ? 'text-amber-400' : 'text-slate-200'" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.957a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.368 2.447a1 1 0 00-.363 1.118l1.287 3.957c.3.922-.755 1.688-1.538 1.118l-3.367-2.447a1 1 0 00-1.176 0l-3.367 2.447c-.783.57-1.838-.196-1.538-1.118l1.286-3.957a1 1 0 00-.362-1.118L2.813 9.384c-.783-.57-.38-1.81.588-1.81h4.163a1 1 0 00.95-.69l1.286-3.957z"/></svg>
                                </button>
                            </template>
                            <input type="hidden" name="csat_rating" :value="rating">
                        </div>
                        <textarea name="csat_comment" rows="2" placeholder="{{ __('Optional comment...') }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:ring-2 focus:ring-indigo-500 bg-slate-50 resize-none mb-3"></textarea>
                        <button type="submit" :disabled="rating === 0" :class="rating === 0 ? 'opacity-40 cursor-not-allowed' : ''" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">{{ __('Submit Rating') }}</button>
                    </form>
                    @endif
                </div>
            </div>
            @endif

            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden"
                 x-data="{
                     tab: 'all',
                     showComposer: true,
                     scrollBottom() {
                         $nextTick(() => {
                             ['chat-box','comments-box','history-box','worklog-box'].forEach(id => {
                                 const el = document.getElementById(id);
                                 if (el) el.scrollTop = el.scrollHeight;
                             });
                         });
                     }
                 }"
                 x-init="scrollBottom()">
                <div class="px-5 py-3 border-b border-slate-100 bg-slate-50">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="text-sm font-semibold text-slate-600 mr-2">{{ __('Activity') }}</span>
                        <button @click="tab = 'all'; showComposer = true; scrollBottom()"
                                :class="tab === 'all' ? 'bg-white border-slate-200 text-indigo-600 shadow-sm font-semibold' : 'text-slate-500 hover:text-slate-700'"
                                class="px-3 py-1.5 rounded-lg text-xs border transition-all flex items-center gap-1.5">
                            <span>{{ __('All') }}</span>
                            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold"
                                  :class="tab === 'all' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-200 text-slate-600'">{{ $allActivity->count() }}</span>
                        </button>
                        <button @click="tab = 'comments'; showComposer = true; scrollBottom()"
                                :class="tab === 'comments' ? 'bg-white border-slate-200 text-indigo-600 shadow-sm font-semibold' : 'text-slate-500 hover:text-slate-700'"
                                class="px-3 py-1.5 rounded-lg text-xs border transition-all flex items-center gap-1.5">
                            <span>{{ __('Comments') }}</span>
                            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold"
                                  :class="tab === 'comments' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-200 text-slate-600'">{{ $ticket->messages->count() }}</span>
                        </button>
                        <button @click="tab = 'history'; showComposer = false; scrollBottom()"
                                :class="tab === 'history' ? 'bg-white border-slate-200 text-indigo-600 shadow-sm font-semibold' : 'text-slate-500 hover:text-slate-700'"
                                class="px-3 py-1.5 rounded-lg text-xs border transition-all flex items-center gap-1.5">
                            <span>{{ __('History') }}</span>
                            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold"
                                  :class="tab === 'history' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-200 text-slate-600'">{{ $ticket->history->count() }}</span>
                        </button>
                        @if($user->isAdmin() || $user->isNoc())
                        <button @click="tab = 'worklog'; showComposer = false; scrollBottom()"
                                :class="tab === 'worklog' ? 'bg-amber-500 border-amber-500 text-white shadow-sm font-semibold' : 'text-slate-500 hover:text-slate-700'"
                                class="px-3 py-1.5 rounded-lg text-xs border transition-all flex items-center gap-1.5">
                            <span>🔒 {{ __('Work Log') }}</span>
                            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold"
                                  :class="tab === 'worklog' ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-800'">{{ $ticket->notes->count() }}</span>
                        </button>
                        @endif
                    </div>
                </div>
                <div class="p-5">

                    {{-- ALL TAB --}}
                    <div x-show="tab === 'all'" x-cloak>
                        <div class="space-y-4 mb-5 max-h-96 overflow-y-auto pr-1" id="chat-box">
                            @forelse($allActivity as $item)
                            @if($item['type'] === 'message')
                            @php $msg = $item['obj']; @endphp
                            @if($msg->canViewPrivate($user))
                            @include('tickets.partials.message', ['msg' => $msg])
                            @endif
                            @elseif($item['type'] === 'history')
                            @php $h = $item['obj']; $changer = $h->changedBy; @endphp
                            <div class="flex items-start gap-3">
                                @if($changer)
                                <img src="{{ $changer->avatarUrl() }}" alt="{{ $changer->name }}" class="w-7 h-7 rounded-full object-cover flex-shrink-0 mt-0.5">
                                @else
                                <div class="w-7 h-7 rounded-full bg-slate-400 flex items-center justify-center text-white font-bold text-xs flex-shrink-0 mt-0.5">?</div>
                                @endif
                                <div class="flex-1">
                                    <p class="text-sm text-slate-700"><span class="font-semibold">{{ $changer->name ?? 'System' }}</span> — {{ $h->action }}</p>
                                    <p class="text-xs text-slate-400">{{ $h->created_at->diffForHumans() }}</p>
                                    @if($h->oldAssignee || $h->newAssignee)
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="inline-flex px-2 py-0.5 rounded bg-slate-200 text-slate-600 text-xs">{{ $h->oldAssignee->name ?? 'Unassigned' }}</span>
                                        <span class="text-slate-400">→</span>
                                        <span class="inline-flex px-2 py-0.5 rounded bg-indigo-100 text-indigo-700 text-xs font-medium">{{ $h->newAssignee->name ?? 'Unassigned' }}</span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @elseif($item['type'] === 'note')
                            @php $note = $item['obj']; @endphp
                            <div class="flex items-start gap-3 p-3 bg-amber-50/80 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-700/50 rounded-xl shadow-xs">
                                <div class="w-7 h-7 rounded-full bg-amber-500 text-white flex items-center justify-center font-bold text-xs flex-shrink-0 mt-0.5 shadow-xs">
                                    {{ strtoupper(substr($note->user->name ?? '?', 0, 1)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1 flex-wrap">
                                        <span class="text-xs font-bold text-amber-900 dark:text-amber-300">{{ $note->user->name ?? 'Unknown' }}</span>
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-amber-200 text-amber-900 dark:bg-amber-900/60 dark:text-amber-200 border border-amber-300 dark:border-amber-700">
                                            🔒 {{ __('Staff Internal Note') }}
                                        </span>
                                        <span class="text-[11px] text-slate-400 font-normal ml-auto">{{ $note->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-sm text-slate-800 dark:text-slate-200 font-normal leading-relaxed whitespace-pre-line">{!! $note->formatted_note !!}</p>
                                </div>
                            </div>
                            @endif
                            @empty
                            <div class="py-8 text-center"><p class="text-sm text-slate-400">{{ __('No activity yet.') }}</p></div>
                            @endforelse
                        </div>
                    </div>

                    {{-- COMMENTS TAB --}}
                    <div x-show="tab === 'comments'" x-cloak>
                        <div class="space-y-4 mb-5 max-h-80 overflow-y-auto pr-1" id="comments-box">
                            @forelse($ticket->messages->filter(fn($m) => $m->canViewPrivate($user))->sortBy('created_at') as $msg)
                            @include('tickets.partials.message', ['msg' => $msg])
                            @empty
                            <div class="py-8 text-center"><p class="text-sm text-slate-400">{{ __('No comments yet.') }}</p></div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Comment box (shared by All & Comments tabs) --}}
                    <div x-show="showComposer" x-cloak>
                        @if($ticket->status !== 'closed')
                        <form id="chat-form" method="POST" action="{{ route('tickets.messages.store', $ticket) }}" enctype="multipart/form-data" class="border-t border-slate-100 pt-4" x-data="{ isPrivate: false }">
                            @csrf
                            @error('message')<p class="text-red-500 text-xs mb-2">{{ $message }}</p>@enderror
                            <input type="hidden" name="reply_to_id" id="reply-to-id" value="">
                            <input type="hidden" name="editing_message_id" id="editing-message-id" value="">
                            <input type="hidden" name="is_private" :value="isPrivate ? '1' : '0'">

                            {{-- Public vs Private Comment Mode Toggles (Hidden for Resellers) --}}
                            @if(!$user->isReseller())
                            <div class="flex items-center gap-2 mb-3 flex-wrap">
                                <button type="button" @click="isPrivate = false"
                                        :class="!isPrivate ? 'bg-indigo-600 text-white border-indigo-600 font-bold shadow-xs' : 'bg-slate-100 text-slate-600 border-slate-200 hover:bg-slate-200'"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs border transition-all">
                                    <span>🌐</span>
                                    <span>{{ __('Public Comment') }}</span>
                                </button>
                                <button type="button" @click="isPrivate = true"
                                        :class="isPrivate ? 'bg-amber-500 text-white border-amber-500 font-bold shadow-xs ring-2 ring-amber-300' : 'bg-slate-100 text-slate-600 border-slate-200 hover:bg-slate-200'"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs border transition-all">
                                    <span>🔒</span>
                                    <span>{{ __('Private Comment') }}</span>
                                </button>

                                <div x-show="isPrivate" x-cloak class="text-[11px] text-amber-800 bg-amber-50 border border-amber-200 px-2.5 py-1 rounded-lg flex items-center gap-1 font-medium">
                                    <span>🔒</span>
                                    <span>{{ __('Private comment - visible only to Admins, Assignee, Creator & Mentions (@mention)') }}</span>
                                </div>
                            </div>
                            @endif

                            {{-- Typing indicator banner --}}
                            <div id="typing-indicator" class="hidden items-center gap-2 mb-2 px-3 py-1.5 rounded-lg bg-indigo-50/80 border border-indigo-100 text-xs text-indigo-700 font-medium">
                                <span class="relative flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                                </span>
                                <span id="typing-text">✍️ Someone is typing...</span>
                            </div>

                            {{-- Edit preview banner --}}
                            <div id="edit-preview" class="hidden justify-between gap-2 mb-2 px-3 py-2 rounded-lg bg-amber-50 border-l-2 border-amber-400 text-xs items-center">
                                <div class="min-w-0">
                                    <span class="font-semibold text-amber-800">✏ {{ __('Editing comment by') }}</span>
                                    <span class="font-bold text-amber-900" id="edit-preview-sender"></span>
                                </div>
                                <button type="button" id="edit-preview-cancel" class="text-amber-600 hover:text-amber-800 text-xs font-semibold underline flex-shrink-0">{{ __('Cancel Edit') }}</button>
                            </div>

                            {{-- Reply preview banner --}}
                            <div id="reply-preview" class="hidden justify-between gap-2 mb-2 px-3 py-2 rounded-lg bg-indigo-50 border-l-2 border-indigo-400 text-xs items-center">
                                <div class="min-w-0">
                                    <span class="text-slate-400">{{ __('Replying to') }}</span>
                                    <span class="font-semibold text-slate-600" id="reply-preview-sender"></span>:
                                    <span class="text-slate-500" id="reply-preview-text"></span>
                                </div>
                                <button type="button" id="reply-preview-cancel" class="text-slate-400 hover:text-slate-600 flex-shrink-0">&times;</button>
                            </div>

                            {{-- Jira-style editor --}}
                            <div class="flex gap-3 items-start">
                                <img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}" class="w-8 h-8 rounded-full object-cover flex-shrink-0 mt-1">
                                <div class="flex-1">
                                    <div id="quill-editor" class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden" style="min-height:120px">
                                        <div id="quill-toolbar">
                                            <span class="ql-formats">
                                                <select class="ql-header"><option value="2"></option><option value="3"></option><option selected></option></select>
                                            </span>
                                            <span class="ql-formats">
                                                <button class="ql-bold"></button>
                                                <button class="ql-italic"></button>
                                                <button class="ql-underline"></button>
                                                <button class="ql-strike"></button>
                                            </span>
                                            <span class="ql-formats">
                                                <button class="ql-list" value="ordered"></button>
                                                <button class="ql-list" value="bullet"></button>
                                            </span>
                                            <span class="ql-formats">
                                                <button class="ql-blockquote"></button>
                                                <button class="ql-code-block"></button>
                                            </span>
                                            <span class="ql-formats">
                                                <button class="ql-link"></button>
                                                <button class="ql-image"></button>
                                            </span>
                                            <span class="ql-formats">
                                                <button class="ql-clean"></button>
                                                         <span class="ql-formats relative inline-block" style="position:relative !important;" x-data="{ open: false }" @close-emoji-panel.window="open = false">
                                                  <button type="button" id="emoji-picker-btn" @click="open = !open" title="{{ __('Insert emoji') }}"
                                                          style="font-size:16px; line-height:1; width:28px; height:24px; display:inline-flex; align-items:center; justify-content:center; border-radius:4px; border:none; cursor:pointer;"
                                                          :style="open ? 'background:#e0e7ff;' : ''">🙂</button>
                                                  <div id="emoji-picker-panel" x-show="open" x-cloak @click.outside="open = false"
                                                       class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-2xl w-80 overflow-hidden flex flex-col absolute left-0 top-full mt-1 z-50">
                                                      <div id="emoji-category-tabs" class="p-2 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/50 flex items-center justify-between gap-1"></div>
                                                      <div class="p-2.5 border-b border-slate-100 dark:border-slate-700 relative">
                                                          <input type="text" id="emoji-search" placeholder="{{ __('Search...') }}"
                                                                 class="w-full border border-slate-200 dark:border-slate-700 rounded-xl pl-8 pr-3 py-1.5 text-xs text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900"
                                                                 autocomplete="off">
                                                          <svg class="w-3.5 h-3.5 text-slate-400 absolute left-5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                                      </div>
                                                      <div class="px-3 py-1 text-[11px] font-semibold text-slate-500 bg-slate-50/40 border-b border-slate-100/60" id="emoji-cat-heading">
                                                          <span id="emoji-cat-title">Smileys</span>
                                                      </div>
                                                      <div id="emoji-grid" class="p-2.5 overflow-y-auto max-h-56 grid grid-cols-7 gap-1" style="display:grid !important; grid-template-columns:repeat(7, 1fr) !important; gap:4px !important;"></div>
                                                      <div class="px-3 py-2 border-t border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 flex items-center gap-2.5 text-xs text-slate-600 dark:text-slate-300" id="emoji-footer">
                                                          <span class="text-xl flex-shrink-0" id="emoji-footer-icon">😃</span>
                                                          <div class="min-w-0 flex-1">
                                                              <p class="font-medium text-slate-700 dark:text-slate-200 truncate" id="emoji-footer-name">Grinning</p>
                                                              <p class="text-[10px] text-slate-400 truncate" id="emoji-footer-code">:grinning:</p>
                                                          </div>
                                                      </div>
                                                  </div>
                                              </span>           </span>           
                                        </div>
                                        <div id="quill-body" style="min-height:80px; font-size:14px;"></div>
                                    </div>
                                    <input type="hidden" name="message" id="quill-hidden">

                                    {{-- Image attachment --}}
                                    <div class="mt-2">
                                        <input type="file" name="image" id="chat-image-input" accept="image/*" class="hidden">
                                        <button type="button" id="chat-image-btn"
                                                class="inline-flex items-center gap-1.5 text-xs text-slate-500 hover:text-indigo-600 border border-slate-200 dark:border-slate-700 hover:border-indigo-300 rounded-lg px-2.5 py-1.5 transition-colors"
                                                onclick="document.getElementById('chat-image-input').click()">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            {{ __('Attach Image') }}
                                        </button>
                                    </div>

                                    {{-- Image preview --}}
                                    <div id="chat-image-preview" class="hidden mt-2 relative inline-block">
                                        <img id="chat-image-thumb" src="" alt="Preview" class="h-20 w-auto rounded-lg border border-slate-200 object-cover">
                                        <button type="button" id="chat-image-remove"
                                                class="absolute -top-1.5 -right-1.5 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs leading-none hover:bg-red-600">&times;</button>
                                    </div>

                                    @if($user->isAdmin() || $user->isNoc())
                                    <div class="mt-2">
                                        <select id="canned-response-picker" class="text-xs border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1.5 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                            <option value="">{{ __('Insert canned response...') }}</option>
                                        </select>
                                    </div>
                                    @endif

                                    {{-- Save / Cancel --}}
                                    <div id="editor-actions" class="flex items-center gap-2 mt-3">
                                        <button type="submit" id="chat-submit" class="inline-flex items-center bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">{{ __('Save') }}</button>
                                        <button type="button" id="editor-cancel" class="text-sm text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 px-3 py-2">{{ __('Cancel') }}</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        @else
                        <div class="border-t border-slate-100 pt-4 text-slate-400 text-sm">{{ __('This ticket is closed. Comments are disabled.') }}</div>
                        @endif
                    </div>

                    {{-- HISTORY TAB --}}
                    <div x-show="tab === 'history'" x-cloak>
                        <div class="space-y-4 max-h-80 overflow-y-auto pr-1" id="history-box">
                            @forelse($ticket->history->sortBy('created_at') as $h)
                            @php
                                $changer = $h->changedBy;
                                $initials = strtoupper(substr($changer->name ?? '?', 0, 1));
                                // Try to parse status changes from action string
                                preg_match('/Status changed (?:from (\w+) )?to (\w+)/', $h->action, $statusMatch);
                                $oldStatus = $statusMatch[1] ?? null;
                                $newStatus = $statusMatch[2] ?? null;
                                $statusColors = ['open'=>'bg-amber-100 text-amber-700','in_progress'=>'bg-blue-100 text-blue-700','pending'=>'bg-orange-100 text-orange-700','waiting_for_customer_feedback'=>'bg-violet-100 text-violet-700','resolved'=>'bg-emerald-100 text-emerald-700','closed'=>'bg-slate-200 text-slate-600','reopened'=>'bg-purple-100 text-purple-700'];
                            @endphp
                            <div class="flex items-start gap-3">
                                @if($changer)
                <img src="{{ $changer->avatarUrl() }}" alt="{{ $changer->name }}" class="w-8 h-8 rounded-full object-cover flex-shrink-0 mt-0.5">
                @else
                <div class="w-8 h-8 rounded-full bg-slate-500 flex items-center justify-center text-white font-bold text-xs flex-shrink-0 mt-0.5">?</div>
                @endif
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-slate-700"><span class="font-semibold">{{ $changer->name ?? 'System' }}</span></p>
                                    <p class="text-xs text-slate-400 mb-1.5">{{ $h->created_at->diffForHumans() }}</p>
                                    <p class="text-xs text-slate-500 font-medium mb-1.5 px-2 py-1 bg-slate-100 rounded-lg inline-block">{{ $h->action }}</p>
                                    @if($oldStatus || $newStatus)
                                    <div class="flex items-center gap-2 mt-1">
                                        @if($oldStatus)
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$oldStatus] ?? 'bg-slate-100 text-slate-600' }}">{{ ucfirst(str_replace('_',' ',$oldStatus)) }}</span>
                                        <span class="text-slate-400">→</span>
                                        @endif
                                        @if($newStatus)
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$newStatus] ?? 'bg-slate-100 text-slate-600' }}">{{ ucfirst(str_replace('_',' ',$newStatus)) }}</span>
                                        @endif
                                    </div>
                                    @endif
                                    @if($h->oldAssignee || $h->newAssignee)
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="inline-flex px-2 py-0.5 rounded bg-slate-200 text-slate-600 text-xs">{{ $h->oldAssignee->name ?? 'Unassigned' }}</span>
                                        <span class="text-slate-400">→</span>
                                        <div class="flex items-center gap-1">
                                            @if($h->newAssignee)
                                            <img src="{{ $h->newAssignee->avatarUrl() }}" alt="{{ $h->newAssignee->name }}" class="w-5 h-5 rounded-full object-cover">
                                            @endif
                                            <span class="inline-flex px-2 py-0.5 rounded bg-indigo-100 text-indigo-700 text-xs font-medium">{{ $h->newAssignee->name ?? 'Unassigned' }}</span>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @empty
                            <div class="py-8 text-center"><p class="text-sm text-slate-400">{{ __('No history yet.') }}</p></div>
                            @endforelse
                        </div>
                    </div>

                    {{-- WORK LOG TAB --}}
                    @if($user->isAdmin() || $user->isNoc())
                    <div x-show="tab === 'worklog'" x-cloak>
                        @php $internalNotes = $ticket->notes->where('is_internal', true)->sortBy('created_at'); @endphp
                        <div class="space-y-3 mb-4 max-h-80 overflow-y-auto pr-1" id="worklog-box">
                            @forelse($internalNotes as $note)
                            <div class="bg-amber-50 border border-amber-100 rounded-lg p-3">
                                <div class="flex items-center gap-2 mb-1">
                                    @if($note->user)
                                    <img src="{{ $note->user->avatarUrl() }}" alt="{{ $note->user->name }}" class="w-6 h-6 rounded-full object-cover">
                                    @else
                                    <div class="w-6 h-6 rounded-full bg-amber-400 flex items-center justify-center text-white font-bold text-xs">?</div>
                                    @endif
                                    <span class="text-xs font-semibold text-slate-700">{{ $note->user->name ?? 'Unknown' }}</span>
                                    <span class="inline-flex px-1.5 py-0.5 rounded text-xs font-medium {{ $roleColors[$note->user->role ?? ''] ?? 'bg-slate-100' }}">{{ strtoupper($note->user->role ?? '') }}</span>
                                    <span class="text-xs text-slate-400">{{ $note->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-line">{!! $note->formatted_note !!}</p>
                            </div>
                            @empty
                            <p class="text-sm text-slate-400 py-4 text-center">{{ __('No internal notes yet.') }}</p>
                            @endforelse
                        </div>
                        <form method="POST" action="{{ route('tickets.notes.store', $ticket) }}" class="border-t border-amber-100 pt-3">
                            @csrf
                            <textarea name="note" id="worklog-note-input" rows="2" placeholder="{{ __('Internal note (staff only)... (type @ to mention someone)') }}" class="w-full border border-amber-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:ring-2 focus:ring-amber-400 bg-amber-50 resize-none mb-2"></textarea>
                            <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">{{ __('Add Note') }}</button>
                        </form>
                    </div>
                    @endif

                </div>
            </div>

        </div>

        {{-- RIGHT COLUMN --}}
        <div class="space-y-5">

            {{-- Admin / NOC Actions --}}
            @if($user->isAdmin() || $user->isNoc())
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-3 bg-gradient-to-r from-indigo-600 to-indigo-700 border-b border-indigo-700">
                    <h2 class="text-sm font-semibold text-white">{{ $user->isAdmin() ? __('Admin Actions') : __('Ticket Actions') }}</h2>
                </div>
                <form method="POST" action="{{ route('tickets.update', $ticket) }}" class="p-5">
                    @csrf @method('PUT')
                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">{{ __('Status') }}</label>
                        <div class="relative inline-flex items-center w-full">
                            {{-- Visible Styled Pill Badge --}}
                            <div class="w-full inline-flex items-center justify-between gap-2 px-3.5 py-2 rounded-full text-xs font-semibold select-none pointer-events-none {{ $sColor }}">
                                <div class="flex items-center gap-2">
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
                                    <span class="whitespace-nowrap">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span>
                                </div>
                                <svg class="w-3.5 h-3.5 opacity-70 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>

                            {{-- Invisible Native Select Overlay --}}
                            <select name="status"
                                    class="absolute inset-0 opacity-0 w-full h-full cursor-pointer z-20">
                                @foreach(['in_progress','pending','waiting_for_customer_feedback','resolved'] as $s)
                                <option value="{{ $s }}" {{ $ticket->status === $s ? 'selected' : '' }} class="bg-white text-slate-800">{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">{{ __('Priority') }}</label>
                        <select name="priority" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-700 focus:ring-2 focus:ring-indigo-500 bg-slate-50">
                            @foreach(['low','medium','high','critical'] as $p)
                            <option value="{{ $p }}" {{ $ticket->priority === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-5">
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">{{ __('Assign To') }}</label>
                        <select name="assigned_to" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-700 focus:ring-2 focus:ring-indigo-500 bg-slate-50">
                            <option value="">{{ __('Unassigned') }}</option>
                            @foreach($nocUsers as $noc)
                            <option value="{{ $noc->id }}" {{ $ticket->assigned_to == $noc->id ? 'selected' : '' }}>
                                {{ $noc->name }} ({{ strtoupper(str_replace('_',' ',$noc->role)) }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition-colors">{{ __('Update Ticket') }}</button>
                </form>
            </div>
            @endif

            {{-- People card for NOC: Assignee + Reporter side-by-side --}}
            @if($user->isNoc())
            @php
                $lastAssignment = $ticket->history
                    ->where('action', 'assigned')
                    ->whereNotNull('new_assignee_id')
                    ->sortByDesc('created_at')
                    ->first();
                $assignedBy = $lastAssignment?->changedBy ?? null;
            @endphp
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 bg-slate-50">
                    <h2 class="text-sm font-semibold text-slate-600">{{ __('People') }}</h2>
                </div>

                {{-- Assignee + Reporter side by side --}}
                <div class="grid grid-cols-2 divide-x divide-slate-100 border-b border-slate-100">
                    <div class="px-4 py-3.5">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-2">{{ __('Assignee') }}</p>
                        @if($ticket->assignee)
                        <div class="flex items-center gap-2">
                            <img src="{{ $ticket->assignee->avatarUrl() }}" alt="{{ $ticket->assignee->name }}"
                                 class="w-7 h-7 rounded-full object-cover flex-shrink-0">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-slate-700 truncate">{{ $ticket->assignee->name }}</p>
                                <p class="text-xs text-slate-400 truncate">{{ strtoupper(str_replace('_',' ',$ticket->assignee->role)) }}</p>
                            </div>
                        </div>
                        @else
                        <div class="flex items-center gap-2 text-slate-400">
                            <div class="w-7 h-7 rounded-full bg-slate-100 border-2 border-dashed border-slate-300 flex items-center justify-center flex-shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </div>
                            <span class="text-xs">{{ __('Unassigned') }}</span>
                        </div>
                        @endif
                    </div>
                    <div class="px-4 py-3.5">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-2">{{ __('Reporter') }}</p>
                        @if($ticket->creator)
                        <div class="flex items-center gap-2">
                            <img src="{{ $ticket->creator->avatarUrl() }}" alt="{{ $ticket->creator->name }}"
                                 class="w-7 h-7 rounded-full object-cover ring-2 ring-emerald-100 flex-shrink-0">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-slate-700 truncate">{{ $ticket->creator->name }}</p>
                                <p class="text-xs text-slate-400 truncate">{{ strtoupper(str_replace('_',' ',$ticket->creator->role)) }}</p>
                            </div>
                        </div>
                        @else
                        <span class="text-xs text-slate-400">{{ __('N/A') }}</span>
                        @endif
                    </div>
                </div>

                {{-- Assigned By row --}}
                <div class="px-4 py-3.5">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-2">{{ __('Assigned By') }}</p>
                    @if($assignedBy)
                    <div class="flex items-center gap-2">
                        <img src="{{ $assignedBy->avatarUrl() }}" alt="{{ $assignedBy->name }}"
                             class="w-7 h-7 rounded-full object-cover ring-2 ring-violet-100 flex-shrink-0">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-slate-700 truncate">{{ $assignedBy->name }}</p>
                            <p class="text-xs text-slate-400 truncate">{{ strtoupper(str_replace('_',' ',$assignedBy->role)) }}</p>
                        </div>
                    </div>
                    @else
                    <p class="text-xs text-slate-400">{{ __('No assignment recorded') }}</p>
                    @endif
                </div>
            </div>
            @endif

            {{-- Ticket Info Sidebar --}}
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden" x-data="{ open: false }">
                <button type="button" @click="open = !open" class="w-full flex items-center justify-between px-5 py-3 border-b border-slate-100 bg-slate-50">
                    <h2 class="text-sm font-semibold text-slate-600">{{ __('Details') }}</h2>
                    <svg class="w-4 h-4 text-slate-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div class="divide-y divide-slate-50" x-show="open" x-cloak>
                    <div class="px-5 py-3">
                        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide mb-1">{{ __('Ticket ID') }}</p>
                        <p class="text-sm font-mono font-bold text-indigo-600">{{ $ticketKey }}</p>
                    </div>
                    <div class="px-5 py-3">
                        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide mb-1">{{ __('Assignee') }}</p>
                        @if($ticket->assignee)
                        <div class="flex items-center gap-2">
                            <img src="{{ $ticket->assignee->avatarUrl() }}" alt="{{ $ticket->assignee->name }}" class="w-6 h-6 rounded-full object-cover">
                            <span class="text-sm text-slate-700">{{ $ticket->assignee->name }}</span>
                        </div>
                        @else
                        <span class="text-sm text-slate-400">{{ __('Unassigned') }}</span>
                        @endif
                    </div>
                    <div class="px-5 py-3">
                        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide mb-1">{{ __('Reporter') }}</p>
                        <div class="flex items-center gap-2">
                            @if($ticket->creator)
                            <img src="{{ $ticket->creator->avatarUrl() }}" alt="{{ $ticket->creator->name }}" class="w-6 h-6 rounded-full object-cover">
                            @else
                            <div class="w-6 h-6 rounded-full bg-slate-400 flex items-center justify-center text-white font-bold text-xs">?</div>
                            @endif
                            <span class="text-sm text-slate-700">{{ $ticket->creator->name ?? __('N/A') }}</span>
                        </div>
                    </div>
                    <div class="px-5 py-3">
                        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide mb-1">{{ __('Priority') }}</p>
                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $pColor }}">{{ ucfirst($ticket->priority) }}</span>
                    </div>
                    <div class="px-5 py-3">
                        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide mb-1">{{ __('Status') }}</p>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $sColor }}">
                            <span class="w-1.5 h-1.5 rounded-full bg-current opacity-70"></span>
                            {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                        </span>
                    </div>
                    <div class="px-5 py-3">
                        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide mb-1">{{ __('Category') }}</p>
                        <span class="text-sm text-slate-600">{{ ucfirst(str_replace('_', ' ', $ticket->category)) }}</span>
                    </div>
                    <div class="px-5 py-3">
                        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide mb-1">{{ __('Resolution') }}</p>
                        <span class="text-sm {{ in_array($ticket->status, ['resolved','closed']) ? 'text-emerald-600 font-medium' : 'text-slate-400' }}">
                            {{ in_array($ticket->status, ['resolved','closed']) ? __('Done') : __('Unresolved') }}
                        </span>
                    </div>
                    @if($ticket->due_at)
                    <div class="px-5 py-3">
                        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide mb-1">{{ __('Due') }}</p>
                        <span class="text-sm {{ $isOverdue ? 'text-red-600 font-bold' : 'text-slate-600' }}">{{ $ticket->due_at->format('d M Y, H:i') }}@if($isOverdue) ({{ __('OVERDUE') }})@endif</span>
                    </div>
                    @endif
                    <div class="px-5 py-3">
                        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide mb-1">{{ __('Created') }}</p>
                        <span class="text-sm text-slate-600">{{ $ticket->created_at->format('d M Y, H:i') }}</span>
                    </div>
                    <div class="px-5 py-3">
                        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide mb-1">{{ __('Updated') }}</p>
                        <span class="text-sm text-slate-600">{{ $ticket->updated_at->format('d M Y, H:i') }}</span>
                    </div>
                    @if($ticket->resolved_at)
                    <div class="px-5 py-3">
                        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide mb-1">{{ __('Resolved') }}</p>
                        <span class="text-sm text-slate-600">{{ $ticket->resolved_at->format('d M Y, H:i') }}</span>
                    </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <script>
    // ── Inline title edit ─────────────────────────────────────────────────────
    function inlineTitleEdit() {
        return {
            editing: false,
            title: @json($ticket->title),
            originalTitle: @json($ticket->title),
            startEdit() {
                this.editing = true;
                this.$nextTick(() => {
                    this.$refs.titleInput.focus();
                    this.$refs.titleInput.select();
                });
            },
            cancel() {
                this.editing = false;
                this.title = this.originalTitle;
            },
            save() {
                if (!this.title.trim()) return;
                if (this.title === this.originalTitle) { this.editing = false; return; }
                fetch('{{ route("tickets.title.update", $ticket) }}', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ title: this.title }),
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        this.originalTitle = this.title;
                        if (this.$refs.titleDisplay) this.$refs.titleDisplay.textContent = this.title;
                    }
                    this.editing = false;
                })
                .catch(() => { this.editing = false; });
            }
        }
    }

    // ── Role colour map & Admin status ───────────────────────────────────────
    const roleColorsJs = @json($roleColors);
    const isUserAdmin  = {{ $user->isAdmin() ? 'true' : 'false' }};

    // ── Scroll chat to bottom ────────────────────────────────────────────────
    const chatBox = document.getElementById('chat-box');
    const commentsBox = document.getElementById('comments-box');
    function scrollBottom() {
        if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
        if (commentsBox) commentsBox.scrollTop = commentsBox.scrollHeight;
    }
    scrollBottom();

    function pushDesktop(title, body, url) {
        if (typeof pushDesktopGlobal === 'function') {
            pushDesktopGlobal(title, body, url);
        }
    }

    // ── Build a chat bubble element from JSON (Image 2 style) ─────────────────
    const myId = {{ auth()->id() }};

    function buildBubble(msg) {
        const isMe      = msg.isMe;
        const role      = msg.senderRole || 'unknown';
        const roleClass = roleColorsJs[role] || 'bg-slate-100 text-slate-600';
        const roleText  = (role || 'unknown').replace(/_/g, ' ').toUpperCase();

        const wrap = document.createElement('div');
        wrap.classList.add('flex', isMe ? 'justify-end' : 'justify-start');
        wrap.dataset.messageId = msg.id;

        const avatarImgLeft = !isMe
            ? `<img src="${msg.avatarUrl}" alt="${escHtml(msg.senderName || '')}" class="w-7 h-7 rounded-full object-cover mr-2 flex-shrink-0 mt-1">`
            : '';
        const avatarImgRight = isMe
            ? `<img src="${msg.avatarUrl}" alt="${escHtml(msg.senderName || '')}" class="w-7 h-7 rounded-full object-cover ml-2 flex-shrink-0 mt-1">`
            : '';

        let privateTag = '';
        if (msg.is_private) {
            privateTag = `<span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300 shadow-2xs">🔒 {{ __('Private') }}</span>`;
        }

        const roleTag = `<span class="inline-flex px-1.5 py-0.5 rounded text-xs font-medium ${roleClass}">${roleText}</span>`;
        const meta = `
            <div class="flex items-center gap-2 mb-1 ${isMe ? 'justify-end' : ''}">
                <span class="text-xs font-semibold text-slate-600">${escHtml(msg.senderName || 'Unknown')}</span>
                ${roleTag}
                ${privateTag}
                <span class="text-xs text-slate-400" title="${escHtml(msg.time || '')}">${escHtml(msg.time || '')}</span>
            </div>
        `;

        let replyPreview = '';
        if (msg.replyTo) {
            replyPreview = `
                <div class="mb-1 px-2.5 py-1.5 rounded-lg bg-slate-50 border-l-2 border-indigo-300 text-xs text-slate-500 ${isMe ? 'text-right' : ''}">
                    <span class="font-semibold text-slate-600">${escHtml(msg.replyTo.senderName || 'Unknown')}</span>:
                    ${escHtml(msg.replyTo.preview || '📎 Image')}
                </div>
            `;
        }

        const bubbleClasses = msg.is_private
            ? 'bg-amber-50 dark:bg-amber-950/30 border-2 border-amber-300 text-slate-800'
            : (isMe ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-800');

        const msgBody = msg.formatted_message || msg.message || '';
        let content = msgBody ? `<div class="text-sm leading-relaxed prose prose-sm max-w-none ${isMe && !msg.is_private ? 'prose-invert' : ''}">${msgBody}</div>` : '';
        if (msg.image_url) {
            content += `<img src="${msg.image_url}" alt="Attachment"
                             class="mt-2 rounded-lg cursor-pointer hover:opacity-90 transition-opacity"
                             style="max-height:160px; max-width:220px; object-fit:cover; display:block;"
                             onclick="openImgModal(this.src)">`;
        }

        const plainPreview = escHtml((msg.message || '').replace(/<[^>]*>/g, '').slice(0, 60) || '📎 Image');

        let reactionsHtml = '';
        if (msg.reactions && Object.keys(msg.reactions).length > 0) {
            let pills = '';
            for (const [em, info] of Object.entries(msg.reactions)) {
                const cls = info.reacted
                    ? 'bg-indigo-100 border-indigo-300 text-indigo-700 font-semibold shadow-2xs'
                    : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100';
                const names = (info.names || []).join(', ');
                pills += `<button type="button" class="msg-react-btn inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full border transition-all ${cls}" data-msg-id="${msg.id}" data-emoji="${em}" title="${escHtml(names)}"><span>${em}</span><span>${info.count}</span></button>`;
            }
            reactionsHtml = `<div class="reactions-container flex flex-wrap items-center gap-1.5 mt-1.5 ${isMe ? 'justify-end' : ''}" id="reactions-list-${msg.id}">${pills}</div>`;
        }

        const canEdit = isMe || isUserAdmin;
        const editBtnHtml = canEdit ? `
            <button type="button" class="msg-edit-btn p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-slate-100 transition-colors"
                    data-id="${msg.id}"
                    data-sender="${escHtml(msg.senderName || 'Unknown')}"
                    data-message="${escHtml(msg.message || '')}"
                    data-is-private="${msg.is_private ? 1 : 0}"
                    title="{{ __('Edit') }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            </button>
        ` : '';

        const actions = `
            ${reactionsHtml}
            <div class="flex items-center gap-1 mt-1 ${isMe ? 'justify-end' : ''}">
                <button type="button" class="msg-reply-btn p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-slate-100 transition-colors"
                        data-id="${msg.id}"
                        data-sender="${escHtml(msg.senderName || 'Unknown')}"
                        data-preview="${plainPreview}"
                        title="{{ __('Reply') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                </button>

                <button type="button" class="msg-react-btn p-1.5 rounded-lg text-slate-400 hover:text-amber-500 hover:bg-amber-50 transition-colors"
                        data-msg-id="${msg.id}"
                        data-emoji="👍"
                        title="{{ __('Thumbs up') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2"/></svg>
                </button>

                <button type="button" class="msg-emoji-bar-toggle-btn p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-slate-100 transition-colors"
                        data-msg-id="${msg.id}"
                        title="{{ __('Add reaction') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v4m-2-2h4"/></svg>
                </button>

                ${editBtnHtml}

                <button type="button" class="msg-emoji-bar-toggle-btn p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors"
                        data-msg-id="${msg.id}"
                        title="{{ __('More options') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"/></svg>
                </button>
            </div>
        `;

        wrap.innerHTML = `
            ${avatarImgLeft}
            <div class="max-w-sm">
                ${meta}
                ${replyPreview}
                <div class="${bubbleClasses} rounded-xl px-4 py-3 shadow-sm">
                    ${content}
                </div>
                ${actions}
            </div>
            ${avatarImgRight}
        `;

        if (window.Alpine) window.Alpine.initTree(wrap);

        return wrap;
    }

    function escHtml(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // ── Emoji picker for the reply composer ──────────────────────────────────
    const EMOJI_CATALOG = {
        'Smileys': [
            ['😀','grinning'], ['😃','smiley'], ['😄','smile'], ['😁','grin'], ['😆','laughing'],
            ['😅','sweat smile'], ['🤣','rofl'], ['😂','joy tears'], ['🙂','slight smile'], ['🙃','upside down'],
            ['😉','wink'], ['😊','blush'], ['😇','angel innocent'], ['😍','heart eyes love'], ['🥰','smiling hearts'],
            ['😘','kiss'], ['😗','kissing'], ['😋','yum tongue'], ['😛','tongue'], ['😜','wink tongue'],
            ['🤪','zany crazy'], ['🤨','raised eyebrow'], ['🧐','monocle'], ['🤓','nerd glasses'], ['😎','sunglasses cool'],
            ['🥳','party celebrate'], ['😏','smirk'], ['😒','unamused'], ['😞','disappointed'], ['😔','pensive sad'],
            ['😟','worried'], ['😕','confused'], ['🙁','frown'], ['😣','persevere'], ['😖','confounded'],
            ['😫','tired'], ['😩','weary'], ['🥺','pleading puppy'], ['😢','cry sad'], ['😭','sob crying'],
            ['😤','triumph angry'], ['😠','angry'], ['😡','rage mad'], ['🤬','cursing'], ['🤯','mind blown'],
            ['😳','flushed'], ['🥵','hot'], ['🥶','cold'], ['😱','scream fear'], ['😨','fearful'],
            ['😰','anxious sweat'], ['😥','sad relieved'], ['😓','downcast sweat'], ['🤗','hug'], ['🤔','thinking'],
            ['🤭','hand over mouth'], ['🤫','shush quiet'], ['🤥','lying nose'], ['😶','no mouth'], ['😐','neutral'],
            ['😑','expressionless'], ['😯','hushed'], ['😦','frowning open'], ['😧','anguished'], ['😮','open mouth wow'],
            ['😲','astonished'], ['🥱','yawn tired'], ['😴','sleeping zzz'], ['🤤','drooling'], ['😪','sleepy'],
        ],
        'Gestures': [
            ['👍','thumbs up like'], ['👎','thumbs down dislike'], ['👌','ok'], ['✌️','peace victory'], ['🤞','fingers crossed'],
            ['🤟','love you gesture'], ['🤘','rock on'], ['🤙','call me'], ['👈','point left'], ['👉','point right'],
            ['👆','point up'], ['👇','point down'], ['☝️','index up'], ['✋','raised hand stop'], ['🤚','back hand'],
            ['🖐️','hand fingers'], ['🖖','vulcan spock'], ['👋','wave hello bye'], ['🤝','handshake deal'], ['🙏','pray thanks please'],
            ['✍️','writing'], ['💪','muscle strong'], ['🙌','celebrate raised hands'], ['👏','clap applause'], ['🤲','open hands'],
            ['👐','open hands'], ['🤛','fist bump left'], ['🤜','fist bump right'], ['👊','fist punch'], ['✊','raised fist'],
        ],
        'Hearts': [
            ['❤️','red heart love'], ['🧡','orange heart'], ['💛','yellow heart'], ['💚','green heart'], ['💙','blue heart'],
            ['💜','purple heart'], ['🖤','black heart'], ['🤍','white heart'], ['🤎','brown heart'], ['💔','broken heart'],
            ['❣️','heart exclamation'], ['💕','two hearts'], ['💞','revolving hearts'], ['💓','beating heart'], ['💗','growing heart'],
            ['💖','sparkling heart'], ['💘','heart arrow'], ['💝','gift heart'], ['💟','heart decoration'],
        ],
        'Objects': [
            ['🔥','fire hot lit'], ['⭐','star'], ['🌟','glowing star'], ['✨','sparkles'], ['⚡','lightning zap'],
            ['💯','hundred perfect'], ['✅','check done'], ['❌','cross no'], ['⚠️','warning'], ['❗','exclamation'],
            ['❓','question'], ['💡','idea lightbulb'], ['📌','pin'], ['📎','paperclip attach'], ['🔧','wrench tool'],
            ['🔨','hammer'], ['⚙️','gear settings'], ['🔒','lock'], ['🔓','unlock'], ['🔑','key'],
            ['💰','money bag'], ['💳','credit card'], ['📱','phone mobile'], ['💻','laptop computer'], ['🖥️','desktop computer'],
            ['🌐','globe network internet'], ['📶','signal wifi'], ['🔌','plug'], ['🔋','battery'], ['📡','satellite antenna'],
            ['🕐','clock time'], ['⏰','alarm clock'], ['📅','calendar date'], ['📧','email'], ['📞','phone call'],
        ],
        'Celebration': [
            ['🎉','party popper celebrate'], ['🎊','confetti'], ['🎂','birthday cake'], ['🎁','gift present'], ['🏆','trophy win'],
            ['🥇','gold medal first'], ['🎯','target dart'], ['🚀','rocket launch'], ['🎈','balloon'], ['🍾','champagne'],
        ],
    };

    function setupEmojiPicker(quill) {
        const btn        = document.getElementById('emoji-picker-btn');
        const panel      = document.getElementById('emoji-picker-panel');
        const search     = document.getElementById('emoji-search');
        const tabsEl     = document.getElementById('emoji-category-tabs');
        const gridEl     = document.getElementById('emoji-grid');
        const titleEl    = document.getElementById('emoji-cat-title');
        const footerIcon = document.getElementById('emoji-footer-icon');
        const footerName = document.getElementById('emoji-footer-name');
        const footerCode = document.getElementById('emoji-footer-code');
        if (!btn || !panel || !gridEl) return;

        const catIcons = {
            'Smileys': '😃',
            'Gestures': '👍',
            'Hearts': '❤️',
            'Objects': '💡',
            'Celebration': '🎉'
        };

        const categories = Object.keys(EMOJI_CATALOG);
        let activeCat = categories[0];
        let savedCursorIndex = 0;

        quill.on('selection-change', function(range) {
            if (range && typeof range.index === 'number') {
                savedCursorIndex = range.index;
            }
        });

        btn.addEventListener('click', function() {
            const sel = quill.getSelection();
            if (sel && typeof sel.index === 'number') {
                savedCursorIndex = sel.index;
            }
        });

        function renderGrid(list) {
            gridEl.innerHTML = list.map(([emoji, name]) =>
                `<button type="button" class="emoji-insert-btn p-1 rounded-lg text-lg hover:bg-indigo-50 hover:scale-125 transition-all text-center focus:outline-none flex items-center justify-center cursor-pointer" data-emoji="${emoji}" data-name="${escHtml(name)}" title="${escHtml(name)}">${emoji}</button>`
            ).join('') || `<p style="grid-column: span 7 / span 7;" class="text-xs text-slate-400 text-center py-4">No emoji found</p>`;

            gridEl.querySelectorAll('.emoji-insert-btn').forEach(b => {
                b.addEventListener('mouseenter', () => {
                    const em = b.dataset.emoji;
                    const name = b.dataset.name || 'Emoji';
                    if (footerIcon) footerIcon.textContent = em;
                    if (footerName) footerName.textContent = name.charAt(0).toUpperCase() + name.slice(1);
                    if (footerCode) footerCode.textContent = ':' + name.replace(/\s+/g, '_') + ':';
                });
            });
        }

        function renderTabs() {
            if (!tabsEl) return;
            tabsEl.innerHTML = categories.map(cat =>
                `<button type="button" class="emoji-tab-btn p-1.5 rounded-lg text-lg hover:bg-slate-100 transition-colors ${cat === activeCat ? 'bg-indigo-50 text-indigo-600' : 'text-slate-500'}" data-cat="${cat}" title="${cat}">
                    ${catIcons[cat] || '😃'}
                </button>`
            ).join('');

            tabsEl.querySelectorAll('.emoji-tab-btn').forEach(tb => {
                tb.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    activeCat = tb.dataset.cat;
                    if (titleEl) titleEl.textContent = activeCat;
                    if (search) search.value = '';
                    renderTabs();
                    renderGrid(EMOJI_CATALOG[activeCat] || []);
                });
            });
        }

        function showCategory(cat) {
            activeCat = cat;
            if (titleEl) titleEl.textContent = cat;
            renderTabs();
            renderGrid(EMOJI_CATALOG[cat] || []);
        }

        if (search) {
            search.addEventListener('input', function() {
                const q = this.value.trim().toLowerCase();
                if (!q) {
                    if (titleEl) titleEl.textContent = activeCat;
                    renderGrid(EMOJI_CATALOG[activeCat] || []);
                    return;
                }
                if (titleEl) titleEl.textContent = 'Search results';
                const all = Object.values(EMOJI_CATALOG).flat();
                const matches = all.filter(([emoji, name]) => name.toLowerCase().includes(q));
                renderGrid(matches);
            });
        }

        // Prevent panel clicks from stealing focus from Quill editor
        panel.addEventListener('mousedown', function(e) {
            const emojiBtn = e.target.closest('.emoji-insert-btn, .emoji-tab-btn');
            if (emojiBtn) {
                e.preventDefault();
            }
        });

        panel.addEventListener('click', function(e) {
            const emojiBtn = e.target.closest('.emoji-insert-btn');
            if (!emojiBtn) return;
            e.preventDefault();
            e.stopPropagation();

            const emoji = emojiBtn.dataset.emoji;
            if (!emoji) return;

            quill.focus();

            const sel = quill.getSelection(true);
            let idx = (sel && typeof sel.index === 'number') ? sel.index : savedCursorIndex;
            const totalLen = quill.getLength();
            if (idx === undefined || idx === null || idx < 0 || idx >= totalLen) {
                idx = Math.max(0, totalLen - 1);
            }

            quill.insertText(idx, emoji, 'user');
            savedCursorIndex = idx + emoji.length;
            quill.setSelection(savedCursorIndex, 0, 'user');

            const editorAct = document.getElementById('editor-actions');
            if (editorAct) editorAct.style.removeProperty('display');

            window.dispatchEvent(new CustomEvent('close-emoji-panel'));
        });

        showCategory(activeCat);
    }

    // ── Real-time chat polling & Typing status ────────────────────────────────
    let lastMsgId = {{ $ticket->messages->max('id') ?? 0 }};
    const pollUrl = '{{ route("tickets.messages.poll", $ticket) }}';
    const typingUrl = '{{ route("tickets.typing", $ticket) }}';
    const csrfToken = '{{ csrf_token() }}';
    const ticketClosed = {{ in_array($ticket->status, ['closed']) ? 'true' : 'false' }};
    const typingInd = document.getElementById('typing-indicator');
    const typingTxt = document.getElementById('typing-text');
    let lastTypingSent = 0;

    function notifyTyping() {
        if (ticketClosed) return;
        const now = Date.now();
        if (now - lastTypingSent < 2000) return;
        lastTypingSent = now;
        fetch(typingUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).catch(() => {});
    }

    function pollChat() {
        if (ticketClosed) return;
        fetch(`${pollUrl}?after=${lastMsgId}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.messages && data.messages.length > 0) {
                const emptyChat = chatBox?.querySelector('.py-8');
                if (emptyChat) emptyChat.remove();
                const emptyComments = commentsBox?.querySelector('.py-8');
                if (emptyComments) emptyComments.remove();

                data.messages.forEach(msg => {
                    const bubble1 = buildBubble(msg);
                    const bubble2 = buildBubble(msg);
                    if (chatBox) chatBox.appendChild(bubble1);
                    if (commentsBox) commentsBox.appendChild(bubble2);
                    lastMsgId = Math.max(lastMsgId, msg.id);

                    if (!msg.isMe) {
                        pushDesktop(
                            `New message — Ticket {{ $ticket->ticket_key ?? '#'.$ticket->id }}`,
                            `${msg.senderName}: ${msg.message || '📎 Image'}`,
                            window.location.href
                        );
                    }
                });
                scrollBottom();
            }

            // Handle live typing indicator
            if (data.typing && data.typing.length > 0) {
                if (typingTxt) typingTxt.textContent = `✍️ ${data.typing.join(', ')} is typing...`;
                if (typingInd) { typingInd.classList.remove('hidden'); typingInd.classList.add('flex'); }
            } else {
                if (typingInd) { typingInd.classList.add('hidden'); typingInd.classList.remove('flex'); }
            }
        })
        .catch(() => {});
    }

    if (!ticketClosed && (chatBox || commentsBox)) setInterval(pollChat, 3000);

    // ── Quill rich-text editor ───────────────────────────────────────────────
    const quillEl = document.getElementById('quill-body');
    const editorActions = document.getElementById('editor-actions');
    const editorCancel  = document.getElementById('editor-cancel');
    let quill = null;

    const mentionUsers = @json($mentionUsers);

    // Create mention dropdown element
    const mentionDrop = document.createElement('div');
    mentionDrop.id = 'mention-dropdown';
    document.body.appendChild(mentionDrop);

    let mentionActive = false, mentionStart = 0, mentionSearch = '';
    let activeIdx = 0;

    function hideMention() {
        mentionDrop.classList.remove('show');
        mentionActive = false;
        mentionSearch = '';
    }

    let onMentionSelect = null;

    function showMention(results, anchorBounds, customCallback = null) {
        if (!results.length) { hideMention(); return; }
        activeIdx = 0;
        onMentionSelect = customCallback;
        mentionDrop.innerHTML = results.map((u, i) => `
            <div class="mention-item${i === 0 ? ' active' : ''}" data-idx="${i}">
                <img src="${u.avatar}" alt="${u.value}">
                <span>
                    <span class="mi-name">${u.value}</span>
                    <span class="mi-role">${u.role.replace('_',' ').toUpperCase()}</span>
                </span>
            </div>`).join('');

        // Position under cursor
        mentionDrop.style.left = anchorBounds.left + 'px';
        mentionDrop.style.top  = (anchorBounds.bottom + window.scrollY + 4) + 'px';
        mentionDrop.classList.add('show');

        mentionDrop.querySelectorAll('.mention-item').forEach(el => {
            el.addEventListener('mousedown', function(e) {
                e.preventDefault();
                const u = results[parseInt(el.dataset.idx)];
                if (onMentionSelect) {
                    onMentionSelect(u);
                } else {
                    insertMention(u);
                }
            });
        });
    }

    function insertMention(user) {
        // Delete the @search text
        const deleteLen = 1 + mentionSearch.length;
        quill.deleteText(mentionStart, deleteLen);
        // Insert styled mention as HTML via clipboard
        const chip = `<span class="mention-chip" data-id="${user.id}">@${user.value}</span>&nbsp;`;
        const idx  = mentionStart;
        quill.clipboard.dangerouslyPasteHTML(idx, chip);
        quill.setSelection(idx + user.value.length + 2, 0);
        hideMention();
    }

    if (window.Quill) {
        try {
            const Inline = Quill.import('blots/inline');
            class MentionBlot extends Inline {
                static create(value) {
                    let node = super.create();
                    node.setAttribute('class', 'mention-chip');
                    if (typeof value === 'object' && value.id) {
                        node.setAttribute('data-id', value.id);
                    }
                    return node;
                }
                static formats(node) {
                    return {
                        id: node.getAttribute('data-id'),
                        class: node.getAttribute('class')
                    };
                }
            }
            MentionBlot.blotName = 'mentionChip';
            MentionBlot.tagName = 'span';
            MentionBlot.className = 'mention-chip';
            Quill.register(MentionBlot, true);
        } catch (err) {}
    }

    if (quillEl) {
        quill = new Quill('#quill-body', {
            theme: 'snow',
            placeholder: 'Write a comment… (type @ to mention someone)',
            modules: { toolbar: '#quill-toolbar' },
        });

        // ── Canned responses picker ──────────────────────────────────
        const cannedPicker = document.getElementById('canned-response-picker');
        if (cannedPicker) {
            fetch('{{ route("canned-responses.list") }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(list => {
                    list.forEach(r => {
                        const opt = document.createElement('option');
                        opt.value = r.body;
                        opt.textContent = r.title;
                        cannedPicker.appendChild(opt);
                    });
                })
                .catch(() => {});

            cannedPicker.addEventListener('change', function() {
                if (!this.value) return;
                const sel = quill.getSelection(true);
                quill.insertText(sel ? sel.index : quill.getLength(), this.value);
                editorActions.style.removeProperty('display');
                this.value = '';
            });
        }

        // ── Emoji picker (inserts into the reply text) ───────────────────────
        setupEmojiPicker(quill);

        quill.on('text-change', function() {
            notifyTyping();
            // Show/hide Save+Cancel
            if (quill.getText().trim().length > 0) {
                editorActions.style.removeProperty('display');
            } else {
                editorActions.style.setProperty('display', 'none', 'important');
            }

            // @ mention detection
            const sel = quill.getSelection();
            if (!sel) return;
            const cursorPos = sel.index;
            const textBefore = quill.getText(0, cursorPos);
            const atIdx = textBefore.lastIndexOf('@');

            if (atIdx !== -1) {
                const fragment = textBefore.slice(atIdx + 1);
                // Only trigger if no space in fragment (still typing the name)
                if (!fragment.includes(' ') || fragment.length === 0) {
                    mentionActive = true;
                    mentionStart  = atIdx;
                    mentionSearch = fragment;

                    const bounds = quill.getBounds(atIdx);
                    const editorRect = quillEl.closest('#quill-editor').getBoundingClientRect();
                    const anchorBounds = {
                        left:   editorRect.left + bounds.left,
                        bottom: editorRect.top  + bounds.bottom,
                    };

                    const results = mentionUsers.filter(u =>
                        u.value.toLowerCase().includes(fragment.toLowerCase())
                    );
                    showMention(results, anchorBounds);
                    return;
                }
            }
            hideMention();
        });

        // Keyboard navigation inside mention dropdown
        quillEl.addEventListener('keydown', function(e) {
            if (!mentionDrop.classList.contains('show')) return;
            const items = mentionDrop.querySelectorAll('.mention-item');
            const results = mentionUsers.filter(u =>
                u.value.toLowerCase().includes(mentionSearch.toLowerCase())
            );
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                items[activeIdx]?.classList.remove('active');
                activeIdx = (activeIdx + 1) % items.length;
                items[activeIdx]?.classList.add('active');
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                items[activeIdx]?.classList.remove('active');
                activeIdx = (activeIdx - 1 + items.length) % items.length;
                items[activeIdx]?.classList.add('active');
            } else if (e.key === 'Enter' || e.key === 'Tab') {
                e.preventDefault();
                if (results[activeIdx]) insertMention(results[activeIdx]);
            } else if (e.key === 'Escape') {
                hideMention();
            }
        });

        document.addEventListener('click', function(e) {
            if (!mentionDrop.contains(e.target)) hideMention();
        });

        editorCancel?.addEventListener('click', function() {
            quill.setContents([]);
            editorActions.style.setProperty('display', 'none', 'important');
            hideMention();
        });
    }

    // ── Work Log Mention Autocomplete ─────────────────────────────────────────
    const worklogInput = document.getElementById('worklog-note-input');
    if (worklogInput) {
        let wlMentionStart = 0;
        let wlMentionSearch = '';

        worklogInput.addEventListener('input', function() {
            const cursorPos = worklogInput.selectionStart;
            const textBefore = worklogInput.value.slice(0, cursorPos);
            const atIdx = textBefore.lastIndexOf('@');

            if (atIdx !== -1) {
                const fragment = textBefore.slice(atIdx + 1);
                if (!fragment.includes(' ') && !fragment.includes('\n')) {
                    wlMentionStart  = atIdx;
                    wlMentionSearch = fragment;

                    const rect = worklogInput.getBoundingClientRect();
                    const anchorBounds = {
                        left:   rect.left + 10,
                        bottom: rect.bottom,
                    };

                    const results = mentionUsers.filter(u =>
                        u.value.toLowerCase().includes(fragment.toLowerCase())
                    );
                    showMention(results, anchorBounds, function(user) {
                        const before = worklogInput.value.slice(0, wlMentionStart);
                        const after  = worklogInput.value.slice(cursorPos);
                        worklogInput.value = before + '@' + user.value + ' ' + after;
                        worklogInput.focus();
                        const newPos = before.length + user.value.length + 2;
                        worklogInput.setSelectionRange(newPos, newPos);
                        hideMention();
                    });
                    return;
                }
            }
            hideMention();
        });

        worklogInput.addEventListener('keydown', function(e) {
            if (!mentionDrop.classList.contains('show') || !onMentionSelect) return;
            const items = mentionDrop.querySelectorAll('.mention-item');
            const results = mentionUsers.filter(u =>
                u.value.toLowerCase().includes(wlMentionSearch.toLowerCase())
            );
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                items[activeIdx]?.classList.remove('active');
                activeIdx = (activeIdx + 1) % items.length;
                items[activeIdx]?.classList.add('active');
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                items[activeIdx]?.classList.remove('active');
                activeIdx = (activeIdx - 1 + items.length) % items.length;
                items[activeIdx]?.classList.add('active');
            } else if (e.key === 'Enter' || e.key === 'Tab') {
                e.preventDefault();
                if (results[activeIdx] && onMentionSelect) {
                    onMentionSelect(results[activeIdx]);
                }
            } else if (e.key === 'Escape') {
                hideMention();
            }
        });
    }

    // ── Send message via AJAX ────────────────────────────────────────────────
    const chatForm = document.getElementById('chat-form');
    if (chatForm) {
        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Inject Quill HTML into hidden input
            if (quill) {
                const html = (typeof quill.getSemanticHTML === 'function') ? quill.getSemanticHTML() : (quill.root ? quill.root.innerHTML : '');
                document.getElementById('quill-hidden').value = (quill.getText().trim() === '') ? '' : html;
            }

            const fd  = new FormData(chatForm);
            const editingId = document.getElementById('editing-message-id')?.value;

            let targetUrl = chatForm.action;
            if (editingId) {
                targetUrl = "{{ url('tickets/' . $ticket->id . '/messages') }}/" + editingId;
                fd.append('_method', 'PUT');
            }

            const btn = document.getElementById('chat-submit');
            if (btn) { btn.disabled = true; btn.textContent = 'Saving…'; }

            fetch(targetUrl, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: fd,
            })
            .then(r => r.json().catch(() => null))
            .then(data => {
                if (quill) quill.setContents([]);
                clearReplyPreview();
                clearEditPreview();
                if (editingId && data && data.message) {
                    const existingEls = document.querySelectorAll(`[data-message-id="${editingId}"]`);
                    existingEls.forEach(el => {
                        const newBubble = buildBubble(data.message);
                        el.replaceWith(newBubble);
                    });
                } else {
                    pollChat();
                }
            })
            .catch(() => {})
            .finally(() => {
                if (btn) {
                    btn.disabled = false;
                    btn.textContent = editingId ? '{{ __("Update") }}' : '{{ __("Save") }}';
                }
            });
        });
    }

    // ── Image attachment ─────────────────────────────────────────────────────
    const chatImageInput   = document.getElementById('chat-image-input');
    const chatImagePreview = document.getElementById('chat-image-preview');
    const chatImageThumb   = document.getElementById('chat-image-thumb');
    const chatImageRemove  = document.getElementById('chat-image-remove');

    if (chatImageInput) {
        chatImageInput.addEventListener('change', function() {
            const file = this.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function(e) {
                chatImageThumb.src = e.target.result;
                chatImagePreview.classList.remove('hidden');
                if (editorActions) editorActions.style.removeProperty('display');
            };
            reader.readAsDataURL(file);
        });
    }

    if (chatImageRemove) {
        chatImageRemove.addEventListener('click', function() {
            chatImageInput.value = '';
            chatImageThumb.src = '';
            chatImagePreview.classList.add('hidden');
            if (quill && quill.getText().trim() === '') {
                if (editorActions) editorActions.style.setProperty('display', 'none', 'important');
            }
        });
    }

    // Clear image preview after successful send
    const origChatForm = document.getElementById('chat-form');
    if (origChatForm) {
        origChatForm.addEventListener('submit', function() {
            setTimeout(function() {
                if (chatImageInput) chatImageInput.value = '';
                if (chatImageThumb) chatImageThumb.src = '';
                if (chatImagePreview) chatImagePreview.classList.add('hidden');
            }, 500);
        }, { capture: true });
    }

    // ── Reply & Edit Message Helpers ─────────────────────────────────────────
    const replyToInput   = document.getElementById('reply-to-id');
    const replyPreviewEl = document.getElementById('reply-preview');
    const replyPreviewSender = document.getElementById('reply-preview-sender');
    const replyPreviewText   = document.getElementById('reply-preview-text');

    const editingInput     = document.getElementById('editing-message-id');
    const editPreviewEl    = document.getElementById('edit-preview');
    const editPreviewSender = document.getElementById('edit-preview-sender');

    function showReplyPreview(id, sender, preview) {
        if (!replyToInput || !replyPreviewEl) return;
        clearEditPreview();
        replyToInput.value = id;
        replyPreviewSender.textContent = sender;
        replyPreviewText.textContent = preview;
        replyPreviewEl.classList.remove('hidden');
        replyPreviewEl.classList.add('flex');
    }

    function clearReplyPreview() {
        if (!replyToInput || !replyPreviewEl) return;
        replyToInput.value = '';
        replyPreviewEl.classList.add('hidden');
        replyPreviewEl.classList.remove('flex');
    }

    function showEditPreview(id, sender) {
        if (!editingInput || !editPreviewEl) return;
        clearReplyPreview();
        editingInput.value = id;
        if (editPreviewSender) editPreviewSender.textContent = sender;
        editPreviewEl.classList.remove('hidden');
        editPreviewEl.classList.add('flex');
        const submitBtn = document.getElementById('chat-submit');
        if (submitBtn) submitBtn.textContent = '{{ __("Update") }}';
    }

    function clearEditPreview() {
        if (!editingInput || !editPreviewEl) return;
        editingInput.value = '';
        editPreviewEl.classList.add('hidden');
        editPreviewEl.classList.remove('flex');
        const submitBtn = document.getElementById('chat-submit');
        if (submitBtn) submitBtn.textContent = '{{ __("Save") }}';
        if (quill) quill.setContents([]);
    }

    document.getElementById('reply-preview-cancel')?.addEventListener('click', clearReplyPreview);
    document.getElementById('edit-preview-cancel')?.addEventListener('click', clearEditPreview);

    function escHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function updateReactionsUI(msgId, reactions) {
        const msgEl = document.querySelector(`[data-message-id="${msgId}"]`);
        if (!msgEl) return;

        let container = msgEl.querySelector(`.reactions-container`);
        const keys = Object.keys(reactions);

        if (keys.length === 0) {
            if (container) container.remove();
            return;
        }

        if (!container) {
            container = document.createElement('div');
            container.id = `reactions-list-${msgId}`;
            const isMe = msgEl.classList.contains('justify-end');
            container.className = `reactions-container flex flex-wrap items-center gap-1.5 mt-1.5 ${isMe ? 'justify-end' : ''}`;
            const bubble = msgEl.querySelector('.rounded-xl');
            if (bubble) bubble.after(container);
        }

        let pills = '';
        for (const [em, info] of Object.entries(reactions)) {
            const cls = info.reacted
                ? 'bg-indigo-100 border-indigo-300 text-indigo-700 font-semibold shadow-2xs'
                : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100';
            const names = (info.names || []).join(', ');
            pills += `<button type="button" class="msg-react-btn inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full border transition-all ${cls}" data-msg-id="${msgId}" data-emoji="${em}" title="${escHtml(names)}"><span>${em}</span><span>${info.count}</span></button>`;
        }
        container.innerHTML = pills;
    }

    // ── Image 2 Floating Quick Reaction Bar & Image 3 Full Emoji Picker Popover ────────────────
    let activeQuickBar = null;
    let activeFullPicker = null;

    function closeAllEmojiPopovers() {
        if (activeQuickBar) { activeQuickBar.remove(); activeQuickBar = null; }
        if (activeFullPicker) { activeFullPicker.remove(); activeFullPicker = null; }
    }

    function showQuickReactionPopover(msgId, anchorBtn) {
        closeAllEmojiPopovers();
        const rect = anchorBtn.getBoundingClientRect();

        const pop = document.createElement('div');
        pop.className = 'fixed z-50 bg-white border border-slate-200/90 shadow-xl rounded-full px-3.5 py-1.5 flex items-center gap-2.5 animate-in fade-in zoom-in duration-150';

        const quickItems = [
            { em: '👍', name: 'Thumbs up' },
            { em: '👏', name: 'Clapping hands' },
            { em: '🔥', name: 'Fire' },
            { em: '❤️', name: 'Red heart' },
            { em: '😲', name: 'Astonished' },
            { em: '🤔', name: 'Thinking' }
        ];

        let html = quickItems.map(item => `
            <div class="relative group/em flex items-center justify-center">
                <button type="button" class="msg-react-btn text-xl hover:scale-130 transition-transform p-0.5 rounded-full focus:outline-none flex items-center justify-center" data-msg-id="${msgId}" data-emoji="${item.em}">${item.em}</button>
                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover/em:block bg-slate-900 text-white text-xs font-semibold px-2.5 py-1 rounded-lg whitespace-nowrap shadow-lg z-50 pointer-events-none">${item.name}</div>
            </div>
        `).join('');

        html += `<span class="w-px h-4 bg-slate-200"></span>
            <button type="button" class="open-full-picker-btn text-slate-400 hover:text-slate-700 p-1 font-bold text-sm leading-none focus:outline-none flex items-center justify-center" data-msg-id="${msgId}" title="More emojis">•••</button>`;

        pop.innerHTML = html;
        document.body.appendChild(pop);

        const popWidth = 260;
        const popHeight = 44;
        let top, left;

        if (window.innerHeight - rect.bottom >= popHeight + 10) {
            top = rect.bottom + 6;
        } else {
            top = rect.top - popHeight - 6;
        }

        left = rect.left - (popWidth / 2) + (rect.width / 2);
        if (left < 10) left = 10;
        if (left + popWidth > window.innerWidth - 10) left = window.innerWidth - popWidth - 10;

        pop.style.top = top + 'px';
        pop.style.left = left + 'px';
        activeQuickBar = pop;
    }

    function showFullEmojiPicker(msgId, anchorBtn) {
        const rect = anchorBtn.getBoundingClientRect();
        closeAllEmojiPopovers();

        const picker = document.createElement('div');
        picker.className = 'fixed z-50 bg-white border border-slate-200 shadow-2xl rounded-2xl w-80 overflow-hidden flex flex-col animate-in fade-in zoom-in duration-150';
        
        const catIcons = {
            'Smileys': '😃',
            'Gestures': '👍',
            'Hearts': '❤️',
            'Objects': '💡',
            'Celebration': '🎉'
        };

        const categories = Object.keys(EMOJI_CATALOG);
        let activeCat = categories[0];

        let tabsHtml = categories.map(cat => `
            <button type="button" class="fp-cat-tab p-1.5 rounded-lg text-lg hover:bg-slate-100 transition-colors ${cat === activeCat ? 'bg-indigo-50 text-indigo-600' : 'text-slate-500'}" data-cat="${cat}" title="${cat}">
                ${catIcons[cat] || '😃'}
            </button>
        `).join('');

        picker.innerHTML = `
            <div class="p-2 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between gap-1" id="fp-tabs">
                ${tabsHtml}
            </div>
            <div class="p-2.5 border-b border-slate-100 relative">
                <input type="text" id="fp-search" placeholder="Search..." class="w-full border border-slate-200 rounded-xl pl-8 pr-8 py-1.5 text-xs text-slate-700 focus:ring-2 focus:ring-indigo-500 bg-white">
                <svg class="w-3.5 h-3.5 text-slate-400 absolute left-5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <span class="absolute right-5 top-1/2 -translate-y-1/2 text-xs pointer-events-none">👋</span>
            </div>
            <div class="px-3 py-1 text-[11px] font-semibold text-slate-500 bg-slate-50/40 border-b border-slate-100/60" id="fp-cat-heading">
                <span id="fp-cat-title">${activeCat}</span>
            </div>
            <div class="p-2.5 overflow-y-auto max-h-56 grid grid-cols-7 gap-1" id="fp-grid" style="display:grid !important; grid-template-columns:repeat(7, 1fr) !important; gap:4px !important;"></div>
            <div class="px-3 py-2 border-t border-slate-100 bg-slate-50 flex items-center gap-2.5 text-xs text-slate-600" id="fp-footer">
                <span class="text-xl flex-shrink-0" id="fp-footer-emoji">😃</span>
                <div class="min-w-0 flex-1">
                    <p class="font-medium text-slate-700 truncate" id="fp-footer-name">Grinning face with smiling eyes</p>
                    <p class="text-[10px] text-slate-400 truncate" id="fp-footer-code">:smile:</p>
                </div>
            </div>
        `;

        document.body.appendChild(picker);

        // Precise positioning calculation (Image 2 style: anchored beside/below comment)
        const pickerHeight = 360;
        const pickerWidth = 320;

        const isRightSide = rect.left > (window.innerWidth / 2);
        let top, left;

        // Prefer placing below action bar so comment above stays 100% visible
        if (window.innerHeight - rect.bottom >= pickerHeight + 10) {
            top = rect.bottom + 6;
            left = isRightSide 
                ? Math.max(10, rect.right - pickerWidth)
                : Math.min(window.innerWidth - pickerWidth - 10, rect.left);
        } else if (rect.top >= pickerHeight + 10) {
            top = rect.top - pickerHeight - 6;
            left = isRightSide 
                ? Math.max(10, rect.right - pickerWidth)
                : Math.min(window.innerWidth - pickerWidth - 10, rect.left);
        } else {
            // Position beside the comment bubble so comment text isn't covered
            top = Math.max(10, Math.min(window.innerHeight - pickerHeight - 10, rect.top));
            left = isRightSide 
                ? Math.max(10, rect.left - pickerWidth - 10)
                : Math.min(window.innerWidth - pickerWidth - 10, rect.right + 10);
        }

        picker.style.top = top + 'px';
        picker.style.left = left + 'px';
        activeFullPicker = picker;

        const gridEl = picker.querySelector('#fp-grid');
        const searchEl = picker.querySelector('#fp-search');
        const catTitle = picker.querySelector('#fp-cat-title');
        const footerEmoji = picker.querySelector('#fp-footer-emoji');
        const footerName = picker.querySelector('#fp-footer-name');
        const footerCode = picker.querySelector('#fp-footer-code');

        function renderPickerList(list) {
            gridEl.innerHTML = list.map(([em, name]) => `
                <button type="button" class="msg-react-btn p-1 rounded-lg text-lg hover:bg-indigo-50 hover:scale-125 transition-all text-center focus:outline-none flex items-center justify-center" data-msg-id="${msgId}" data-emoji="${em}" data-name="${escHtml(name)}">${em}</button>
            `).join('');

            gridEl.querySelectorAll('.msg-react-btn').forEach(b => {
                b.addEventListener('mouseenter', () => {
                    const em = b.dataset.emoji;
                    const name = b.dataset.name || 'Emoji';
                    footerEmoji.textContent = em;
                    footerName.textContent = name.charAt(0).toUpperCase() + name.slice(1);
                    footerCode.textContent = ':' + name.replace(/\s+/g, '_') + ':';
                });
            });
        }

        renderPickerList(EMOJI_CATALOG[activeCat] || []);

        picker.querySelectorAll('.fp-cat-tab').forEach(tb => {
            tb.addEventListener('click', (e) => {
                e.stopPropagation();
                picker.querySelectorAll('.fp-cat-tab').forEach(t => t.classList.remove('bg-indigo-50', 'text-indigo-600'));
                tb.classList.add('bg-indigo-50', 'text-indigo-600');
                activeCat = tb.dataset.cat;
                catTitle.textContent = activeCat;
                renderPickerList(EMOJI_CATALOG[activeCat] || []);
            });
        });

        searchEl.addEventListener('input', () => {
            const q = searchEl.value.trim().toLowerCase();
            if (!q) {
                catTitle.textContent = activeCat;
                renderPickerList(EMOJI_CATALOG[activeCat] || []);
                return;
            }
            catTitle.textContent = 'Search results';
            const filtered = [];
            Object.values(EMOJI_CATALOG).forEach(cat => {
                cat.forEach(([em, name]) => {
                    if (name.toLowerCase().includes(q)) filtered.push([em, name]);
                });
            });
            renderPickerList(filtered);
        });
    }

    // Event delegation: reply + react buttons are inside dynamically-updated
    // containers (poll-appended bubbles), so bind once on document.
    document.addEventListener('click', function(e) {
        // Close popovers if clicked outside
        if (activeQuickBar && !activeQuickBar.contains(e.target) && !e.target.closest('.msg-emoji-bar-toggle-btn')) {
            activeQuickBar.remove();
            activeQuickBar = null;
        }
        if (activeFullPicker && !activeFullPicker.contains(e.target) && !e.target.closest('.msg-emoji-bar-toggle-btn') && !e.target.closest('.open-full-picker-btn')) {
            activeFullPicker.remove();
            activeFullPicker = null;
        }

        const toggleBtn = e.target.closest('.msg-emoji-bar-toggle-btn');
        if (toggleBtn) {
            e.preventDefault();
            e.stopPropagation();
            const msgId = toggleBtn.dataset.msgId;
            showQuickReactionPopover(msgId, toggleBtn);
            return;
        }

        const moreBtn = e.target.closest('.open-full-picker-btn');
        if (moreBtn) {
            e.preventDefault();
            e.stopPropagation();
            const msgId = moreBtn.dataset.msgId;
            showFullEmojiPicker(msgId, moreBtn);
            return;
        }

        const editBtn = e.target.closest('.msg-edit-btn');
        if (editBtn) {
            e.preventDefault();
            const msgId = editBtn.dataset.id;
            const sender = editBtn.dataset.sender;
            const msgText = editBtn.dataset.message || '';
            showEditPreview(msgId, sender);
            if (quill) {
                quill.setContents([]);
                if (quill.clipboard && typeof quill.clipboard.dangerouslyPasteHTML === 'function') {
                    quill.clipboard.dangerouslyPasteHTML(0, msgText);
                } else {
                    quill.root.innerHTML = msgText;
                }
            }
            editorActions?.style.removeProperty('display');
            document.getElementById('quill-body')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            if (quill) quill.focus();
            return;
        }

        const replyBtn = e.target.closest('.msg-reply-btn');
        if (replyBtn) {
            showReplyPreview(replyBtn.dataset.id, replyBtn.dataset.sender, replyBtn.dataset.preview);
            // Switch to the "All" tab (or whichever is active) and focus the editor.
            editorActions?.style.removeProperty('display');
            document.getElementById('quill-body')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            if (quill) quill.focus();
            return;
        }

        const reactBtn = e.target.closest('.msg-react-btn');
        if (reactBtn) {
            e.preventDefault();
            const msgId = reactBtn.dataset.msgId;
            const emoji = reactBtn.dataset.emoji;
            if (!msgId || !emoji) return;

            closeAllEmojiPopovers();

            fetch(`{{ url('tickets/' . $ticket->id . '/messages') }}/${msgId}/react`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ emoji: emoji })
            })
            .then(r => r.json())
            .then(data => {
                if (data.reactions) {
                    updateReactionsUI(msgId, data.reactions);
                }
            })
            .catch(() => {});
            return;
        }
    });

    // ── Notification polling ─────────────────────────────────────────────────
    let lastNotifCount = null;
    function pollNotifications() {
        fetch('{{ route("notifications.count") }}', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            const count = data.count;
            if (lastNotifCount !== null && count > lastNotifCount) {
                pushDesktop(
                    'VISION Technologies — New Notification',
                    `You have ${count} unread notification${count !== 1 ? 's' : ''}.`,
                    '{{ route("notifications.index") }}'
                );
            }
            lastNotifCount = count;
        })
        .catch(() => {});
    }
    setInterval(pollNotifications, 15000);
    pollNotifications();

    </script>
</x-app-layout>
