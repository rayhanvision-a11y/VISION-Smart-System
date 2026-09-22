<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class RosterController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Get team selection
        $selectedTeam = $request->get('team');
        $search = $request->get('search');

        // Query non-reseller staff members by default (or all active users)
        $query = User::where('role', '!=', 'reseller')->where('is_active', true);

        if ($selectedTeam) {
            $query->where('team', $selectedTeam);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('role', 'like', "%{$search}%");
            });
        }

        $today = now()->toDateString();
        $allUsers = $query->orderBy('name')->get();

        // Ensure daily shift reset for all users
        foreach ($allUsers as $u) {
            if ($u->shift_date !== $today) {
                $u->forceFill([
                    'current_shift' => 'unassigned',
                    'shift_date' => $today,
                ])->save();
            }
        }

        // Group by current_shift
        $grouped = [
            'unassigned' => $allUsers->whereIn('current_shift', ['unassigned', null]),
            'day_shift' => $allUsers->where('current_shift', 'day_shift'),
            'night_shift' => $allUsers->where('current_shift', 'night_shift'),
            'day_off' => $allUsers->where('current_shift', 'day_off'),
        ];

        $shifts = [
            'unassigned' => [
                'label' => __('All Staff'),
                'time' => __('Unassigned Pool'),
                'color' => 'indigo',
                'icon' => '👥',
            ],
            'day_shift' => [
                'label' => __('Day Shift'),
                'time' => '9:00 AM - 6:00 PM',
                'color' => 'amber',
                'icon' => '☀️',
            ],
            'night_shift' => [
                'label' => __('Night Shift'),
                'time' => '2:00 PM - 10:00 PM',
                'color' => 'orange',
                'icon' => '🌙',
            ],
            'day_off' => [
                'label' => __('Day Off'),
                'time' => __('Off Duty'),
                'color' => 'violet',
                'icon' => '🏖️',
            ],
        ];

        $teams = User::TEAMS;

        return view('roster.index', compact('grouped', 'shifts', 'teams', 'selectedTeam', 'search', 'allUsers'));
    }

    public function updateShift(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'shift' => 'required|in:unassigned,day_shift,night_shift,day_off',
        ]);

        $user = User::findOrFail($request->user_id);
        $user->update([
            'current_shift' => $request->shift,
            'shift_date' => now()->toDateString(),
        ]);

        return response()->json([
            'status' => 'ok',
            'user_id' => $user->id,
            'shift' => $user->current_shift,
            'is_on_duty' => $user->isOnDuty(),
        ]);
    }

    public function updateTeam(Request $request, User $user)
    {
        $request->validate([
            'team' => 'nullable|string|max:100',
        ]);

        $user->update(['team' => $request->team]);

        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok', 'team' => $user->team]);
        }

        return back()->with('success', __('User team updated successfully.'));
    }
}
