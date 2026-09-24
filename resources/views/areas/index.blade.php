<x-app-layout>
    <div x-data="{
        showCreateModal: false,
        showEditModal: false,
        editId: null,
        editName: '',
        editActive: true,
        openEdit(a) {
            this.editId = a.id;
            this.editName = a.name;
            this.editActive = a.is_active;
            this.showEditModal = true;
        }
    }">
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">📍 {{ __('Ticket Areas') }}</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Manage area/zone list for ticket assignment (e.g. Shadhupara, Gopalpur).') }}</p>
            </div>
            <button @click="showCreateModal = true"
                    class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition-colors shadow-sm cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('Add Area') }}
            </button>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 text-xs font-semibold uppercase tracking-wider">
                        <th class="py-3.5 px-5">{{ __('Area Name') }}</th>
                        <th class="py-3.5 px-5">{{ __('Ticket Count') }}</th>
                        <th class="py-3.5 px-5">{{ __('Status') }}</th>
                        <th class="py-3.5 px-5 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm text-slate-700 dark:text-slate-200">
                    @forelse($areas as $a)
                        @php $count = \App\Models\Ticket::where('area', $a->name)->count(); @endphp
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="py-4 px-5 font-semibold text-slate-900 dark:text-white">
                                <span class="inline-flex items-center gap-2">
                                    <span class="text-lg">📍</span>{{ $a->name }}
                                </span>
                            </td>
                            <td class="py-4 px-5">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 dark:bg-indigo-950/60 text-indigo-800 dark:text-indigo-300">
                                    {{ $count }} {{ __('tickets') }}
                                </span>
                            </td>
                            <td class="py-4 px-5">
                                @if($a->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300">{{ __('Active') }}</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">{{ __('Inactive') }}</span>
                                @endif
                            </td>
                            <td class="py-4 px-5 text-right space-x-2 whitespace-nowrap">
                                <button @click="openEdit({{ json_encode($a) }})"
                                        class="inline-flex items-center gap-1 text-xs font-semibold px-3 py-1.5 rounded-lg text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 cursor-pointer">
                                    {{ __('Edit') }}
                                </button>
                                <form method="POST" action="{{ route('areas.destroy', $a) }}" class="inline-block" onsubmit="return confirm('{{ __('Delete this area?') }}');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-1 text-xs font-semibold px-3 py-1.5 rounded-lg text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/50 cursor-pointer">
                                        {{ __('Delete') }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-slate-500 dark:text-slate-400">
                                {{ __('No areas yet. Click "Add Area" to create one.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Create Modal --}}
        <div x-show="showCreateModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full border border-slate-200 dark:border-slate-800 shadow-2xl p-6" @click.away="showCreateModal = false">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 mb-4">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">{{ __('Add New Area') }}</h3>
                    <button @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">✕</button>
                </div>
                <form method="POST" action="{{ route('areas.store') }}">
                    @csrf
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Area Name') }} <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required placeholder="{{ __('e.g. Shadhupara, Gopalpur, Power House Para') }}"
                           class="w-full border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="showCreateModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">{{ __('Cancel') }}</button>
                        <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm">{{ __('Save') }}</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Edit Modal --}}
        <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full border border-slate-200 dark:border-slate-800 shadow-2xl p-6" @click.away="showEditModal = false">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 mb-4">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">{{ __('Edit Area') }}</h3>
                    <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">✕</button>
                </div>
                <form :action="'/areas/' + editId" method="POST">
                    @csrf @method('PUT')
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Area Name') }} <span class="text-red-500">*</span></label>
                    <input type="text" name="name" x-model="editName" required
                           class="w-full border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    <div class="flex items-center gap-2 mt-3">
                        <input type="checkbox" name="is_active" id="edit_is_active_area" value="1" x-model="editActive" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <label for="edit_is_active_area" class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ __('Active') }}</label>
                    </div>
                    <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="showEditModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">{{ __('Cancel') }}</button>
                        <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm">{{ __('Update') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
