<x-app-layout>
    <x-slot name="pageTitle">
        {{ __('Custom Menu Links') }}
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-6">

        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <div>
                <h2 class="text-xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                    <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                    {{ __('Custom Navigation Links') }}
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    {{ __('Add custom links (e.g. Billing, App, External Portals) to the main menu. Super Admins can toggle Important status and links will open in a new tab.') }}
                </p>
            </div>

            <button onclick="document.getElementById('addLinkModal').classList.remove('hidden')"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-xl shadow-md transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('Add New Link') }}
            </button>
        </div>

        {{-- Links Table --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider">
                    {{ __('Active & Custom Links') }} ({{ $links->count() }})
                </h3>
            </div>

            @if($links->isEmpty())
            <div class="p-12 text-center">
                <div class="w-12 h-12 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-500 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                </div>
                <h4 class="text-base font-semibold text-slate-700 dark:text-slate-200">{{ __('No menu links added yet') }}</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                    {{ __('Click "Add New Link" above to add your Billing, App, or custom internal links to the top bar and sidebar menu.') }}
                </p>
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-slate-100 dark:border-slate-800">
                        <tr>
                            <th class="px-6 py-3.5">{{ __('Name') }}</th>
                            <th class="px-6 py-3.5">{{ __('URL') }}</th>
                            <th class="px-6 py-3.5 text-center">{{ __('Important Flag') }}</th>
                            <th class="px-6 py-3.5 text-center">{{ __('Status') }}</th>
                            <th class="px-6 py-3.5 text-center">{{ __('Open Mode') }}</th>
                            <th class="px-6 py-3.5 text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($links as $link)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="px-6 py-4 font-semibold text-slate-800 dark:text-slate-100">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center {{ $link->is_important ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">
                                        {!! $link->svgIcon('w-4 h-4') !!}
                                    </div>
                                    <div>
                                        <span class="text-sm font-semibold">{{ $link->name }}</span>
                                        @if($link->is_important)
                                            <span class="ml-2 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-600 dark:text-amber-400 border border-amber-300 dark:border-amber-700/50">
                                                🔥 {{ __('IMPORTANT') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ $link->formatted_url }}" target="_blank" rel="noopener noreferrer" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1 font-mono">
                                    {{ Str::limit($link->url, 40) }}
                                    <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                </a>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <form method="POST" action="{{ route('custom-menu-links.toggle-important', $link) }}" class="inline-block">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold transition-all shadow-sm
                                                   {{ $link->is_important ? 'bg-amber-500 text-white hover:bg-amber-600' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300' }}">
                                        <svg class="w-3.5 h-3.5" fill="{{ $link->is_important ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                        </svg>
                                        {{ $link->is_important ? __('Important ON') : __('Normal') }}
                                    </button>
                                </form>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <form method="POST" action="{{ route('custom-menu-links.toggle-active', $link) }}" class="inline-block">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold transition-all shadow-sm
                                                   {{ $link->is_active ? 'bg-emerald-500 text-white hover:bg-emerald-600' : 'bg-slate-200 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">
                                        <span class="w-2 h-2 rounded-full {{ $link->is_active ? 'bg-white' : 'bg-slate-400' }}"></span>
                                        {{ $link->is_active ? __('Active') : __('Disabled') }}
                                    </button>
                                </form>
                            </td>
                            <td class="px-6 py-4 text-center text-xs">
                                <span class="px-2.5 py-1 rounded-md bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 font-mono">
                                    target="_blank" (New Tab)
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button onclick="editLinkModal({{ json_encode($link) }})"
                                            class="p-1.5 text-slate-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <form method="POST" action="{{ route('custom-menu-links.destroy', $link) }}" onsubmit="return confirm('{{ __('Are you sure you want to delete this custom link?') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-slate-500 hover:text-red-600 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

    {{-- Add Link Modal --}}
    <div id="addLinkModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('Add Custom Menu Link') }}
                </h3>
                <button onclick="document.getElementById('addLinkModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('custom-menu-links.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-1">{{ __('Link Name') }} *</label>
                    <input type="text" name="name" required placeholder="e.g. Billing, Customer App, Portal"
                           class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-1">{{ __('Target URL') }} *</label>
                    <input type="text" name="url" required placeholder="https://billing.yourdomain.com"
                           class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                    <p class="text-[11px] text-slate-400 mt-1">{{ __('Full URL where user will be redirected in a new tab.') }}</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-1">{{ __('Select Icon') }}</label>
                    <select name="icon" class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                        <option value="link">🔗 Link (Default)</option>
                        <option value="credit-card">💳 Credit Card (Billing / Payment)</option>
                        <option value="app">📱 Mobile App / Device</option>
                        <option value="globe">🌐 Globe / Website / Portal</option>
                        <option value="server">🖥️ Server / Database / System</option>
                        <option value="chart">📊 Chart / Reports</option>
                        <option value="cog">⚙️ Settings / Admin</option>
                        <option value="shield">🛡️ Security / VPN</option>
                    </select>
                </div>

                <div class="flex items-center justify-between p-3 bg-amber-50/70 dark:bg-amber-900/20 rounded-xl border border-amber-200 dark:border-amber-800/50">
                    <div>
                        <span class="text-xs font-bold text-amber-800 dark:text-amber-300 block">🔥 {{ __('Important Link Toggle') }}</span>
                        <span class="text-[11px] text-amber-600 dark:text-amber-400 block">{{ __('Highlight this link with a special badge in menu.') }}</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_important" value="1" class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                    </label>
                </div>

                <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700">
                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ __('Open in New Tab') }}</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="open_in_new_tab" value="1" checked class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                    </label>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" onclick="document.getElementById('addLinkModal').classList.add('hidden')"
                            class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl">
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-xl shadow">
                        {{ __('Save Link') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Link Modal --}}
    <div id="editLinkModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    {{ __('Edit Custom Menu Link') }}
                </h3>
                <button onclick="document.getElementById('editLinkModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form id="editLinkForm" method="POST" action="" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-1">{{ __('Link Name') }} *</label>
                    <input type="text" id="edit_name" name="name" required
                           class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-1">{{ __('Target URL') }} *</label>
                    <input type="text" id="edit_url" name="url" required
                           class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-1">{{ __('Select Icon') }}</label>
                    <select id="edit_icon" name="icon" class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                        <option value="link">🔗 Link (Default)</option>
                        <option value="credit-card">💳 Credit Card (Billing / Payment)</option>
                        <option value="app">📱 Mobile App / Device</option>
                        <option value="globe">🌐 Globe / Website / Portal</option>
                        <option value="server">🖥️ Server / Database / System</option>
                        <option value="chart">📊 Chart / Reports</option>
                        <option value="cog">⚙️ Settings / Admin</option>
                        <option value="shield">🛡️ Security / VPN</option>
                    </select>
                </div>

                <div class="flex items-center justify-between p-3 bg-amber-50/70 dark:bg-amber-900/20 rounded-xl border border-amber-200 dark:border-amber-800/50">
                    <div>
                        <span class="text-xs font-bold text-amber-800 dark:text-amber-300 block">🔥 {{ __('Important Link Toggle') }}</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="edit_is_important" name="is_important" value="1" class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                    </label>
                </div>

                <div class="flex items-center justify-between p-3 bg-emerald-50/70 dark:bg-emerald-900/20 rounded-xl border border-emerald-200 dark:border-emerald-800/50">
                    <div>
                        <span class="text-xs font-bold text-emerald-800 dark:text-emerald-300 block">{{ __('Active Status Toggle') }}</span>
                        <span class="text-[11px] text-emerald-600 dark:text-emerald-400 block">{{ __('Show this link in the Important URL menu.') }}</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="edit_is_active" name="is_active" value="1" class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                    </label>
                </div>

                <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700">
                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ __('Open in New Tab') }}</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="edit_open_in_new_tab" name="open_in_new_tab" value="1" class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                    </label>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" onclick="document.getElementById('editLinkModal').classList.add('hidden')"
                            class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl">
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-xl shadow">
                        {{ __('Update Link') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editLinkModal(link) {
            document.getElementById('editLinkForm').action = '/custom-menu-links/' + link.id;
            document.getElementById('edit_name').value = link.name;
            document.getElementById('edit_url').value = link.url;
            document.getElementById('edit_icon').value = link.icon || 'link';
            document.getElementById('edit_is_important').checked = !!link.is_important;
            document.getElementById('edit_is_active').checked = !!link.is_active;
            document.getElementById('edit_open_in_new_tab').checked = !!link.open_in_new_tab;
            document.getElementById('editLinkModal').classList.remove('hidden');
        }
    </script>
</x-app-layout>
