<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use App\Models\UserLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiLocationController extends Controller
{
    /**
     * Technician pushes own location.
     */
    public function updateOwn(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|integer|min:0|max:10000',
            'battery' => 'nullable|integer|min:0|max:100',
            'speed' => 'nullable|numeric|min:0|max:200',
        ]);

        $user = $request->user();

        $loc = UserLocation::updateOrCreate(
            ['user_id' => $user->id],
            [
                'latitude' => $validated['lat'],
                'longitude' => $validated['lng'],
                'accuracy_meters' => $validated['accuracy'] ?? null,
                'battery_level' => $validated['battery'] ?? null,
                'speed_mps' => $validated['speed'] ?? null,
                'is_sharing' => true,
            ]
        );

        // Trail — throttle to 1 entry per 2 minutes
        $lastHistory = \App\Models\UserLocation::query()
            ->from('user_location_history')
            ->where('user_id', $user->id)
            ->latest('recorded_at')
            ->value('recorded_at');
        if (! $lastHistory || strtotime($lastHistory) < strtotime('-2 minutes')) {
            \DB::table('user_location_history')->insert([
                'user_id' => $user->id,
                'latitude' => $validated['lat'],
                'longitude' => $validated['lng'],
                'recorded_at' => now(),
            ]);
        }

        return response()->json([
            'message' => 'Location saved',
            'updated_at' => $loc->updated_at->toDateTimeString(),
        ]);
    }

    /**
     * Toggle location sharing on/off.
     */
    public function toggleSharing(Request $request): JsonResponse
    {
        $validated = $request->validate(['sharing' => 'required|boolean']);
        $user = $request->user();
        UserLocation::updateOrCreate(
            ['user_id' => $user->id],
            ['latitude' => 0, 'longitude' => 0, 'is_sharing' => $validated['sharing']]
        );

        return response()->json(['sharing' => (bool) $validated['sharing']]);
    }

    /**
     * Admin/Super Admin fetches all trackable staff locations.
     */
    public function all(Request $request): JsonResponse
    {
        $actor = $request->user();
        if (! $actor || ! $actor->isAdmin()) {
            return response()->json(['error' => 'Not authorized. Only Admin & Super Admin can view staff locations.'], 403);
        }

        $roleFilter = $request->input('role');
        $query = User::where('is_active', true)
            ->whereNotIn('role', ['reseller', 'call_center']);
        if ($roleFilter) {
            $query->where('role', $roleFilter);
        }

        $users = $query->with('userLocation')->get()->filter(function ($u) {
            return $u->userLocation !== null && (float) $u->userLocation->latitude != 0 && (float) $u->userLocation->longitude != 0;
        });

        $items = $users->map(function ($u) {
            $loc = $u->userLocation;
            $ageMins = $loc->updated_at ? now()->diffInMinutes($loc->updated_at) : null;
            $activeTickets = Ticket::where('assigned_to', $u->id)
                ->whereNotIn('status', ['resolved', 'closed'])->count();
            $status = 'offline';
            if ($loc->is_sharing) {
                if ($ageMins !== null && $ageMins < 5) $status = 'online';
                elseif ($ageMins !== null && $ageMins < 30) $status = 'idle';
            }

            return [
                'user_id' => $u->id,
                'name' => $u->name,
                'phone' => $u->phone,
                'role' => $u->role,
                'team' => $u->team,
                'avatar_url' => $u->avatarUrl(),
                'is_on_duty' => $u->isOnDuty(),
                'is_sharing' => (bool) $loc->is_sharing,
                'latitude' => (float) $loc->latitude,
                'longitude' => (float) $loc->longitude,
                'accuracy_meters' => $loc->accuracy_meters,
                'battery_level' => $loc->battery_level,
                'speed_mps' => $loc->speed_mps,
                'active_ticket_count' => $activeTickets,
                'last_seen_minutes' => $ageMins,
                'status' => $status,
                'updated_at' => $loc->updated_at?->toDateTimeString(),
            ];
        })->values();

        return response()->json(['locations' => $items]);
    }

    /**
     * History trail for one user (last N hours).
     */
    public function history(Request $request, int $userId): JsonResponse
    {
        $actor = $request->user();
        if (! $actor || ! $actor->isAdmin()) {
            return response()->json(['error' => 'Not authorized'], 403);
        }
        $hours = min(72, (int) $request->input('hours', 24));

        $points = \DB::table('user_location_history')
            ->where('user_id', $userId)
            ->where('recorded_at', '>=', now()->subHours($hours))
            ->orderBy('recorded_at')
            ->get(['latitude', 'longitude', 'recorded_at']);

        return response()->json(['points' => $points]);
    }
}
