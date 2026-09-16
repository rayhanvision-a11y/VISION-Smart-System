<x-app-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">{{ __('Labels') }}</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Manage ticket labels/tags.') }}</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800">
                            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">{{ __('Color') }}</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">{{ __('Name') }}</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">{{ __('Tickets') }}</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($labels as $label)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="px-5 py-3">
                                <span class="inline-block w-6 h-6 rounded-full border border-slate-200 dark:border-slate-700 shadow-2xs" style="background-color: {{ $label->color }}"></span>
                            </td>
                            <td class="px-5 py-3">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold text-white shadow-2xs" style="background-color: {{ $label->color }}">{{ $label->name }}</span>
                            </td>
                            <td class="px-5 py-3 text-slate-500 dark:text-slate-400">{{ $label->tickets_count }}</td>
                            <td class="px-5 py-3 text-right">
                                <form method="POST" action="{{ route('labels.destroy', $label) }}" onsubmit="return confirm('{{ __('Delete label?') }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs text-red-600 dark:text-red-400 hover:underline font-medium">{{ __('Delete') }}</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-5">
                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4">{{ __('Create Label') }}</h3>
                <form method="POST" action="{{ route('labels.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1">{{ __('Name') }}</label>
                        <input type="text" name="name" value="{{ old('name') }}" required maxlength="50" class="w-full border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                        @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1">{{ __('Color') }}</label>
                        <input type="color" name="color" value="{{ old('color', '#6366f1') }}" class="w-full h-10 border border-slate-200 dark:border-slate-700 rounded-lg cursor-pointer bg-slate-50 dark:bg-slate-800">
                    </div>
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors shadow-sm">{{ __('Create') }}</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
