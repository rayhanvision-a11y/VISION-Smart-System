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
            $html .= '<p class="px-4 py-8 text-center text-sm text-slate-400 dark:text-slate-500">No notifications yet.</p>';
        } else {
            foreach ($notifications as $n) {
                $dot = $n->is_read ? '' : '<span class="w-2 h-2 rounded-full bg-indigo-500 flex-shrink-0 mt-1.5 shadow-xs"></span>';
                $bg = $n->is_read 
                    ? 'bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700/60' 
                    : 'bg-indigo-50/70 dark:bg-indigo-950/40 hover:bg-indigo-100/70 dark:hover:bg-indigo-900/50';
                $fw = $n->is_read ? 'text-slate-700 dark:text-slate-200' : 'font-semibold text-slate-900 dark:text-white';
                $time = $n->created_at->diffForHumans();
                $url = route('notifications.read', $n->id);

                $html .= "<a href=\"{$url}\" class=\"flex items-start gap-3 px-4 py-3.5 border-b border-slate-100 dark:border-slate-700/60 last:border-0 transition-colors cursor-pointer group block {$bg}\">
                    <div class=\"flex-1 min-w-0\">
                        <p class=\"text-xs leading-snug {$fw} group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors\">{$n->message}</p>
                        <div class=\"flex items-center justify-between mt-1.5\">
                            <span class=\"text-[11px] text-slate-400 dark:text-slate-500\">{$time}</span>
                            <span class=\"text-[11px] font-medium text-indigo-600 dark:text-indigo-400 group-hover:translate-x-0.5 transition-transform flex items-center gap-0.5\">View &rarr;</span>
                        </div>
                    </div>
                    {$dot}
                </a>";
            }
        }

        return response($html);
    }
}
