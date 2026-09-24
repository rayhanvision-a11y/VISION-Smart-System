<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\User;
use Illuminate\Http\Request;

class BoardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Ticket::forUser($user)->with(['assignee', 'labels', 'creator'])
            ->whereNull('parent_id'); // don't show subtasks on board

        // Filters
        if ($request->get('my_tickets')) {
            $query->where('assigned_to', $user->id);
        }
        if ($request->filled('assignee')) {
            $query->where('assigned_to', $request->assignee);
        }
        if ($request->filled('team')) {
            $query->whereHas('assignee', function ($q) use ($request) {
                $q->where('team', $request->team);
            });
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $tickets = $query->orderBy('created_at', 'desc')->get();

        $columns = [
            'in_progress' => ['label' => __('In Progress'),              'color' => 'blue'],
            'pending' => ['label' => __('Pending'),                  'color' => 'orange'],
            'waiting_for_customer_feedback' => ['label' => __('Waiting for Feedback'),     'color' => 'violet'],
            'resolved' => ['label' => __('Resolved'),                 'color' => 'emerald'],
        ];

        $grouped = [];
        foreach (array_keys($columns) as $status) {
            $grouped[$status] = $tickets->where('status', $status)->values();
        }

        $nocUsers = User::whereIn('role', ['super_admin', 'admin', 'noc', 'supervisor', 'senior_supervisor', 'technician'])->where('is_active', true)->get();

        return view('board.index', compact('grouped', 'columns', 'nocUsers'));
    }

    public function move(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'ticket_id' => 'required|exists:tickets,id',
            'new_status' => 'required|in:in_progress,pending,waiting_for_customer_feedback,resolved',
        ]);

        $canView = Ticket::where('id', $request->ticket_id)->forUser($user)->exists();
        if (! $canView || $user->isReseller()) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $ticket = Ticket::findOrFail($request->ticket_id);

        $oldStatus = $ticket->status;
        $ticket->update(['status' => $request->new_status]);

        if ($request->new_status === 'resolved' && ! $ticket->resolved_at) {
            $ticket->update(['resolved_at' => now()]);
        }

        TicketHistory::create([
            'ticket_id' => $ticket->id,
            'action' => 'Status changed from '.$oldStatus.' to '.$request->new_status.' (board drag)',
            'old_assignee_id' => null,
            'new_assignee_id' => null,
            'changed_by' => $user->id,
        ]);

        return response()->json(['success' => true]);
    }
}
