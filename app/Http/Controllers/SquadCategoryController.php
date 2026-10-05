<?php

namespace App\Http\Controllers;

use App\Models\SquadCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SquadCategoryController extends Controller
{
    private function guard(): void
    {
        $u = auth()->user();
        if (! $u || (! $u->isAdmin() && ! $u->isSupervisorLevel())) {
            abort(403);
        }
    }

    public function index()
    {
        $this->guard();

        $categories = SquadCategory::orderBy('sort_order')->orderBy('label')->get();

        return view('squad_categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $this->guard();

        $validated = $request->validate([
            'key' => 'nullable|string|max:60|unique:squad_categories,key',
            'label' => 'required|string|max:120',
            'label_bn' => 'nullable|string|max:120',
            'icon' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:20',
            'badge' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['key'] = $validated['key'] ?: Str::slug($validated['label'], '_');
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = $validated['sort_order'] ?? 100;

        SquadCategory::create($validated);

        return redirect()->route('squad-categories.index')->with('success', __('Squad category created.'));
    }

    public function update(Request $request, SquadCategory $squadCategory)
    {
        $this->guard();

        $validated = $request->validate([
            'label' => 'required|string|max:120',
            'label_bn' => 'nullable|string|max:120',
            'icon' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:20',
            'badge' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', false);
        $validated['sort_order'] = $validated['sort_order'] ?? $squadCategory->sort_order;

        $squadCategory->update($validated);

        return redirect()->route('squad-categories.index')->with('success', __('Squad category updated.'));
    }

    public function destroy(SquadCategory $squadCategory)
    {
        $this->guard();

        // Prevent deleting a category still in use
        $inUse = \App\Models\DailyTechnicianTeam::where('category', $squadCategory->key)->exists();
        if ($inUse) {
            return redirect()->route('squad-categories.index')
                ->with('error', __('Cannot delete: category is used by one or more teams. Deactivate it instead.'));
        }

        $squadCategory->delete();

        return redirect()->route('squad-categories.index')->with('success', __('Squad category deleted.'));
    }
}
