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

        $activeTicketsByUser = Ticket::whereIn('assigned_to', $users->pluck('id'))
            ->whereNotIn('status', ['resolved', 'closed'])
            ->select('id', 'ticket_key', 'title', 'priority', 'status', 'assigned_to', 'area')
            ->get()
            ->groupBy('assigned_to');

        $items = $users->map(function ($u) use ($activeTicketsByUser) {
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

            return [
                'user_id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'phone' => $u->phone,
                'role' => ucfirst(str_replace('_', ' ', $u->role)),
                'raw_role' => $u->role,
                'team' => $u->team ?? 'Unassigned Team',
                'avatar_url' => $u->avatarUrl(),
                'is_on_duty' => $u->isOnDuty(),
                'current_shift' => User::SHIFTS[$u->current_shift] ?? ($u->current_shift ?? 'Unassigned'),
                'is_sharing' => $loc ? (bool) $loc->is_sharing : false,
                'has_location' => $hasLocation,
                'latitude' => $hasLocation ? (float) $loc->latitude : null,
                'longitude' => $hasLocation ? (float) $loc->longitude : null,
                'accuracy_meters' => $loc?->accuracy_meters,
                'battery_level' => $loc?->battery_level,
                'speed_kmh' => $loc?->speed_mps ? round($loc->speed_mps * 3.6, 1) : null,
                'status' => $status,
                'active_tickets_count' => $userTickets->count(),
                'active_tickets' => $userTickets->take(5)->values(),
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
        ]);

        $loc = UserLocation::updateOrCreate(
            ['user_id' => $actor->id],
            [
                'latitude' => $validated['lat'],
                'longitude' => $validated['lng'],
                'accuracy_meters' => $validated['accuracy'] ?? null,
                'is_sharing' => true,
                'updated_at' => now(),
            ]
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
}
