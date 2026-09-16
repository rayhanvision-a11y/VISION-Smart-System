<x-app-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">{{ __('Canned Responses') }}</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Reusable reply templates for common issues.') }}</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($responses as $r)
                <div class="p-4" x-data="{ editing: false }">
                    <div x-show="!editing">
                        <div class="flex items-center justify-between mb-1">
                            <p class="font-semibold text-sm text-slate-800 dark:text-slate-100">{{ $r->title }}</p>
                            <div class="flex items-center gap-3 flex-shrink-0">
                                <button @click="editing = true" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline font-medium">{{ __('Edit') }}</button>
                                <form method="POST" action="{{ route('canned-responses.destroy', $r) }}" onsubmit="return confirm('{{ __('Delete this canned response?') }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs text-red-600 dark:text-red-400 hover:underline font-medium">{{ __('Delete') }}</button>
                                </form>
                            </div>
                        </div>
                        <p class="text-sm text-slate-600 dark:text-slate-300 whitespace-pre-wrap leading-relaxed">{{ $r->body }}</p>
                    </div>
                    <form x-show="editing" x-cloak method="POST" action="{{ route('canned-responses.update', $r) }}" class="space-y-2">
                        @csrf @method('PUT')
                        <input type="text" name="title" value="{{ $r->title }}" required maxlength="100" class="w-full border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                        <textarea name="body" rows="3" required class="w-full border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 resize-none">{{ $r->body }}</textarea>
                        <div class="flex items-center gap-2 pt-1">
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors shadow-sm">{{ __('Save') }}</button>
                            <button type="button" @click="editing = false" class="text-xs text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 px-2">{{ __('Cancel') }}</button>
                        </div>
                    </form>
                </div>
                @empty
                <div class="p-10 text-center text-slate-400 dark:text-slate-500 text-sm">{{ __('No canned responses yet.') }}</div>
                @endforelse
            </div>
        </div>

        <div>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-5">
                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4">{{ __('New Canned Response') }}</h3>
                <form method="POST" action="{{ route('canned-responses.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1">{{ __('Title') }}</label>
                        <input type="text" name="title" value="{{ old('title') }}" required maxlength="100" placeholder="{{ __('e.g. No internet troubleshooting') }}" class="w-full border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                        @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1">{{ __('Message') }}</label>
                        <textarea name="body" rows="6" required class="w-full border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 resize-none">{{ old('body') }}</textarea>
                        @error('body')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors shadow-sm">{{ __('Create') }}</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
