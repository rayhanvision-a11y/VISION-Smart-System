<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\BlogPostComment;
use App\Models\BlogPostLike;
use App\Models\KbCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogPostController extends Controller
{
    /**
     * Display a listing of blog posts.
     */
    public function index(Request $request)
    {
        $query = BlogPost::with('author')->withCount(['likes', 'comments'])->where('is_published', true);

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $search = $request->search ?? $request->q;
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        $posts = $query->latest()->paginate(9)->withQueryString();

        $kbCategories = KbCategory::orderBy('name')->get();
        $dbCategories = $kbCategories->pluck('name')->toArray();
        $existingCategories = BlogPost::where('is_published', true)->distinct()->pluck('category')->filter()->toArray();
        $categories = array_values(array_unique(array_merge($dbCategories, $existingCategories)));

        return view('blogs.index', compact('posts', 'categories', 'kbCategories'));
    }

    /**
     * Show the form for creating a new blog post.
     */
    public function create()
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        $categories = KbCategory::orderBy('name')->pluck('name')->toArray();
        if (empty($categories)) {
            $categories = ['General'];
        }

        return view('blogs.create', compact('categories'));
    }

    /**
     * Store a newly created blog post in storage.
     */
    public function store(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string|max:200000',
            'youtube_video_url' => 'nullable|url|max:255',
            'featured_image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:3072',
            'is_published' => 'boolean',
        ]);

        $featuredPath = null;
        if ($request->hasFile('featured_image')) {
            $featuredPath = $request->file('featured_image')->store('blogs', 'public');
        }

        $post = BlogPost::create([
            'user_id' => auth()->id(),
            'title' => $validated['title'],
            'slug' => BlogPost::generateSlug($validated['title']),
            'category' => $validated['category'],
            'excerpt' => $validated['excerpt'] ?? Str::limit(strip_tags($validated['content']), 150),
            'content' => $validated['content'],
            'youtube_video_url' => $validated['youtube_video_url'] ?? null,
            'featured_image' => $featuredPath,
            'is_published' => $request->has('is_published') ? (bool) $request->is_published : true,
        ]);

        return redirect()->route('knowledge-base.show', $post->slug)->with('status', __('Blog post created successfully!'));
    }

    /**
     * Display the specified blog post.
     */
    public function show(string $slug)
    {
        $post = BlogPost::where('slug', $slug)->with(['author', 'comments.user'])->firstOrFail();

        // Increment view count (session guarded to avoid duplicate spam on same request)
        $sessionKey = 'viewed_blog_'.$post->id;
        if (! session()->has($sessionKey)) {
            $post->increment('views_count');
            session()->put($sessionKey, true);
        }

        if (auth()->check()) {
            try {
                DB::table('knowledge_base_reads')->updateOrInsert(
                    ['article_id' => $post->id, 'user_id' => auth()->id()],
                    ['read_at' => now(), 'created_at' => now(), 'updated_at' => now()]
                );
            } catch (\Throwable $e) {
            }
        }

        $isLiked = $post->isLikedBy(auth()->user());

        $recentPosts = BlogPost::where('id', '!=', $post->id)
            ->where('is_published', true)
            ->latest()
            ->take(5)
            ->get();

        $categoryCounts = BlogPost::where('is_published', true)
            ->selectRaw('category, count(*) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        return view('blogs.show', compact('post', 'isLiked', 'recentPosts', 'categoryCounts'));
    }

    /**
     * Show the form for editing the specified blog post.
     */
    public function edit(string $slug)
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        $blog = BlogPost::where('slug', $slug)->firstOrFail();
        $categories = KbCategory::orderBy('name')->pluck('name')->toArray();
        if (empty($categories)) {
            $categories = ['General'];
        }

        return view('blogs.edit', compact('blog', 'categories'));
    }

    /**
     * Update the specified blog post in storage.
     */
    public function update(Request $request, string $slug)
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        $blog = BlogPost::where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string|max:200000',
            'youtube_video_url' => 'nullable|url|max:255',
            'featured_image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:3072',
            'is_published' => 'boolean',
        ]);

        if ($request->hasFile('featured_image')) {
            if ($blog->featured_image && Storage::disk('public')->exists($blog->featured_image)) {
                Storage::disk('public')->delete($blog->featured_image);
            }
            $blog->featured_image = $request->file('featured_image')->store('blogs', 'public');
        }

        $blog->update([
            'title' => $validated['title'],
            'category' => $validated['category'],
            'excerpt' => $validated['excerpt'] ?? Str::limit(strip_tags($validated['content']), 150),
            'content' => $validated['content'],
            'youtube_video_url' => $validated['youtube_video_url'] ?? null,
            'is_published' => $request->has('is_published') ? (bool) $request->is_published : true,
        ]);

        return redirect()->route('knowledge-base.show', $blog->slug)->with('status', __('Blog post updated successfully!'));
    }

    /**
     * Remove the specified blog post from storage.
     */
    public function destroy(string $slug)
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        $blog = BlogPost::where('slug', $slug)->firstOrFail();

        if ($blog->featured_image && Storage::disk('public')->exists($blog->featured_image)) {
            Storage::disk('public')->delete($blog->featured_image);
        }

        $blog->delete();

        return redirect()->route('knowledge-base.index')->with('status', __('Blog post deleted successfully.'));
    }

    /**
     * Toggle reaction (Like, Love, Haha, Wow, Sad, Angry) for the blog post.
     */
    public function toggleLike(Request $request, string $slug)
    {
        $blog = BlogPost::where('slug', $slug)->firstOrFail();
        $user = auth()->user();
        $reaction = $request->input('reaction', 'like');

        $allowedReactions = ['like', 'love', 'haha', 'wow', 'sad', 'angry'];
        if (! in_array($reaction, $allowedReactions)) {
            $reaction = 'like';
        }

        $existing = BlogPostLike::where('blog_post_id', $blog->id)->where('user_id', $user->id)->first();

        if ($existing) {
            if ($existing->reaction_type === $reaction) {
                // Remove reaction if clicking same reaction type
                $existing->delete();
                $blog->decrement('likes_count');
                $userReaction = null;
            } else {
                // Change reaction type
                $existing->update(['reaction_type' => $reaction]);
                $userReaction = $reaction;
            }
        } else {
            BlogPostLike::create([
                'blog_post_id' => $blog->id,
                'user_id' => $user->id,
                'reaction_type' => $reaction,
            ]);
            $blog->increment('likes_count');
            $userReaction = $reaction;
        }

        $blog->refresh();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'user_reaction' => $userReaction,
                'likes_count' => $blog->likes_count,
            ]);
        }

        return redirect()->to(route('knowledge-base.show', $blog->slug).'#reactions');
    }

    /**
     * Store a comment or reply for the blog post.
     */
    public function storeComment(Request $request, string $slug)
    {
        $blog = BlogPost::where('slug', $slug)->firstOrFail();

        $request->validate([
            'comment' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:blog_post_comments,id',
        ]);

        BlogPostComment::create([
            'blog_post_id' => $blog->id,
            'user_id' => auth()->id(),
            'parent_id' => $request->filled('parent_id') ? $request->parent_id : null,
            'comment' => trim($request->comment),
        ]);

        $commentAnchor = $request->filled('parent_id') ? '#comment-'.$request->parent_id : '#comments';

        return redirect()->to(route('knowledge-base.show', $blog->slug).$commentAnchor)->with('status', __('Comment added successfully!'));
    }

    /**
     * Remove a comment.
     */
    public function destroyComment(BlogPostComment $comment)
    {
        if (! auth()->user()->isAdmin() && $comment->user_id !== auth()->id()) {
            abort(403);
        }

        $comment->delete();

        return back()->with('status', __('Comment removed.'));
    }
}
