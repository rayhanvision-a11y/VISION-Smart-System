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
     */
    public function index(Request $request): JsonResponse
    {
        $teams = User::TEAMS;
        $users = User::whereNotNull('team')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone', 'role', 'team', 'current_shift', 'shift_date', 'avatar']);

        $roster = [];
        foreach ($teams as $teamKey => $teamLabel) {
            $teamMembers = $users->filter(fn ($u) => $u->team === $teamKey)->values()->map(function ($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'phone' => $u->phone,
                    'role' => $u->role,
                    'team' => $u->team,
                    'current_shift' => $u->current_shift,
                    'is_on_duty' => $u->isOnDuty(),
                    'avatar' => $u->avatar,
                ];
            });

            $roster[] = [
                'team_key' => $teamKey,
                'team_label' => $teamLabel,
                'total' => $teamMembers->count(),
                'on_duty_count' => $teamMembers->filter(fn ($m) => $m['is_on_duty'])->count(),
                'members' => $teamMembers,
            ];
        }

        return response()->json([
            'roster' => $roster,
        ]);
    }
}
