<?php

namespace App\Http\Controllers;

use App\Mail\NewChatMessage;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\TicketMessageReaction;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TicketMessageController extends Controller
{
    // Quick-access set (shown by default) + the full picker set (shown behind "More").
    public const QUICK_EMOJI = ['👍', '❤️', '😂', '🎉'];

    public const ALL_EMOJI = [
        '👍', '👎', '❤️', '😂', '😮', '😢', '🎉', '👀', '🙏', '🔥',
        '💯', '✅', '❌', '⚡', '👏', '😍', '🤔', '😅', '😡', '🥳',
        '🚀', '💡', '👌', '🙌', '😴', '🤝', '💪', '⭐', '🕐', '📌',
    ];

    public function poll(Request $request, Ticket $ticket)
    {
        $user = auth()->user();
        if (! Ticket::where('id', $ticket->id)->forUser($user)->exists()) {
            abort(403);
        }

        $after = (int) $request->get('after', 0);

        $messages = $ticket->messages()
            ->with(['sender', 'replyTo.sender', 'reactions.user'])
            ->where('id', '>', $after)
            ->orderBy('id')
            ->get()
            ->filter(fn ($msg) => $msg->canViewPrivate($user))
            ->map(fn ($msg) => $this->serialize($msg, $user))
            ->values();

        // Get active typing users (excluding self)
        $cacheKey = "ticket:{$ticket->id}:typing";
        $typingList = Cache::get($cacheKey, []);
        $now = now()->timestamp;
        $activeTyping = [];

        foreach ($typingList as $uId => $item) {
            if ((int) $uId !== (int) $user->id && ($now - ($item['time'] ?? 0)) < 4) {
                $activeTyping[] = $item['name'].($item['role'] ? ' ('.$item['role'].')' : '');
            }
        }

        return response()->json([
            'messages' => $messages,
            'typing' => $activeTyping,
        ]);
    }

    public function typing(Request $request, Ticket $ticket)
    {
        $user = auth()->user();
        if (! Ticket::where('id', $ticket->id)->forUser($user)->exists()) {
            abort(403);
        }

        $cacheKey = "ticket:{$ticket->id}:typing";
        $typingList = Cache::get($cacheKey, []);
        $now = now()->timestamp;

        // Filter entries older than 4 seconds
        $typingList = array_filter($typingList, fn ($item) => ($now - ($item['time'] ?? 0)) < 4);

        $typingList[$user->id] = [
            'id' => $user->id,
            'name' => $user->name,
            'role' => strtoupper($user->role),
            'time' => $now,
        ];

        Cache::put($cacheKey, $typingList, now()->addSeconds(10));

        return response()->json(['status' => 'ok']);
    }

    private function serialize(TicketMessage $msg, User $user): array
    {
        $imgUrls = $msg->image_urls;

        return [
            'id' => $msg->id,
            'isMe' => $msg->sender_id === $user->id,
            'is_private' => (bool) $msg->is_private,
            'senderName' => $msg->sender->name ?? 'Unknown',
            'senderRole' => $msg->sender->role ?? 'unknown',
            'avatarUrl' => $msg->sender ? $msg->sender->avatarUrl() : '',
            'message' => $msg->message,
            'formatted_message' => $msg->formatted_message ?? $msg->message,
            'image_url' => count($imgUrls) > 0 ? $imgUrls[0] : null,
            'images' => $imgUrls,
            'time' => $msg->created_at->diffForHumans(),
            'replyTo' => $msg->replyTo ? [
                'id' => $msg->replyTo->id,
                'senderName' => $msg->replyTo->sender->name ?? 'Unknown',
                'preview' => Str::limit(strip_tags($msg->replyTo->message ?? '📎 Image'), 80),
            ] : null,
            'reactions' => $msg->reactionSummary($user->id),
        ];
    }

    public function store(Request $request, Ticket $ticket)
    {
        $user = auth()->user();

        if (! Ticket::where('id', $ticket->id)->forUser($user)->exists()) {
            abort(403);
        }

        $request->validate([
            'message' => 'nullable|string|max:5000',
            'image' => 'nullable|image|max:4096',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|max:4096',
            'reply_to_id' => 'nullable|exists:ticket_messages,id',
            'is_private' => 'nullable|boolean',
        ]);

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if ($file && $file->isValid()) {
                    $imagePaths[] = $file->store('ticket-images', 'public');
                }
            }
        } elseif ($request->hasFile('image')) {
            $imagePaths[] = $request->file('image')->store('ticket-images', 'public');
        }

        $imagePathValue = null;
        if (! empty($imagePaths)) {
            $imagePathValue = count($imagePaths) === 1 ? $imagePaths[0] : json_encode(array_values($imagePaths));
        }

        if (! $request->filled('message') && empty($imagePaths)) {
            return back()->withErrors(['message' => 'Please enter a message or attach an image.']);
        }

        // Only allow replying to a message that actually belongs to this ticket.
        $replyToId = null;
        if ($request->filled('reply_to_id')) {
            $replyToId = TicketMessage::where('id', $request->reply_to_id)
                ->where('ticket_id', $ticket->id)
                ->value('id');
        }

        // Process mentions to wrap unformatted @User Name in <span class="mention-chip">
        $rawMessage = $request->message;
        if ($rawMessage && (str_contains($rawMessage, '&lt;') || str_contains($rawMessage, '&gt;'))) {
            $rawMessage = html_entity_decode($rawMessage, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        [$finalMessage, $mentionedIds] = $this->processMentions($rawMessage);

        $message = TicketMessage::create([
            'ticket_id' => $ticket->id,
            'sender_id' => $user->id,
            'reply_to_id' => $replyToId,
            'message' => $finalMessage,
            'image_path' => $imagePathValue,
            'is_private' => $user->isReseller() ? false : $request->boolean('is_private'),
        ]);

        // Notify mentioned users (except sender)
        $notifiedIds = [];
        foreach ($mentionedIds as $mid) {
            if ($mid === $user->id) {
                continue;
            }
            $mentioned = User::find($mid);
            if ($mentioned) {
                if ($message->is_private && $mentioned->isReseller()) {
                    continue;
                }
                NotificationService::send($mentioned->id, "🔔 {$user->name} mentioned you in ticket #{$ticket->ticket_key}", $ticket->id);
                $notifiedIds[] = $mid;
            }
        }

        // Notify the message this one replies to (if not already notified/self)
        if ($message->replyTo && $message->replyTo->sender_id !== $user->id && ! in_array($message->replyTo->sender_id, $notifiedIds)) {
            $replyUser = $message->replyTo->sender;
            if (! ($message->is_private && $replyUser && $replyUser->isReseller())) {
                NotificationService::send($message->replyTo->sender_id, "↩️ {$user->name} replied to your message on ticket #{$ticket->ticket_key}", $ticket->id);
                $notifiedIds[] = $message->replyTo->sender_id;
            }
        }

        // Notify creator and assignee (except sender and already notified)
        $notify = collect();
        if ($ticket->creator && $ticket->creator->id !== $user->id && ! in_array($ticket->creator->id, $notifiedIds)) {
            if (! ($message->is_private && $ticket->creator->isReseller())) {
                $notify->push($ticket->creator);
            }
        }
        if ($ticket->assignee && $ticket->assignee->id !== $user->id
            && ! in_array($ticket->assignee->id, $notifiedIds)
            && $ticket->assignee->id !== ($ticket->creator->id ?? null)) {
            $notify->push($ticket->assignee);
        }

        $plainText = strip_tags($request->message ?? '');
        $preview = $user->name.': '.(strlen($plainText) > 60 ? substr($plainText, 0, 60).'…' : ($plainText ?: '📎 Image'));

        foreach ($notify as $recipient) {
            if ($recipient->email && $recipient->notify_on_message) {
                try {
                    Mail::to($recipient->email)->send(new NewChatMessage($ticket));
                } catch (\Exception $e) {
                }
            }
            NotificationService::send($recipient->id, "💬 New message on ticket #{$ticket->id}: {$preview}", $ticket->id);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => 'ok',
                'message' => $this->serialize($message, $user),
            ]);
        }

        return back()->with('success', 'Message sent.');
    }

    public function update(Request $request, Ticket $ticket, TicketMessage $message)
    {
        $user = auth()->user();

        if ($message->ticket_id !== $ticket->id) {
            abort(404);
        }

        if (! $user->isAdmin() && $message->sender_id !== $user->id) {
            abort(403, 'Unauthorized to edit this message.');
        }

        $request->validate([
            'message' => 'required|string|max:5000',
            'is_private' => 'nullable|boolean',
        ]);

        $rawMessage = $request->message;
        if ($rawMessage && (str_contains($rawMessage, '&lt;') || str_contains($rawMessage, '&gt;'))) {
            $rawMessage = html_entity_decode($rawMessage, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        [$finalMessage, $mentionedIds] = $this->processMentions($rawMessage);

        $message->update([
            'message' => $finalMessage,
            'is_private' => $user->isReseller() ? false : $request->boolean('is_private'),
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => 'ok',
                'message' => $this->serialize($message, $user),
            ]);
        }

        return back()->with('success', 'Message updated.');
    }

    public function toggleReaction(Request $request, Ticket $ticket, TicketMessage $message)
    {
        $user = auth()->user();
        if ($user->isReseller() && $ticket->created_by !== $user->id) {
            abort(403);
        }
        if ($message->ticket_id !== $ticket->id) {
            abort(404);
        }

        $validated = $request->validate([
            'emoji' => 'required|string|max:16',
        ]);

        $existing = TicketMessageReaction::where('ticket_message_id', $message->id)
            ->where('user_id', $user->id)
            ->where('emoji', $validated['emoji'])
            ->first();

        if ($existing) {
            $existing->delete();
            $reacted = false;
        } else {
            TicketMessageReaction::create([
                'ticket_message_id' => $message->id,
                'user_id' => $user->id,
                'emoji' => $validated['emoji'],
            ]);
            $reacted = true;

            if ($message->sender_id !== $user->id) {
                NotificationService::send($message->sender_id, "{$validated['emoji']} {$user->name} reacted to your message on ticket #{$ticket->id}", $ticket->id);
            }
        }

        $message->load('reactions.user');

        return response()->json([
            'reacted' => $reacted,
            'reactions' => $message->reactionSummary($user->id),
        ]);
    }

    private function processMentions(?string $message): array
    {
        if (empty($message)) {
            return ['', []];
        }

        $allUsers = User::all(['id', 'name']);
        $usersSorted = $allUsers->sortByDesc(fn ($u) => strlen($u->name));

        $mentionedIds = [];

        // 1. Extract IDs already present in <span class="mention-chip" data-id="X">
        preg_match_all('/class="mention-chip"[^>]*data-id="(\d+)"/', $message, $matches);
        if (! empty($matches[1])) {
            $mentionedIds = array_map('intval', $matches[1]);
        }

        // 2. Automatically wrap any un-wrapped @UserName or @Name in the text
        foreach ($usersSorted as $user) {
            $name = preg_quote($user->name, '/');
            $pattern = '/(<span class="mention-chip"[^>]*>.*?<\/span>)|@('.$name.')\b/i';

            $message = preg_replace_callback($pattern, function ($m) use ($user, &$mentionedIds) {
                if (! empty($m[1])) {
                    return $m[1];
                }
                $mentionedIds[] = $user->id;

                return '<span class="mention-chip" data-id="'.$user->id.'">@'.e($user->name).'</span>';
            }, $message);
        }

        return [$message, array_unique($mentionedIds)];
    }
}
