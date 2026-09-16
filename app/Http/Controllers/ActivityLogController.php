<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->isAdmin()) abort(403);

        $query = ActivityLog::with('user')->latest();

        if ($search = $request->input('search')) {
            $query->where('description', 'like', "%{$search}%");
        }
        if ($action = $request->input('action')) {
            $query->where('action', $action);
        }
        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        $logs  = $query->paginate(50)->withQueryString();
        $users = User::orderBy('name')->get(['id', 'name']);

        return view('activity-logs.index', compact('logs', 'users'));
    }

    public function clear()
    {
        if (!auth()->user()->isSuperAdminOnly()) abort(403);
        ActivityLog::where('created_at', '<', now()->subDays(90))->delete();
        return back()->with('status', 'Logs older than 90 days cleared.');
    }
}
