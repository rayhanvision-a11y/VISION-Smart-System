<x-app-layout>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">{{ __('User Management') }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Manage admin, NOC, and reseller accounts.') }}</p>
        </div>
        <a href="{{ route('users.create') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition-colors shadow-sm">+ {{ __('New User') }}</a>
    </div>
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">{{ __('User') }}</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">{{ __('Role') }}</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">{{ __('Status') }}</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">{{ __('Created') }}</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">{{ __('Resolved') }}</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">{{ __('Phone') }}</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($users as $u)
                    @php
                        $roleColor = match($u->role) {
                            'super_admin'       => 'bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 dark:border dark:border-purple-800/50',
                            'admin'             => 'bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-red-300 dark:border dark:border-red-800/50',
                            'noc'               => 'bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 dark:border dark:border-blue-800/50',
                            'senior_supervisor' => 'bg-cyan-100 text-cyan-700 dark:bg-cyan-950/60 dark:text-cyan-300 dark:border dark:border-cyan-800/50',
                            'supervisor'        => 'bg-sky-100 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300 dark:border dark:border-sky-800/50',
                            'call_center'       => 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 dark:border dark:border-amber-800/50',
                            'technician'        => 'bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 dark:border dark:border-purple-800/50',
                            default             => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border dark:border-emerald-800/50',
                        };
                    @endphp
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <img src="{{ $u->avatarUrl() }}" alt="{{ $u->name }}" class="w-10 h-10 rounded-full object-cover flex-shrink-0 border border-slate-200 dark:border-slate-700">
                                <div>
                                    <div class="font-bold text-slate-800 dark:text-slate-100 text-sm leading-snug">{{ $u->name }}</div>
                                    <div class="text-xs text-slate-400 dark:text-slate-500 font-normal">{{ $u->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3.5"><span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $roleColor }}">{{ strtoupper(str_replace('_', ' ', $u->role)) }}</span></td>
                        <td class="px-5 py-3.5"><span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $u->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-red-300' }}">{{ $u->is_active ? __('Active') : __('Inactive') }}</span></td>
                        <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400">{{ $u->created_count }}</td>
                        <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400">{{ $u->resolved_count }}</td>
                        <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400 text-xs">{{ $u->phone ?? '—' }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2 justify-end">
                                <a href="{{ route('users.edit', $u) }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline font-medium">{{ __('Edit') }}</a>
                                <form method="POST" action="{{ route('users.reset-password', $u) }}" onsubmit="return confirm('{{ __('Reset password for :name?', ['name' => $u->name]) }}')">
                                    @csrf
                                    <button type="submit" class="text-xs text-amber-600 dark:text-amber-400 hover:underline font-medium">{{ __('Reset Pwd') }}</button>
                                </form>
                                @if(auth()->user()->isSuperAdmin() && $u->id !== auth()->id())
                                <form method="POST" action="{{ route('users.destroy', $u) }}" onsubmit="return confirm('{{ __('Permanently delete :name? This cannot be undone.', ['name' => $u->name]) }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-red-600 dark:text-red-400 hover:underline font-medium">{{ __('Delete') }}</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-800">{{ $users->links() }}</div>
    </div>
</x-app-layout>
