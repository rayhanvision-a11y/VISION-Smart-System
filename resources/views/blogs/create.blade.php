<x-app-layout>
    @slot('pageTitle', __('Create New Blog Post'))

    <div class="max-w-4xl mx-auto space-y-6">

        <div class="flex items-center justify-between">
            <a href="{{ route('knowledge-base.index') }}" class="inline-flex items-center text-xs font-bold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 transition-colors">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                {{ __('Back to Knowledge Base') }}
            </a>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-10 shadow-sm">
            <h1 class="text-xl font-extrabold text-slate-900 dark:text-slate-100 mb-1">নতুন নিবন্ধ / টিউটোরিয়াল প্রকাশ করুন</h1>
            <p class="text-xs text-slate-500 mb-6">কাস্টমার, রিসেলার এবং সার্ভিস টিমের সহায়তার জন্য টিউটোরিয়াল এবং ভিডিও গাইড যুক্ত করুন।</p>

            <form method="POST" action="{{ route('knowledge-base.store') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf

                {{-- Title --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-1.5">{{ __('Post Title') }} *</label>
                    <input type="text" name="title" value="{{ old('title') }}" required placeholder="e.g. বিলিং প্যানেলে কীভাবে নতুন কাস্টমার অ্যাকাউন্ট তৈরি করবেন"
                           class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 font-semibold">
                    @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Category & YouTube Link --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-1.5">{{ __('Category') }} *</label>
                        <select name="category" required class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                            @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-1.5">{{ __('YouTube Video URL (Optional)') }}</label>
                        <input type="url" name="youtube_video_url" value="{{ old('youtube_video_url') }}" placeholder="https://www.youtube.com/watch?v=..."
                               class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                        <p class="text-[11px] text-slate-400 mt-1">{{ __('YouTube video link will auto-embed as a video player.') }}</p>
                    </div>
                </div>

                {{-- Featured Image Upload --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-1.5">{{ __('Featured Image (Optional)') }}</label>
                    <input type="file" name="featured_image" accept="image/*"
                           class="block w-full text-xs text-slate-500 border border-slate-200 dark:border-slate-700 rounded-xl cursor-pointer bg-slate-50 dark:bg-slate-800 file:mr-4 file:py-2.5 file:px-4 file:rounded-l-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100">
                    <p class="text-[11px] text-slate-400 mt-1">{{ __('JPG, PNG, WEBP max 3MB.') }}</p>
                </div>

                {{-- Excerpt --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-1.5">{{ __('Short Summary / Excerpt') }}</label>
                    <textarea name="excerpt" rows="2" placeholder="পোস্টের সংক্ষিপ্ত বিবরণ..."
                              class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 leading-relaxed">{{ old('excerpt') }}</textarea>
                </div>

                {{-- Content Editor --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-1.5">{{ __('Blog Content (Rich Text / Instructions)') }} *</label>
                    <div id="quill-editor" class="rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden min-h-[300px]"></div>
                    <textarea name="content" id="blog-content-hidden" class="hidden" required>{{ old('content') }}</textarea>
                    @error('content')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="pt-4 flex items-center justify-between border-t border-slate-100 dark:border-slate-800">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_published" value="1" checked class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ __('Publish Immediately') }}</span>
                    </label>

                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-xl text-xs font-bold transition-all shadow-md">
                        {{ __('Publish Blog Post') }}
                    </button>
                </div>
            </form>
        </div>

    </div>

    {{-- Quill Setup Script --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const quill = new Quill('#quill-editor', {
                theme: 'snow',
                placeholder: 'এখানে বিস্তারিত লিখুন, ছবি পেস্ট বা যুক্ত করুন...',
                modules: {
                    toolbar: [
                        [{ 'header': [1, 2, 3, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ 'color': [] }, { 'background': [] }],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        ['link', 'image', 'video'],
                        ['clean']
                    ]
                }
            });

            const hiddenTextarea = document.getElementById('blog-content-hidden');
            if (hiddenTextarea.value) {
                quill.clipboard.dangerouslyPasteHTML(hiddenTextarea.value);
            }

            quill.on('text-change', function () {
                hiddenTextarea.value = quill.root.innerHTML;
            });
        });
    </script>
</x-app-layout>
