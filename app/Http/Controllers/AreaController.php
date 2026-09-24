<?php

namespace App\Http\Controllers;

use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AreaController extends Controller
{
    private function guard(): void
    {
        $u = auth()->user();
        if (! $u->isAdmin() && ! $u->isNoc() && ! $u->isSupervisorLevel()) {
            abort(403, 'Unauthorized');
        }
    }

    public function index()
    {
        $this->guard();
        $areas = Area::orderBy('name')->get();

        return view('areas.index', compact('areas'));
    }

    public function store(Request $request)
    {
        $this->guard();
        $validated = $request->validate([
            'name' => 'required|string|max:120|unique:areas,name',
        ]);
        Area::create(['name' => trim($validated['name']), 'is_active' => true]);

        return redirect()->back()->with('success', __('Area added.'));
    }

    public function update(Request $request, Area $area)
    {
        $this->guard();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('areas', 'name')->ignore($area->id)],
            'is_active' => 'nullable|boolean',
        ]);
        $area->update([
            'name' => trim($validated['name']),
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : $area->is_active,
        ]);

        return redirect()->back()->with('success', __('Area updated.'));
    }

    public function destroy(Area $area)
    {
        $this->guard();
        $area->delete();

        return redirect()->back()->with('success', __('Area deleted.'));
    }
}
