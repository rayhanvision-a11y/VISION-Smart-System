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

                <div class="mb-6">
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">{{ __('Assign to NOC') }}</label>
                    <select name="assigned_to" class="w-full border border-slate-200 dark:border-slate-700 rounded-lg px-3.5 py-2.5 text-sm text-slate-700 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800">
                        <option value="">{{ __('Unassigned') }}</option>
                        @foreach($nocUsers as $noc)
                        <option value="{{ $noc->id }}" {{ $ticket->assigned_to == $noc->id ? 'selected' : '' }}>{{ $noc->name }}</option>
                        @endforeach
                    </select>
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
