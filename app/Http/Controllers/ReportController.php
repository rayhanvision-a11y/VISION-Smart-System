<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\TicketMessage;
use App\Models\TicketMessageReaction;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        [$from, $to] = $this->periodDates($request);

        $tab = $request->get('tab', 'team');
        $selectedTeam = $request->get('team_name');
        $personId = $request->get('person_id');

        // --- Dynamic Teams list (from teams table + distinct user teams) ---
        $allTeams = \Schema::hasTable('teams')
            ? \App\Models\Team::where('is_active', true)->orderBy('name')->pluck('name')->toArray()
            : array_values(\App\Models\User::TEAMS);

        $userTeams = User::whereNotNull('team')->where('team', '!=', '')->distinct()->pluck('team')->toArray();
        $teamsList = array_values(array_unique(array_filter(array_merge($allTeams, $userTeams))));
        sort($teamsList);

        // --- Team members query ---
        $teamUsersQuery = User::where('role', '!=', 'reseller');
        if ($selectedTeam) {
            $teamUsersQuery->where('team', $selectedTeam);
        }
        $teamUsers = $teamUsersQuery->orderBy('name')->get();
        $teamMembers = $this->bulkMemberStats($teamUsers, $from, $to);

        // --- Resellers ---
        $resellerUsers = User::where('role', 'reseller')->orderBy('name')->get();
        $resellers = $this->bulkMemberStats($resellerUsers, $from, $to);

        // --- Selected person drill-down ---
        $selectedPerson = null;
        $personTickets = collect();
        $personChartDays = [];
        $personChartCounts = [];

        if ($personId) {
            $person = User::find($personId);
            if ($person) {
                $personStatsArr = $this->bulkMemberStats(collect([$person]), $from, $to)->first() ?? [];
                $selectedPerson = array_merge($personStatsArr, [
                    'email' => $person->email,
                    'phone' => $person->phone ?? null,
                    'team' => $person->team ?? null,
                ]);

                if (!$selectedTeam && !empty($person->team)) {
                    $selectedTeam = $person->team;
                }

                // Base closure for reuse
                $baseQuery = fn () => $tab === 'reseller'
                    ? Ticket::where('created_by', $person->id)
                    : Ticket::where('assigned_to', $person->id);

                $personTickets = $baseQuery()
                    ->with(['creator', 'assignee'])
                    ->whereBetween('created_at', [$from, $to])
                    ->latest()->get();

                // Person trend chart
                $diff = $from->diffInDays($to);
                if ($diff <= 31) {
                    $pData = $baseQuery()
                        ->whereBetween('created_at', [$from, $to])
                        ->selectRaw('DATE(created_at) as date, count(*) as count')
                        ->groupBy('date')
                        ->pluck('count', 'date');

                    for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
                        $personChartDays[] = $d->format('d M');
                        $personChartCounts[] = (int) ($pData[$d->format('Y-m-d')] ?? 0);
                    }
                } elseif ($diff <= 92) {
                    $cur = $from->copy()->startOfWeek();
                    while ($cur->lte($to)) {
                        $wEnd = $cur->copy()->endOfWeek()->min($to);
                        $personChartDays[] = $cur->format('d M');
                        $personChartCounts[] = $baseQuery()->whereBetween('created_at', [$cur, $wEnd])->count();
                        $cur->addWeek();
                    }
                } else {
                    $cur = $from->copy()->startOfMonth();
                    while ($cur->lte($to)) {
                        $mEnd = $cur->copy()->endOfMonth()->min($to);
                        $personChartDays[] = $cur->format('M Y');
                        $personChartCounts[] = $baseQuery()->whereBetween('created_at', [$cur, $mEnd])->count();
                        $cur->addMonth();
                    }
                }
            }
        }

        // --- Team Performance stats if a specific team is selected ---
        $selectedTeamStats = null;
        if ($selectedTeam) {
            $teamUserIds = $teamUsers->pluck('id')->toArray();
            if (!empty($teamUserIds)) {
                $teamAgg = Ticket::whereIn('assigned_to', $teamUserIds)
                    ->whereBetween('created_at', [$from, $to])
                    ->selectRaw("
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                        SUM(CASE WHEN status = 'waiting_for_customer_feedback' THEN 1 ELSE 0 END) as waiting_for_customer_feedback,
                        SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved_all_time,
                        SUM(CASE WHEN status = 'resolved' AND resolved_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as resolved_period,
                        AVG(CASE WHEN resolved_at IS NOT NULL THEN TIMESTAMPDIFF(MINUTE, created_at, resolved_at) END) as avg_res
                    ", [$from, $to])->first();

                $tot = (int) ($teamAgg->total ?? 0);
                $res = (int) ($teamAgg->resolved_period ?? 0);
                $selectedTeamStats = [
                    'name' => $selectedTeam,
                    'members_count' => $teamUsers->count(),
                    'total' => $tot,
                    'in_progress' => (int) ($teamAgg->in_progress ?? 0),
                    'pending' => (int) ($teamAgg->pending ?? 0),
                    'waiting_for_customer_feedback' => (int) ($teamAgg->waiting_for_customer_feedback ?? 0),
                    'resolved' => $res,
                    'resolved_all_time' => (int) ($teamAgg->resolved_all_time ?? 0),
                    'rate' => $tot > 0 ? round(($res / $tot) * 100) : 0,
                    'resolution_rate' => $tot > 0 ? round(($res / $tot) * 100) : 0,
                    'avg_resolution_time' => $this->formatDuration($teamAgg->avg_res),
                    'avg_time' => $this->formatDuration($teamAgg->avg_res),
                ];
            } else {
                $selectedTeamStats = [
                    'name' => $selectedTeam,
                    'members_count' => 0,
                    'total' => 0,
                    'in_progress' => 0,
                    'pending' => 0,
                    'waiting_for_customer_feedback' => 0,
                    'resolved' => 0,
                    'resolved_all_time' => 0,
                    'rate' => 0,
                    'resolution_rate' => 0,
                    'avg_resolution_time' => 'N/A',
                    'avg_time' => 'N/A',
                ];
            }
            [$chartDays, $chartCounts] = $this->buildTrendChart($from, $to, $teamUserIds);
        } else {
            [$chartDays, $chartCounts] = $this->buildTrendChart($from, $to);
        }

        // --- All Teams Performance Overview (for leaderboard / comparison cards) ---
        $teamsPerformance = collect($teamsList)->map(function ($tName) use ($from, $to) {
            $members = User::where('team', $tName)->where('role', '!=', 'reseller')->get();
            $memberIds = $members->pluck('id')->toArray();

            $tot = 0;
            $res = 0;
            $inProg = 0;
            $avgTime = 'N/A';

            if (!empty($memberIds)) {
                $agg = Ticket::whereIn('assigned_to', $memberIds)
                    ->whereBetween('created_at', [$from, $to])
                    ->selectRaw("
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved,
                        SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                        AVG(CASE WHEN resolved_at IS NOT NULL THEN TIMESTAMPDIFF(MINUTE, created_at, resolved_at) END) as avg_res
                    ")->first();

                $tot = (int) ($agg->total ?? 0);
                $res = (int) ($agg->resolved ?? 0);
                $inProg = (int) ($agg->in_progress ?? 0);
                $avgTime = $this->formatDuration($agg->avg_res);
            }

            $rate = $tot > 0 ? round(($res / $tot) * 100) : 0;

            return [
                'name' => $tName,
                'members_count' => $members->count(),
                'total' => $tot,
                'resolved' => $res,
                'in_progress' => $inProg,
                'rate' => $rate,
                'resolution_rate' => $rate,
                'avg_resolution_time' => $avgTime,
                'avg_time' => $avgTime,
            ];
        });

        // --- Period summary stats (Global or Team) ---
        $periodAgg = Ticket::selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'waiting_for_customer_feedback' THEN 1 ELSE 0 END) as waiting_for_customer_feedback,
            SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved_all_time,
            SUM(CASE WHEN status = 'resolved' AND resolved_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as resolved_period,
            AVG(CASE WHEN resolved_at IS NOT NULL THEN TIMESTAMPDIFF(MINUTE, created_at, resolved_at) END) as avg_res
        ", [$from, $to])->first();

        $periodStats = [
            'total' => (int) ($periodAgg->total ?? 0),
            'in_progress' => (int) ($periodAgg->in_progress ?? 0),
            'pending' => (int) ($periodAgg->pending ?? 0),
            'waiting_for_customer_feedback' => (int) ($periodAgg->waiting_for_customer_feedback ?? 0),
            'resolved' => (int) ($periodAgg->resolved_period ?? 0),
            'resolved_all_time' => (int) ($periodAgg->resolved_all_time ?? 0),
            'avg_resolution_time' => $this->formatDuration($periodAgg->avg_res),
        ];

        // --- Trend calculation ---
        $periodLengthDays = $from->diffInDays($to) ?: 1;
        $prevTo = $from->copy()->subSecond();
        $prevFrom = $prevTo->copy()->subDays($periodLengthDays)->startOfDay();
        $currentCreated = Ticket::whereBetween('created_at', [$from, $to])->count();
        $prevStats = [
            'created' => Ticket::whereBetween('created_at', [$prevFrom, $prevTo])->count(),
            'resolved' => Ticket::whereBetween('resolved_at', [$prevFrom, $prevTo])->where('status', 'resolved')->count(),
        ];
        $trend = [
            'total' => $this->pctChange($prevStats['created'], $currentCreated),
            'resolved' => $this->pctChange($prevStats['resolved'], $periodStats['resolved']),
        ];

        $period = $request->get('period', 'month');
        $dateFrom = $from->format('Y-m-d');
        $dateTo = $to->format('Y-m-d');

        return view('reports.index', compact(
            'tab', 'period', 'dateFrom', 'dateTo',
            'teamsList', 'selectedTeam', 'selectedTeamStats', 'teamsPerformance',
            'teamMembers', 'resellers',
            'periodStats', 'chartDays', 'chartCounts', 'trend',
            'selectedPerson', 'personTickets',
            'personChartDays', 'personChartCounts', 'personId'
        ));
    }

    public function downloadPdf(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }
        [$from, $to] = $this->periodDates($request);

        $personId = $request->get('person_id');
        $person = $personId ? User::find($personId) : null;
        $teamName = $request->get('team_name');

        if ($person) {
            $personStats = $this->bulkMemberStats(collect([$person]), $from, $to)->first();
            $tickets = ($person->isReseller() ? Ticket::where('created_by', $person->id) : Ticket::where('assigned_to', $person->id))
                ->with(['creator', 'assignee'])
                ->whereBetween('created_at', [$from, $to])
                ->latest()
                ->get();

            $teamStr = !empty($person->team) ? " | Team: {$person->team}" : "";
            $filters = "Person: {$person->name} ({$person->role}){$teamStr} | Period: {$from->format('d M Y')} — {$to->format('d M Y')}";
            $pdf = Pdf::loadView('reports.pdf', compact('tickets', 'filters', 'person', 'personStats'))->setPaper('a4', 'landscape');
            $safeName = Str::slug($person->name);

            return $pdf->download("report-{$safeName}-".now()->format('Y-m-d').'.pdf');
        }

        if ($teamName) {
            $teamUsers = User::where('team', $teamName)->where('role', '!=', 'reseller')->orderBy('name')->get();
            $teamMembers = $this->bulkMemberStats($teamUsers, $from, $to);
            $teamUserIds = $teamUsers->pluck('id')->toArray();
            $tickets = Ticket::whereIn('assigned_to', $teamUserIds)
                ->with(['creator', 'assignee'])
                ->whereBetween('created_at', [$from, $to])
                ->latest()
                ->get();

            $teamStats = [
                'total' => $tickets->count(),
                'in_progress' => $tickets->where('status', 'in_progress')->count(),
                'resolved' => $tickets->where('status', 'resolved')->count(),
                'closed' => $tickets->where('status', 'closed')->count(),
                'pending' => $tickets->where('status', 'pending')->count(),
                'members_count' => $teamUsers->count(),
                'rate' => $tickets->count() > 0 ? round(($tickets->where('status', 'resolved')->count() / $tickets->count()) * 100) : 0,
            ];

            $filters = "Team: {$teamName} ({$teamUsers->count()} members) | Period: {$from->format('d M Y')} — {$to->format('d M Y')}";
            $pdf = Pdf::loadView('reports.pdf', compact('tickets', 'filters', 'teamName', 'teamStats', 'teamMembers'))->setPaper('a4', 'landscape');
            $safeName = Str::slug($teamName);

            return $pdf->download("team-report-{$safeName}-".now()->format('Y-m-d').'.pdf');
        }

        $tickets = Ticket::with(['creator', 'assignee'])->whereBetween('created_at', [$from, $to])->latest()->get();
        $stats = [
            'total' => $tickets->count(),
            'open' => $tickets->where('status', 'open')->count(),
            'in_progress' => $tickets->where('status', 'in_progress')->count(),
            'resolved' => $tickets->where('status', 'resolved')->count(),
            'closed' => $tickets->where('status', 'closed')->count(),
        ];
        $filters = "Period: {$from->format('d M Y')} — {$to->format('d M Y')}";
        $pdf = Pdf::loadView('reports.pdf', compact('tickets', 'stats', 'filters'))->setPaper('a4', 'landscape');

        return $pdf->download('ticket-report-'.now()->format('Y-m-d').'.pdf');
    }

    public function downloadExcel(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }
        [$from, $to] = $this->periodDates($request);

        $personId = $request->get('person_id');
        $person = $personId ? User::find($personId) : null;
        $teamName = $request->get('team_name');

        $csvEscape = function ($value): string {
            $value = (string) $value;
            if ($value !== '' && in_array($value[0], ['=', '+', '-', '@'], true)) {
                $value = "\t".$value;
            }

            return $value;
        };

        if ($person) {
            $personStats = $this->bulkMemberStats(collect([$person]), $from, $to)->first();
            $tickets = ($person->isReseller() ? Ticket::where('created_by', $person->id) : Ticket::where('assigned_to', $person->id))
                ->with(['creator', 'assignee', 'popOffice'])
                ->whereBetween('created_at', [$from, $to])
                ->latest()
                ->get();

            $safeName = Str::slug($person->name);
            $filename = "report-{$safeName}-".now()->format('Y-m-d').'.csv';

            $callback = function () use ($tickets, $person, $personStats, $csvEscape, $from, $to) {
                $h = fopen('php://output', 'w');
                fputcsv($h, ['SINGLE PERSON PERFORMANCE REPORT']);
                fputcsv($h, ['Name', $person->name]);
                fputcsv($h, ['Role', strtoupper(str_replace('_', ' ', $person->role))]);
                if (!empty($person->team)) {
                    fputcsv($h, ['Team', $person->team]);
                }
                fputcsv($h, ['Email', $person->email]);
                fputcsv($h, ['Period', $from->format('Y-m-d').' to '.$to->format('Y-m-d')]);
                fputcsv($h, []);
                fputcsv($h, ['METRICS SUMMARY']);
                fputcsv($h, ['Created Tickets', $personStats['created'] ?? 0]);
                fputcsv($h, ['Assigned Tickets', $personStats['assigned'] ?? 0]);
                fputcsv($h, ['Transferred Tickets', $personStats['transferred'] ?? 0]);
                fputcsv($h, ['Comments Made', $personStats['comments'] ?? 0]);
                fputcsv($h, ['Reactions Given', $personStats['reactions'] ?? 0]);
                fputcsv($h, ['Resolved Tickets', $personStats['resolved'] ?? 0]);
                fputcsv($h, ['In Progress', $personStats['in_progress'] ?? 0]);
                fputcsv($h, ['Pending', $personStats['pending'] ?? 0]);
                fputcsv($h, ['Resolution Rate', ($personStats['rate'] ?? 0).'%']);
                fputcsv($h, ['Avg Resolution Time', $personStats['avg_resolution_time'] ?? 'N/A']);
                fputcsv($h, []);
                fputcsv($h, ['TICKETS DETAIL']);
                fputcsv($h, ['ID', 'Title', 'Category', 'Priority', 'Status', 'Created By', 'Assigned To', 'Created At', 'Resolved At']);
                foreach ($tickets as $t) {
                    fputcsv($h, [
                        '#'.$t->id,
                        $csvEscape($t->title),
                        $csvEscape(ucfirst(str_replace('_', ' ', $t->category))),
                        $csvEscape(ucfirst($t->priority)),
                        $csvEscape(ucfirst(str_replace('_', ' ', $t->status))),
                        $csvEscape($t->creator->name ?? 'N/A'),
                        $csvEscape($t->assignee->name ?? 'Unassigned'),
                        $t->created_at->format('Y-m-d H:i'),
                        $t->resolved_at ? $t->resolved_at->format('Y-m-d H:i') : '',
                    ]);
                }
                fclose($h);
            };

            return response()->stream($callback, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ]);
        }

        if ($teamName) {
            $teamUsers = User::where('team', $teamName)->where('role', '!=', 'reseller')->orderBy('name')->get();
            $teamMembers = $this->bulkMemberStats($teamUsers, $from, $to);
            $teamUserIds = $teamUsers->pluck('id')->toArray();
            $tickets = Ticket::whereIn('assigned_to', $teamUserIds)
                ->with(['creator', 'assignee', 'popOffice'])
                ->whereBetween('created_at', [$from, $to])
                ->latest()
                ->get();

            $safeName = Str::slug($teamName);
            $filename = "team-report-{$safeName}-".now()->format('Y-m-d').'.csv';

            $callback = function () use ($tickets, $teamName, $teamUsers, $teamMembers, $csvEscape, $from, $to) {
                $h = fopen('php://output', 'w');
                fputcsv($h, ['TEAM PERFORMANCE REPORT']);
                fputcsv($h, ['Team Name', $teamName]);
                fputcsv($h, ['Total Members', $teamUsers->count()]);
                fputcsv($h, ['Total Tickets', $tickets->count()]);
                fputcsv($h, ['Resolved Tickets', $tickets->where('status', 'resolved')->count()]);
                fputcsv($h, ['Period', $from->format('Y-m-d').' to '.$to->format('Y-m-d')]);
                fputcsv($h, []);
                fputcsv($h, ['TEAM MEMBERS BREAKDOWN']);
                fputcsv($h, ['Member Name', 'Role', 'Assigned Tickets', 'Resolved Tickets', 'Resolution Rate', 'Avg Resolution Time']);
                foreach ($teamMembers as $tm) {
                    fputcsv($h, [
                        $tm['name'],
                        strtoupper(str_replace('_', ' ', $tm['role'])),
                        $tm['assigned'],
                        $tm['resolved'],
                        $tm['rate'].'%',
                        $tm['avg_resolution_time'],
                    ]);
                }
                fputcsv($h, []);
                fputcsv($h, ['TICKETS DETAIL']);
                fputcsv($h, ['ID', 'Title', 'Category', 'Priority', 'Status', 'Created By', 'Assigned To', 'Created At', 'Resolved At']);
                foreach ($tickets as $t) {
                    fputcsv($h, [
                        '#'.$t->id,
                        $csvEscape($t->title),
                        $csvEscape(ucfirst(str_replace('_', ' ', $t->category))),
                        $csvEscape(ucfirst($t->priority)),
                        $csvEscape(ucfirst(str_replace('_', ' ', $t->status))),
                        $csvEscape($t->creator->name ?? 'N/A'),
                        $csvEscape($t->assignee->name ?? 'Unassigned'),
                        $t->created_at->format('Y-m-d H:i'),
                        $t->resolved_at ? $t->resolved_at->format('Y-m-d H:i') : '',
                    ]);
                }
                fclose($h);
            };

            return response()->stream($callback, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ]);
        }

        $tickets = Ticket::with(['creator', 'assignee', 'popOffice'])->whereBetween('created_at', [$from, $to])->latest()->get();
        $filename = 'ticket-report-'.now()->format('Y-m-d').'.csv';

        $callback = function () use ($tickets, $csvEscape) {
            $h = fopen('php://output', 'w');
            fputcsv($h, ['ID', 'Title', 'Category', 'Priority', 'Status', 'Created By', 'Assigned To', 'Created At', 'Resolved At']);
            foreach ($tickets as $t) {
                fputcsv($h, [
                    '#'.$t->id,
                    $csvEscape($t->title),
                    $csvEscape(ucfirst(str_replace('_', ' ', $t->category))),
                    $csvEscape(ucfirst($t->priority)),
                    $csvEscape(ucfirst(str_replace('_', ' ', $t->status))),
                    $csvEscape($t->creator->name ?? 'N/A'),
                    $csvEscape($t->assignee->name ?? 'Unassigned'),
                    $t->created_at->format('Y-m-d H:i'),
                    $t->resolved_at ? $t->resolved_at->format('Y-m-d H:i') : '',
                ]);
            }
            fclose($h);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    // ── helpers ──────────────────────────────────────────────

    private function bulkMemberStats($users, Carbon $from, Carbon $to): Collection
    {
        $userIds = $users->pluck('id');
        if ($userIds->isEmpty()) {
            return collect();
        }

        $assignedStats = Ticket::whereIn('assigned_to', $userIds)
            ->selectRaw("
                assigned_to,
                COUNT(*) as total,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'waiting_for_customer_feedback' THEN 1 ELSE 0 END) as waiting,
                SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved,
                AVG(CASE WHEN status = 'resolved' AND resolved_at IS NOT NULL THEN TIMESTAMPDIFF(MINUTE, created_at, resolved_at) END) as avg_res
            ")
            ->groupBy('assigned_to')
            ->get()
            ->keyBy('assigned_to');

        $createdStats = Ticket::whereIn('created_by', $userIds)
            ->selectRaw("
                created_by,
                COUNT(*) as total,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'waiting_for_customer_feedback' THEN 1 ELSE 0 END) as waiting,
                SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved,
                AVG(CASE WHEN status = 'resolved' AND resolved_at IS NOT NULL THEN TIMESTAMPDIFF(MINUTE, created_at, resolved_at) END) as avg_res
            ")
            ->groupBy('created_by')
            ->get()
            ->keyBy('created_by');

        $periodCreated = Ticket::whereIn('created_by', $userIds)
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('created_by, COUNT(*) as count')
            ->groupBy('created_by')
            ->pluck('count', 'created_by');

        $transferredStats = TicketHistory::whereIn('changed_by', $userIds)
            ->whereBetween('created_at', [$from, $to])
            ->where(function ($q) {
                $q->whereNotNull('old_assignee_id')
                    ->orWhereNotNull('new_assignee_id')
                    ->orWhere('action', 'LIKE', '%assigned%')
                    ->orWhere('action', 'LIKE', '%reassigned%')
                    ->orWhere('action', 'LIKE', '%transferred%');
            })
            ->selectRaw('changed_by, COUNT(*) as count')
            ->groupBy('changed_by')
            ->pluck('count', 'changed_by');

        $commentsStats = TicketMessage::whereIn('sender_id', $userIds)
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('sender_id, COUNT(*) as count')
            ->groupBy('sender_id')
            ->pluck('count', 'sender_id');

        $reactionsStats = TicketMessageReaction::whereIn('user_id', $userIds)
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('user_id, COUNT(*) as count')
            ->groupBy('user_id')
            ->pluck('count', 'user_id');

        return $users->map(function ($u) use ($assignedStats, $createdStats, $periodCreated, $transferredStats, $commentsStats, $reactionsStats) {
            $isReseller = $u->isReseller();
            $stats = $isReseller ? ($createdStats[$u->id] ?? null) : ($assignedStats[$u->id] ?? null);

            $total = (int) ($stats->total ?? 0);
            $inProg = (int) ($stats->in_progress ?? 0);
            $pending = (int) ($stats->pending ?? 0);
            $waiting = (int) ($stats->waiting ?? 0);
            $resolved = (int) ($stats->resolved ?? 0);

            $created = (int) ($periodCreated[$u->id] ?? 0);
            $assigned = $isReseller ? 0 : $total;
            $transferred = (int) ($transferredStats[$u->id] ?? 0);
            $comments = (int) ($commentsStats[$u->id] ?? 0);
            $reactions = (int) ($reactionsStats[$u->id] ?? 0);

            $rate = ($isReseller ? $created : $assigned) > 0
                ? round($resolved / ($isReseller ? $created : $assigned) * 100) : 0;

            $avgMin = $stats ? $stats->avg_res : null;

            return [
                'id' => $u->id,
                'name' => $u->name,
                'role' => $u->role,
                'team' => $u->team ?? null,
                'avatarUrl' => $u->avatarUrl(),
                'total' => $total,
                'created' => $created,
                'assigned' => $assigned,
                'transferred' => $transferred,
                'comments' => $comments,
                'reactions' => $reactions,
                'in_progress' => $inProg,
                'pending' => $pending,
                'waiting' => $waiting,
                'resolved' => $resolved,
                'rate' => $rate,
                'avg_resolution_time' => $this->formatDuration($avgMin),
            ];
        });
    }

    private function memberStats(User $u, Carbon $from, Carbon $to): array
    {
        return $this->bulkMemberStats(collect([$u]), $from, $to)->first();
    }

    private function formatDuration(?float $minutes): string
    {
        if (! $minutes || $minutes <= 0) {
            return 'N/A';
        }
        $m = (int) round($minutes);
        $d = floor($m / 1440);
        $h = floor(($m % 1440) / 60);
        $mins = $m % 60;
        if ($d > 0) {
            return "{$d}d {$h}h";
        }
        if ($h > 0) {
            return "{$h}h {$mins}m";
        }

        return "{$mins}m";
    }

    private function buildTrendChart(Carbon $from, Carbon $to, ?array $assignedUserIds = null): array
    {
        $days = [];
        $counts = [];
        $diff = $from->diffInDays($to);

        $filterUsers = function ($query) use ($assignedUserIds) {
            if (!empty($assignedUserIds)) {
                $query->whereIn('assigned_to', $assignedUserIds);
            }
            return $query;
        };

        if ($diff <= 31) {
            $q = Ticket::whereBetween('created_at', [$from, $to]);
            $filterUsers($q);
            $tData = $q->selectRaw('DATE(created_at) as date, count(*) as count')
                ->groupBy('date')
                ->pluck('count', 'date');

            for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
                $days[] = $d->format('d M');
                $counts[] = (int) ($tData[$d->format('Y-m-d')] ?? 0);
            }
        } elseif ($diff <= 92) {
            $cur = $from->copy()->startOfWeek();
            while ($cur->lte($to)) {
                $wEnd = $cur->copy()->endOfWeek()->min($to);
                $days[] = $cur->format('d M');
                $q = Ticket::whereBetween('created_at', [$cur, $wEnd]);
                $filterUsers($q);
                $counts[] = $q->count();
                $cur->addWeek();
            }
        } else {
            $cur = $from->copy()->startOfMonth();
            while ($cur->lte($to)) {
                $mEnd = $cur->copy()->endOfMonth()->min($to);
                $days[] = $cur->format('M Y');
                $q = Ticket::whereBetween('created_at', [$cur, $mEnd]);
                $filterUsers($q);
                $counts[] = $q->count();
                $cur->addMonth();
            }
        }

        return [$days, $counts];
    }

    private function pctChange(int $prev, int $current): ?float
    {
        if ($prev === 0) {
            return $current > 0 ? 100.0 : null;
        }

        return round((($current - $prev) / $prev) * 100, 1);
    }

    private function periodDates(Request $request): array
    {
        $period = $request->get('period', 'month');
        if ($period === 'custom') {
            $from = Carbon::parse($request->get('date_from', now()->subMonth()))->startOfDay();
            $to = Carbon::parse($request->get('date_to', now()))->endOfDay();
        } else {
            $to = now()->endOfDay();
            $from = match ($period) {
                'today' => now()->startOfDay(),
                'week' => now()->subWeek()->startOfDay(),
                'month' => now()->subMonth()->startOfDay(),
                '3months' => now()->subMonths(3)->startOfDay(),
                '6months' => now()->subMonths(6)->startOfDay(),
                'year' => now()->subYear()->startOfDay(),
                default => now()->subMonth()->startOfDay(),
            };
        }

        return [$from, $to];
    }
}
