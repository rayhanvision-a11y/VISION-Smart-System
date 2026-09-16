<x-app-layout>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">{{ __('POP Offices') }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Manage POP office locations and view pending tickets.') }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Office list --}}
        <div class="lg:col-span-2 space-y-3">
            @forelse($offices as $office)
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-5 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-950/60 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <a href="{{ route('pop-offices.tickets', $office) }}" class="font-semibold text-slate-800 dark:text-slate-100 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">{{ $office->name }}</a>
                        @if($office->location)<p class="text-xs text-slate-400 dark:text-slate-500">{{ $office->location }}</p>@endif
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-center">
                        <span class="block text-2xl font-bold {{ $office->pending_count > 0 ? 'text-red-500 dark:text-red-400' : 'text-emerald-500 dark:text-emerald-400' }}">{{ $office->pending_count }}</span>
                        <span class="text-xs text-slate-400 dark:text-slate-500">{{ __('Pending') }}</span>
                    </div>
                    <div class="text-center">
                        <span class="block text-2xl font-bold text-slate-600 dark:text-slate-300">{{ $office->total_count }}</span>
                        <span class="text-xs text-slate-400 dark:text-slate-500">{{ __('Total') }}</span>
                    </div>
                    @if(auth()->user()->isSuperAdmin())
                    <form method="POST" action="{{ route('pop-offices.destroy', $office) }}" onsubmit="return confirm('{{ __('Delete :name?', ['name' => $office->name]) }}')">
                        @csrf @method('DELETE')
                        <button class="text-xs text-red-500 dark:text-red-400 hover:underline font-medium">{{ __('Delete') }}</button>
                    </form>
                    @endif
                </div>
            </div>
            @empty
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-10 text-center">
                <p class="text-slate-400 dark:text-slate-500 text-sm">{{ __('No POP offices yet. Add one') }} →</p>
            </div>
            @endforelse
        </div>

        {{-- Add new office (super admin only) --}}
        @if(auth()->user()->isSuperAdmin())
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-5">
            <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4">{{ __('Add New POP Office') }}</h2>
            @if(session('success'))<div class="mb-3 text-xs text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 rounded-lg px-3 py-2 border border-emerald-200 dark:border-emerald-800/50">{{ session('success') }}</div>@endif
            <form method="POST" action="{{ route('pop-offices.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1">{{ __('Office Name') }} *</label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="{{ __('e.g. Dhaka POP Office') }}"
                           class="w-full border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 @error('name') border-red-400 @enderror">
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1">{{ __('Location') }}</label>
                    <input type="text" name="location" value="{{ old('location') }}" placeholder="{{ __('e.g. Mirpur, Dhaka') }}"
                           class="w-full border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800">
                </div>
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-lg text-sm font-semibold transition-colors shadow-sm">
                    + {{ __('Add Office') }}
                </button>
            </form>
        </div>
        @endif
    </div>
</x-app-layout>
