<x-app-layout>
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('pop-offices.index') }}" class="text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">{{ $popOffice->name }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Pending tickets for this office') }}</p>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">{{ __('Ticket') }}</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">{{ __('Title') }}</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">{{ __('Priority') }}</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">{{ __('Status') }}</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">{{ __('Assignee') }}</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">{{ __('Created') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($tickets as $ticket)
                    @php
                        $pColors = [
                            'critical' => 'bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-red-300',
                            'high'     => 'bg-orange-100 text-orange-700 dark:bg-orange-950/60 dark:text-orange-300',
                            'medium'   => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-950/60 dark:text-yellow-300',
                            'low'      => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300'
                        ];
                        $sColors = [
                            'open'        => 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300',
                            'in_progress' => 'bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300',
                            'reopened'    => 'bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300'
                        ];
                    @endphp
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-5 py-3 font-mono text-xs font-bold text-indigo-600 dark:text-indigo-400">{{ $ticket->ticket_key ?? '#'.$ticket->id }}</td>
                        <td class="px-5 py-3 max-w-xs">
                            <a href="{{ route('tickets.show', $ticket) }}" class="font-medium text-slate-800 dark:text-slate-100 hover:text-indigo-600 dark:hover:text-indigo-400 truncate block">{{ $ticket->title }}</a>
                        </td>
                        <td class="px-5 py-3"><span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $pColors[$ticket->priority] ?? 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }}">{{ __(ucfirst($ticket->priority)) }}</span></td>
                        <td class="px-5 py-3"><span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $sColors[$ticket->status] ?? 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }}">{{ __(ucfirst(str_replace('_',' ',$ticket->status))) }}</span></td>
                        <td class="px-5 py-3">
                            @if($ticket->assignee)
                            <div class="flex items-center gap-2">
                                <img src="{{ $ticket->assignee->avatarUrl() }}" class="w-6 h-6 rounded-full object-cover">
                                <span class="text-xs text-slate-600 dark:text-slate-300">{{ $ticket->assignee->name }}</span>
                            </div>
                            @else<span class="text-slate-400 dark:text-slate-500 text-xs">{{ __('Unassigned') }}</span>@endif
                        </td>
                        <td class="px-5 py-3 text-xs text-slate-400 dark:text-slate-500">{{ $ticket->created_at->diffForHumans() }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-5 py-10 text-center text-slate-400 dark:text-slate-500 text-sm">{{ __('No pending tickets for this office.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($tickets->hasPages())
        <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-800">{{ $tickets->links() }}</div>
        @endif
    </div>
</x-app-layout>
