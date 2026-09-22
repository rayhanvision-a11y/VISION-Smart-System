<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketMessage;
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
                'messages.sender:id,name,avatar',
                'messages.attachments',
                'attachments',
            ])
            ->findOrFail($id);

        return response()->json([
            'ticket' => $ticket,
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
        ]);

        $ticket = Ticket::create([
            'ticket_key' => Ticket::generateKey(),
            'title' => $validated['title'],
            'description' => $validated['description'],
            'priority' => $validated['priority'],
            'category' => $validated['category'] ?? 'other',
            'pop_office_id' => $validated['pop_office_id'] ?? null,
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
        ]);

        $message = TicketMessage::create([
            'ticket_id' => $ticket->id,
            'sender_id' => $user->id,
            'message' => $validated['message'],
            'is_private' => $validated['is_private'] ?? false,
        ]);

        return response()->json([
            'message' => 'Reply added successfully',
            'data' => $message->load('sender:id,name,avatar'),
        ], 201);
    }
}
