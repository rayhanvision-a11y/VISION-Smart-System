<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiRosterController extends Controller
{
    /**
     * Get team rosters grouped by team with on-duty status.
     * Includes technicians as a separate group and untagged staff as "Other Staff".
     */
    public function index(Request $request): JsonResponse
    {
        $teams = \Schema::hasTable('teams')
            ? \App\Models\Team::where('is_active', true)->orderBy('name')->pluck('name', 'name')->toArray()
            : User::TEAMS;

        // All active non-reseller staff (includes technicians)
        $users = User::where('role', '!=', 'reseller')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone', 'role', 'team', 'current_shift', 'shift_date', 'avatar']);

        $shape = function ($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'phone' => $u->phone,
                'role' => $u->role,
                'team' => $u->team,
                'current_shift' => $u->current_shift,
                'is_on_duty' => $u->isOnDuty(),
                'avatar' => $u->avatarUrl(),
            ];
        };

        $roster = [];

        // 1. Team-based groups
        foreach ($teams as $teamKey => $teamLabel) {
            $members = $users->filter(fn ($u) => $u->team === $teamKey)->values()->map($shape);
            $roster[] = [
                'team_key' => $teamKey,
                'team_label' => $teamLabel,
                'total' => $members->count(),
                'on_duty_count' => $members->filter(fn ($m) => $m['is_on_duty'])->count(),
                'members' => $members,
            ];
        }

        // 2. Technicians group (regardless of team)
        $techs = $users->filter(fn ($u) => $u->role === 'technician')->values()->map($shape);
        if ($techs->count() > 0) {
            $roster[] = [
                'team_key' => 'technicians',
                'team_label' => 'Technicians',
                'total' => $techs->count(),
                'on_duty_count' => $techs->filter(fn ($m) => $m['is_on_duty'])->count(),
                'members' => $techs,
            ];
        }

        // 3. Other staff without team (excluding technicians already listed)
        $others = $users->filter(fn ($u) => empty($u->team) && $u->role !== 'technician')->values()->map($shape);
        if ($others->count() > 0) {
            $roster[] = [
                'team_key' => 'other',
                'team_label' => 'Other Staff',
                'total' => $others->count(),
                'on_duty_count' => $others->filter(fn ($m) => $m['is_on_duty'])->count(),
                'members' => $others,
            ];
        }

        return response()->json(['roster' => $roster]);
    }

    /**
     * Update current user's shift (self-service duty toggle).
     */
    public function updateOwnShift(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'shift' => 'required|in:unassigned,day_shift,night_shift,day_off',
        ]);

        $user = $request->user();
        $user->update([
            'current_shift' => $validated['shift'],
            'shift_date' => now()->toDateString(),
        ]);

        return response()->json([
            'message' => 'Shift updated',
            'current_shift' => $user->current_shift,
            'is_on_duty' => $user->isOnDuty(),
        ]);
    }

    /**
     * Admin/Supervisor: update another user's shift.
     */
    public function updateUserShift(Request $request, int $userId): JsonResponse
    {
        $actor = $request->user();
        if (! $actor->isAdmin() && ! $actor->isSupervisorLevel()) {
            return response()->json(['error' => 'Not authorized'], 403);
        }

        $validated = $request->validate([
            'shift' => 'required|in:unassigned,day_shift,night_shift,day_off',
        ]);

        $target = User::findOrFail($userId);
        $target->update([
            'current_shift' => $validated['shift'],
            'shift_date' => now()->toDateString(),
        ]);

        return response()->json([
            'message' => 'Shift updated',
            'user_id' => $target->id,
            'current_shift' => $target->current_shift,
            'is_on_duty' => $target->isOnDuty(),
        ]);
    }
}
