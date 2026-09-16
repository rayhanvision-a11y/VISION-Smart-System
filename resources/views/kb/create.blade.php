<x-app-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">{{ __('New Article') }}</h1>
    </div>

    <div class="max-w-2xl bg-white border border-slate-200 rounded-xl shadow-sm p-6">
        <form method="POST" action="{{ route('kb.store') }}">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">{{ __('Title') }} <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" required class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm bg-slate-50 focus:ring-2 focus:ring-indigo-500">
                @error('title')<p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>@enderror
            </div>
            <div class="mb-4">
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">{{ __('Category') }}</label>
                <input type="text" name="category" value="{{ old('category') }}" placeholder="{{ __('e.g. Router, Billing, Connectivity') }}" class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm bg-slate-50 focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">{{ __('Body') }} <span class="text-red-500">*</span></label>
                <textarea name="body" rows="12" required class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm bg-slate-50 focus:ring-2 focus:ring-indigo-500 resize-none">{{ old('body') }}</textarea>
                @error('body')<p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>@enderror
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-600 mb-5">
                <input type="checkbox" name="is_published" value="1" checked class="rounded border-slate-300">
                {{ __('Published (visible to everyone)') }}
            </label>
            <div class="flex gap-3 pt-4 border-t border-slate-100">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition-colors">{{ __('Publish Article') }}</button>
                <a href="{{ route('kb.index') }}" class="px-5 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:text-slate-800 hover:bg-slate-100 transition-colors">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
</x-app-layout>
