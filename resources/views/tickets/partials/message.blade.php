@php
    $isMe = $msg->sender_id === $user->id;
    $senderRole = $msg->sender->role ?? 'unknown';
    $roleClass = $roleColors[$senderRole] ?? 'bg-slate-100 text-slate-600';
    $reactions = $msg->reactionSummary($user->id);
    $quickEmojis = ['👍', '❤️', '😂', '😮', '😢', '🎉'];
@endphp
<div class="flex {{ $isMe ? 'justify-end' : 'justify-start' }}" data-message-id="{{ $msg->id }}">
    @if(!$isMe)<img src="{{ $msg->sender->avatarUrl() }}" alt="{{ $msg->sender->name ?? '' }}" class="w-7 h-7 rounded-full object-cover mr-2 flex-shrink-0 mt-1">@endif
    <div class="max-w-sm">
        <div class="flex items-center gap-2 mb-1 {{ $isMe ? 'justify-end' : '' }}">
            <span class="text-xs font-semibold text-slate-600">{{ $msg->sender->name ?? 'Unknown' }}</span>
            <span class="inline-flex px-1.5 py-0.5 rounded text-xs font-medium {{ $roleClass }}">{{ strtoupper(str_replace('_', ' ', $senderRole)) }}</span>
            @if($msg->is_private)
            <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300 shadow-2xs">
                🔒 {{ __('Private') }}
            </span>
            @endif
            <span class="text-xs text-slate-400" title="{{ $msg->created_at->format('d M Y, H:i') }}">{{ $msg->created_at->diffForHumans() }}</span>
        </div>

        @if($msg->replyTo)
        <div class="mb-1 px-2.5 py-1.5 rounded-lg bg-slate-50 border-l-2 border-indigo-300 text-xs text-slate-500 {{ $isMe ? 'text-right' : '' }}">
            <span class="font-semibold text-slate-600">{{ $msg->replyTo->sender->name ?? __('Unknown') }}</span>:
            {{ \Illuminate\Support\Str::limit(strip_tags($msg->replyTo->message ?? __('📎 Image')), 60) }}
        </div>
        @endif

        <div class="{{ $msg->is_private ? 'bg-amber-50 dark:bg-amber-950/30 border-2 border-amber-300 text-slate-800' : ($isMe ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-800') }} rounded-xl px-4 py-3 shadow-sm">
            @if($msg->message)<div class="text-sm leading-relaxed prose prose-sm max-w-none {{ $isMe && !$msg->is_private ? 'prose-invert' : '' }}">{!! $msg->formatted_message !!}</div>@endif
            @if($msg->image_path)
            <img src="{{ asset('storage/' . $msg->image_path) }}" alt="Attachment"
                 class="mt-2 rounded-lg cursor-pointer hover:opacity-90 transition-opacity"
                 style="max-height:160px; max-width:220px; object-fit:cover; display:block;"
                 onclick="openImgModal(this.src)">
            @endif
        </div>

        {{-- Active Reactions List --}}
        <div class="reactions-container flex flex-wrap items-center gap-1.5 mt-1.5 {{ $isMe ? 'justify-end' : '' }}" id="reactions-list-{{ $msg->id }}">
            @foreach($reactions as $emoji => $info)
            <button type="button"
                    class="msg-react-btn inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full border transition-all {{ $info['reacted'] ? 'bg-indigo-100 border-indigo-300 text-indigo-700 font-semibold shadow-2xs' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100' }}"
                    data-msg-id="{{ $msg->id }}"
                    data-emoji="{{ $emoji }}"
                    title="{{ implode(', ', $info['names']) }}">
                <span>{{ $emoji }}</span>
                <span>{{ $info['count'] }}</span>
            </button>
            @endforeach
        </div>

        {{-- Action Bar (Image 2 Style Toolbar: Reply ↩, Quick Reaction 👍, Add Reaction ☺+, Edit ✏, More ...) --}}
        <div class="flex items-center gap-1 mt-1 {{ $isMe ? 'justify-end' : '' }}">
            {{-- Reply Button Icon ↩ --}}
            <button type="button" class="msg-reply-btn p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-slate-100 transition-colors"
                    data-id="{{ $msg->id }}"
                    data-sender="{{ $msg->sender->name ?? __('Unknown') }}"
                    data-preview="{{ \Illuminate\Support\Str::limit(strip_tags($msg->message ?? __('📎 Image')), 60) }}"
                    title="{{ __('Reply') }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
            </button>

            {{-- Quick Thumbs Up Button 👍 --}}
            <button type="button" class="msg-react-btn p-1.5 rounded-lg text-slate-400 hover:text-amber-500 hover:bg-amber-50 transition-colors"
                    data-msg-id="{{ $msg->id }}"
                    data-emoji="👍"
                    title="{{ __('Thumbs up') }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2"/></svg>
            </button>

            {{-- Add Reaction Button ☺+ (Toggles Image 2 Quick Reaction Floating Bar / Image 3 Picker) --}}
            <button type="button" class="msg-emoji-bar-toggle-btn p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-slate-100 transition-colors"
                    data-msg-id="{{ $msg->id }}"
                    title="{{ __('Add reaction') }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v4m-2-2h4"/></svg>
            </button>

            @if($isMe || $user->isAdmin())
            {{-- Edit Button ✏ --}}
            <button type="button" class="msg-edit-btn p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-slate-100 transition-colors"
                    data-id="{{ $msg->id }}"
                    data-sender="{{ $msg->sender->name ?? __('Unknown') }}"
                    data-message="{{ e($msg->message) }}"
                    data-is-private="{{ $msg->is_private ? 1 : 0 }}"
                    title="{{ __('Edit') }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            </button>
            @endif

            {{-- More Options Button ... --}}
            <button type="button" class="msg-emoji-bar-toggle-btn p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors"
                    data-msg-id="{{ $msg->id }}"
                    title="{{ __('More options') }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"/></svg>
            </button>
        </div>
    </div>
    @if($isMe)<img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}" class="w-7 h-7 rounded-full object-cover ml-2 flex-shrink-0 mt-1">@endif
</div>
