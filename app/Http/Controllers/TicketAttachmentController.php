<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TicketAttachmentController extends Controller
{
    private const MAX_KB = 10240; // 10MB
    private const ALLOWED = 'jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,txt,csv,zip,log,conf,cfg';

    public function store(Request $request, Ticket $ticket)
    {
        $user = auth()->user();
        if ($user->isReseller() && $ticket->created_by !== $user->id) {
            abort(403);
        }

        $request->validate([
            'files'   => 'required|array|max:5',
            'files.*' => 'file|max:' . self::MAX_KB . '|mimes:' . self::ALLOWED,
        ]);

        foreach ($request->file('files') as $file) {
            $path = $file->store('ticket-attachments/' . $ticket->id, 'public');

            TicketAttachment::create([
                'ticket_id'      => $ticket->id,
                'uploaded_by'    => $user->id,
                'path'           => $path,
                'original_name'  => $file->getClientOriginalName(),
                'mime_type'      => $file->getMimeType(), // server-detected, not client-supplied
                'size'           => $file->getSize(),
            ]);
        }

        TicketHistory::create([
            'ticket_id'  => $ticket->id,
            'action'     => count($request->file('files')) . ' file(s) attached',
            'changed_by' => $user->id,
        ]);

        return back()->with('success', 'Attachment(s) uploaded.');
    }

    public function destroy(Ticket $ticket, TicketAttachment $attachment)
    {
        $user = auth()->user();
        if ($attachment->ticket_id !== $ticket->id) abort(404);
        if (!$user->isAdmin() && $attachment->uploaded_by !== $user->id) {
            abort(403);
        }

        Storage::disk('public')->delete($attachment->path);
        $attachment->delete();

        return back()->with('success', 'Attachment removed.');
    }

    public function download(Ticket $ticket, TicketAttachment $attachment)
    {
        $user = auth()->user();
        if ($user->isReseller() && $ticket->created_by !== $user->id) abort(403);
        if ($attachment->ticket_id !== $ticket->id) abort(404);

        return Storage::disk('public')->download($attachment->path, $attachment->original_name);
    }
}
