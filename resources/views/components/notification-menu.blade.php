@props(['notifications', 'unread', 'dark' => false])

<div class="relative">
    <button type="button" data-toggle="#notifications-panel" aria-expanded="false" aria-label="Notifications"
        @class([
            'relative grid size-10 place-items-center rounded-full transition',
            'text-ink-300 hover:bg-white/10 hover:text-white' => $dark,
            'text-ink-600 hover:bg-ink-100/70 hover:text-ink-900' => ! $dark,
        ])>
        <x-icon name="bell" />
        @if ($unread > 0)
            <span class="absolute top-1.5 right-1.5 grid min-w-4 place-items-center rounded-full bg-brass-500 px-1 text-[10px] leading-4 font-bold text-white ring-2 ring-white">{{ $unread > 9 ? '9+' : $unread }}</span>
        @endif
    </button>

    <div id="notifications-panel" data-toggle-panel hidden class="absolute right-0 z-50 mt-3 w-[22rem] max-w-[calc(100vw-2rem)] origin-top-right animate-toast-in overflow-hidden rounded-2xl bg-white text-left shadow-lift ring-1 ring-ink-100">
        <div class="flex items-center justify-between border-b border-ink-100 px-5 py-4">
            <p class="font-display text-lg text-ink-900">Notifications</p>
            @if ($unread > 0)
                <form method="POST" action="{{ route('account.notifications.read-all') }}">
                    @csrf
                    <button class="text-xs font-semibold text-brass-600 hover:text-brass-700">Mark all read</button>
                </form>
            @endif
        </div>
        <div class="max-h-96 divide-y divide-ink-50 overflow-y-auto">
            @forelse ($notifications as $notification)
                <a href="{{ route('account.notifications.show', $notification->id) }}" class="flex gap-3 px-5 py-4 transition hover:bg-ivory">
                    <span @class(['mt-1.5 size-2 shrink-0 rounded-full', 'bg-brass-500' => $notification->unread(), 'bg-transparent' => $notification->read()])></span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-sm font-semibold text-ink-900">{{ $notification->data['title'] ?? 'Update' }}</span>
                        <span class="mt-0.5 line-clamp-2 block text-xs text-ink-500">{{ $notification->data['body'] ?? '' }}</span>
                        <span class="mt-1 block text-[11px] text-ink-300">{{ $notification->created_at->diffForHumans() }}</span>
                    </span>
                </a>
            @empty
                <div class="px-5 py-10 text-center text-sm text-ink-400">You're all caught up.</div>
            @endforelse
        </div>
        <a href="{{ route('account.notifications.index') }}" class="block border-t border-ink-100 bg-ivory/60 px-5 py-3 text-center text-xs font-semibold text-ink-700 hover:text-ink-900">View all notifications</a>
    </div>
</div>
