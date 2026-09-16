<x-app-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Search Results</h1>
        <p class="text-sm text-slate-500 mt-0.5">{{ $results->count() }} result(s) for "{{ $q }}"</p>
    </div>

    <form method="GET" action="{{ route('search') }}" class="mb-6">
        <div class="flex gap-3">
            <input type="text" name="q" value="{{ $q }}" placeholder="Search tickets..." autofocus
                   class="flex-1 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 bg-white">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold">Search</button>
        </div>
    </form>

    @if($results->isEmpty())
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-10 text-center">
        <p class="text-slate-500">No tickets found matching "{{ $q }}"</p>
    </div>
    @else
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm divide-y divide-slate-100">
        @foreach($results as $ticket)
        @php
            $sColor = match($ticket->status) { 'open' => 'bg-amber-100 text-amber-700', 'in_progress' => 'bg-blue-100 text-blue-700', 'pending' => 'bg-orange-100 text-orange-700', 'waiting_for_customer_feedback' => 'bg-violet-100 text-violet-700', 'resolved' => 'bg-emerald-100 text-emerald-700', 'closed' => 'bg-slate-100 text-slate-600', default => 'bg-slate-100 text-slate-600' };
        @endphp
        <a href="{{ route('tickets.show', $ticket) }}" class="flex items-start justify-between p-4 hover:bg-slate-50 transition-colors group">
            <div class="flex-1 min-w-0 mr-4">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs font-mono text-slate-400">#{{ $ticket->id }}</span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold {{ $sColor }}">{{ ucfirst(str_replace('_',' ',$ticket->status)) }}</span>
                    @foreach($ticket->labels as $label)<span class="inline-flex px-1.5 py-0.5 rounded text-xs font-medium text-white" style="background-color: {{ $label->color }}">{{ $label->name }}</span>@endforeach
                </div>
                <p class="font-medium text-slate-800 group-hover:text-indigo-600 truncate">{{ $ticket->title }}</p>
                <p class="text-xs text-slate-400 mt-1">By {{ $ticket->creator->name ?? 'N/A' }} &bull; {{ $ticket->created_at->format('d M Y') }}</p>
            </div>
            <span class="text-xs text-indigo-600 font-medium flex-shrink-0 mt-1">View &rarr;</span>
        </a>
        @endforeach
    </div>
    @endif
</x-app-layout>
