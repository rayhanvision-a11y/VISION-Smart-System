<?php

namespace App\Http\Controllers;

use App\Models\KbArticle;
use Illuminate\Http\Request;

class KbArticleController extends Controller
{
    public function index(Request $request)
    {
        $query = KbArticle::query();

        if (! auth()->user()->isAdmin()) {
            $query->where('is_published', true);
        }

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%");
            });
        }

        if ($category = $request->get('category')) {
            $query->where('category', $category);
        }

        $articles = $query->orderByDesc('created_at')->paginate(15);
        $categories = KbArticle::where('is_published', true)
            ->whereNotNull('category')->distinct()->pluck('category');

        return view('kb.index', compact('articles', 'categories'));
    }

    public function show(KbArticle $article)
    {
        if (! $article->is_published && ! auth()->user()->isAdmin()) {
            abort(404);
        }

        $article->increment('views');

        return view('kb.show', compact('article'));
    }

    public function create()
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        return view('kb.create');
    }

    public function store(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'category' => 'nullable|string|max:60',
            'body' => 'required|string',
            'is_published' => 'nullable|boolean',
        ]);

        $article = KbArticle::create([
            'title' => $validated['title'],
            'slug' => KbArticle::makeSlug($validated['title']),
            'category' => $validated['category'] ?? null,
            'body' => $validated['body'],
            'is_published' => $request->boolean('is_published'),
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('kb.show', $article)->with('success', 'Article published.');
    }

    public function edit(KbArticle $article)
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        return view('kb.edit', compact('article'));
    }

    public function update(Request $request, KbArticle $article)
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'category' => 'nullable|string|max:60',
            'body' => 'required|string',
            'is_published' => 'nullable|boolean',
        ]);

        $article->update([
            'title' => $validated['title'],
            'category' => $validated['category'] ?? null,
            'body' => $validated['body'],
            'is_published' => $request->boolean('is_published'),
        ]);

        return redirect()->route('kb.show', $article)->with('success', 'Article updated.');
    }

    public function destroy(KbArticle $article)
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }
        $article->delete();

        return redirect()->route('kb.index')->with('success', 'Article deleted.');
    }
}
