<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiNotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $items = Notification::forUser($user)
            ->latest()
            ->limit(100)
            ->get()
            ->map(fn ($n) => [
                'id' => $n->id,
                'ticket_id' => $n->ticket_id,
                'message' => $n->message,
                'is_read' => (bool) $n->is_read,
                'created_at' => $n->created_at ? $n->created_at->toDateTimeString() : null,
            ]);

        $unread = Notification::forUser($user)->where('is_read', false)->count();

        return response()->json(['notifications' => $items, 'unread_count' => $unread]);
    }

    public function markRead(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $n = Notification::forUser($user)->findOrFail($id);
        $n->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $user = $request->user();
        $count = Notification::forUser($user)->where('is_read', false)->update(['is_read' => true]);

        return response()->json(['success' => true, 'count' => $count]);
    }
}
