<?php

namespace App\Http\Controllers;

use App\Mail\TicketAssigned;
use App\Mail\TicketResolved;
use App\Mail\TicketReopened;
use App\Models\Label;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketHistory;
use App\Models\TicketLink;
use App\Models\TicketNote;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Ticket::forUser($user)->with(['creator', 'assignee', 'labels']);

        if ($request->filled('status')) {
            if ($request->status === 'overdue') {
                $query->whereNotNull('due_at')
                      ->where('due_at', '<', now())
                      ->whereNotIn('status', ['resolved']);
            } else {
                $query->where('status', $request->status);
            }
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }
        if ($request->get('assigned') === 'me') {
            $query->where('assigned_to', auth()->id());
        }
        if ($request->get('created') === 'me') {
            $query->where('created_by', auth()->id());
        }
        if ($request->filled('created_by') && auth()->user()->isAdmin()) {
            $query->where('created_by', $request->created_by);
        }
        if ($request->filled('label')) {
            $query->whereHas('labels', fn($q) => $q->where('labels.id', $request->label));
        }

        $tickets = $query->latest()->paginate(15)->withQueryString();
        $allLabels = Label::orderBy('name')->get();
        $categories = TicketCategory::where('is_active', true)->orderBy('name')->get();

        return view('tickets.index', compact('tickets', 'allLabels', 'categories'));
    }

    public function create()
    {
        $nocUsers = User::whereIn('role', ['super_admin', 'admin', 'noc'])->orderBy('name')->get();
        $labels = Label::orderBy('name')->get();
        $categories = TicketCategory::where('is_active', true)->orderBy('name')->get();
        return view('tickets.create', compact('nocUsers', 'labels', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string|max:10000',
            'category'    => 'required|string|max:255',
            'priority'    => 'required|in:low,medium,high,critical',
            'pop_office_id' => 'nullable|exists:pop_offices,id',
            'assigned_to' => ['nullable', 'exists:users,id', function($attr, $val, $fail) {
                if ($val) {
                    $targetUser = \App\Models\User::find($val);
                    if ($targetUser && in_array($targetUser->role, ['reseller', 'call_center'])) {
                        $fail('Tickets can only be assigned to Admin or NOC users.');
                    }
                }
            }],
            'labels'      => 'nullable|array',
            'labels.*'    => 'exists:labels,id',
        ]);

        $ticket = Ticket::create([
            'ticket_key'    => Ticket::generateKey(),
            'title'         => $validated['title'],
            'description'   => $validated['description'],
            'category'      => $validated['category'],
            'priority'      => $validated['priority'],
            'status'        => 'in_progress',
            'created_by'    => auth()->id(),
            'assigned_to'   => auth()->user()->isReseller() ? null : ($validated['assigned_to'] ?? null),
            'pop_office_id' => auth()->user()->isReseller() ? null : ($validated['pop_office_id'] ?? null),
        ]);

        $slaPolicy = \App\Models\SlaPolicy::forPriority($validated['priority']);
        $resolutionHours = $slaPolicy?->resolution_hours ?? match($validated['priority']) {
            'critical' => 2,
            'high'     => 4,
            'medium'   => 24,
            'low'      => 72,
        };
        $ticket->update(['due_at' => now()->addHours($resolutionHours)]);

        if (!empty($validated['labels'])) {
            $ticket->labels()->sync($validated['labels']);
        }

        TicketHistory::create([
            'ticket_id'       => $ticket->id,
            'action'          => 'Ticket created',
            'old_assignee_id' => null,
            'new_assignee_id' => $ticket->assigned_to,
            'changed_by'      => auth()->id(),
        ]);

        // Notify all super_admin, admin, noc about new ticket
        $notifyIds = User::whereIn('role', ['super_admin', 'admin', 'noc'])
            ->where('id', '!=', auth()->id())
            ->pluck('id')->toArray();
        NotificationService::sendToMany($notifyIds, "🎫 New ticket #{$ticket->id} from {$ticket->creator->name}: {$ticket->title}", $ticket->id);

        // Notify assigned NOC if set (and not already notified above)
        if ($ticket->assigned_to && !in_array($ticket->assigned_to, $notifyIds)) {
            NotificationService::send($ticket->assigned_to, "You have been assigned ticket #{$ticket->id}: {$ticket->title}", $ticket->id);
        }

        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket created successfully.');
    }

    public function show(string $id)
    {
        $ticket = Ticket::with([
            'creator', 'assignee', 'messages.sender', 'messages.replyTo.sender', 'messages.reactions.user', 'notes.user',
            'history.changedBy', 'history.oldAssignee', 'history.newAssignee', 'labels',
            'subtasks.assignee', 'subtasks.creator',
            'links.linkedTicket.assignee', 'linkedFrom.ticket.assignee',
            'attachments.uploader', 'mergedInto',
        ])->findOrFail($id);

        $user = auth()->user();

        $canView = Ticket::where('id', $id)->forUser($user)->exists();
        if (!$canView) {
            abort(403, 'You are not authorized to view this ticket.');
        }

        if ($user->isReseller()) {
            $ticket->setRelation('notes', collect());
        }

        $nocUsers = User::whereIn('role', ['super_admin', 'admin', 'noc'])->orderBy('name')->get();
        $allLabels = Label::orderBy('name')->get();

        $mentionUsers = User::orderBy('name')->get()->map(fn($u) => [
            'id'       => $u->id,
            'value'    => $u->name,
            'avatar'   => $u->avatarUrl(),
            'role'     => $u->role,
        ])->values();

        return view('tickets.show', compact('ticket', 'nocUsers', 'allLabels', 'mentionUsers'));
    }

    public function storeSubtask(Request $request, Ticket $ticket)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->isNoc()) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $subtask = Ticket::create([
            'ticket_key'  => Ticket::generateKey(),
            'title'       => $validated['title'],
            'description' => '',
            'category'    => $ticket->category,
            'priority'    => $ticket->priority,
            'status'      => 'in_progress',
            'created_by'  => auth()->id(),
            'assigned_to' => $ticket->assigned_to,
            'parent_id'   => $ticket->id,
        ]);

        TicketHistory::create([
            'ticket_id'       => $ticket->id,
            'action'          => 'Subtask ' . $subtask->ticket_key . ' created: ' . $subtask->title,
            'old_assignee_id' => null,
            'new_assignee_id' => null,
            'changed_by'      => $user->id,
        ]);

        return redirect()->route('tickets.show', $ticket)->with('success', 'Subtask created.');
    }

    public function updateTitle(Request $request, Ticket $ticket)
    {
        if (auth()->user()->isReseller()) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $ticket->update(['title' => $validated['title']]);

        return response()->json(['success' => true, 'title' => $ticket->title]);
    }

    public function edit(string $id)
    {
        $user = auth()->user();
        $canView = Ticket::where('id', $id)->forUser($user)->exists();
        if (!$canView || $user->isReseller()) {
            abort(403);
        }
        $ticket = Ticket::findOrFail($id);
        $nocUsers = User::whereIn('role', ['super_admin', 'admin', 'noc'])->orderBy('name')->get();
        return view('tickets.edit', compact('ticket', 'nocUsers'));
    }

    public function updateStatus(Request $request, string $id)
    {
        $user = auth()->user();
        if ($user->isReseller()) abort(403);

        $ticket = Ticket::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:in_progress,pending,waiting_for_customer_feedback,resolved',
        ]);

        $newStatus = $validated['status'];
        $data = ['status' => $newStatus];
        if ($newStatus === 'resolved') {
            $data['resolved_at'] = now();
        } else {
            $data['resolved_at'] = null;
        }

        $ticket->update($data);

        $label = ucfirst(str_replace('_', ' ', $newStatus));

        TicketHistory::create([
            'ticket_id'  => $ticket->id,
            'action'     => "Status changed to {$newStatus}",
            'changed_by' => $user->id,
        ]);

        return redirect()->route('tickets.show', $ticket)->with('success', "Ticket status updated to {$label}.");
    }

    public function resolve(Request $request, string $id)
    {
        $user = auth()->user();
        if ($user->isReseller()) {
            abort(403);
        }

        $ticket = Ticket::findOrFail($id);

        $validated = $request->validate([
            'resolution_note' => 'required|string',
        ]);

        $ticket->update(['status' => 'resolved', 'resolved_at' => now()]);

        TicketNote::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $user->id,
            'note'      => $validated['resolution_note'],
            'type'      => 'resolution',
        ]);

        TicketHistory::create([
            'ticket_id'       => $ticket->id,
            'action'          => 'Ticket resolved by NOC',
            'old_assignee_id' => null,
            'new_assignee_id' => null,
            'changed_by'      => $user->id,
        ]);

        if ($ticket->creator && $ticket->creator->email && $ticket->creator->notify_on_resolve) {
            try { Mail::to($ticket->creator->email)->send(new TicketResolved($ticket)); } catch (\Exception $e) {}
        }

        $whatsapp = new WhatsAppService();
        if ($ticket->creator && $ticket->creator->phone) {
            $whatsapp->send($ticket->creator->phone, "Your ticket #{$ticket->id} '{$ticket->title}' has been resolved. Please check and confirm.");
        }

        // DB notification to ticket creator
        if ($ticket->created_by) {
            NotificationService::send($ticket->created_by, "✅ Ticket #{$ticket->id} \"{$ticket->title}\" has been resolved. Please review and close or reopen.", $ticket->id);
        }
        // Notify super_admin + admin
        $adminIds = User::whereIn('role', ['super_admin', 'admin'])->where('id', '!=', $user->id)->pluck('id')->toArray();
        NotificationService::sendToMany($adminIds, "✅ Ticket #{$ticket->id} resolved by {$user->name}", $ticket->id);

        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket marked as resolved.');
    }

    public function reopen(Request $request, string $id)
    {
        $user = auth()->user();
        $ticket = Ticket::findOrFail($id);

        if (!$user->isAdmin() && $ticket->created_by !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'reopen_reason' => 'required|string',
        ]);

        $ticket->update(['status' => 'in_progress', 'resolved_at' => null, 'sla_notified_at' => null]);

        TicketNote::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $user->id,
            'note'      => $validated['reopen_reason'],
            'type'      => 'reopen',
        ]);

        TicketHistory::create([
            'ticket_id'       => $ticket->id,
            'action'          => 'Ticket reopened',
            'old_assignee_id' => null,
            'new_assignee_id' => null,
            'changed_by'      => $user->id,
        ]);

        if ($ticket->assignee && $ticket->assignee->email && $ticket->assignee->notify_on_assign) {
            try { Mail::to($ticket->assignee->email)->send(new TicketReopened($ticket)); } catch (\Exception $e) {}
        }

        $whatsapp = new WhatsAppService();
        if ($ticket->assignee && $ticket->assignee->phone) {
            $whatsapp->send($ticket->assignee->phone, "Ticket #{$ticket->id} has been reopened. Please check.");
        }

        // DB notification to assigned NOC
        if ($ticket->assigned_to) {
            NotificationService::send($ticket->assigned_to, "🔁 Ticket #{$ticket->id} \"{$ticket->title}\" has been reopened. Please check.", $ticket->id);
        }
        // Notify super_admin + admin
        $adminIds = User::whereIn('role', ['super_admin', 'admin'])->where('id', '!=', $user->id)->pluck('id')->toArray();
        NotificationService::sendToMany($adminIds, "🔁 Ticket #{$ticket->id} reopened by {$user->name}", $ticket->id);

        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket reopened.');
    }

    public function close(string $id)
    {
        $user = auth()->user();
        $ticket = Ticket::findOrFail($id);

        if (!$user->isAdmin() && $ticket->created_by !== $user->id) {
            abort(403);
        }

        $ticket->update(['status' => 'resolved', 'resolved_at' => $ticket->resolved_at ?? now()]);

        TicketHistory::create([
            'ticket_id'       => $ticket->id,
            'action'          => 'Ticket closed',
            'old_assignee_id' => null,
            'new_assignee_id' => null,
            'changed_by'      => $user->id,
        ]);

        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket closed.');
    }

    public function update(Request $request, string $id)
    {
        $user = auth()->user();
        if ($user->isReseller()) {
            abort(403);
        }

        $ticket = Ticket::findOrFail($id);

        $validated = $request->validate([
            'status'      => 'required|in:in_progress,pending,waiting_for_customer_feedback,resolved',
            'priority'    => 'required|in:low,medium,high,critical',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $oldAssignee = $ticket->assigned_to;
        $oldStatus = $ticket->status;

        if ($validated['status'] === 'resolved') {
            $validated['resolved_at'] = $ticket->resolved_at ?? now();
        } elseif ($ticket->status === 'resolved' && $validated['status'] !== 'resolved') {
            $validated['resolved_at'] = null;
        }

        $ticket->update($validated);

        $action = 'Status changed to ' . $validated['status'];
        if ($oldAssignee != $validated['assigned_to']) {
            $action .= '; Reassigned';
            $ticket->refresh();
            if ($ticket->assignee && $ticket->assignee->email && $ticket->assignee->notify_on_assign) {
                try { Mail::to($ticket->assignee->email)->send(new TicketAssigned($ticket)); } catch (\Exception $e) {}
            }
            if ($ticket->assigned_to) {
                NotificationService::send($ticket->assigned_to, "📋 Ticket #{$ticket->id} \"{$ticket->title}\" has been assigned to you.", $ticket->id);
            }
        }

        TicketHistory::create([
            'ticket_id'       => $ticket->id,
            'action'          => $action,
            'old_assignee_id' => $oldAssignee,
            'new_assignee_id' => $validated['assigned_to'] ?? null,
            'changed_by'      => auth()->id(),
        ]);

        if ($oldStatus !== 'resolved' && $validated['status'] === 'resolved') {
            if ($ticket->creator && $ticket->creator->email && $ticket->creator->notify_on_resolve) {
                try { Mail::to($ticket->creator->email)->send(new TicketResolved($ticket)); } catch (\Exception $e) {}
            }
            if ($ticket->created_by) {
                NotificationService::send($ticket->created_by, "✅ Ticket #{$ticket->id} \"{$ticket->title}\" has been resolved by {$user->name}. Please review and close or reopen.", $ticket->id);
            }
            $adminIds = User::whereIn('role', ['super_admin', 'admin'])->where('id', '!=', $user->id)->pluck('id')->toArray();
            NotificationService::sendToMany($adminIds, "✅ Ticket #{$ticket->id} resolved by {$user->name}", $ticket->id);
        }

        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket updated successfully.');
    }

    public function assign(Request $request, string $id)
    {
        $authUser = auth()->user();
        $canView = Ticket::where('id', $id)->forUser($authUser)->exists();
        if (!$canView || $authUser->isReseller()) {
            abort(403);
        }

        $ticket = Ticket::findOrFail($id);
        $validated = $request->validate([
            'assigned_to' => ['required', 'exists:users,id', function($attr, $val, $fail) {
                $targetUser = \App\Models\User::find($val);
                if ($targetUser && in_array($targetUser->role, ['reseller', 'call_center'])) {
                    $fail('Tickets can only be assigned to Admin or NOC users.');
                }
            }],
        ]);

        $oldAssignee = $ticket->assigned_to;
        $ticket->update(['assigned_to' => $validated['assigned_to']]);

        TicketHistory::create([
            'ticket_id'       => $ticket->id,
            'action'          => 'Ticket reassigned',
            'old_assignee_id' => $oldAssignee,
            'new_assignee_id' => $validated['assigned_to'],
            'changed_by'      => auth()->id(),
        ]);

        $ticket->refresh();
        if ($ticket->assignee && $ticket->assignee->email && $ticket->assignee->notify_on_assign) {
            try { Mail::to($ticket->assignee->email)->send(new TicketAssigned($ticket)); } catch (\Exception $e) {}
        }

        // DB notification to the new assignee (if changed)
        if ($validated['assigned_to'] != $oldAssignee) {
            NotificationService::send(
                $validated['assigned_to'],
                "🎫 Ticket #{$ticket->ticket_key} has been assigned to you: {$ticket->title}",
                $ticket->id
            );
        }

        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket assigned successfully.');
    }

    public function attachLabel(Request $request, Ticket $ticket)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->isNoc()) {
            abort(403);
        }

        $request->validate(['label_id' => 'required|exists:labels,id']);
        $ticket->labels()->syncWithoutDetaching([$request->label_id]);

        return redirect()->route('tickets.show', $ticket)->with('success', 'Label added.');
    }

    public function detachLabel(Ticket $ticket, Label $label)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->isNoc()) {
            abort(403);
        }

        $ticket->labels()->detach($label->id);

        return redirect()->route('tickets.show', $ticket)->with('success', 'Label removed.');
    }

    public function bulkAction(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'ticket_ids'  => 'required|array',
            'ticket_ids.*' => 'exists:tickets,id',
            'action'      => 'required|in:assign,status,delete',
            'assigned_to' => 'nullable|exists:users,id',
            'bulk_status' => 'nullable|in:in_progress,pending,waiting_for_customer_feedback,resolved',
        ]);

        if ($request->action === 'delete') {
            if (!auth()->user()->isSuperAdmin()) {
                abort(403, 'Only Super Admin can permanently delete tickets.');
            }
            $tickets = Ticket::with(['attachments', 'messages', 'notes', 'history', 'labels', 'subtasks'])->whereIn('id', $request->ticket_ids)->get();
            $count = 0;
            foreach ($tickets as $ticket) {
                $this->purgeTicket($ticket);
                $count++;
            }
            return redirect()->route('tickets.index')->with('success', "{$count} ticket(s) permanently deleted.");
        }

        $tickets = Ticket::whereIn('id', $request->ticket_ids)->get();
        $count = 0;

        foreach ($tickets as $ticket) {
            if ($request->action === 'assign' && $request->filled('assigned_to')) {
                $ticket->update(['assigned_to' => $request->assigned_to]);
                $action = 'Ticket reassigned (bulk action)';
                $newAssignee = $request->assigned_to;
            } elseif ($request->action === 'status' && $request->filled('bulk_status')) {
                $statusUpdate = ['status' => $request->bulk_status];
                if ($request->bulk_status === 'resolved') {
                    $statusUpdate['resolved_at'] = $ticket->resolved_at ?? now();
                } elseif ($ticket->status === 'resolved') {
                    $statusUpdate['resolved_at'] = null;
                }
                $ticket->update($statusUpdate);
                $action = 'Status changed to ' . $request->bulk_status . ' (bulk action)';
                $newAssignee = null;
            } else {
                continue;
            }

            TicketHistory::create([
                'ticket_id'       => $ticket->id,
                'action'          => $action,
                'old_assignee_id' => null,
                'new_assignee_id' => $newAssignee ?? null,
                'changed_by'      => auth()->id(),
            ]);

            $count++;
        }

        return redirect()->route('tickets.index')->with('success', "Bulk action applied to {$count} ticket(s).");
    }

    public function submitCsat(Request $request, string $id)
    {
        $user = auth()->user();
        $ticket = Ticket::findOrFail($id);

        if ($ticket->created_by !== $user->id) {
            abort(403);
        }
        if (!in_array($ticket->status, ['resolved', 'closed'])) {
            abort(422, 'Ticket is not resolved yet.');
        }
        if ($ticket->csat_submitted_at) {
            return back()->with('success', 'You already rated this ticket.');
        }

        $validated = $request->validate([
            'csat_rating'  => 'required|integer|min:1|max:5',
            'csat_comment' => 'nullable|string|max:1000',
        ]);

        $ticket->update([
            'csat_rating'       => $validated['csat_rating'],
            'csat_comment'      => $validated['csat_comment'] ?? null,
            'csat_submitted_at' => now(),
        ]);

        TicketHistory::create([
            'ticket_id'  => $ticket->id,
            'action'     => "Customer rated {$validated['csat_rating']}/5",
            'changed_by' => $user->id,
        ]);

        return back()->with('success', 'Thanks for your feedback!');
    }

    public function showMergeForm(string $id)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->isNoc()) abort(403);

        $ticket = Ticket::with('creator')->findOrFail($id);
        $candidates = Ticket::where('id', '!=', $ticket->id)
            ->whereNull('merged_into_id')
            ->whereNull('parent_id')
            ->orderByDesc('id')
            ->limit(200)
            ->get(['id', 'ticket_key', 'title', 'status']);

        return view('tickets.merge', compact('ticket', 'candidates'));
    }

    public function merge(Request $request, string $id)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->isNoc()) abort(403);

        $validated = $request->validate([
            'target_id' => 'required|exists:tickets,id|different:id',
        ]);

        $source = Ticket::findOrFail($id);
        $target = Ticket::findOrFail($validated['target_id']);

        if ($source->merged_into_id || $target->merged_into_id) {
            return back()->withErrors(['target_id' => 'One of these tickets is already merged.']);
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($source, $target, $user) {
            $source->messages()->update(['ticket_id' => $target->id]);
            $source->notes()->update(['ticket_id' => $target->id]);
            $source->attachments()->update(['ticket_id' => $target->id]);

            TicketHistory::create([
                'ticket_id'  => $target->id,
                'action'     => "Merged ticket #{$source->id} ({$source->title}) into this ticket",
                'changed_by' => $user->id,
            ]);

            $source->update([
                'status'         => 'closed',
                'merged_into_id' => $target->id,
            ]);

            TicketHistory::create([
                'ticket_id'  => $source->id,
                'action'     => "Merged into ticket #{$target->id} ({$target->title})",
                'changed_by' => $user->id,
            ]);
        });

        return redirect()->route('tickets.show', $target)->with('success', "Ticket #{$source->id} merged into this ticket.");
    }

    public function destroy(Ticket $ticket)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin()) {
            abort(403, 'Only Super Admin can delete tickets.');
        }

        $ticketKey = $ticket->ticket_key ?? '#' . $ticket->id;
        $this->purgeTicket($ticket);

        return redirect()->route('tickets.index')->with('success', "Ticket {$ticketKey} and all associated data permanently deleted.");
    }

    private function purgeTicket(Ticket $ticket): void
    {
        // 1. Delete attachments and physical files
        foreach ($ticket->attachments as $att) {
            if ($att->file_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($att->file_path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($att->file_path);
            }
            $att->delete();
        }

        // 2. Delete messages, message images, and reactions
        foreach ($ticket->messages as $msg) {
            if ($msg->image_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($msg->image_path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($msg->image_path);
            }
            $msg->reactions()->delete();
            $msg->delete();
        }

        // 3. Delete notes
        $ticket->notes()->delete();

        // 4. Delete history
        $ticket->history()->delete();

        // 5. Detach labels
        $ticket->labels()->detach();

        // 6. Delete links
        \App\Models\TicketLink::where('ticket_id', $ticket->id)
            ->orWhere('linked_ticket_id', $ticket->id)
            ->delete();

        // 7. Delete subtasks
        foreach ($ticket->subtasks as $subtask) {
            foreach ($subtask->attachments as $subAtt) {
                if ($subAtt->file_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($subAtt->file_path)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($subAtt->file_path);
                }
                $subAtt->delete();
            }
            $subtask->messages()->delete();
            $subtask->notes()->delete();
            $subtask->history()->delete();
            $subtask->labels()->detach();
            $subtask->delete();
        }

        // 8. Delete notifications
        \App\Models\Notification::where('ticket_id', $ticket->id)->delete();

        // 9. Delete ticket record
        $ticket->delete();
    }
}
