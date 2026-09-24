<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PopOffice;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketMessage;
use App\Models\TicketAttachment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiTicketController extends Controller
{
    /**
     * List tickets accessible by current user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Ticket::forUser($user)
            ->with(['assignedTo:id,name,role', 'user:id,name'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('area')) {
            $query->where('area', $request->input('area'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('ticket_key', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            });
        }

        $tickets = $query->paginate(20);

        return response()->json($tickets);
    }

    /**
     * Display ticket details.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $ticket = Ticket::forUser($user)
            ->with([
                'assignedTo:id,name,email,role',
                'user:id,name,email',
                'popOffice:id,name',
                'messages.sender:id,name,email,avatar,role',
                'messages.replyTo.sender:id,name,email',
                'messages.reactions.user:id,name,email',
                'notes.user:id,name,email,avatar,role',
                'attachments',
            ])
            ->findOrFail($id);

        $messages = collect();

        // 1. Add ticket messages
        foreach ($ticket->messages as $m) {
            if ($m->canViewPrivate($user)) {
                $messages->push([
                    'id' => $m->id,
                    'ticket_id' => $m->ticket_id,
                    'sender_id' => $m->sender_id,
                    'sender' => $m->sender ? [
                        'id' => $m->sender->id,
                        'name' => $m->sender->name,
                        'avatar' => $m->sender->avatarUrl(),
                        'role' => $m->sender->role,
                    ] : null,
                    'message' => $m->message,
                    'is_private' => (bool) $m->is_private,
                    'created_at' => $m->created_at ? $m->created_at->toDateTimeString() : null,
                    'reply_to' => $m->replyTo ? [
                        'id' => $m->replyTo->id,
                        'sender' => $m->replyTo->sender ? ['name' => $m->replyTo->sender->name] : null,
                        'message' => \Illuminate\Support\Str::limit(strip_tags($m->replyTo->message ?? ''), 80),
                    ] : null,
                    'reactions' => $m->reactionSummary($user->id),
                ]);
            }
        }

        // 2. Add ticket internal notes (for staff/admin)
        if (! $user->isReseller()) {
            foreach ($ticket->notes as $n) {
                $messages->push([
                    'id' => 900000 + $n->id,
                    'ticket_id' => $n->ticket_id,
                    'sender_id' => $n->user_id,
                    'sender' => $n->user ? [
                        'id' => $n->user->id,
                        'name' => $n->user->name,
                        'avatar' => $n->user->avatarUrl(),
                        'role' => $n->user->role,
                    ] : null,
                    'message' => $n->note ?? '',
                    'is_private' => true,
                    'created_at' => $n->created_at ? $n->created_at->toDateTimeString() : null,
                    'reply_to' => null,
                    'reactions' => [],
                ]);
            }
        }

        // Sort all activity chronologically
        $sortedMessages = $messages->sortBy('created_at')->values();

        $ticketData = $ticket->toArray();
        $ticketData['messages'] = $sortedMessages;

        return response()->json([
            'ticket' => $ticketData,
        ]);
    }

    /**
     * Create a new ticket.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'category' => 'nullable|string',
            'pop_office_id' => 'nullable|exists:pop_offices,id',
            'assigned_to' => 'nullable|exists:users,id',
            'due_at' => 'nullable|date',
            'area' => 'nullable|string|max:120',
        ]);

        $ticket = Ticket::create([
            'ticket_key' => Ticket::generateKey(),
            'title' => $validated['title'],
            'description' => $validated['description'],
            'priority' => $validated['priority'],
            'category' => $validated['category'] ?? 'other',
            'pop_office_id' => $validated['pop_office_id'] ?? null,
            'assigned_to' => $validated['assigned_to'] ?? null,
            'due_at' => $validated['due_at'] ?? null,
            'area' => isset($validated['area']) ? trim($validated['area']) : null,
            'status' => 'in_progress',
            'created_by' => $user->id,
        ]);

        return response()->json([
            'message' => 'Ticket created successfully',
            'ticket' => $ticket->load(['user']),
        ], 201);
    }

    /**
     * Update ticket status.
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $ticket = Ticket::forUser($user)->findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:in_progress,pending,waiting_for_customer_feedback,resolved',
        ]);

        $ticket->update([
            'status' => $validated['status'],
            'resolved_at' => $validated['status'] === 'resolved' ? now() : $ticket->resolved_at,
        ]);

        return response()->json([
            'message' => 'Ticket status updated successfully',
            'ticket' => $ticket,
        ]);
    }

    /**
     * Add reply message to ticket.
     */
    public function addMessage(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $ticket = Ticket::forUser($user)->findOrFail($id);

        $validated = $request->validate([
            'message' => 'required|string',
            'is_private' => 'nullable|boolean',
            'reply_to_id' => 'nullable|integer',
        ]);

        $isPrivate = $user->isReseller() ? false : ($validated['is_private'] ?? false);

        $message = TicketMessage::create([
            'ticket_id' => $ticket->id,
            'sender_id' => $user->id,
            'message' => $validated['message'],
            'is_private' => $isPrivate,
            'reply_to_id' => $validated['reply_to_id'] ?? null,
        ]);

        if ($isPrivate) {
            \App\Models\TicketNote::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'note' => $validated['message'],
                'is_internal' => true,
            ]);
        }

        return response()->json([
            'message' => 'Reply added successfully',
            'data' => $message->load('sender:id,name,avatar,role'),
        ], 201);
    }

    /**
     * Assign ticket to a staff member.
     */
    public function assign(Request $request, int $id): JsonResponse
    {
        $currentUser = $request->user();

        if ($currentUser->isReseller() || $currentUser->isTechnician()) {
            return response()->json(['error' => 'Not authorized to assign tickets'], 403);
        }

        $ticket = Ticket::forUser($currentUser)->findOrFail($id);

        $validated = $request->validate([
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $ticket->update([
            'assigned_to' => $validated['assigned_to'] ?? null,
        ]);

        return response()->json([
            'message' => 'Ticket assigned successfully',
            'ticket' => $ticket->fresh(['assignedTo:id,name,email,role']),
        ]);
    }

    /**
     * Update ticket priority.
     */
    public function updatePriority(Request $request, int $id): JsonResponse
    {
        $currentUser = $request->user();

        if ($currentUser->isReseller() || $currentUser->isTechnician()) {
            return response()->json(['error' => 'Not authorized to change priority'], 403);
        }

        $ticket = Ticket::forUser($currentUser)->findOrFail($id);

        $validated = $request->validate([
            'priority' => 'required|in:low,medium,high,urgent',
        ]);

        $ticket->update([
            'priority' => $validated['priority'],
        ]);

        return response()->json([
            'message' => 'Ticket priority updated successfully',
            'ticket' => $ticket,
        ]);
    }

    /**
     * Get active ticket categories.
     */
    public function categories(): JsonResponse
    {
        $categories = TicketCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'slug']);

        return response()->json(['categories' => $categories]);
    }

    /**
     * Get POP offices.
     */
    public function popOffices(): JsonResponse
    {
        $offices = PopOffice::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json(['pop_offices' => $offices]);
    }

    /**
     * Get eligible staff for ticket assignment.
     */
    public function staff(): JsonResponse
    {
        $staff = User::whereIn('role', ['admin', 'super_admin', 'noc', 'call_center', 'supervisor', 'senior_supervisor', 'technician'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role', 'team']);

        return response()->json(['staff' => $staff]);
    }

    /**
     * List technicians with current workload count (active tickets).
     */
    public function technicians(Request $request): JsonResponse
    {
        $technicians = User::where('role', 'technician')
            ->where('is_active', true)
            ->withCount(['assignedTickets as active_count' => function ($q) {
                $q->whereIn('status', ['in_progress', 'pending', 'waiting_for_customer_feedback']);
            }])
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role', 'team']);

        return response()->json(['technicians' => $technicians]);
    }

    /**
     * Distinct area autocomplete (from existing tickets).
     */
    public function areas(Request $request): JsonResponse
    {
        $search = $request->input('search');
        $query = Ticket::query()
            ->whereNotNull('area')
            ->where('area', '!=', '');
        if ($search) {
            $query->where('area', 'like', '%'.$search.'%');
        }
        $areas = $query->distinct()
            ->orderBy('area')
            ->limit(50)
            ->pluck('area');

        return response()->json(['areas' => $areas]);
    }

    /**
     * Bulk-assign multiple tickets to a single user (supervisor/admin only).
     */
    public function bulkAssign(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! ($user->isSupervisorLevel() || $user->isAdmin() || $user->isNoc())) {
            return response()->json(['error' => 'Not authorized'], 403);
        }

        $validated = $request->validate([
            'ticket_ids' => 'required|array|min:1',
            'ticket_ids.*' => 'integer',
            'assigned_to' => 'required|exists:users,id',
        ]);

        $affected = Ticket::forUser($user)
            ->whereIn('id', $validated['ticket_ids'])
            ->update(['assigned_to' => $validated['assigned_to']]);

        return response()->json([
            'message' => "$affected tickets assigned",
            'count' => $affected,
        ]);
    }

    /**
     * Upload attachment(s) to a ticket.
     */
    public function uploadAttachment(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $ticket = Ticket::forUser($user)->findOrFail($id);

        $request->validate([
            'file' => 'required|file|max:10240|mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx,txt',
        ]);

        $file = $request->file('file');
        $filename = 'ticket_'.$ticket->id.'_'.time().'_'.preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
        $file->storeAs('ticket-attachments', $filename, 'public');

        $attachment = TicketAttachment::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => 'ticket-attachments/'.$filename,
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
        ]);

        return response()->json([
            'message' => 'Attachment uploaded',
            'attachment' => [
                'id' => $attachment->id,
                'file_name' => $attachment->file_name,
                'url' => asset('storage/'.$attachment->file_path),
                'mime_type' => $attachment->mime_type,
                'file_size' => $attachment->file_size,
            ],
        ], 201);
    }

    /**
     * Toggle emoji reaction on a message.
     */
    public function toggleReaction(Request $request, int $ticketId, int $messageId): JsonResponse
    {
        $user = $request->user();
        $ticket = Ticket::forUser($user)->findOrFail($ticketId);
        $message = TicketMessage::where('ticket_id', $ticket->id)->findOrFail($messageId);

        $validated = $request->validate([
            'emoji' => 'required|string|max:16',
        ]);

        $existing = \App\Models\TicketMessageReaction::where('ticket_message_id', $message->id)
            ->where('user_id', $user->id)
            ->where('emoji', $validated['emoji'])
            ->first();

        if ($existing) {
            $existing->delete();
            $reacted = false;
        } else {
            \App\Models\TicketMessageReaction::create([
                'ticket_message_id' => $message->id,
                'user_id' => $user->id,
                'emoji' => $validated['emoji'],
            ]);
            $reacted = true;
        }

        $message->load('reactions.user');

        return response()->json([
            'reacted' => $reacted,
            'reactions' => $message->reactionSummary($user->id),
        ]);
    }
}
