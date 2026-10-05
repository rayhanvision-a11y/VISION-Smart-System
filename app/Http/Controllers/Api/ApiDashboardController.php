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

        $unreadCount = \App\Models\Notification::forUser($user)->where('is_read', false)->count();

        $stats = [
            'total' => (int) ($aggregated->total ?? 0),
            'in_progress' => (int) ($aggregated->in_progress ?? 0),
            'pending' => (int) ($aggregated->pending ?? 0),
            'waiting_for_customer_feedback' => (int) ($aggregated->waiting_for_customer_feedback ?? 0),
            'resolved' => (int) ($aggregated->resolved ?? 0),
            'urgent' => (int) ($aggregated->urgent ?? 0),
            'overdue' => (int) ($aggregated->overdue ?? 0),
            'unread_notifications' => $unreadCount,
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

        // ─── Today's Daily 3-Person Technician Teams (with leader & team ticket stats & ranking) ───
        $catSortWeights = ['complain' => 1, 'new_connection' => 2, 'line_transfer' => 3, 'transfer' => 3];
        $rawTodayTeams = \App\Models\DailyTechnicianTeam::with([
            'leader:id,name,email,phone,avatar',
            'member1:id,name,email,phone,avatar',
            'member2:id,name,email,phone,avatar',
        ])->whereDate('duty_date', today())
          ->get();

        // Calculate today's resolved and open tickets for leaders & members
        $allTeamUserIds = $rawTodayTeams->flatMap(fn($t) => [$t->leader_id, $t->member_1_id, $t->member_2_id])
            ->filter()->unique()->values();

        $openCounts = [];
        $doneCounts = [];
        if ($allTeamUserIds->isNotEmpty()) {
            $todayStart = today();
            $openCounts = \App\Models\Ticket::whereIn('assigned_to', $allTeamUserIds)
                ->whereNotIn('status', ['resolved', 'closed'])
                ->selectRaw('assigned_to, COUNT(*) as c')
                ->groupBy('assigned_to')->pluck('c', 'assigned_to');

            $doneCounts = \App\Models\Ticket::whereIn('assigned_to', $allTeamUserIds)
                ->whereIn('status', ['resolved', 'closed'])
                ->where('resolved_at', '>=', $todayStart)
                ->selectRaw('assigned_to, COUNT(*) as c')
                ->groupBy('assigned_to')->pluck('c', 'assigned_to');
        }

        // Attach stats and sort by solved tickets descending (so top performers are #1)
        $todayTeams = $rawTodayTeams->map(function ($t) use ($doneCounts, $openCounts) {
            $leaderDone = (int) ($doneCounts[$t->leader_id] ?? 0);
            $leaderOpen = (int) ($openCounts[$t->leader_id] ?? 0);
            $m1Done = (int) ($doneCounts[$t->member_1_id] ?? 0);
            $m1Open = (int) ($openCounts[$t->member_1_id] ?? 0);
            $m2Done = (int) ($doneCounts[$t->member_2_id] ?? 0);
            $m2Open = (int) ($openCounts[$t->member_2_id] ?? 0);

            $teamDone = $leaderDone + $m1Done + $m2Done;
            $teamOpen = $leaderOpen + $m1Open + $m2Open;

            $t->leader_done_count = $leaderDone;
            $t->leader_open_count = $leaderOpen;
            $t->team_done_count = $teamDone;
            $t->team_open_count = $teamOpen;
            return $t;
        })->sortBy([
            fn($a, $b) => ($b->team_done_count ?? 0) <=> ($a->team_done_count ?? 0),
            fn($a, $b) => ($b->leader_done_count ?? 0) <=> ($a->leader_done_count ?? 0),
            fn($a, $b) => ($catSortWeights[$a->category] ?? 99) <=> ($catSortWeights[$b->category] ?? 99),
            fn($a, $b) => $a->id <=> $b->id,
        ])->values();

        $formatTeam = function ($t, $rank = null) use ($user) {
            if (!$t) return null;
            $meta = $t->getCategoryMeta();
            $isLeader = (int)$t->leader_id === (int)$user->id;
            return [
                'id' => $t->id,
                'rank' => $rank,
                'is_champion' => $rank === 1 && ($t->team_done_count ?? 0) > 0,
                'duty_date' => $t->duty_date->format('Y-m-d'),
                'category' => $t->category,
                'category_label' => $meta['label'] ?? $t->category,
                'category_label_bn' => $meta['label_bn'] ?? ($meta['label'] ?? $t->category),
                'team_name' => $t->team_name,
                'area' => $t->area,
                'vehicle_no' => $t->vehicle_no,
                'notes' => $t->notes,
                'leader_done_count' => (int) ($t->leader_done_count ?? 0),
                'leader_open_count' => (int) ($t->leader_open_count ?? 0),
                'team_done_count' => (int) ($t->team_done_count ?? 0),
                'team_open_count' => (int) ($t->team_open_count ?? 0),
                'is_leader' => $isLeader,
                'is_member' => !$isLeader && ((int)$t->member_1_id === (int)$user->id || (int)$t->member_2_id === (int)$user->id),
                'leader' => $t->leader ? [
                    'id' => $t->leader->id,
                    'name' => $t->leader->name,
                    'phone' => $t->leader->phone,
                    'email' => $t->leader->email,
                    'avatar' => $t->leader->avatar,
                ] : null,
                'member_1' => $t->member1 ? [
                    'id' => $t->member1->id,
                    'name' => $t->member1->name,
                    'phone' => $t->member1->phone,
                    'email' => $t->member1->email,
                    'avatar' => $t->member1->avatar,
                ] : null,
                'member_2' => $t->member2 ? [
                    'id' => $t->member2->id,
                    'name' => $t->member2->name,
                    'phone' => $t->member2->phone,
                    'email' => $t->member2->email,
                    'avatar' => $t->member2->avatar,
                ] : null,
            ];
        };

        $myRawTeam = $todayTeams->first(fn ($t) => $t->hasUser($user->id));
        $myTechnicianTeam = $myRawTeam ? $formatTeam($myRawTeam, $todayTeams->search(fn($t) => $t->id === $myRawTeam->id) + 1) : null;
        $todayTechnicianTeams = $todayTeams->map(fn ($t, $idx) => $formatTeam($t, $idx + 1))->values();

        // ─── Daily Summary & Leaderboard (Today, Previous Day, Month-to-Date) ───
        $buildSummary = function (string $date) use ($user) {
            $dayStart = $date.' 00:00:00';
            $dayEnd = $date.' 23:59:59';
            $rows = \App\Models\Ticket::forUser($user)
                ->whereIn('status', ['resolved', 'closed'])
                ->whereBetween('resolved_at', [$dayStart, $dayEnd])
                ->whereNotNull('assigned_to')
                ->selectRaw('category, assigned_to, COUNT(*) as cnt')
                ->groupBy('category', 'assigned_to')
                ->get();

            // Map user_id → squad category for the day
            $teams = \App\Models\DailyTechnicianTeam::whereDate('duty_date', $date)
                ->get(['category', 'team_name', 'leader_id', 'member_1_id', 'member_2_id']);
            $userToSquadCat = [];
            foreach ($teams as $t) {
                foreach ([$t->leader_id, $t->member_1_id, $t->member_2_id] as $uid) {
                    if ($uid) $userToSquadCat[(int) $uid] = $t->category;
                }
            }

            $names = \App\Models\User::whereIn('id', $rows->pluck('assigned_to')->unique())->pluck('name', 'id');
            $allCats = \App\Models\DailyTechnicianTeam::allCategories();

            $regrouped = [];
            foreach ($rows as $r) {
                $effectiveCat = $userToSquadCat[(int) $r->assigned_to] ?? ($r->category ?: 'uncategorized');
                $regrouped[$effectiveCat] ??= [];
                $regrouped[$effectiveCat][] = [
                    'user_id' => (int) $r->assigned_to,
                    'name' => $names[$r->assigned_to] ?? '—',
                    'count' => (int) $r->cnt,
                ];
            }

            $grouped = collect($regrouped)->map(function ($items, $key) use ($allCats) {
                $byUser = collect($items)->groupBy('user_id')->map(fn ($g) => [
                    'user_id' => $g->first()['user_id'],
                    'name' => $g->first()['name'],
                    'count' => (int) $g->sum('count'),
                ])->values();

                $rawLabel = $allCats[$key]['label'] ?? ucwords(str_replace('_', ' ', $key));
                $teamLabel = !str_ends_with(strtoupper($rawLabel), 'TEAM')
                    ? strtoupper($rawLabel) . ' TEAM'
                    : strtoupper($rawLabel);

                return [
                    'key' => $key,
                    'label' => $teamLabel,
                    'raw_label' => $rawLabel,
                    'icon' => $allCats[$key]['icon'] ?? '🔧',
                    'total' => (int) $byUser->sum('count'),
                    'items' => $byUser->sortByDesc('count')->values()->all(),
                ];
            })->sortByDesc(fn ($g) => $g['total'])->values();

            return [
                'date' => $date,
                'formatted_date' => \Carbon\Carbon::parse($date)->format('d · M · Y'),
                'day_total' => (int) $rows->sum('cnt'),
                'categories' => $grouped->all(),
            ];
        };

        $summaryToday = $buildSummary(today()->toDateString());
        $summaryPrev = $buildSummary(today()->subDay()->toDateString());
        $summaryMonthLabel = now()->format('M / Y');
        $summaryMonthTotal = \App\Models\Ticket::forUser($user)
            ->whereIn('status', ['resolved', 'closed'])
            ->whereBetween('resolved_at', [now()->startOfMonth()->toDateTimeString(), now()->endOfDay()->toDateTimeString()])
            ->count();

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
            'my_technician_team' => $myTechnicianTeam,
            'today_technician_teams' => $todayTechnicianTeams,
            'recent_tickets' => $recentTickets,
            'summary_today' => $summaryToday,
            'summary_prev' => $summaryPrev,
            'summary_month' => [
                'label' => $summaryMonthLabel,
                'total' => (int) $summaryMonthTotal,
            ],
            'daily_summary' => [
                'date' => $summaryToday['date'],
                'day_total' => $summaryToday['day_total'],
                'month_total' => (int) $summaryMonthTotal,
                'month_label' => $summaryMonthLabel,
                'categories' => $summaryToday['categories'],
            ],
        ]);
    }

    /**
     * Get all technician teams for today or filtered by date
     */
    public function todayTeams(Request $request): JsonResponse
    {
        $user = $request->user();
        $date = $request->input('date', today()->toDateString());

        $rawTeams = \App\Models\DailyTechnicianTeam::with([
            'leader:id,name,email,phone,avatar',
            'member1:id,name,email,phone,avatar',
            'member2:id,name,email,phone,avatar',
        ])->whereDate('duty_date', $date)->get();

        $allUserIds = $rawTeams->flatMap(fn($t) => [$t->leader_id, $t->member_1_id, $t->member_2_id])
            ->filter()->unique()->values();

        $openCounts = [];
        $doneCounts = [];
        if ($allUserIds->isNotEmpty()) {
            $dayStart = $date.' 00:00:00';
            $dayEnd = $date.' 23:59:59';
            $openCounts = \App\Models\Ticket::whereIn('assigned_to', $allUserIds)
                ->whereNotIn('status', ['resolved', 'closed'])
                ->selectRaw('assigned_to, COUNT(*) as c')
                ->groupBy('assigned_to')->pluck('c', 'assigned_to');

            $doneCounts = \App\Models\Ticket::whereIn('assigned_to', $allUserIds)
                ->whereIn('status', ['resolved', 'closed'])
                ->whereBetween('resolved_at', [$dayStart, $dayEnd])
                ->selectRaw('assigned_to, COUNT(*) as c')
                ->groupBy('assigned_to')->pluck('c', 'assigned_to');
        }

        $catSortWeights = ['complain' => 1, 'new_connection' => 2, 'line_transfer' => 3, 'transfer' => 3];
        $teams = $rawTeams->map(function ($t) use ($doneCounts, $openCounts) {
            $leaderDone = (int) ($doneCounts[$t->leader_id] ?? 0);
            $leaderOpen = (int) ($openCounts[$t->leader_id] ?? 0);
            $m1Done = (int) ($doneCounts[$t->member_1_id] ?? 0);
            $m1Open = (int) ($openCounts[$t->member_1_id] ?? 0);
            $m2Done = (int) ($doneCounts[$t->member_2_id] ?? 0);
            $m2Open = (int) ($openCounts[$t->member_2_id] ?? 0);

            $t->leader_done_count = $leaderDone;
            $t->leader_open_count = $leaderOpen;
            $t->team_done_count = $leaderDone + $m1Done + $m2Done;
            $t->team_open_count = $leaderOpen + $m1Open + $m2Open;
            return $t;
        })->sortBy([
            fn($a, $b) => ($b->team_done_count ?? 0) <=> ($a->team_done_count ?? 0),
            fn($a, $b) => ($b->leader_done_count ?? 0) <=> ($a->leader_done_count ?? 0),
            fn($a, $b) => ($catSortWeights[$a->category] ?? 99) <=> ($catSortWeights[$b->category] ?? 99),
            fn($a, $b) => $a->id <=> $b->id,
        ])->values();

        $formatTeam = function ($t, $rank = null) use ($user) {
            $meta = $t->getCategoryMeta();
            $isLeader = (int)$t->leader_id === (int)$user->id;
            return [
                'id' => $t->id,
                'rank' => $rank,
                'is_champion' => $rank === 1 && ($t->team_done_count ?? 0) > 0,
                'duty_date' => $t->duty_date->format('Y-m-d'),
                'category' => $t->category,
                'category_label' => $meta['label'] ?? $t->category,
                'category_label_bn' => $meta['label_bn'] ?? ($meta['label'] ?? $t->category),
                'team_name' => $t->team_name,
                'area' => $t->area,
                'vehicle_no' => $t->vehicle_no,
                'notes' => $t->notes,
                'leader_done_count' => (int) ($t->leader_done_count ?? 0),
                'leader_open_count' => (int) ($t->leader_open_count ?? 0),
                'team_done_count' => (int) ($t->team_done_count ?? 0),
                'team_open_count' => (int) ($t->team_open_count ?? 0),
                'is_leader' => $isLeader,
                'is_member' => !$isLeader && ((int)$t->member_1_id === (int)$user->id || (int)$t->member_2_id === (int)$user->id),
                'leader' => $t->leader ? [
                    'id' => $t->leader->id,
                    'name' => $t->leader->name,
                    'phone' => $t->leader->phone,
                    'email' => $t->leader->email,
                ] : null,
                'member_1' => $t->member1 ? [
                    'id' => $t->member1->id,
                    'name' => $t->member1->name,
                    'phone' => $t->member1->phone,
                    'email' => $t->member1->email,
                ] : null,
                'member_2' => $t->member2 ? [
                    'id' => $t->member2->id,
                    'name' => $t->member2->name,
                    'phone' => $t->member2->phone,
                    'email' => $t->member2->email,
                ] : null,
            ];
        };

        $myTeam = $teams->first(fn ($t) => $t->hasUser($user->id));
        $myRank = $myTeam ? $teams->search(fn($t) => $t->id === $myTeam->id) + 1 : null;

        return response()->json([
            'date' => $date,
            'my_team' => $myTeam ? $formatTeam($myTeam, $myRank) : null,
            'teams' => $teams->map(fn ($t, $idx) => $formatTeam($t, $idx + 1))->values(),
        ]);
    }
}
