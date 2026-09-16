<x-app-layout>
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('kb.index') }}" class="text-slate-400 hover:text-slate-600 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="flex-1">
            <h1 class="text-2xl font-bold text-slate-800">{{ $article->title }}
                @if(!$article->is_published)<span class="ml-2 inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700 align-middle">{{ __('Draft') }}</span>@endif
            </h1>
            @if($article->category)<p class="text-sm text-slate-500 mt-0.5">{{ $article->category }}</p>@endif
        </div>
        @if(auth()->user()->isAdmin())
        <a href="{{ route('kb.edit', $article) }}" class="inline-flex items-center gap-1.5 border border-slate-200 text-slate-600 hover:bg-slate-100 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors">{{ __('Edit') }}</a>
        <form method="POST" action="{{ route('kb.destroy', $article) }}" onsubmit="return confirm('{{ __('Delete this article?') }}')">
            @csrf @method('DELETE')
            <button type="submit" class="inline-flex items-center gap-1.5 border border-red-200 text-red-500 hover:bg-red-50 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors">{{ __('Delete') }}</button>
        </form>
        @endif
    </div>

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 max-w-3xl">
        <div class="prose prose-sm max-w-none text-slate-700 whitespace-pre-wrap">{!! nl2br(e($article->body)) !!}</div>
    </div>

    <p class="text-xs text-slate-400 mt-4">{{ __('Last updated') }} {{ $article->updated_at->diffForHumans() }} &middot; {{ $article->views }} {{ __('views') }}</p>
</x-app-layout>
