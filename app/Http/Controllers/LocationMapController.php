<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Models\UserLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LocationMapController extends Controller
{
    /**
     * Display the Live Tracking Map view.
     */
    public function index(Request $request): View
    {
        $actor = $request->user() ?? auth()->user();
        if (! $actor || ! $actor->isAdmin()) {
            abort(403, 'Unauthorized access to Live Staff Map. Only Admin & Super Admin are permitted.');
        }

        $techniciansCount = User::where('is_active', true)
            ->whereNotIn('role', ['reseller', 'call_center'])
            ->count();

        return view('map.index', [
            'techniciansCount' => $techniciansCount,
        ]);
    }

    /**
     * Return live technician coordinates and profile data for the map.
     */
    public function data(Request $request): JsonResponse
    {
        $actor = $request->user() ?? auth()->user();
        if (! $actor || ! $actor->isAdmin()) {
            return response()->json(['error' => 'Not authorized. Only Admin & Super Admin can view live locations.'], 403);
        }

        $roleFilter = $request->input('role');
        $teamFilter = $request->input('team');
        $statusFilter = $request->input('status');

        $query = User::where('is_active', true)
            ->whereNotIn('role', ['reseller', 'call_center']);

        if ($roleFilter) {
            $query->where('role', $roleFilter);
        }
        if ($teamFilter) {
            $query->where('team', $teamFilter);
        }

        $users = $query->with(['userLocation'])->get();

        // 1. Fetch Today's Daily Technician Teams to get squad duty area for each user
        $todayTeams = \App\Models\DailyTechnicianTeam::whereDate('duty_date', today())
            ->whereNotNull('area')
            ->where('area', '!=', '')
            ->get(['area', 'team_name', 'category', 'leader_id', 'member_1_id', 'member_2_id']);
        
        $userSquadInfo = [];
        foreach ($todayTeams as $tm) {
            foreach ([$tm->leader_id, $tm->member_1_id, $tm->member_2_id] as $uid) {
                if ($uid && !isset($userSquadInfo[(int) $uid])) {
                    $userSquadInfo[(int) $uid] = [
                        'area' => $tm->area,
                        'team_name' => $tm->team_name,
                        'category' => $tm->category,
                        'is_leader' => (int) $tm->leader_id === (int) $uid,
                    ];
                }
            }
        }

        $userIds = $users->pluck('id');
        $todayStart = today();

        $activeTicketsByUser = Ticket::whereIn('assigned_to', $userIds)
            ->whereNotIn('status', ['resolved', 'closed'])
            ->select('id', 'ticket_key', 'title', 'priority', 'status', 'assigned_to', 'area')
            ->get()
            ->groupBy('assigned_to');

        // Solved today count by user
        $solvedTodayByUser = Ticket::whereIn('assigned_to', $userIds)
            ->whereIn('status', ['resolved', 'closed'])
            ->where('resolved_at', '>=', $todayStart)
            ->selectRaw('assigned_to, COUNT(*) as cnt')
            ->groupBy('assigned_to')
            ->pluck('cnt', 'assigned_to');

        // Total solved tickets (all time) by user
        $totalSolvedByUser = Ticket::whereIn('assigned_to', $userIds)
            ->whereIn('status', ['resolved', 'closed'])
            ->selectRaw('assigned_to, COUNT(*) as cnt')
            ->groupBy('assigned_to')
            ->pluck('cnt', 'assigned_to');

        // Total assigned tickets (all time) by user
        $totalTicketsByUser = Ticket::whereIn('assigned_to', $userIds)
            ->selectRaw('assigned_to, COUNT(*) as cnt')
            ->groupBy('assigned_to')
            ->pluck('cnt', 'assigned_to');

        $items = $users->map(function ($u) use ($activeTicketsByUser, $solvedTodayByUser, $totalSolvedByUser, $totalTicketsByUser, $userSquadInfo) {
            $loc = $u->userLocation;
            $hasLocation = $loc && $loc->latitude != 0 && $loc->longitude != 0;
            $ageMins = ($loc && $loc->updated_at) ? (int) max(0, now()->diffInMinutes($loc->updated_at)) : null;

            $status = 'offline';
            if ($hasLocation && $loc->is_sharing) {
                if ($ageMins !== null && $ageMins < 5) {
                    $status = 'online';
                } elseif ($ageMins !== null && $ageMins < 30) {
                    $status = 'idle';
                }
            }

            $userTickets = ($activeTicketsByUser->get($u->id) ?? collect())->map(function ($t) {
                return [
                    'id' => $t->id,
                    'key' => $t->ticket_key ?? ('#'.$t->id),
                    'title' => $t->title,
                    'priority' => $t->priority,
                    'status' => $t->status,
                    'area' => $t->area,
                    'url' => route('tickets.show', $t->id),
                ];
            });

            $squad = $userSquadInfo[(int) $u->id] ?? null;
            $ticketAreas = $userTickets->pluck('area')->filter()->unique()->values()->all();

            $realLoc = self::getPabnaRealLocation($hasLocation ? (float) $loc->latitude : null, $hasLocation ? (float) $loc->longitude : null);

            return [
                'user_id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'phone' => $u->phone,
                'role' => ucfirst(str_replace('_', ' ', $u->role)),
                'raw_role' => $u->role,
                'team' => $u->team ?? 'Technician',
                'squad_area' => $squad['area'] ?? null,
                'squad_name' => $squad['team_name'] ?? null,
                'is_squad_leader' => $squad['is_leader'] ?? false,
                'ticket_areas' => $ticketAreas,
                'avatar_url' => $u->avatarUrl(),
                'is_on_duty' => $u->isOnDuty(),
                'current_shift' => User::SHIFTS[$u->current_shift] ?? ($u->current_shift ?? 'Unassigned'),
                'is_sharing' => $loc ? (bool) $loc->is_sharing : false,
                'has_location' => $hasLocation,
                'latitude' => $hasLocation ? (float) $loc->latitude : null,
                'longitude' => $hasLocation ? (float) $loc->longitude : null,
                'real_area' => $hasLocation ? $realLoc['area'] : null,
                'real_address' => $hasLocation ? $realLoc['full_address'] : null,
                'accuracy_meters' => $loc?->accuracy_meters,
                'battery_level' => ($loc && $ageMins !== null && $ageMins < 60) ? $loc->battery_level : null,
                'speed_kmh' => $loc?->speed_mps ? round($loc->speed_mps * 3.6, 1) : 0,
                'status' => $status,
                'active_tickets_count' => $userTickets->count(),
                'total_tickets_count' => (int) ($totalTicketsByUser[$u->id] ?? 0),
                'solved_today_count' => (int) ($solvedTodayByUser[$u->id] ?? 0),
                'solved_total_count' => (int) ($totalSolvedByUser[$u->id] ?? 0),
                'active_tickets' => $userTickets->take(15)->values(),
                'last_seen_text' => $ageMins === null ? 'Never' : ($ageMins < 1 ? 'Just now' : ($ageMins < 60 ? "{$ageMins}m ago" : round($ageMins/60).'h ago')),
                'updated_at' => $loc?->updated_at?->toIso8601String(),
            ];
        });

        if ($statusFilter) {
            $items = $items->filter(fn($i) => $i['status'] === $statusFilter);
        }

        return response()->json([
            'technicians' => $items->values(),
            'counts' => [
                'all' => $items->count(),
                'online' => $items->where('status', 'online')->count(),
                'idle' => $items->where('status', 'idle')->count(),
                'offline' => $items->where('status', 'offline')->count(),
            ],
            'server_time' => now()->toIso8601String(),
        ]);
    }

    /**
     * History trail points for a single technician.
     */
    public function history(Request $request, int $userId): JsonResponse
    {
        $actor = $request->user() ?? auth()->user();
        if (! $actor || ! $actor->isAdmin()) {
            return response()->json(['error' => 'Not authorized'], 403);
        }

        $hours = min(48, (int) $request->input('hours', 12));

        $points = \DB::table('user_location_history')
            ->where('user_id', $userId)
            ->where('recorded_at', '>=', now()->subHours($hours))
            ->orderBy('recorded_at')
            ->get(['latitude', 'longitude', 'recorded_at']);

        $user = User::find($userId);

        return response()->json([
            'user' => [
                'id' => $user?->id,
                'name' => $user?->name,
                'avatar_url' => $user?->avatarUrl(),
            ],
            'points' => $points,
        ]);
    }

    /**
     * Web user updates their own browser location.
     */
    public function updateMyLocation(Request $request): JsonResponse
    {
        $actor = $request->user() ?? auth()->user();
        if (! $actor) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $validated = $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|integer|min:0|max:10000',
            'battery' => 'nullable|integer|min:0|max:100',
        ]);

        $updateFields = [
            'latitude' => $validated['lat'],
            'longitude' => $validated['lng'],
            'accuracy_meters' => $validated['accuracy'] ?? null,
            'is_sharing' => true,
            'updated_at' => now(),
        ];
        if (array_key_exists('battery', $validated) && $validated['battery'] !== null) {
            $updateFields['battery_level'] = $validated['battery'];
        }

        $loc = UserLocation::updateOrCreate(
            ['user_id' => $actor->id],
            $updateFields
        );

        \DB::table('user_location_history')->insert([
            'user_id' => $actor->id,
            'latitude' => $validated['lat'],
            'longitude' => $validated['lng'],
            'recorded_at' => now(),
        ]);

        return response()->json([
            'message' => 'Location updated successfully',
            'latitude' => (float) $loc->latitude,
            'longitude' => (float) $loc->longitude,
        ]);
    }

    /**
     * Resolve precise landmark and real location name in Pabna city.
     */
    public static function getPabnaRealLocation(?float $lat, ?float $lng): array
    {
        if (! $lat || ! $lng) {
            return [
                'area' => 'Location unavailable',
                'full_address' => 'No GPS Fix',
            ];
        }

        // 1. Arman Center & Gopalpur (Pabna town center, DC Road, Zilla School, Mohila College, Hospital Rd intersection)
        if ($lat >= 24.0010 && $lat <= 24.0055 && $lng >= 89.2320 && $lng <= 89.2378) {
            return [
                'area' => 'Arman Center, Gopalpur',
                'full_address' => 'Arman Center, Gopalpur, Pabna Sadar, Pabna',
            ];
        }

        // 2. Hospital Road / Abdul Hamid Road / Indira Mor
        if ($lat >= 24.0055 && $lat <= 24.0080 && $lng >= 89.2340 && $lng <= 89.2395) {
            return [
                'area' => 'Hospital Road, Gopalpur',
                'full_address' => 'Hospital Road, Gopalpur, Pabna Sadar, Pabna',
            ];
        }

        // 3. Radhanagar / Edward College Area
        if ($lat >= 24.0080 && $lat <= 24.0180 && $lng >= 89.2250 && $lng <= 89.2400) {
            return [
                'area' => 'Radhanagar',
                'full_address' => 'Radhanagar, Pabna Sadar, Pabna',
            ];
        }

        // 4. Dilalpur / Traffic Mor / Central Bus Terminal
        if ($lat >= 23.9950 && $lat <= 24.0020 && $lng >= 89.2360 && $lng <= 89.2500) {
            return [
                'area' => 'Dilalpur, Traffic Mor',
                'full_address' => 'Dilalpur, Traffic Mor, Pabna Sadar, Pabna',
            ];
        }

        // 5. Shalgaria / Jubilee Tank
        if ($lat >= 24.0020 && $lat <= 24.0150 && $lng >= 89.2480 && $lng <= 89.2650) {
            return [
                'area' => 'Shalgaria',
                'full_address' => 'Shalgaria, Pabna Sadar, Pabna',
            ];
        }

        // 6. Shadhupara (Geographic landmark area northeast of town)
        if ($lat >= 24.0150 && $lat <= 24.0300 && $lng >= 89.2400 && $lng <= 89.2600) {
            return [
                'area' => 'Shadhupara',
                'full_address' => 'Shadhupara, Pabna Sadar, Pabna',
            ];
        }

        // 7. Hemayetpur / Mental Hospital Area
        if ($lat >= 23.9900 && $lat <= 24.0100 && $lng >= 89.1800 && $lng <= 89.2200) {
            return [
                'area' => 'Hemayetpur',
                'full_address' => 'Hemayetpur, Pabna Sadar, Pabna',
            ];
        }

        return [
            'area' => 'Pabna Sadar',
            'full_address' => 'Pabna Sadar, Pabna',
        ];
    }
}

