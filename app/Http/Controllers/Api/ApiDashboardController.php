<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiDashboardController extends Controller
{
    /**
     * Get aggregated dashboard stats, duty counts, and recent tickets.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Base ticket query scoped for user
        $baseQuery = Ticket::forUser($user);

        $now = now()->toDateTimeString();
        $aggregated = (clone $baseQuery)->selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'waiting_for_customer_feedback' THEN 1 ELSE 0 END) as waiting_for_customer_feedback,
            SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved,
            SUM(CASE WHEN priority = 'urgent' AND status != 'resolved' THEN 1 ELSE 0 END) as urgent,
            SUM(CASE WHEN due_at IS NOT NULL AND due_at < '{$now}' AND status != 'resolved' THEN 1 ELSE 0 END) as overdue
        ")->first();

        $stats = [
            'total' => (int) ($aggregated->total ?? 0),
            'in_progress' => (int) ($aggregated->in_progress ?? 0),
            'pending' => (int) ($aggregated->pending ?? 0),
            'waiting_for_customer_feedback' => (int) ($aggregated->waiting_for_customer_feedback ?? 0),
            'resolved' => (int) ($aggregated->resolved ?? 0),
            'urgent' => (int) ($aggregated->urgent ?? 0),
            'overdue' => (int) ($aggregated->overdue ?? 0),
        ];

        // Active Team Duty calculations
        $teams = User::TEAMS;
        $allStaff = User::whereNotNull('team')->get();
        $dutyTeams = [];

        foreach ($teams as $teamKey => $teamLabel) {
            $tUsers = $allStaff->filter(fn ($u) => $u->team === $teamKey);
            $tot = $tUsers->count();
            $act = $tUsers->filter(fn ($u) => $u->isOnDuty())->count();
            $dayShift = $tUsers->where('current_shift', 'day_shift')->count();
            $nightShift = $tUsers->where('current_shift', 'night_shift')->count();
            $dayOff = $tUsers->where('current_shift', 'day_off')->count();
            $pct = $tot > 0 ? (int) round(($act / $tot) * 100) : 0;

            $dutyTeams[] = [
                'team_key' => $teamKey,
                'team_label' => $teamLabel,
                'total_members' => $tot,
                'active_on_duty' => $act,
                'duty_percentage' => $pct,
                'day_shift' => $dayShift,
                'night_shift' => $nightShift,
                'day_off' => $dayOff,
            ];
        }

        // Recent 10 tickets
        $recentTickets = (clone $baseQuery)
            ->with(['user:id,name,email', 'assignedTo:id,name,email'])
            ->latest()
            ->take(10)
            ->get();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'team' => $user->team,
            ],
            'stats' => $stats,
            'duty_teams' => $dutyTeams,
            'recent_tickets' => $recentTickets,
        ]);
    }
}
