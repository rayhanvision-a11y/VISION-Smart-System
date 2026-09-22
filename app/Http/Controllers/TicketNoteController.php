<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketNote;
use Illuminate\Http\Request;

class TicketNoteController extends Controller
{
    public function store(Request $request, Ticket $ticket)
    {
        $user = auth()->user();
        if ($user->isReseller()) {
            abort(403, 'Resellers cannot create internal notes.');
        }

        $request->validate(['note' => 'required|string']);

        TicketNote::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'note' => $request->note,
            'type' => 'internal',
            'is_internal' => true,
        ]);

        return redirect()->route('tickets.show', $ticket)->with('success', 'Internal note added.');
    }
}
