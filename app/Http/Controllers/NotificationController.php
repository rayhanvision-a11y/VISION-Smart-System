<?php

namespace App\Http\Controllers;

use App\Models\Notification;

class NotificationController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $notifications = Notification::forUser($user)
            ->with('ticket')
            ->latest()
            ->paginate(20);

        // Mark all as read when viewing the full list
        Notification::forUser($user)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return view('notifications.index', compact('notifications'));
    }

    public function markRead($id)
    {
        $user = auth()->user();
        $notification = Notification::forUser($user)->findOrFail($id);
        $notification->update(['is_read' => true]);

        if ($notification->ticket_id) {
            return redirect()->route('tickets.show', $notification->ticket_id);
        }

        return back();
    }

    public function markAllRead()
    {
        $user = auth()->user();
        Notification::forUser($user)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return back()->with('success', 'All notifications marked as read.');
    }

    public function unreadCount()
    {
        $user = auth()->user();

        $count = Notification::forUser($user)->where('is_read', false)->count();

        $latest = Notification::forUser($user)
            ->where('is_read', false)
            ->latest()
            ->take(10)
            ->get()
            ->map(fn ($n) => [
                'id' => $n->id,
                'message' => $n->message,
                'url' => $n->ticket_id ? route('tickets.show', $n->ticket_id) : route('notifications.index'),
            ]);

        return response()->json([
            'count' => $count,
            'latest' => $latest,
        ])->header('Cache-Control', 'no-store');
    }

    public function dropdown()
    {
        $user = auth()->user();
        $notifications = Notification::forUser($user)
            ->latest()
            ->take(8)
            ->get();

        $permBtn = '<div id="desktop-push-banner" class="px-3.5 py-2 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
            <span class="text-[11px] text-slate-500 font-medium">Browser Alerts</span>
            <button type="button" onclick="requestDesktopPermission()" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800 bg-white px-2.5 py-1 rounded-md border border-slate-200 hover:border-indigo-300 shadow-2xs transition-all">🔔 Enable Desktop Push</button>
        </div>';

        $html = $permBtn;
        if ($notifications->isEmpty()) {
            $html .= '<p class="px-4 py-8 text-center text-sm text-slate-400">No notifications yet.</p>';
        } else {
            foreach ($notifications as $n) {
                $dot = $n->is_read ? '' : '<span class="w-2 h-2 rounded-full bg-indigo-500 flex-shrink-0 mt-1.5"></span>';
                $bg = $n->is_read ? 'bg-white' : 'bg-indigo-50';
                $fw = $n->is_read ? '' : 'font-semibold';
                $time = $n->created_at->diffForHumans();
                $link = $n->ticket_id
                    ? '<form method="POST" action="'.route('notifications.read', $n->id).'" class="mt-1"><input type="hidden" name="_token" value="'.csrf_token().'"><button type="submit" class="text-xs text-indigo-600 hover:underline">View ticket →</button></form>'
                    : '';
                $html .= "<div class=\"flex gap-3 px-4 py-3 {$bg}\">
                    <div class=\"flex-1 min-w-0\">
                        <p class=\"text-xs text-slate-700 leading-snug {$fw}\">{$n->message}</p>
                        <p class=\"text-xs text-slate-400 mt-0.5\">{$time}</p>
                        {$link}
                    </div>
                    {$dot}
                </div>";
            }
        }

        return response($html);
    }
}
