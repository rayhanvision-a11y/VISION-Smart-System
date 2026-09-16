<x-app-layout>
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('tickets.show', $ticket) }}" class="text-slate-400 hover:text-slate-600 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-800">{{ __('Merge Ticket') }} <span class="text-slate-400 font-normal">#{{ $ticket->id }}</span></h1>
            <p class="text-sm text-slate-500 mt-0.5 truncate max-w-md">{{ $ticket->title }}</p>
        </div>
    </div>

    <div class="max-w-lg">
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-5 text-xs text-amber-700">
            {{ __('All messages, notes and attachments from this ticket will be moved into the ticket you select below. This ticket will then be closed and marked as merged.') }}
        </div>

        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
            <form method="POST" action="{{ route('tickets.merge', $ticket) }}">
                @csrf
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">{{ __('Merge into') }}</label>
                <select name="target_id" required class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-700 focus:ring-2 focus:ring-indigo-500 bg-slate-50 @error('target_id') border-red-400 @enderror">
                    <option value="">{{ __('Select target ticket...') }}</option>
                    @foreach($candidates as $c)
                    <option value="{{ $c->id }}">{{ $c->ticket_key ?? '#'.$c->id }} — {{ \Illuminate\Support\Str::limit($c->title, 60) }} ({{ ucfirst(str_replace('_',' ',$c->status)) }})</option>
                    @endforeach
                </select>
                @error('target_id')<p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>@enderror

                <div class="flex gap-3 pt-5 mt-2 border-t border-slate-100">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition-colors">
                        {{ __('Merge Tickets') }}
                    </button>
                    <a href="{{ route('tickets.show', $ticket) }}" class="px-5 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:text-slate-800 hover:bg-slate-100 transition-colors">
                        {{ __('Cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
