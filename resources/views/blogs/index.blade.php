<x-app-layout>
    @slot('pageTitle', __('Knowledge Base'))

    <style>
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>

    <div class="max-w-7xl mx-auto space-y-6 pb-12" x-data="{
        showCatModal: false,
        editingCat: null,
        catName: '',
        catDesc: '',
        editUrl: '',
        openCreate() {
            this.editingCat = null;
            this.catName = '';
            this.catDesc = '';
            this.showCatModal = true;
        },
        openEdit(cat) {
            this.editingCat = cat;
            this.catName = cat.name;
            this.catDesc = cat.description || '';
            this.editUrl = '{{ route('kb-categories.index') }}/' + cat.id;
            this.showCatModal = true;
        }
    }">

        {{-- Header Banner --}}
        <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-6 relative overflow-hidden">
            <div class="absolute -top-24 -right-24 w-72 h-72 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="relative z-10">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-indigo-500/15 text-indigo-300 border border-indigo-500/30 mb-3">
                    <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                    📚 {{ __('Official Support Portal & Tutorials') }}
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">{{ __('Knowledge Base') }}</h1>
                <p class="mt-2 text-xs sm:text-sm text-slate-300 leading-relaxed max-w-3xl">
                    {{ __('Detailed tutorials and video guides for billing panel, MikroTik routing, app server & security.') }}
                </p>
            </div>

            @if(auth()->user()->isAdmin())
            <div class="flex-shrink-0 relative z-10 flex items-center gap-3">
                <button @click="openCreate()" type="button" class="inline-flex items-center gap-2 bg-slate-800 hover:bg-slate-700 text-slate-100 border border-slate-700 px-4 py-3 rounded-xl font-bold text-xs shadow-md transition-all hover:scale-[1.02] cursor-pointer">
                    <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 11h.01M7 15h.01M13 7h7M13 11h7M13 15h7M3 19h18a2 2 0 002-2V5a2 2 0 00-2-2H3a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>{{ __('Manage Categories') }}</span>
                </button>

                <a href="{{ route('knowledge-base.create') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white px-5 py-3 rounded-xl font-bold text-xs shadow-lg shadow-indigo-600/30 transition-all hover:scale-[1.02] cursor-pointer">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>{{ __('Publish New Tutorial') }}</span>
                </a>
            </div>
            @endif
        </div>

        {{-- Status Notification --}}
        @if(session('status'))
        <div class="px-4 py-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-sm font-semibold flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('status') }}
        </div>
        @endif

        {{-- Category Pills & Search Bar --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-3.5 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
            {{-- Filter Pills --}}
            <div class="flex items-center gap-2 overflow-x-auto w-full md:w-auto pb-1 md:pb-0 no-scrollbar" style="-ms-overflow-style: none; scrollbar-width: none;">
                <a href="{{ route('knowledge-base.index') }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap {{ !request('category') ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                    {{ __('All Categories') }}
                </a>
                @foreach($categories as $cat)
                <a href="{{ route('knowledge-base.index', ['category' => $cat]) }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap {{ request('category') === $cat ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                    {{ $cat }}
                </a>
                @endforeach
            </div>

            {{-- Search Input --}}
            <form method="GET" action="{{ route('knowledge-base.index') }}" class="w-full md:w-80">
                @if(request('category'))
                <input type="hidden" name="category" value="{{ request('category') }}">
                @endif
                <div class="relative flex items-center bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2 transition-all focus-within:border-indigo-500 focus-within:ring-2 focus-within:ring-indigo-500/20">
                    <svg class="w-4 h-4 text-slate-400 mr-2.5 flex-shrink-0 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __('Search blogs & articles...') }}"
                           class="w-full border-0 bg-transparent text-sm text-slate-700 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-0 p-0">
                </div>
            </form>
        </div>

        {{-- 3-Column Blog Cards Grid --}}
        @if($posts->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-8" style="gap: 2rem !important;">
            @foreach($posts as $post)
            <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/60 rounded-2xl overflow-hidden shadow-md hover:shadow-xl transition-all duration-300 flex flex-col justify-between group">
                
                {{-- 1. Image Container --}}
                <div class="relative w-full overflow-hidden bg-slate-100 dark:bg-slate-900/60" style="aspect-ratio: 16 / 9 !important; width: 100% !important;">
                    <a href="{{ route('knowledge-base.show', $post->slug) }}" class="block w-full h-full">
                        @php
                            $imageUrl = null;
                            if ($post->featured_image) {
                                $imageUrl = str_starts_with($post->featured_image, 'http') ? $post->featured_image : asset('storage/' . $post->featured_image);
                            } else {
                                $demoImages = [
                                    'App Server' => 'https://images.unsplash.com/photo-1544197150-b99a580bb7a8?w=800&auto=format&fit=crop&q=80',
                                    'Billing' => 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=800&auto=format&fit=crop&q=80',
                                    'Smart Form' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=800&auto=format&fit=crop&q=80',
                                ];
                                $imageUrl = $demoImages[$post->category] ?? 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?w=800&auto=format&fit=crop&q=80';
                            }
                        @endphp
                        <img src="{{ $imageUrl }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    </a>

                    {{-- Floating Category & Video Guide Badges --}}
                    <div class="absolute top-3 left-1/2 -translate-x-1/2 flex items-center gap-1.5 z-0 whitespace-nowrap">
                        <span class="text-white text-[11px] font-extrabold px-3 py-1 rounded-md shadow-md uppercase tracking-wider" style="background-color: #2563eb !important; color: #ffffff !important;">
                            {{ $post->category }}
                        </span>
                        @if($post->youtube_video_url)
                        <span class="text-white text-xs font-black px-2.5 py-1 rounded-md shadow-md uppercase tracking-wider flex items-center gap-1" style="background-color: #dc2626 !important; color: #ffffff !important;">
                            <svg class="w-3 h-3 fill-current" viewBox="0 0 24 24"><path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"/></svg>
                            Video Guide
                        </span>
                        @endif
                    </div>
                </div>

                {{-- 2. Card Body --}}
                <div class="p-6 flex-1 flex flex-col justify-between bg-white dark:bg-slate-800">
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-slate-100 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors line-clamp-2 leading-snug mb-3">
                            <a href="{{ route('knowledge-base.show', $post->slug) }}">{{ $post->title }}</a>
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-3 leading-relaxed mb-4">
                            {{ $post->excerpt }}
                        </p>
                    </div>

                    {{-- 3. Card Footer Meta --}}
                    <div class="pt-4 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-between text-xs text-slate-400 font-medium">
                        <div>
                            <span>{{ $post->created_at->format('M d, Y') }}</span>
                            <span class="mx-1">•</span>
                            <span>{{ $post->comments_count }} Comments</span>
                        </div>

                        <div class="flex items-center gap-3">
                            <form method="POST" action="{{ route('knowledge-base.like', $post->slug) }}" class="inline-block">
                                @csrf
                                <button type="submit" class="flex items-center gap-1 text-rose-500 font-bold hover:scale-110 transition-transform cursor-pointer">
                                    <svg class="w-4 h-4 fill-rose-500 stroke-rose-500" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                                    {{ $post->likes_count }}
                                </button>
                            </form>

                            <button onclick="navigator.clipboard.writeText('{{ route('knowledge-base.show', $post->slug) }}'); alert('Link copied to clipboard!');" 
                                    class="text-slate-400 hover:text-blue-600 transition-colors cursor-pointer" title="Share link">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-8">
            {{ $posts->links() }}
        </div>
        @else
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-12 text-center shadow-sm">
            <h3 class="text-base font-bold text-slate-700 dark:text-slate-200 mb-1">{{ __('No blog posts found') }}</h3>
            <p class="text-xs text-slate-400 max-w-sm mx-auto mb-4">{{ __('কোনো ব্লগ পোস্ট পাওয়া যায়নি।') }}</p>
        </div>
        @endif

        {{-- Category Management Modal (Root Level z-[9999] Stacking Context Fix) --}}
        @if(auth()->user()->isAdmin())
        <div x-show="showCatModal" 
             x-cloak 
             class="fixed inset-0 z-[9999] overflow-y-auto flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
            
            <div @click.away="showCatModal = false" class="bg-slate-900 border border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-6 relative overflow-hidden text-slate-100 max-h-[90vh] flex flex-col justify-between">
                <div>
                    {{-- Modal Header --}}
                    <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                        <h3 class="text-lg font-bold text-white flex items-center gap-2">
                            📁 <span x-text="editingCat ? '{{ __('Edit Category') }}' : '{{ __('Knowledge Base Categories') }}'"></span>
                        </h3>
                        <button @click="showCatModal = false" type="button" class="text-slate-400 hover:text-white p-1 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    {{-- Add / Edit Form --}}
                    <form :action="editingCat ? editUrl : '{{ route('kb-categories.store') }}'" method="POST" class="space-y-4 pt-4">
                        @csrf
                        <template x-if="editingCat">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">{{ __('Category Name') }} *</label>
                            <input type="text" name="name" x-model="catName" required placeholder="{{ __('e.g., Billing Guide') }}"
                                   class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">{{ __('Description (Optional)') }}</label>
                            <textarea name="description" x-model="catDesc" rows="2" placeholder="{{ __('Short description of this category...') }}"
                                      class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2">
                            <button type="button" @click="editingCat ? editingCat = null : showCatModal = false" class="px-4 py-2 text-xs font-bold text-slate-400 hover:bg-slate-800 rounded-xl transition-colors">
                                <span x-text="editingCat ? '{{ __('Cancel Edit') }}' : '{{ __('Close') }}'"></span>
                            </button>
                            <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold shadow-md transition-all">
                                <span x-text="editingCat ? '{{ __('Update Category') }}' : '{{ __('Add Category') }}'"></span>
                            </button>
                        </div>
                    </form>

                    {{-- Category List Table --}}
                    <div class="pt-6 border-t border-slate-800 space-y-3 mt-4">
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ __('Existing Categories') }}</h4>
                        
                        <div class="max-h-56 overflow-y-auto space-y-2 pr-1 no-scrollbar">
                            @foreach($kbCategories as $catItem)
                            <div class="flex items-center justify-between p-3 bg-slate-800/80 rounded-xl border border-slate-700/60">
                                <div>
                                    <div class="text-sm font-bold text-white">{{ $catItem->name }}</div>
                                    @if($catItem->description)
                                    <div class="text-xs text-slate-400 truncate max-w-xs">{{ $catItem->description }}</div>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2">
                                    <button @click="openEdit({{ json_encode($catItem) }})" type="button" class="px-2.5 py-1 bg-indigo-950/80 text-indigo-300 hover:bg-indigo-900 border border-indigo-700/50 rounded-lg text-xs font-bold transition-colors">
                                        {{ __('Edit') }}
                                    </button>

                                    <form method="POST" action="{{ route('kb-categories.destroy', $catItem->id) }}" onsubmit="return confirm('{{ __('Are you sure you want to delete this category?') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1 bg-rose-950/80 text-rose-300 hover:bg-rose-900 border border-rose-700/50 rounded-lg text-xs font-bold transition-colors">
                                            {{ __('Delete') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>
</x-app-layout>
