<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\KbCategory;
use Illuminate\Http\Request;

class KbCategoryController extends Controller
{
    private function adminOnly()
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }
    }

    public function index()
    {
        $this->adminOnly();
        $categories = KbCategory::orderBy('name')->get();

        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $this->adminOnly();

        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:kb_categories,name',
            'description' => 'nullable|string|max:500',
        ]);

        $category = KbCategory::create([
            'name' => $validated['name'],
            'slug' => KbCategory::generateSlug($validated['name']),
            'description' => $validated['description'] ?? null,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'category' => $category, 'message' => __('Category created successfully!')]);
        }

        return redirect()->back()->with('status', __('Category created successfully!'));
    }

    public function update(Request $request, KbCategory $category)
    {
        $this->adminOnly();

        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:kb_categories,name,'.$category->id,
            'description' => 'nullable|string|max:500',
        ]);

        $oldName = $category->name;
        $newName = $validated['name'];

        $category->update([
            'name' => $newName,
            'slug' => KbCategory::generateSlug($newName),
            'description' => $validated['description'] ?? null,
        ]);

        // Update posts using old category name
        if ($oldName !== $newName) {
            BlogPost::where('category', $oldName)->update(['category' => $newName]);
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'category' => $category, 'message' => __('Category updated successfully!')]);
        }

        return redirect()->back()->with('status', __('Category updated successfully!'));
    }

    public function destroy(Request $request, KbCategory $category)
    {
        $this->adminOnly();

        $categoryName = $category->name;
        $category->delete();

        // Optionally reassign posts with deleted category to 'General'
        BlogPost::where('category', $categoryName)->update(['category' => 'General']);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => __('Category deleted successfully.')]);
        }

        return redirect()->back()->with('status', __('Category deleted successfully.'));
    }
}
