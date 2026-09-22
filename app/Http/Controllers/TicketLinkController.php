<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketLink;
use Illuminate\Http\Request;

class TicketLinkController extends Controller
{
    public function store(Request $request, Ticket $ticket)
    {
        $user = auth()->user();
        if (! $user->isAdmin() && ! $user->isNoc()) {
            abort(403);
        }

        $request->validate([
            'linked_ticket_id' => 'required|exists:tickets,id|different:ticket_id',
            'link_type' => 'required|in:relates_to,blocks,is_blocked_by,duplicates',
        ]);

        if ($request->linked_ticket_id == $ticket->id) {
            return back()->withErrors(['linked_ticket_id' => 'Cannot link a ticket to itself.']);
        }

        // Prevent duplicate links in either direction
        $exists = TicketLink::where(function ($q) use ($ticket, $request) {
            $q->where('ticket_id', $ticket->id)->where('linked_ticket_id', $request->linked_ticket_id);
        })->orWhere(function ($q) use ($ticket, $request) {
            $q->where('ticket_id', $request->linked_ticket_id)->where('linked_ticket_id', $ticket->id);
        })->exists();

        if ($exists) {
            return back()->with('error', 'These tickets are already linked.');
        }

        TicketLink::create([
            'ticket_id' => $ticket->id,
            'linked_ticket_id' => $request->linked_ticket_id,
            'link_type' => $request->link_type,
            'created_by' => $user->id,
        ]);

        return back()->with('success', 'Work item linked.');
    }

    public function destroy(Ticket $ticket, TicketLink $link)
    {
        $user = auth()->user();
        if (! $user->isAdmin() && ! $user->isNoc()) {
            abort(403);
        }

        if ($link->ticket_id !== $ticket->id && $link->linked_ticket_id !== $ticket->id) {
            abort(404);
        }

        $link->delete();

        return back()->with('success', 'Link removed.');
    }
}
