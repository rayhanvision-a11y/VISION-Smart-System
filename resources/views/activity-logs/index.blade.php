<x-app-layout>
<div class="max-w-7xl mx-auto px-4">

    <div class="flex items-center justify-between mb-5">
        <div>
            <h1 class="text-lg font-bold text-slate-800 dark:text-slate-100">{{ __('Activity / Audit Log') }}</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Track all actions performed across the system.') }}</p>
        </div>
        @if(auth()->user()->isSuperAdminOnly())
        <form method="POST" action="{{ route('activity-logs.clear') }}" onsubmit="return confirm('{{ __('Delete logs older than 90 days?') }}')">
            @csrf @method('DELETE')
            <button class="text-xs text-red-500 hover:text-red-700 border border-red-200 hover:border-red-400 px-3 py-1.5 rounded-lg transition-all">
                🗑 {{ __('Clear Old Logs (90d+)') }}
            </button>
        </form>
        @endif
    </div>

    @if(session('status'))
    <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm">{{ session('status') }}</div>
    @endif

    {{-- Filters --}}
    <form method="GET" class="flex flex-wrap gap-3 mb-5">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search description...') }}"
            class="px-3 py-2 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 w-56">

        <select name="action" class="px-3 py-2 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
            <option value="">{{ __('All Actions') }}</option>
            @foreach(['created','updated','deleted','replied','logged_in','setting_changed','broadcast_sent'] as $act)
            <option value="{{ $act }}" {{ request('action') === $act ? 'selected' : '' }}>{{ ucfirst($act) }}</option>
            @endforeach
        </select>

        <select name="user_id" class="px-3 py-2 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
            <option value="">{{ __('All Users') }}</option>
            @foreach($users as $u)
            <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
            @endforeach
        </select>

        <button class="px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-xl hover:bg-indigo-700 transition-all">{{ __('Filter') }}</button>
        @if(request()->hasAny(['search','action','user_id']))
        <a href="{{ route('activity-logs.index') }}" class="px-4 py-2 text-sm text-slate-500 border border-slate-200 dark:border-slate-700 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">{{ __('Clear') }}</a>
        @endif
    </form>

    {{-- Table --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">{{ __('Time') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">{{ __('User') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">{{ __('Action') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">{{ __('Description') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">{{ __('IP') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($logs as $log)
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                    <td class="px-4 py-3 text-xs text-slate-500 dark:text-slate-400 whitespace-nowrap">
                        <span title="{{ $log->created_at }}">{{ $log->created_at->diffForHumans() }}</span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        @if($log->user)
                        <span class="font-semibold text-slate-700 dark:text-slate-200 text-xs">{{ $log->user->name }}</span>
                        @else
                        <span class="text-slate-400 text-xs italic">{{ __('System') }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        @php
                        $colors = [
                            'created' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
                            'updated' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
                            'deleted' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
                            'replied' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300',
                            'logged_in' => 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300',
                            'setting_changed' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
                            'broadcast_sent' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300',
                        ];
                        $cls = $colors[$log->action] ?? 'bg-slate-100 text-slate-600';
                        @endphp
                        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-bold {{ $cls }}">{{ $log->action }}</span>
                    </td>
                    <td class="px-4 py-3 text-slate-700 dark:text-slate-200 text-xs max-w-md">
                        {{ $log->description }}
                        @if($log->properties)
                        <details class="mt-1">
                            <summary class="text-xs text-slate-400 cursor-pointer hover:text-indigo-600 select-none">{{ __('Details') }}</summary>
                            <pre class="mt-1 text-xs bg-slate-50 dark:bg-slate-800 rounded p-2 overflow-x-auto text-slate-600 dark:text-slate-300">{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </details>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-400 font-mono whitespace-nowrap">{{ $log->ip_address }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-12 text-center text-slate-400 text-sm">{{ __('No activity logs found.') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $logs->links() }}</div>
</div>
</x-app-layout>
