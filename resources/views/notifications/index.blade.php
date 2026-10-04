<x-app-layout>
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-slate-800">{{ __('Notifications') }}</h1>
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <button type="submit"
                        class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                    {{ __('Mark all as read') }}
                </button>
            </form>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            @forelse($notifications as $notification)
                <a href="{{ route('notifications.read', $notification->id) }}"
                   class="flex items-start gap-4 px-5 py-4 border-b border-slate-100 dark:border-slate-800 last:border-0 transition-colors group cursor-pointer block {{ $notification->is_read ? 'bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800/80' : 'bg-indigo-50/70 dark:bg-indigo-950/40 hover:bg-indigo-100/70 dark:hover:bg-indigo-900/50' }}">
                    {{-- Icon --}}
                    <div class="flex-shrink-0 mt-0.5">
                        @if(str_contains($notification->message, '✅'))
                            <div class="w-9 h-9 rounded-full bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center text-lg">✅</div>
                        @elseif(str_contains($notification->message, '🔁'))
                            <div class="w-9 h-9 rounded-full bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center text-lg">🔁</div>
                        @elseif(str_contains($notification->message, '💬'))
                            <div class="w-9 h-9 rounded-full bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center text-lg">💬</div>
                        @elseif(str_contains($notification->message, '📋'))
                            <div class="w-9 h-9 rounded-full bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center text-lg">📋</div>
                        @else
                            <div class="w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                            </div>
                        @endif
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-slate-700 dark:text-slate-200 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors {{ $notification->is_read ? '' : 'font-bold text-slate-900 dark:text-white' }}">
                            {{ $notification->message }}
                        </p>
                        <p class="text-xs text-slate-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>

                    {{-- Actions --}}
                    <div class="flex-shrink-0 flex items-center gap-2">
                        @if(!$notification->is_read)
                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                        @endif
                        @if($notification->ticket_id)
                            <span class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold whitespace-nowrap group-hover:translate-x-0.5 transition-transform flex items-center gap-1">
                                {{ __('View Ticket') }} &rarr;
                            </span>
                        @endif
                    </div>
                </a>
            @empty
                <div class="px-5 py-16 text-center">
                    <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <p class="text-slate-500 font-medium">{{ __('No notifications yet') }}</p>
                    <p class="text-slate-400 text-sm mt-1">{{ __("You'll see ticket updates here") }}</p>
                </div>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $notifications->links() }}
        </div>
    </div>
</x-app-layout>
