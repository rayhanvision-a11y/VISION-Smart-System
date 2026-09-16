<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\TicketMessage;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Receives inbound email and turns it into a ticket (or a reply on an
 * existing one), so customers can email support@yourdomain instead of
 * only using the web form.
 *
 * Setup: point your mail provider's inbound-parse webhook at
 * POST /webhooks/inbound-email. Configured out of the box for Mailgun's
 * payload shape (sender, subject, body-plain, stripped-text); adjust the
 * field names in parsePayload() if you use Postmark/SendGrid/SES instead.
 * Protect the endpoint by setting MAIL_WEBHOOK_SECRET in .env and passing
 * it as ?secret=... in the webhook URL you give your provider.
 */
class InboundEmailController extends Controller
{
    public function handle(Request $request)
    {
        $secret = config('services.inbound_email.secret');
        if (!$secret) {
            abort(500, 'Webhook secret not configured.');
        }
        $provided = (string) ($request->header('X-Webhook-Secret') ?? $request->get('secret') ?? '');
        if (!hash_equals($secret, $provided)) {
            abort(403);
        }

        [$fromEmail, $subject, $body] = $this->parsePayload($request);

        if (!$fromEmail) {
            Log::warning('Inbound email webhook: no sender email found', $request->all());
            return response()->json(['status' => 'ignored'], 200);
        }

        $user = User::where('email', $fromEmail)->first();
        if (!$user) {
            Log::info("Inbound email from unknown address {$fromEmail} — ignored.");
            return response()->json(['status' => 'unknown_sender'], 200);
        }

        // Reply to existing ticket via "Re: [TKT-123] ..." style subject
        if ($subject && preg_match('/TKT-(\d+)/i', $subject, $m)) {
            $ticket = Ticket::where('id', $m[1])->first();
            if ($ticket && ($user->isAdmin() || $ticket->created_by === $user->id)) {
                TicketMessage::create([
                    'ticket_id' => $ticket->id,
                    'sender_id' => $user->id,
                    'message'   => nl2br(e($body)),
                ]);
                TicketHistory::create([
                    'ticket_id'  => $ticket->id,
                    'action'     => 'Reply received via email',
                    'changed_by' => $user->id,
                ]);
                return response()->json(['status' => 'reply_added', 'ticket_id' => $ticket->id]);
            }
        }

        // Otherwise create a brand new ticket
        $ticket = Ticket::create([
            'title'       => $subject ?: 'Email support request',
            'description' => $body ?: '(no body)',
            'category'    => 'other',
            'priority'    => 'medium',
            'status'      => 'in_progress',
            'created_by'  => $user->id,
        ]);
        $ticket->update(['ticket_key' => \App\Models\Ticket::generateKey()]);
        $ticket->update(['due_at' => now()->addHours(24)]);

        TicketHistory::create([
            'ticket_id'  => $ticket->id,
            'action'     => 'Ticket created via inbound email',
            'changed_by' => $user->id,
        ]);

        $notifyIds = User::whereIn('role', ['super_admin', 'admin', 'noc'])->pluck('id')->toArray();
        NotificationService::sendToMany($notifyIds, "📧 New ticket #{$ticket->id} via email from {$user->name}: {$ticket->title}", $ticket->id);

        return response()->json(['status' => 'ticket_created', 'ticket_id' => $ticket->id]);
    }

    /**
     * @return array{0: ?string, 1: ?string, 2: ?string} [fromEmail, subject, bodyText]
     */
    private function parsePayload(Request $request): array
    {
        // Mailgun-style
        if ($request->has('sender') || $request->has('from')) {
            $from = $request->get('sender', $request->get('from', ''));
            preg_match('/[\w.+\-]+@[\w\-]+\.[\w.\-]+/', $from, $m);
            $email = $m[0] ?? null;

            $subject = $request->get('subject');
            $body = $request->get('stripped-text') ?? $request->get('body-plain');

            return [$email ? strtolower($email) : null, $subject, $body];
        }

        return [null, null, null];
    }
}
