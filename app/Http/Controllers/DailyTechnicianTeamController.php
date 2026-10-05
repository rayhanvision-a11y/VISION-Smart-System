<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\DailyTechnicianTeam;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DailyTechnicianTeamController extends Controller
{
    private function guardManage(): void
    {
        $u = auth()->user();
        if (! $u || (! $u->isAdmin() && ! $u->isSupervisorLevel() && ! $u->isNoc())) {
            abort(403, 'Unauthorized');
        }
    }

    /**
     * Ensure selected users are technicians AND not already assigned to another
     * team on the same duty_date. $ignoreTeamId is used when updating.
     */
    private function assertTechniciansAndUnique(array $validated, ?int $ignoreTeamId = null): void
    {
        $ids = collect([$validated['leader_id'], $validated['member_1_id'] ?? null, $validated['member_2_id'] ?? null])
            ->filter()->map(fn ($i) => (int) $i)->unique()->values();

        $nonTechnicianIds = User::whereIn('id', $ids)
            ->where('role', '!=', 'technician')
            ->pluck('name', 'id');
        if ($nonTechnicianIds->isNotEmpty()) {
            throw ValidationException::withMessages([
                'leader_id' => __('Only technicians can be assigned to a Field Squad. Not allowed: :names', [
                    'names' => $nonTechnicianIds->implode(', '),
                ]),
            ]);
        }

        $busyQuery = DailyTechnicianTeam::whereDate('duty_date', $validated['duty_date'])
            ->where(function ($q) use ($ids) {
                $q->whereIn('leader_id', $ids)
                    ->orWhereIn('member_1_id', $ids)
                    ->orWhereIn('member_2_id', $ids);
            });
        if ($ignoreTeamId) {
            $busyQuery->where('id', '!=', $ignoreTeamId);
        }
        $busyTeams = $busyQuery->with(['leader', 'member1', 'member2'])->get();

        $conflicts = [];
        foreach ($busyTeams as $t) {
            foreach ([$t->leader, $t->member1, $t->member2] as $u) {
                if ($u && $ids->contains($u->id)) {
                    $conflicts[$u->id] = $u->name . ' (' . $t->team_name . ')';
                }
            }
        }
        if (! empty($conflicts)) {
            throw ValidationException::withMessages([
                'leader_id' => __('These technicians are already in another team today: :names', [
                    'names' => implode(', ', $conflicts),
                ]),
            ]);
        }
    }

    public function index(Request $request)
    {
        $rawDate = $request->get('date', now()->format('Y-m-d'));
        try {
            $selectedDate = Carbon::parse($rawDate)->format('Y-m-d');
        } catch (\Exception $e) {
            $selectedDate = now()->format('Y-m-d');
        }

        $teams = DailyTechnicianTeam::with(['leader', 'member1', 'member2', 'creator'])
            ->whereDate('duty_date', $selectedDate)
            ->latest('id')
            ->get();

        // Only technicians can be assigned to field squads
        $eligibleStaff = User::where('role', 'technician')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $areas = Area::where('is_active', true)->orderBy('name')->pluck('name');

        // Group teams by category for easy display
        $groupedTeams = $teams->groupBy('category');

        $categories = DailyTechnicianTeam::allCategories();

        return view('daily_technician_teams.index', compact(
            'selectedDate',
            'teams',
            'groupedTeams',
            'eligibleStaff',
            'areas',
            'categories'
        ));
    }

    public function store(Request $request)
    {
        $this->guardManage();

        if ($request->input('category') === 'transfer') {
            $request->merge(['category' => 'line_transfer']);
        }

        $validated = $request->validate([
            'duty_date'   => 'required|date',
            'category'    => 'required|string|max:60',
            'team_name'   => 'nullable|string|max:100',
            'leader_id'   => 'required|exists:users,id',
            'member_1_id' => 'nullable|exists:users,id|different:leader_id',
            'member_2_id' => 'nullable|exists:users,id|different:leader_id|different:member_1_id',
            'area'        => 'nullable|string|max:120',
            'vehicle_no'  => 'nullable|string|max:60',
            'notes'       => 'nullable|string|max:1000',
        ]);

        $this->assertTechniciansAndUnique($validated);

        $category = $validated['category'];
        $dutyDate = $validated['duty_date'];
        $teamName = $validated['team_name'] ?: null;
        if (! $teamName) {
            $existingCountInCat = DailyTechnicianTeam::whereDate('duty_date', $dutyDate)
                ->where('category', $category)
                ->count();
            $catLabel = DailyTechnicianTeam::allCategories()[$category]['label'] ?? 'Team';
            $teamName = $catLabel . ' #' . ($existingCountInCat + 1);
        }

        $team = DailyTechnicianTeam::create([
            'duty_date'   => $dutyDate,
            'category'    => $category,
            'team_name'   => $teamName,
            'leader_id'   => $validated['leader_id'],
            'member_1_id' => $validated['member_1_id'] ?? null,
            'member_2_id' => $validated['member_2_id'] ?? null,
            'area'        => $validated['area'] ?? null,
            'vehicle_no'  => $validated['vehicle_no'] ?? null,
            'notes'       => $validated['notes'] ?? null,
            'created_by'  => auth()->id(),
        ]);

        $team->load(['leader', 'member1', 'member2']);
        $catMeta = $team->getCategoryMeta();

        $buildMemberMsg = function ($member, $leader, $catMeta) {
            $isBn = ($member->locale ?? 'en') === 'bn' || app()->getLocale() === 'bn';
            $cat = $isBn ? ($catMeta['label_bn'] ?? $catMeta['label']) : $catMeta['label'];
            return $isBn
                ? "আপনি \"{$leader->name}\" টিম লিডারের সাথে {$cat}-এ আছেন।"
                : "You are assigned to {$cat} with Team Leader \"{$leader->name}\".";
        };

        $buildLeaderMsg = function ($leader, $members, $catMeta) {
            $isBn = ($leader->locale ?? 'en') === 'bn' || app()->getLocale() === 'bn';
            $cat = $isBn ? ($catMeta['label_bn'] ?? $catMeta['label']) : $catMeta['label'];
            if ($isBn) {
                $msg = "আপনি আজকের {$cat}-এর টিম লিডার।";
                if ($members->isNotEmpty()) {
                    $msg .= " আপনার সাথে আছেন: " . $members->join(' ও ') . "।";
                }
            } else {
                $msg = "You are the Team Leader for today's {$cat}.";
                if ($members->isNotEmpty()) {
                    $msg .= " Members with you: " . $members->join(', ') . ".";
                }
            }
            return $msg;
        };

        // 1. Notify Member 1
        if ($team->member_1_id && $team->member1) {
            $msg = $buildMemberMsg($team->member1, $team->leader, $catMeta);
            NotificationService::send($team->member_1_id, $msg, null, false);
        }

        // 2. Notify Member 2
        if ($team->member_2_id && $team->member2) {
            $msg = $buildMemberMsg($team->member2, $team->leader, $catMeta);
            NotificationService::send($team->member_2_id, $msg, null, false);
        }

        // 3. Notify Team Leader
        if ($team->leader) {
            $assistants = collect([$team->member1?->name, $team->member2?->name])->filter();
            $leaderMsg = $buildLeaderMsg($team->leader, $assistants, $catMeta);
            NotificationService::send($team->leader_id, $leaderMsg, null, false);
        }

        return redirect()->route('technician-teams.index', ['date' => $validated['duty_date']])
            ->with('success', __('Team assigned successfully and notifications sent.'));
    }

    public function update(Request $request, DailyTechnicianTeam $dailyTeam)
    {
        $this->guardManage();

        if ($request->input('category') === 'transfer') {
            $request->merge(['category' => 'line_transfer']);
        }

        $validated = $request->validate([
            'duty_date'   => 'required|date',
            'category'    => 'required|string|max:60',
            'team_name'   => 'nullable|string|max:100',
            'leader_id'   => 'required|exists:users,id',
            'member_1_id' => 'nullable|exists:users,id|different:leader_id',
            'member_2_id' => 'nullable|exists:users,id|different:leader_id|different:member_1_id',
            'area'        => 'nullable|string|max:120',
            'vehicle_no'  => 'nullable|string|max:60',
            'notes'       => 'nullable|string|max:1000',
        ]);

        $this->assertTechniciansAndUnique($validated, $dailyTeam->id);

        $prevLeaderId = $dailyTeam->leader_id;
        $prevMember1Id = $dailyTeam->member_1_id;
        $prevMember2Id = $dailyTeam->member_2_id;

        $category = $validated['category'];
        $teamName = $validated['team_name'] ?: ($dailyTeam->team_name ?: (DailyTechnicianTeam::allCategories()[$category]['label'] ?? 'Team'));

        $dailyTeam->update([
            'duty_date'   => $validated['duty_date'],
            'category'    => $category,
            'team_name'   => $teamName,
            'leader_id'   => $validated['leader_id'],
            'member_1_id' => $validated['member_1_id'] ?? null,
            'member_2_id' => $validated['member_2_id'] ?? null,
            'area'        => $validated['area'] ?? null,
            'vehicle_no'  => $validated['vehicle_no'] ?? null,
            'notes'       => $validated['notes'] ?? null,
        ]);

        $dailyTeam->load(['leader', 'member1', 'member2']);
        $catMeta = $dailyTeam->getCategoryMeta();

        $buildMemberMsg = function ($member, $leader, $catMeta) {
            $isBn = ($member->locale ?? 'en') === 'bn' || app()->getLocale() === 'bn';
            $cat = $isBn ? ($catMeta['label_bn'] ?? $catMeta['label']) : $catMeta['label'];
            return $isBn
                ? "আপনি \"{$leader->name}\" টিম লিডারের সাথে {$cat}-এ আছেন।"
                : "You are assigned to {$cat} with Team Leader \"{$leader->name}\".";
        };

        $buildLeaderMsg = function ($leader, $members, $catMeta) {
            $isBn = ($leader->locale ?? 'en') === 'bn' || app()->getLocale() === 'bn';
            $cat = $isBn ? ($catMeta['label_bn'] ?? $catMeta['label']) : $catMeta['label'];
            if ($isBn) {
                $msg = "আপনি আজকের {$cat}-এর টিম লিডার।";
                if ($members->isNotEmpty()) {
                    $msg .= " আপনার সাথে আছেন: " . $members->join(' ও ') . "।";
                }
            } else {
                $msg = "You are the Team Leader for today's {$cat}.";
                if ($members->isNotEmpty()) {
                    $msg .= " Members with you: " . $members->join(', ') . ".";
                }
            }
            return $msg;
        };

        // Notify member 1 if changed or new
        if ($dailyTeam->member_1_id && $dailyTeam->member1 && ($dailyTeam->member_1_id !== $prevMember1Id || $dailyTeam->leader_id !== $prevLeaderId)) {
            $msg = $buildMemberMsg($dailyTeam->member1, $dailyTeam->leader, $catMeta);
            NotificationService::send($dailyTeam->member_1_id, $msg, null, false);
        }

        // Notify member 2 if changed or new
        if ($dailyTeam->member_2_id && $dailyTeam->member2 && ($dailyTeam->member_2_id !== $prevMember2Id || $dailyTeam->leader_id !== $prevLeaderId)) {
            $msg = $buildMemberMsg($dailyTeam->member2, $dailyTeam->leader, $catMeta);
            NotificationService::send($dailyTeam->member_2_id, $msg, null, false);
        }

        // Notify leader if leader changed
        if ($dailyTeam->leader && $dailyTeam->leader_id !== $prevLeaderId) {
            $assistants = collect([$dailyTeam->member1?->name, $dailyTeam->member2?->name])->filter();
            $leaderMsg = $buildLeaderMsg($dailyTeam->leader, $assistants, $catMeta);
            NotificationService::send($dailyTeam->leader_id, $leaderMsg, null, false);
        }

        return redirect()->route('technician-teams.index', ['date' => $validated['duty_date']])
            ->with('success', __('Team updated successfully.'));
    }

    public function destroy(DailyTechnicianTeam $dailyTeam)
    {
        $this->guardManage();
        $date = $dailyTeam->duty_date->format('Y-m-d');
        $dailyTeam->delete();

        return redirect()->route('technician-teams.index', ['date' => $date])
            ->with('success', __('Team deleted successfully.'));
    }
}
