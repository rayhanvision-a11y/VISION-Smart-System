<x-app-layout>
    <div x-data="{
        showCreateModal: false,
        showEditModal: false,
        editId: null,
        editForm: { label: '', label_bn: '', icon: '👷', color: '#64748b', badge: '', sort_order: 100, is_active: true },
        openEdit(c) {
            this.editId = c.id;
            this.editForm = {
                label: c.label || '',
                label_bn: c.label_bn || '',
                icon: c.icon || '👷',
                color: c.color || '#64748b',
                badge: c.badge || '',
                sort_order: c.sort_order ?? 100,
                is_active: !!c.is_active,
            };
            this.showEditModal = true;
        }
    }">
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">🏷️ {{ __('Squad Categories') }}</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Categories available when creating a Daily Technician Team (Complain, New Connection, etc).') }}</p>
            </div>
            <button @click="showCreateModal = true"
                    class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition-colors shadow-sm cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('Add Category') }}
            </button>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 p-4 rounded-xl bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 text-sm font-medium">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 text-xs font-semibold uppercase tracking-wider">
                        <th class="py-3.5 px-5">{{ __('Category') }}</th>
                        <th class="py-3.5 px-5">{{ __('Key') }}</th>
                        <th class="py-3.5 px-5">{{ __('Sort') }}</th>
                        <th class="py-3.5 px-5">{{ __('Status') }}</th>
                        <th class="py-3.5 px-5 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm text-slate-700 dark:text-slate-200">
                    @forelse($categories as $c)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="py-4 px-5 font-semibold text-slate-900 dark:text-white">
                                <span class="inline-flex items-center gap-2">
                                    <span class="text-xl">{{ $c->icon ?: '👷' }}</span>
                                    <span>{{ $c->label }}</span>
                                    @if($c->label_bn)
                                        <span class="text-xs text-slate-400 dark:text-slate-500 font-normal">· {{ $c->label_bn }}</span>
                                    @endif
                                </span>
                            </td>
                            <td class="py-4 px-5 text-xs text-slate-500 dark:text-slate-400 font-mono">{{ $c->key }}</td>
                            <td class="py-4 px-5 text-xs">{{ $c->sort_order }}</td>
                            <td class="py-4 px-5">
                                @if($c->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300">{{ __('Active') }}</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">{{ __('Inactive') }}</span>
                                @endif
                            </td>
                            <td class="py-4 px-5 text-right space-x-2 whitespace-nowrap">
                                <button @click="openEdit({{ json_encode($c) }})"
                                        class="inline-flex items-center gap-1 text-xs font-semibold px-3 py-1.5 rounded-lg text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 cursor-pointer">
                                    {{ __('Edit') }}
                                </button>
                                <form method="POST" action="{{ route('squad-categories.destroy', $c) }}" class="inline-block" onsubmit="return confirm('{{ __('Delete this category? Teams already using it will keep working but the category will not be selectable.') }}');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-1 text-xs font-semibold px-3 py-1.5 rounded-lg text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/50 cursor-pointer">
                                        {{ __('Delete') }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-slate-500 dark:text-slate-400">{{ __('No categories yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Create Modal --}}
        <div x-show="showCreateModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full border border-slate-200 dark:border-slate-800 shadow-2xl p-6" @click.away="showCreateModal = false">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 mb-4">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">{{ __('Add Squad Category') }}</h3>
                    <button @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">✕</button>
                </div>
                <form method="POST" action="{{ route('squad-categories.store') }}" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div class="col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Label (English)') }} <span class="text-red-600 dark:text-red-400">*</span></label>
                            <input type="text" name="label" required placeholder="{{ __('e.g. Maintenance Team') }}"
                                   class="w-full border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Label (Bengali)') }}</label>
                            <input type="text" name="label_bn" placeholder="{{ __('e.g. রক্ষণাবেক্ষণ টিম') }}"
                                   class="w-full border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Icon (emoji)') }}</label>
                            <input type="text" name="icon" value="👷" maxlength="4" class="w-full border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Sort Order') }}</label>
                            <input type="number" name="sort_order" value="100" min="0" max="9999" class="w-full border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Color') }}</label>
                            <input type="color" name="color" value="#64748b" class="w-full h-10 border border-slate-200 dark:border-slate-700 rounded-xl px-2 py-1 bg-slate-50 dark:bg-slate-800">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Key (optional)') }}</label>
                            <input type="text" name="key" placeholder="{{ __('auto from label') }}" class="w-full border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 font-mono">
                        </div>
                        <div class="col-span-2 flex items-center gap-2">
                            <input type="checkbox" name="is_active" id="create_is_active" value="1" checked class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <label for="create_is_active" class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ __('Active') }}</label>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="showCreateModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">{{ __('Cancel') }}</button>
                        <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm">{{ __('Save') }}</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Edit Modal --}}
        <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full border border-slate-200 dark:border-slate-800 shadow-2xl p-6" @click.away="showEditModal = false">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 mb-4">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">{{ __('Edit Squad Category') }}</h3>
                    <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">✕</button>
                </div>
                <form :action="'/squad-categories/' + editId" method="POST" class="space-y-3">
                    @csrf @method('PUT')
                    <div class="grid grid-cols-2 gap-3">
                        <div class="col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Label (English)') }} <span class="text-red-600 dark:text-red-400">*</span></label>
                            <input type="text" name="label" x-model="editForm.label" required class="w-full border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500">
                            @error('label')<p class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Label (Bengali)') }}</label>
                            <input type="text" name="label_bn" x-model="editForm.label_bn" class="w-full border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500">
                            @error('label_bn')<p class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Icon (emoji)') }}</label>
                            <input type="text" name="icon" x-model="editForm.icon" maxlength="4" class="w-full border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500">
                            @error('icon')<p class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Sort Order') }}</label>
                            <input type="number" name="sort_order" x-model="editForm.sort_order" min="0" max="9999" class="w-full border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500">
                            @error('sort_order')<p class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Color') }}</label>
                            <input type="color" name="color" x-model="editForm.color" class="w-full h-10 border border-slate-200 dark:border-slate-700 rounded-xl px-2 py-1 bg-slate-50 dark:bg-slate-800">
                            @error('color')<p class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="col-span-2 flex items-center gap-2">
                            <input type="checkbox" name="is_active" id="edit_is_active_cat" value="1" x-model="editForm.is_active" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <label for="edit_is_active_cat" class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ __('Active') }}</label>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="showEditModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">{{ __('Cancel') }}</button>
                        <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm">{{ __('Update') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
