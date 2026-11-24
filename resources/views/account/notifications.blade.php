<x-layouts.client title="Notifications" subtitle="Updates about the properties you've engaged with.">
    @if ($unreadCount > 0)
        <x-slot:actions>
            <form method="POST" action="{{ route('account.notifications.read-all') }}">
                @csrf
                <button class="btn btn-outline btn-sm"><x-icon name="check" class="size-4" /> <span class="hidden sm:inline">Mark all read</span></button>
            </form>
        </x-slot:actions>
    @endif

    <section class="max-w-3xl">
        <div class="mb-6 inline-flex gap-1 rounded-full bg-white p-1 shadow-soft ring-1 ring-ink-100">
            @foreach (['all' => 'All', 'unread' => 'Unread'] as $key => $label)
                <a href="{{ route('account.notifications.index', $key === 'all' ? [] : ['filter' => $key]) }}" @class(['rounded-full px-4 py-2 text-sm font-medium transition', 'bg-ink-900 text-white' => $filter === $key, 'text-ink-500 hover:text-ink-900' => $filter !== $key])>
                    {{ $label }}
                    @if ($key === 'unread' && $unreadCount > 0)
                        <span class="ml-1 text-xs opacity-70">{{ $unreadCount }}</span>
                    @endif
                </a>
            @endforeach
        </div>

        @if ($notifications->isEmpty())
            <x-empty-state icon="bell" :title="$filter === 'unread' ? 'You are all caught up' : 'No notifications yet'" :description="$filter === 'unread' ? 'There are no unread notifications.' : 'Updates on the properties you engage with will appear here and in your email.'" />
        @else
            <div class="divide-y divide-ink-100 overflow-hidden rounded-3xl bg-white shadow-soft ring-1 ring-ink-100">
                @foreach ($notifications as $notification)
                    <a href="{{ route('account.notifications.show', $notification->id) }}" @class(['flex gap-4 px-6 py-5 transition hover:bg-ivory', 'bg-brass-50/40' => $notification->unread()])>
                        <span @class(['grid size-10 shrink-0 place-items-center rounded-full', 'bg-brass-500 text-white' => $notification->unread(), 'bg-ink-50 text-ink-400' => $notification->read()])><x-icon name="bell" class="size-4" /></span>
                        <span class="min-w-0 flex-1">
                            <span class="block font-semibold text-ink-900">{{ $notification->data['title'] ?? 'Update' }}</span>
                            <span class="mt-0.5 block text-sm text-ink-500">{{ $notification->data['body'] ?? '' }}</span>
                            <span class="mt-1.5 block text-xs text-ink-300">{{ $notification->created_at->diffForHumans() }}</span>
                        </span>
                        <x-icon name="chevron-right" class="mt-3 size-4 text-ink-300" />
                    </a>
                @endforeach
            </div>
            <div class="mt-8">{{ $notifications->links('pagination') }}</div>
        @endif
    </section>
</x-layouts.client>
