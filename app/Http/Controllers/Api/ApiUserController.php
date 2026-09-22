<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiUserController extends Controller
{
    /**
     * Get searchable directory of users (staff & resellers).
     * Only accessible to Admins & Super Admins.
     */
    public function index(Request $request): JsonResponse
    {
        $currentUser = $request->user();

        // Only Admin / Super Admin can access full user list
        if (! $currentUser->isAdmin()) {
            return response()->json(['error' => 'Forbidden: Admins only'], 403);
        }

        $query = User::query();

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter by team
        if ($request->filled('team')) {
            $query->where('team', $request->team);
        }

        // Search name, email, or phone
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('name')
            ->select(['id', 'name', 'email', 'phone', 'role', 'team', 'current_shift', 'is_active', 'avatar'])
            ->paginate(30);

        return response()->json($users);
    }
}
