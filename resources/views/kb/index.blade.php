<x-app-layout>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">{{ __('Knowledge Base') }}</h1>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Self-service articles for common issues.') }}</p>
        </div>
        @if(auth()->user()->isAdmin())
        <a href="{{ route('kb.create') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition-colors shadow-sm">+ {{ __('New Article') }}</a>
        @endif
    </div>

    <form method="GET" action="{{ route('kb.index') }}" class="bg-white border border-slate-200 rounded-xl shadow-sm p-3 mb-5 flex flex-wrap items-center gap-3">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __('Search articles...') }}" class="flex-1 min-w-48 border border-slate-200 rounded-lg px-3 py-2 text-sm bg-slate-50 focus:ring-2 focus:ring-indigo-500">
        @if($categories->isNotEmpty())
        <select name="category" class="border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-700 bg-slate-50">
            <option value="">{{ __('All Categories') }}</option>
            @foreach($categories as $cat)
            <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
            @endforeach
        </select>
        @endif
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">{{ __('Filter') }}</button>
    </form>

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden divide-y divide-slate-100">
        @forelse($articles as $article)
        <a href="{{ route('kb.show', $article) }}" class="block px-5 py-4 hover:bg-slate-50 transition-colors">
            <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="font-semibold text-slate-800 truncate">{{ $article->title }}
                        @if(!$article->is_published)<span class="ml-2 inline-flex px-1.5 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-700">{{ __('Draft') }}</span>@endif
                    </p>
                    @if($article->category)<p class="text-xs text-slate-400 mt-0.5">{{ $article->category }}</p>@endif
                </div>
                <span class="text-xs text-slate-400 flex-shrink-0">{{ $article->views }} {{ __('views') }}</span>
            </div>
        </a>
        @empty
        <div class="px-5 py-16 text-center text-slate-400 text-sm">{{ __('No articles found.') }}</div>
        @endforelse
    </div>

    @if($articles->hasPages())
    <div class="mt-4">{{ $articles->links() }}</div>
    @endif
</x-app-layout>
