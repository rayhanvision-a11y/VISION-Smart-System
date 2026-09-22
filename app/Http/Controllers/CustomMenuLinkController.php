<?php

namespace App\Http\Controllers;

use App\Models\CustomMenuLink;
use Illuminate\Http\Request;

class CustomMenuLinkController extends Controller
{
    /**
     * Display custom menu links management list.
     */
    public function index()
    {
        $this->authorizeAdmin();

        $links = CustomMenuLink::orderBy('sort_order')->orderBy('id', 'desc')->get();

        return view('custom_menu_links.index', compact('links'));
    }

    /**
     * Store a newly created custom menu link.
     */
    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|string|max:500',
            'icon' => 'nullable|string|max:100',
            'is_important' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'open_in_new_tab' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        CustomMenuLink::create([
            'name' => $validated['name'],
            'url' => $validated['url'],
            'icon' => $validated['icon'] ?? 'link',
            'is_important' => $request->has('is_important') ? (bool) $request->is_important : false,
            'is_active' => $request->has('is_active') ? (bool) $request->is_active : true,
            'open_in_new_tab' => $request->has('open_in_new_tab') ? (bool) $request->open_in_new_tab : true,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->route('custom-menu-links.index')->with('success', __('Custom menu link created successfully!'));
    }

    /**
     * Update the specified custom menu link.
     */
    public function update(Request $request, CustomMenuLink $customMenuLink)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|string|max:500',
            'icon' => 'nullable|string|max:100',
            'is_important' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'open_in_new_tab' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $customMenuLink->update([
            'name' => $validated['name'],
            'url' => $validated['url'],
            'icon' => $validated['icon'] ?? 'link',
            'is_important' => $request->boolean('is_important'),
            'is_active' => $request->boolean('is_active'),
            'open_in_new_tab' => $request->boolean('open_in_new_tab'),
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->route('custom-menu-links.index')->with('success', __('Custom menu link updated successfully!'));
    }

    /**
     * Toggle the "Important" status.
     */
    public function toggleImportant(CustomMenuLink $customMenuLink)
    {
        $this->authorizeAdmin();

        $customMenuLink->update([
            'is_important' => ! $customMenuLink->is_important,
        ]);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_important' => $customMenuLink->is_important,
            ]);
        }

        return redirect()->back()->with('success', __('Important status toggled!'));
    }

    /**
     * Toggle the "Active" status.
     */
    public function toggleActive(CustomMenuLink $customMenuLink)
    {
        $this->authorizeAdmin();

        $customMenuLink->update([
            'is_active' => ! $customMenuLink->is_active,
        ]);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_active' => $customMenuLink->is_active,
            ]);
        }

        return redirect()->back()->with('success', __('Active status toggled!'));
    }

    /**
     * Remove the specified custom menu link.
     */
    public function destroy(CustomMenuLink $customMenuLink)
    {
        $this->authorizeAdmin();

        $customMenuLink->delete();

        return redirect()->route('custom-menu-links.index')->with('success', __('Custom menu link deleted successfully!'));
    }

    /**
     * Helper to ensure strictly Super Admin can manage links.
     */
    private function authorizeAdmin(): void
    {
        if (! auth()->user()->isSuperAdminOnly()) {
            abort(403, 'Unauthorized access. Only Super Admin can manage Important URLs.');
        }
    }
}
