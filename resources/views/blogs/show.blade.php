<x-app-layout>
    @slot('pageTitle', $post->title)

    @php
        $heroImageUrl = null;
        if ($post->featured_image) {
            $heroImageUrl = str_starts_with($post->featured_image, 'http') ? $post->featured_image : asset('storage/' . $post->featured_image);
        } else {
            $demoImages = [
                'App Server' => 'https://images.unsplash.com/photo-1544197150-b99a580bb7a8?w=1600&auto=format&fit=crop&q=85',
                'Billing' => 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=1600&auto=format&fit=crop&q=85',
                'Smart Form' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=1600&auto=format&fit=crop&q=85',
                'General' => 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?w=1600&auto=format&fit=crop&q=85',
            ];
            $heroImageUrl = $demoImages[$post->category] ?? 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?w=1600&auto=format&fit=crop&q=85';
        }
    @endphp

    <div class="max-w-7xl mx-auto space-y-6 pb-16">

        {{-- Navigation Bar & Admin Actions --}}
        <div class="flex items-center justify-between gap-4 px-1">
            <a href="{{ route('knowledge-base.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors bg-white dark:bg-slate-800 px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                {{ __('Back to Knowledge Base') }}
            </a>

            @if(auth()->user()->isAdmin())
            <div class="flex items-center gap-2">
                <a href="{{ route('knowledge-base.edit', $post->slug) }}" class="px-3.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold shadow-sm transition-all flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002-2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    {{ __('Edit') }}
                </a>
                <form method="POST" action="{{ route('knowledge-base.destroy', $post->slug) }}" onsubmit="return confirm('Permanently delete this article?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3.5 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-bold shadow-sm transition-all flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        {{ __('Delete') }}
                    </button>
                </form>
            </div>
            @endif
        </div>

        {{-- Main Article Content Canvas --}}
        <div class="bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-3xl p-6 sm:p-10 shadow-xl border border-slate-200/90 dark:border-slate-700/80">
            
            {{-- Header Title & Meta Section --}}
            <div class="max-w-4xl mb-8 space-y-4">
                <div class="flex items-center gap-2.5 flex-wrap">
                    <span class="bg-blue-600 text-white text-xs font-black px-4 py-1.5 rounded-full shadow-sm uppercase tracking-wider inline-flex items-center" style="background-color: #2563eb !important; color: #ffffff !important;">
                        {{ $post->category }}
                    </span>
                    @if($post->youtube_video_url)
                    <span class="bg-red-600 text-white text-xs font-black px-4 py-1.5 rounded-full shadow-md uppercase tracking-wider flex items-center gap-1.5" style="background-color: #dc2626 !important; color: #ffffff !important;">
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"/></svg>
                        Video Guide
                    </span>
                    @endif
                </div>

                <h1 class="text-3xl sm:text-4xl md:text-5xl font-black text-slate-900 dark:text-white tracking-tight leading-[1.15]">
                    {{ $post->title }}
                </h1>

                <div class="flex items-center gap-3 text-xs sm:text-sm text-slate-500 dark:text-slate-400 font-medium pt-1 flex-wrap">
                    <div class="flex items-center gap-2">
                        <img src="{{ $post->author->avatarUrl() }}" alt="{{ $post->author->name }}" class="w-7 h-7 rounded-full object-cover border border-slate-200 dark:border-slate-700">
                        <span class="text-slate-900 dark:text-slate-100 font-bold">{{ $post->author->name }}</span>
                    </div>
                    <span>•</span>
                    <span>{{ $post->created_at->format('M d, Y') }}</span>
                    <span>•</span>
                    <span>{{ $post->allCommentsCount() }} Comments</span>
                    <span>•</span>
                    <span class="flex items-center gap-1 text-rose-600 dark:text-rose-400 font-bold">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                        {{ $post->likes_count }} Likes
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-12 gap-6 md:gap-10">
                
                {{-- Left/Main Article Content Column (8 Cols out of 12) --}}
                <div class="col-span-12 md:col-span-8 space-y-8 md:pr-6">
                    
                    {{-- Stunning Full-Width 16:9 Featured Cover Image Card --}}
                    @if($heroImageUrl)
                    <div class="relative w-full rounded-3xl overflow-hidden shadow-2xl border border-slate-200/90 dark:border-slate-700/80 group bg-slate-900" style="aspect-ratio: 16 / 9 !important; width: 100% !important;">
                        <img src="{{ $heroImageUrl }}" alt="{{ $post->title }}" class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-700" style="aspect-ratio: 16 / 9 !important; width: 100% !important; height: 100% !important; object-fit: cover !important;">
                    </div>
                    @endif

                    {{-- Article Excerpt / Highlight Callout --}}
                    @if($post->excerpt)
                    <p class="text-base sm:text-lg text-slate-700 dark:text-slate-200 font-medium leading-relaxed italic border-l-4 border-blue-600 dark:border-blue-500 pl-4 py-3 bg-blue-50/50 dark:bg-blue-950/40 rounded-r-2xl">
                        {{ $post->excerpt }}
                    </p>
                    @endif

                    {{-- Article Rich Text Body --}}
                    <div class="prose dark:prose-invert max-w-none text-slate-800 dark:text-slate-200 leading-relaxed text-base sm:text-lg space-y-6 font-normal">
                        {!! $post->content !!}
                    </div>

                    {{-- Video Guide Embed (If Present) --}}
                    @if($post->youtube_embed_url)
                    <div class="rounded-2xl overflow-hidden bg-slate-950 p-5 border border-slate-800 shadow-xl my-8">
                        <p class="text-base sm:text-lg font-black text-red-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"/></svg>
                            Video Guide
                        </p>
                        <div class="w-full rounded-xl overflow-hidden" style="aspect-ratio: 16 / 9 !important; width: 100% !important;">
                            <iframe src="{{ $post->youtube_embed_url }}" title="YouTube video player" class="w-full h-full" style="aspect-ratio: 16 / 9 !important; width: 100% !important; height: 100% !important; border: 0;" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                        </div>
                    </div>
                    @endif

                    {{-- Article Footer Action & Facebook Reactions Bar --}}
                    @php
                        $myReaction = $post->userReaction(auth()->user());
                        $reactionsList = [
                            'like'  => ['label' => 'LIKE',  'emoji' => '👍', 'color' => 'text-blue-600'],
                            'love'  => ['label' => 'LOVE',  'emoji' => '❤️', 'color' => 'text-rose-600'],
                            'haha'  => ['label' => 'HAHA',  'emoji' => '😂', 'color' => 'text-amber-500'],
                            'wow'   => ['label' => 'WOW',   'emoji' => '😮', 'color' => 'text-amber-500'],
                            'sad'   => ['label' => 'SAD',   'emoji' => '😢', 'color' => 'text-amber-500'],
                            'angry' => ['label' => 'ANGRY', 'emoji' => '😡', 'color' => 'text-orange-600'],
                        ];
                        $activeReaction = $myReaction && isset($reactionsList[$myReaction]) ? $reactionsList[$myReaction] : null;
                    @endphp

                    <div id="reactions" class="py-8 border-t border-slate-200 dark:border-slate-700/80 flex items-center justify-between flex-wrap gap-4">
                        {{-- Reaction Button with Floating Popover Box --}}
                        <div x-data="{ openReactions: false }" class="relative inline-block">
                            {{-- Floating Emoji Selector --}}
                            <div x-show="openReactions" 
                                 @click.outside="openReactions = false" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                 class="absolute bottom-full left-0 mb-3 bg-white dark:bg-slate-800 rounded-full px-3.5 py-2 shadow-2xl border border-slate-200/90 dark:border-slate-700 flex items-center gap-2.5 z-50">
                                @foreach($reactionsList as $key => $data)
                                <form method="POST" action="{{ route('knowledge-base.like', $post->slug) }}" class="inline">
                                    @csrf
                                    <input type="hidden" name="reaction" value="{{ $key }}">
                                    <button type="submit" 
                                            title="{{ $data['label'] }}"
                                            class="text-2xl hover:scale-130 transition-transform duration-200 p-0.5 flex items-center justify-center transform active:scale-95 cursor-pointer">
                                        {{ $data['emoji'] }}
                                    </button>
                                </form>
                                @endforeach
                            </div>

                            {{-- Main Action Button Pill Design --}}
                            <div class="flex items-center gap-2">
                                <form method="POST" action="{{ route('knowledge-base.like', $post->slug) }}" class="inline-block">
                                    @csrf
                                    <input type="hidden" name="reaction" value="{{ $myReaction ? $myReaction : 'like' }}">
                                    <button type="submit" 
                                            @mouseenter="openReactions = true"
                                            class="px-5 py-2.5 rounded-full text-xs font-bold transition-all flex items-center gap-2.5 shadow-sm border border-blue-200 dark:border-blue-800/80 bg-blue-50 dark:bg-blue-950/70 text-blue-600 dark:text-blue-400 cursor-pointer hover:bg-blue-100 dark:hover:bg-blue-900/80">
                                        <span class="text-base leading-none">{{ $activeReaction ? $activeReaction['emoji'] : '👍' }}</span>
                                        <span class="tracking-wide uppercase font-extrabold text-blue-600 dark:text-blue-400">{{ $activeReaction ? $activeReaction['label'] : 'LIKE' }}</span>
                                        <span class="px-2.5 py-0.5 rounded-full bg-white dark:bg-slate-800 text-blue-600 dark:text-blue-400 font-bold text-xs shadow-2xs border border-blue-100 dark:border-blue-800">{{ $post->likes_count }}</span>
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- Share Button --}}
                        <button onclick="navigator.clipboard.writeText(window.location.href); alert('Article URL copied to clipboard!');" 
                                class="px-4 py-2.5 rounded-2xl bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors flex items-center gap-1.5 border border-slate-200 dark:border-slate-600 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            {{ __('Share') }}
                        </button>
                    </div>

                    {{-- Comments & Feedback Section --}}
                    <div id="comments" class="pt-6 border-t border-slate-200 dark:border-slate-700/80 space-y-6 scroll-mt-6">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2 border-b border-slate-200 dark:border-slate-700 pb-3">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            {{ __('Comments') }} ({{ $post->allCommentsCount() }})
                        </h3>

                        {{-- Alert Notifications --}}
                        @if(session('status'))
                        <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-bold flex items-center gap-2 shadow-sm">
                            <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>{{ session('status') }}</span>
                        </div>
                        @endif

                        @if($errors->any())
                        <div class="p-4 rounded-xl bg-red-50 dark:bg-red-950/50 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 text-xs font-bold space-y-1 shadow-sm">
                            @foreach($errors->all() as $err)
                            <p class="flex items-center gap-1.5"><span class="text-red-500">•</span> {{ $err }}</p>
                            @endforeach
                        </div>
                        @endif

                        {{-- Primary Comment Form --}}
                        <form method="POST" action="{{ route('knowledge-base.comments.store', $post->slug) }}#comments" class="space-y-3">
                            @csrf
                            <textarea name="comment" rows="3" required placeholder="Leave a comment..."
                                      class="w-full px-4 py-3 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-slate-900 transition-all"></textarea>
                            <div class="flex justify-end pt-1">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-extrabold px-6 py-2.5 rounded-xl text-xs shadow-md hover:shadow-lg transition-all inline-flex items-center gap-2 cursor-pointer" style="background-color: #2563eb !important; color: #ffffff !important;">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                    <span>{{ __('Post Comment') }}</span>
                                </button>
                            </div>
                        </form>

                        {{-- Comment List with Nested Replies --}}
                        <div class="space-y-5 pt-2">
                            @forelse($post->comments as $comment)
                            <div id="comment-{{ $comment->id }}" class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/80 space-y-3" x-data="{ openReply: false }">
                                {{-- Main Parent Comment --}}
                                <div class="flex items-start gap-3">
                                    <img src="{{ $comment->user->avatarUrl() }}" alt="{{ $comment->user->name }}" class="w-9 h-9 rounded-full object-cover border border-slate-300 dark:border-slate-600 flex-shrink-0">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between mb-1">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="text-xs font-extrabold text-slate-900 dark:text-slate-100">{{ $comment->user->name }}</span>
                                                @if($comment->user_id === $post->user_id)
                                                <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase bg-amber-100 dark:bg-amber-950/80 text-amber-800 dark:text-amber-300 border dark:border-amber-800/60 flex items-center gap-1">
                                                    <svg class="w-2.5 h-2.5 fill-current" viewBox="0 0 24 24"><path d="M12 2l2.4 7.4h7.6l-6.2 4.5 2.4 7.4-6.2-4.5-6.2 4.5 2.4-7.4-6.2-4.5h7.6z"/></svg>
                                                    Author
                                                </span>
                                                @endif
                                                <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase bg-blue-100 dark:bg-blue-950/80 text-blue-700 dark:text-blue-300 border dark:border-blue-800/60">
                                                    {{ str_replace('_', ' ', $comment->user->role) }}
                                                </span>
                                            </div>
                                            <span class="text-[10px] text-slate-400 font-semibold">{{ $comment->created_at->diffForHumans() }}</span>
                                        </div>

                                        <p class="text-xs sm:text-sm text-slate-800 dark:text-slate-200 leading-relaxed whitespace-pre-line my-1">{{ $comment->comment }}</p>

                                        {{-- Reply & Delete Actions --}}
                                        <div class="flex items-center gap-4 mt-2 text-xs font-bold text-slate-500 dark:text-slate-400">
                                            <button @click="openReply = !openReply" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors flex items-center gap-1 cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                                <span>Reply</span>
                                            </button>

                                            @if(auth()->user()->isAdmin() || auth()->id() === $comment->user_id)
                                            <form method="POST" action="{{ route('knowledge-base.comments.destroy', $comment->id) }}" class="inline-block" onsubmit="return confirm('Delete comment?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:text-red-700 dark:hover:text-red-400 transition-colors cursor-pointer">Delete</button>
                                            </form>
                                            @endif
                                        </div>

                                        {{-- Inline Reply Form --}}
                                        <div x-show="openReply" x-cloak class="mt-3 pt-2">
                                            <form method="POST" action="{{ route('knowledge-base.comments.store', $post->slug) }}#comment-{{ $comment->id }}" class="flex items-center gap-2">
                                                @csrf
                                                <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                                <input type="text" name="comment" required placeholder="Write a reply to {{ $comment->user->name }}..." 
                                                       class="flex-1 px-3.5 py-2 text-xs border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-blue-500">
                                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-xs font-extrabold shadow-sm cursor-pointer" style="background-color: #2563eb !important; color: #ffffff !important;">
                                                    Reply
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                {{-- Nested Replies --}}
                                @if($comment->replies->count() > 0)
                                <div class="pl-5 sm:pl-9 border-l-2 border-slate-200 dark:border-slate-700 space-y-3 pt-2">
                                    @foreach($comment->replies as $reply)
                                    <div id="comment-{{ $reply->id }}" class="p-3.5 rounded-xl bg-white dark:bg-slate-800/90 border border-slate-200/90 dark:border-slate-700 flex items-start gap-3 shadow-2xs">
                                        <img src="{{ $reply->user->avatarUrl() }}" alt="{{ $reply->user->name }}" class="w-7 h-7 rounded-full object-cover border border-slate-300 dark:border-slate-600 flex-shrink-0">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between mb-1">
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    <span class="text-xs font-extrabold text-slate-900 dark:text-slate-100">{{ $reply->user->name }}</span>
                                                    @if($reply->user_id === $post->user_id)
                                                    <span class="px-1.5 py-0.5 rounded text-[8px] font-black uppercase bg-amber-100 dark:bg-amber-950/80 text-amber-800 dark:text-amber-300 border dark:border-amber-800/60">Author</span>
                                                    @endif
                                                </div>
                                                <span class="text-[10px] text-slate-400 font-semibold">{{ $reply->created_at->diffForHumans() }}</span>
                                            </div>
                                            <p class="text-xs text-slate-800 dark:text-slate-200 leading-relaxed whitespace-pre-line">{{ $reply->comment }}</p>

                                            @if(auth()->user()->isAdmin() || auth()->id() === $reply->user_id)
                                            <div class="mt-1 text-right">
                                                <form method="POST" action="{{ route('knowledge-base.comments.destroy', $reply->id) }}" class="inline-block" onsubmit="return confirm('Delete reply?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-[10px] text-red-500 font-medium hover:underline cursor-pointer">Delete</button>
                                                </form>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                            @empty
                            <div class="py-8 text-center text-slate-400 text-xs">
                                No comments yet. Be the first to start the discussion!
                            </div>
                            @endforelse
                        </div>
                    </div>

                </div>

                {{-- Right Sidebar Column (Exact Match to Screenshot 2 Sidebar) --}}
                <div class="col-span-12 md:col-span-4 space-y-8 md:pl-6 md:border-l md:border-slate-200/80 dark:md:border-slate-700/80">
                    
                    {{-- 1. Search Bar Widget --}}
                    <div class="mb-8">
                        <form method="GET" action="{{ route('knowledge-base.index') }}">
                            <div class="relative flex items-center bg-[#f8fafc] dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2 transition-all focus-within:border-blue-400 focus-within:ring-2 focus-within:ring-blue-100">
                                <svg class="w-4 h-4 text-slate-400 mr-2.5 flex-shrink-0 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <input type="text" name="q" value="{{ request('q') }}" placeholder="টিউটোরিয়াল ও নির্দেশিকা খুঁজুন..."
                                       class="w-full border-0 bg-transparent text-sm text-slate-700 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-0 p-0" style="border: none !important; outline: none !important; box-shadow: none !important;">
                            </div>
                        </form>
                    </div>

                    {{-- 2. Categories Widget (Exact Match to Screenshot 2) --}}
                    <div class="mb-8 pb-6 border-b border-slate-100 dark:border-slate-700/60">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white mb-4 pb-1 border-b-2 border-blue-600 dark:border-blue-500 inline-block tracking-tight">
                            Categories
                        </h3>

                        <ul class="space-y-2.5 text-xs font-semibold text-slate-700 dark:text-slate-300">
                            @php
                                $allCats = ['Billing', 'App Server', 'Smart Form', 'Tutorial', 'General'];
                            @endphp
                            @foreach($allCats as $c)
                            @php
                                $count = $categoryCounts[$c] ?? 0;
                            @endphp
                            <li>
                                <a href="{{ route('knowledge-base.index', ['category' => $c]) }}" 
                                   class="flex items-center gap-2 py-1 px-1 hover:text-blue-600 dark:hover:text-blue-400 transition-colors {{ request('category') === $c ? 'text-blue-600 dark:text-blue-400 font-bold' : '' }}">
                                    <span class="text-slate-400 font-bold">•</span>
                                    <span class="flex-1 text-slate-800 dark:text-slate-200 hover:text-blue-600 dark:hover:text-blue-400">{{ $c }}</span>
                                    <span class="text-slate-400 font-normal">({{ $count }})</span>
                                </a>
                            </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- 3. Recent Articles Widget --}}
                    @if(isset($recentPosts) && $recentPosts->count() > 0)
                    <div class="mb-8">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white mb-4 pb-1 border-b-2 border-blue-600 dark:border-blue-500 inline-block tracking-tight">
                            Recent Articles
                        </h3>

                        <div class="space-y-4">
                            @foreach($recentPosts->take(4) as $recent)
                            <a href="{{ route('knowledge-base.show', $recent->slug) }}" class="flex items-start gap-3 group">
                                <div class="w-14 h-14 rounded-lg bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 overflow-hidden flex-shrink-0">
                                    @php
                                        $recImg = $recent->featured_image ? (str_starts_with($recent->featured_image, 'http') ? $recent->featured_image : asset('storage/' . $recent->featured_image)) : 'https://images.unsplash.com/photo-1544197150-b99a580bb7a8?w=200&auto=format&fit=crop&q=80';
                                    @endphp
                                    <img src="{{ $recImg }}" alt="{{ $recent->title }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-xs font-bold text-slate-900 dark:text-slate-100 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors line-clamp-2 leading-snug">
                                        {{ $recent->title }}
                                    </h4>
                                    <span class="text-[10px] text-slate-400 mt-1 block">{{ $recent->created_at->format('M d, Y') }}</span>
                                </div>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                </div>

            </div>

        </div>

    </div>
</x-app-layout>
