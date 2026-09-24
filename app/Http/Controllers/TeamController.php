<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TeamController extends Controller
{
    private function guard(): void
    {
        $u = auth()->user();
        if (! $u->isAdmin() && ! $u->isSupervisorLevel()) {
            abort(403, 'Unauthorized');
        }
    }

    public function index()
    {
        $this->guard();
        $teams = Team::orderBy('name')->get();

        return view('teams.index', compact('teams'));
    }

    public function store(Request $request)
    {
        $this->guard();
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:teams,name',
        ]);
        Team::create(['name' => trim($validated['name']), 'is_active' => true]);

        return redirect()->back()->with('success', __('Team added.'));
    }

    public function update(Request $request, Team $team)
    {
        $this->guard();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('teams', 'name')->ignore($team->id)],
            'is_active' => 'nullable|boolean',
        ]);
        $oldName = $team->name;
        $team->update([
            'name' => trim($validated['name']),
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : $team->is_active,
        ]);
        // Sync existing users if team was renamed
        if ($oldName !== $team->name) {
            User::where('team', $oldName)->update(['team' => $team->name]);
        }

        return redirect()->back()->with('success', __('Team updated.'));
    }

    public function destroy(Team $team)
    {
        $this->guard();
        $team->delete();

        return redirect()->back()->with('success', __('Team deleted.'));
    }
}
