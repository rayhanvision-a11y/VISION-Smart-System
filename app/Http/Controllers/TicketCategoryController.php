<?php

namespace App\Http\Controllers;

use App\Models\TicketCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TicketCategoryController extends Controller
{
    public function index()
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->isNoc()) {
            abort(403, 'Unauthorized access');
        }

        $categories = TicketCategory::orderBy('name')->get();
        return view('ticket_categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->isNoc()) {
            abort(403, 'Unauthorized access');
        }

        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:ticket_categories,name',
            'description' => 'nullable|string|max:1000',
            'color'       => 'nullable|string|max:30',
        ]);

        $slug = Str::slug($validated['name'], '_');
        // Ensure unique slug
        $count = TicketCategory::where('slug', $slug)->count();
        if ($count > 0) {
            $slug .= '_' . time();
        }

        TicketCategory::create([
            'name'        => $validated['name'],
            'slug'        => $slug,
            'description' => $validated['description'] ?? null,
            'color'       => $validated['color'] ?? '#4f46e5',
            'is_active'   => true,
        ]);

        return redirect()->back()->with('success', __('Ticket category created successfully!'));
    }

    public function update(Request $request, TicketCategory $ticketCategory)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->isNoc()) {
            abort(403, 'Unauthorized access');
        }

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255', Rule::unique('ticket_categories', 'name')->ignore($ticketCategory->id)],
            'description' => 'nullable|string|max:1000',
            'color'       => 'nullable|string|max:30',
            'is_active'   => 'nullable|boolean',
        ]);

        $ticketCategory->update([
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'color'       => $validated['color'] ?? $ticketCategory->color,
            'is_active'   => $request->has('is_active') ? $request->boolean('is_active') : $ticketCategory->is_active,
        ]);

        return redirect()->back()->with('success', __('Ticket category updated successfully!'));
    }

    public function destroy(TicketCategory $ticketCategory)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->isNoc()) {
            abort(403, 'Unauthorized access');
        }

        $ticketCategory->delete();

        return redirect()->back()->with('success', __('Ticket category deleted successfully!'));
    }
}
