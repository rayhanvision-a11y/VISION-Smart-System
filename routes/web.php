<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\BlogPostController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\CannedResponseController;
use App\Http\Controllers\CustomMenuLinkController;
use App\Http\Controllers\KbCategoryController;
use App\Http\Controllers\LabelController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PopOfficeController;
use App\Http\Controllers\ProfileAvatarController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RosterController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SlaPolicyController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\TicketAttachmentController;
use App\Http\Controllers\TicketCategoryController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketLinkController;
use App\Http\Controllers\TicketMessageController;
use App\Http\Controllers\TicketNoteController;
use App\Http\Controllers\TwoFactorController;
// use App\Http\Controllers\WhatsAppController;
use App\Http\Controllers\UserController;
use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::match(['get', 'post'], '/deploy-webhook', function (Request $request) {
    $secret = env('DEPLOY_WEBHOOK_SECRET', 'visiontech-deploy-secret-2026');
    $provided = $request->query('secret') ?? $request->input('secret');
    if (! $provided || ! hash_equals($secret, (string) $provided)) {
        return response()->json(['error' => 'Forbidden: Invalid deploy secret'], 403);
    }

    $repoPath = base_path();
    $sshKey = '/home/visiontech/.ssh/github_deploy_key';
    $sshCmd = file_exists($sshKey) ? "git config core.sshCommand \"ssh -i {$sshKey} -o StrictHostKeyChecking=no\" && " : '';
    $cmd = "cd {$repoPath} && {$sshCmd}git fetch origin main 2>&1 && git reset --hard origin/main 2>&1";

    exec($cmd, $output, $returnCode);

    if ($returnCode === 0) {
        return response()->json(['success' => true, 'output' => $output]);
    } else {
        return response()->json(['success' => false, 'output' => $output, 'code' => $returnCode], 500);
    }
});

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::post('/locale/{locale}', function (string $locale) {
    if (in_array($locale, ['en', 'bn'], true)) {
        session(['locale' => $locale]);
        if (auth()->check()) {
            auth()->user()->forceFill(['locale' => $locale])->save();
        }
    }

    return redirect()->back();
})->middleware(['auth', 'throttle:10,1'])->name('locale.set');

/*
// WhatsApp Web System Routes — Disabled
Route::middleware(['auth'])->group(function () {
    Route::get('/whatsapp', [WhatsAppController::class, 'index'])->name('whatsapp.index');
    Route::get('/api/whatsapp/status', [WhatsAppController::class, 'status']);
    Route::get('/api/whatsapp/qr', [WhatsAppController::class, 'qr']);
    Route::get('/api/whatsapp/chats', [WhatsAppController::class, 'chats']);
    Route::get('/api/whatsapp/profile-pic', [WhatsAppController::class, 'profilePic']);
    Route::post('/api/whatsapp/profile-pics', [WhatsAppController::class, 'profilePics']);
    Route::get('/api/whatsapp/messages', [WhatsAppController::class, 'messages']);
    Route::get('/api/whatsapp/search', [WhatsAppController::class, 'search']);
    Route::post('/api/whatsapp/send', [WhatsAppController::class, 'send']);
    Route::post('/api/whatsapp/add-contact', [WhatsAppController::class, 'addContact']);
    Route::post('/api/whatsapp/logout', [WhatsAppController::class, 'logout']);
    Route::get('/api/whatsapp/saved-lists', [WhatsAppController::class, 'getSavedLists']);
    Route::post('/api/whatsapp/saved-lists', [WhatsAppController::class, 'saveList']);
    Route::post('/api/whatsapp/saved-lists/{id}/rename', [WhatsAppController::class, 'renameList']);
    Route::post('/api/whatsapp/saved-lists/{id}/toggle-public', [WhatsAppController::class, 'toggleListPublic']);
    Route::delete('/api/whatsapp/saved-lists', [WhatsAppController::class, 'deleteList']);
    Route::get('/api/whatsapp/templates', [WhatsAppController::class, 'getTemplates']);
    Route::post('/api/whatsapp/templates', [WhatsAppController::class, 'saveTemplate']);
    Route::post('/api/whatsapp/templates/{id}/rename', [WhatsAppController::class, 'renameTemplate']);
    Route::delete('/api/whatsapp/templates', [WhatsAppController::class, 'deleteTemplate']);
    Route::get('/api/whatsapp/contacts', [WhatsAppController::class, 'getContacts']);
    Route::post('/api/whatsapp/sync', [WhatsAppController::class, 'sync']);
    Route::post('/api/whatsapp/broadcast', [WhatsAppController::class, 'broadcast']);
    Route::get('/api/whatsapp/scheduled-broadcasts', [WhatsAppController::class, 'getScheduledBroadcasts']);
    Route::post('/api/whatsapp/scheduled-broadcasts', [WhatsAppController::class, 'saveScheduledBroadcast']);
    Route::delete('/api/whatsapp/scheduled-broadcasts', [WhatsAppController::class, 'deleteScheduledBroadcast']);
    Route::get('/api/whatsapp/broadcast-history', [WhatsAppController::class, 'getBroadcastHistory']);
    Route::delete('/api/whatsapp/broadcast-history', [WhatsAppController::class, 'deleteBroadcastHistory']);
    Route::get('/api/whatsapp/sessions', [WhatsAppController::class, 'getSessions']);
    Route::post('/api/whatsapp/sessions/{userId}/logout', [WhatsAppController::class, 'logoutSession']);
});
*/

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', function (Request $request) {
        $user = auth()->user();

        // Base query (admin/noc views only)
        $base = fn () => Ticket::query();

        $resellersQuick = $user->isAdmin()
            ? User::where('role', 'reseller')->orderBy('name')->get()
            : collect();

        $formatDuration = function ($minutes) {
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
        };

        if ($user->isAdmin()) {
            $aggregated = $base()->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'waiting_for_customer_feedback' THEN 1 ELSE 0 END) as waiting_for_customer_feedback,
                SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved,
                SUM(CASE WHEN status IN ('in_progress','pending','waiting_for_customer_feedback') AND priority = 'critical' THEN 1 ELSE 0 END) as critical,
                SUM(CASE WHEN due_at IS NOT NULL AND due_at < NOW() AND status != 'resolved' THEN 1 ELSE 0 END) as overdue,
                AVG(CASE WHEN resolved_at IS NOT NULL THEN TIMESTAMPDIFF(MINUTE, created_at, resolved_at) END) as avg_res
            ")->first();

            $stats = [
                'in_progress' => (int) ($aggregated->in_progress ?? 0),
                'pending' => (int) ($aggregated->pending ?? 0),
                'waiting_for_customer_feedback' => (int) ($aggregated->waiting_for_customer_feedback ?? 0),
                'resolved' => (int) ($aggregated->resolved ?? 0),
                'total' => (int) ($aggregated->total ?? 0),
                'critical' => (int) ($aggregated->critical ?? 0),
                'overdue' => (int) ($aggregated->overdue ?? 0),
                'avg_resolution_time' => $formatDuration($aggregated->avg_res),
            ];
            $recentTickets = $base()->with(['creator', 'assignee'])->latest()->take(5)->get();

            // Chart data - Single aggregated query
            $startDate = now()->subDays(13)->startOfDay();
            $daysData = Ticket::where('created_at', '>=', $startDate)
                ->selectRaw('DATE(created_at) as date, count(*) as count')
                ->groupBy('date')
                ->pluck('count', 'date');

            $chartDays = [];
            $chartCreated = [];
            for ($i = 13; $i >= 0; $i--) {
                $day = now()->subDays($i)->format('Y-m-d');
                $chartDays[] = now()->subDays($i)->format('d M');
                $chartCreated[] = (int) ($daysData[$day] ?? 0);
            }

            $chartByCategory = $base()->selectRaw('category, count(*) as count')
                ->groupBy('category')->pluck('count', 'category');

            // NOC Leaderboard - Bulk average query
            $nocUsers = User::where('role', 'noc')->get();
            $nocUserIds = $nocUsers->pluck('id');
            $nocAvgTimes = Ticket::whereIn('assigned_to', $nocUserIds)
                ->where('status', 'resolved')
                ->whereNotNull('resolved_at')
                ->selectRaw('assigned_to, AVG(TIMESTAMPDIFF(MINUTE, created_at, resolved_at)) as avg_res')
                ->groupBy('assigned_to')
                ->pluck('avg_res', 'assigned_to');

            $nocLeaderboard = User::where('role', 'noc')
                ->withCount(['assignedTickets as resolved_count' => fn ($q) => $q->where('status', 'resolved')
                    ->whereMonth('resolved_at', now()->month)
                    ->whereYear('resolved_at', now()->year),
                ])
                ->get()
                ->map(function ($noc) use ($nocAvgTimes, $formatDuration) {
                    $avgMin = $nocAvgTimes[$noc->id] ?? null;
                    $noc->avg_resolution_time = $formatDuration($avgMin);

                    return $noc;
                })
                ->sortByDesc('resolved_count')
                ->take(5);

            $activityFeed = TicketHistory::with(['ticket', 'changedBy'])
                ->latest()->take(20)->get();
        } elseif ($user->isNoc()) {
            $myBase = fn () => Ticket::where('assigned_to', $user->id);
            $aggregated = $myBase()->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'waiting_for_customer_feedback' THEN 1 ELSE 0 END) as waiting_for_customer_feedback,
                SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved,
                SUM(CASE WHEN status NOT IN ('resolved', 'closed') THEN 1 ELSE 0 END) as my_assigned,
                SUM(CASE WHEN status IN ('in_progress','pending','waiting_for_customer_feedback') AND priority = 'critical' THEN 1 ELSE 0 END) as critical,
                SUM(CASE WHEN due_at IS NOT NULL AND due_at < NOW() AND status != 'resolved' THEN 1 ELSE 0 END) as overdue,
                AVG(CASE WHEN resolved_at IS NOT NULL THEN TIMESTAMPDIFF(MINUTE, created_at, resolved_at) END) as avg_res
            ")->first();

            $stats = [
                'in_progress' => (int) ($aggregated->in_progress ?? 0),
                'pending' => (int) ($aggregated->pending ?? 0),
                'waiting_for_customer_feedback' => (int) ($aggregated->waiting_for_customer_feedback ?? 0),
                'resolved' => (int) ($aggregated->resolved ?? 0),
                'total' => (int) ($aggregated->total ?? 0),
                'my_assigned' => (int) ($aggregated->my_assigned ?? 0),
                'critical' => (int) ($aggregated->critical ?? 0),
                'overdue' => (int) ($aggregated->overdue ?? 0),
                'avg_resolution_time' => $formatDuration($aggregated->avg_res),
            ];
            $recentTickets = $base()->with(['creator', 'assignee'])->latest()->take(5)->get();

            $startDate = now()->subDays(6)->startOfDay();
            $daysData = Ticket::where('created_at', '>=', $startDate)
                ->selectRaw('DATE(created_at) as date, count(*) as count')
                ->groupBy('date')
                ->pluck('count', 'date');

            $chartDays = [];
            $chartCreated = [];
            for ($i = 6; $i >= 0; $i--) {
                $day = now()->subDays($i)->format('Y-m-d');
                $chartDays[] = now()->subDays($i)->format('d M');
                $chartCreated[] = (int) ($daysData[$day] ?? 0);
            }
            $activityFeed = TicketHistory::with(['ticket', 'changedBy'])
                ->latest()->take(20)->get();
            $chartByCategory = $base()->selectRaw('category, count(*) as count')->groupBy('category')->pluck('count', 'category');
            $nocLeaderboard = collect();
        } else {
            // Reseller — only their own tickets
            $aggregated = Ticket::where('created_by', $user->id)->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved,
                AVG(CASE WHEN resolved_at IS NOT NULL THEN TIMESTAMPDIFF(MINUTE, created_at, resolved_at) END) as avg_res
            ")->first();

            $stats = [
                'in_progress' => (int) ($aggregated->in_progress ?? 0),
                'pending' => (int) ($aggregated->pending ?? 0),
                'resolved' => (int) ($aggregated->resolved ?? 0),
                'total' => (int) ($aggregated->total ?? 0),
                'avg_resolution_time' => $formatDuration($aggregated->avg_res),
            ];
            $recentTickets = Ticket::with(['creator', 'assignee'])->where('created_by', $user->id)->latest()->take(5)->get();
            $chartDays = [];
            $chartCreated = [];
            $chartByCategory = collect();
            $nocLeaderboard = collect();
            $activityFeed = TicketHistory::with(['ticket', 'changedBy'])
                ->whereHas('ticket', fn ($q) => $q->where('created_by', $user->id))
                ->latest()->take(20)->get();
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['stats' => $stats]);
        }

        return view('dashboard', compact('stats', 'recentTickets', 'chartDays', 'chartCreated', 'chartByCategory', 'nocLeaderboard', 'activityFeed', 'resellersQuick'));
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/theme', [ProfileController::class, 'updateTheme'])->name('profile.theme');
    Route::patch('/profile/preferences', [ProfileController::class, 'updatePreferences'])->name('profile.preferences');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/avatar', [ProfileAvatarController::class, 'update'])->name('profile.avatar');

    // Bulk action BEFORE resource so it is not treated as ticket ID
    Route::post('/tickets/bulk-action', [TicketController::class, 'bulkAction'])->name('tickets.bulk-action');

    Route::resource('tickets', TicketController::class);
    Route::post('/tickets/{ticket}/assign', [TicketController::class, 'assign'])->name('tickets.assign');
    Route::post('/tickets/{ticket}/status', [TicketController::class, 'updateStatus'])->name('tickets.status');
    Route::post('/tickets/{ticket}/resolve', [TicketController::class, 'resolve'])->name('tickets.resolve');
    Route::post('/tickets/{ticket}/reopen', [TicketController::class, 'reopen'])->name('tickets.reopen');
    Route::post('/tickets/{ticket}/close', [TicketController::class, 'close'])->name('tickets.close');
    Route::post('/tickets/{ticket}/messages', [TicketMessageController::class, 'store'])->name('tickets.messages.store');
    Route::put('/tickets/{ticket}/messages/{message}', [TicketMessageController::class, 'update'])->name('tickets.messages.update');
    Route::get('/tickets/{ticket}/messages/poll', [TicketMessageController::class, 'poll'])->name('tickets.messages.poll');
    Route::post('/tickets/{ticket}/typing', [TicketMessageController::class, 'typing'])->name('tickets.typing');
    Route::post('/tickets/{ticket}/messages/{message}/react', [TicketMessageController::class, 'toggleReaction'])->name('tickets.messages.react');
    Route::post('/tickets/{ticket}/notes', [TicketNoteController::class, 'store'])->name('tickets.notes.store');
    Route::post('/tickets/{ticket}/labels', [TicketController::class, 'attachLabel'])->name('tickets.labels.attach');
    Route::delete('/tickets/{ticket}/labels/{label}', [TicketController::class, 'detachLabel'])->name('tickets.labels.detach');
    Route::post('/tickets/{ticket}/subtasks', [TicketController::class, 'storeSubtask'])->name('tickets.subtasks.store');
    Route::patch('/tickets/{ticket}/title', [TicketController::class, 'updateTitle'])->name('tickets.title.update');
    Route::post('/tickets/{ticket}/links', [TicketLinkController::class, 'store'])->name('tickets.links.store');
    Route::delete('/tickets/{ticket}/links/{link}', [TicketLinkController::class, 'destroy'])->name('tickets.links.destroy');

    Route::get('/board', [BoardController::class, 'index'])->name('board.index');
    Route::post('/board/move', [BoardController::class, 'move'])->name('board.move');

    Route::get('/roster', [RosterController::class, 'index'])->name('roster.index');
    Route::post('/roster/update-shift', [RosterController::class, 'updateShift'])->name('roster.update-shift');
    Route::post('/roster/users/{user}/team', [RosterController::class, 'updateTeam'])->name('roster.users.team');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/pdf', [ReportController::class, 'downloadPdf'])->name('reports.pdf');
    Route::get('/reports/excel', [ReportController::class, 'downloadExcel'])->name('reports.excel');

    Route::resource('users', UserController::class)->except(['show']);
    Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');

    Route::resource('labels', LabelController::class)->only(['index', 'store', 'destroy']);
    Route::resource('ticket-categories', TicketCategoryController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('areas', AreaController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('teams', \App\Http\Controllers\TeamController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::get('/search', [SearchController::class, 'index'])->name('search');

    Route::get('/pop-offices', [PopOfficeController::class, 'index'])->name('pop-offices.index');
    Route::post('/pop-offices', [PopOfficeController::class, 'store'])->name('pop-offices.store');
    Route::delete('/pop-offices/{popOffice}', [PopOfficeController::class, 'destroy'])->name('pop-offices.destroy');
    Route::get('/pop-offices/{popOffice}/tickets', [PopOfficeController::class, 'tickets'])->name('pop-offices.tickets');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/dropdown', [NotificationController::class, 'dropdown'])->name('notifications.dropdown');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.count');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');

    Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
    Route::post('/settings/logo', [SettingController::class, 'update'])->name('settings.logo.update');
    Route::delete('/settings/logo', [SettingController::class, 'destroy'])->name('settings.logo.destroy');
    Route::post('/settings/favicon', [SettingController::class, 'updateFavicon'])->name('settings.favicon.update');
    Route::delete('/settings/favicon', [SettingController::class, 'destroyFavicon'])->name('settings.favicon.destroy');
    Route::post('/settings/notice', [SettingController::class, 'updateNotice'])->name('settings.notice.update');
    Route::post('/settings/notice/toggle', [SettingController::class, 'toggleNotice'])->name('settings.notice.toggle');
    Route::post('/settings/theme', [SettingController::class, 'updateTheme'])->name('settings.theme.update');
    Route::get('/settings/backup/export', [SettingController::class, 'backupExport'])->name('settings.backup.export');
    Route::post('/settings/backup/restore', [SettingController::class, 'backupRestore'])->name('settings.backup.restore');

    // ── Database Backup System ─────────────────────────────────────
    Route::post('/settings/backup/create', [BackupController::class, 'create'])->name('settings.backup.create');
    Route::get('/settings/backup/download/{filename}', [BackupController::class, 'download'])->name('settings.backup.download');
    Route::post('/settings/backup/restore/{filename}', [BackupController::class, 'restore'])->name('settings.backup.restore');
    Route::delete('/settings/backup/{filename}', [BackupController::class, 'destroy'])->name('settings.backup.destroy');

    // ── Activity Logs ─────────────────────────────────────────────
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::delete('/activity-logs/clear', [ActivityLogController::class, 'clear'])->name('activity-logs.clear');

    // ── File attachments ──────────────────────────────────────────
    Route::post('/tickets/{ticket}/attachments', [TicketAttachmentController::class, 'store'])->name('tickets.attachments.store');
    Route::delete('/tickets/{ticket}/attachments/{attachment}', [TicketAttachmentController::class, 'destroy'])->name('tickets.attachments.destroy');
    Route::get('/tickets/{ticket}/attachments/{attachment}/download', [TicketAttachmentController::class, 'download'])->name('tickets.attachments.download');

    // ── Canned responses ──────────────────────────────────────────
    Route::get('/canned-responses', [CannedResponseController::class, 'index'])->name('canned-responses.index');
    Route::post('/canned-responses', [CannedResponseController::class, 'store'])->name('canned-responses.store');
    Route::put('/canned-responses/{cannedResponse}', [CannedResponseController::class, 'update'])->name('canned-responses.update');
    Route::delete('/canned-responses/{cannedResponse}', [CannedResponseController::class, 'destroy'])->name('canned-responses.destroy');
    Route::get('/canned-responses-list', [CannedResponseController::class, 'list'])->name('canned-responses.list');

    // ── CSAT ──────────────────────────────────────────
    Route::post('/tickets/{ticket}/csat', [TicketController::class, 'submitCsat'])->name('tickets.csat');

    // ── Ticket merge ──────────────────────────────────────────
    Route::get('/tickets/{ticket}/merge', [TicketController::class, 'showMergeForm'])->name('tickets.merge.form');
    Route::post('/tickets/{ticket}/merge', [TicketController::class, 'merge'])->name('tickets.merge');

    // ── SLA policies ──────────────────────────────────────────
    Route::get('/sla-policies', [SlaPolicyController::class, 'index'])->name('sla-policies.index');
    Route::post('/sla-policies', [SlaPolicyController::class, 'update'])->name('sla-policies.update');

    // ── Custom Menu Links ──────────────────────────────────────
    Route::resource('custom-menu-links', CustomMenuLinkController::class)->except(['create', 'edit', 'show']);
    Route::post('/custom-menu-links/{customMenuLink}/toggle-important', [CustomMenuLinkController::class, 'toggleImportant'])->name('custom-menu-links.toggle-important');
    Route::post('/custom-menu-links/{customMenuLink}/toggle-active', [CustomMenuLinkController::class, 'toggleActive'])->name('custom-menu-links.toggle-active');

    // ── Two-factor management (while logged in) ─────────────────
    Route::get('/two-factor', [TwoFactorController::class, 'show'])->name('two-factor.show');
    Route::get('/two-factor/enroll', [TwoFactorController::class, 'enroll'])->name('two-factor.enroll');
    Route::post('/two-factor/confirm', [TwoFactorController::class, 'confirm'])->name('two-factor.confirm');
    Route::delete('/two-factor', [TwoFactorController::class, 'disable'])->name('two-factor.disable');

    // ── Knowledge Base (জ্ঞান ভাণ্ডার) ──────────────────────────────────
    Route::get('/knowledge-base/categories', [KbCategoryController::class, 'index'])->name('kb-categories.index');
    Route::post('/knowledge-base/categories', [KbCategoryController::class, 'store'])->name('kb-categories.store');
    Route::put('/knowledge-base/categories/{category}', [KbCategoryController::class, 'update'])->name('kb-categories.update');
    Route::delete('/knowledge-base/categories/{category}', [KbCategoryController::class, 'destroy'])->name('kb-categories.destroy');

    Route::get('/knowledge-base', [BlogPostController::class, 'index'])->name('knowledge-base.index');
    Route::get('/knowledge-base/create', [BlogPostController::class, 'create'])->name('knowledge-base.create');
    Route::post('/knowledge-base', [BlogPostController::class, 'store'])->name('knowledge-base.store');
    Route::get('/knowledge-base/{blogPost:slug}', [BlogPostController::class, 'show'])->name('knowledge-base.show');
    Route::get('/knowledge-base/{blogPost:slug}/edit', [BlogPostController::class, 'edit'])->name('knowledge-base.edit');
    Route::put('/knowledge-base/{blogPost:slug}', [BlogPostController::class, 'update'])->name('knowledge-base.update');
    Route::delete('/knowledge-base/{blogPost:slug}', [BlogPostController::class, 'destroy'])->name('knowledge-base.destroy');
    Route::post('/knowledge-base/{blogPost:slug}/like', [BlogPostController::class, 'toggleLike'])->name('knowledge-base.like');
    Route::post('/knowledge-base/{blogPost:slug}/comments', [BlogPostController::class, 'storeComment'])->name('knowledge-base.comments.store');
    Route::delete('/knowledge-base/comments/{comment}', [BlogPostController::class, 'destroyComment'])->name('knowledge-base.comments.destroy');

    // ── Backward-compatible Route Aliases ──────────────────────────────
    Route::get('/knowledge-base-blogs-index', [BlogPostController::class, 'index'])->name('blogs.index');
    Route::get('/knowledge-base-blogs-create', [BlogPostController::class, 'create'])->name('blogs.create');
    Route::post('/knowledge-base-blogs-store', [BlogPostController::class, 'store'])->name('blogs.store');
    Route::get('/knowledge-base-blogs-show/{blogPost:slug}', [BlogPostController::class, 'show'])->name('blogs.show');
    Route::get('/knowledge-base-blogs-edit/{blogPost:slug}', [BlogPostController::class, 'edit'])->name('blogs.edit');
    Route::put('/knowledge-base-blogs-update/{blogPost:slug}', [BlogPostController::class, 'update'])->name('blogs.update');
    Route::delete('/knowledge-base-blogs-destroy/{blogPost:slug}', [BlogPostController::class, 'destroy'])->name('blogs.destroy');
    Route::post('/knowledge-base-blogs-like/{blogPost:slug}', [BlogPostController::class, 'toggleLike'])->name('blogs.like');
    Route::post('/knowledge-base-blogs-comments/{blogPost:slug}', [BlogPostController::class, 'storeComment'])->name('blogs.comments.store');
    Route::delete('/knowledge-base-blogs-comments/{comment}', [BlogPostController::class, 'destroyComment'])->name('blogs.comments.destroy');

    // ── Redirect legacy /blogs and /kb paths to /knowledge-base ──────
    Route::redirect('/blogs', '/knowledge-base');
    Route::redirect('/kb', '/knowledge-base');
});

// ── Two-factor login challenge (partially-authenticated state) ─────
Route::get('/two-factor-challenge', [TwoFactorController::class, 'challenge'])->name('two-factor.challenge');
Route::post('/two-factor-challenge', [TwoFactorController::class, 'verifyChallenge'])->middleware('throttle:5,1')->name('two-factor.challenge.verify');

require __DIR__.'/auth.php';
