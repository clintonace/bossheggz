@php($current = auth()->user())

<div class="fixed right-4 bottom-24 z-[80] lg:bottom-5">
    <div id="dev-switcher-panel" data-toggle-panel hidden class="absolute right-0 bottom-full mb-3 w-80 max-w-[calc(100vw-2rem)] animate-toast-in overflow-hidden rounded-2xl bg-white shadow-lift ring-1 ring-ink-100">
        <div class="flex items-center justify-between bg-ink-950 px-4 py-3 text-white">
            <p class="font-display text-base">Test accounts</p>
            <span class="rounded-full bg-amber-400/20 px-2 py-0.5 text-[10px] font-bold tracking-widest text-amber-300 uppercase">Local only</span>
        </div>

        <p class="border-b border-ink-100 px-4 py-2.5 text-xs text-ink-500">
            @if ($current)
                Signed in as <span class="font-semibold text-ink-800">{{ $current->name }}</span> ({{ $current->isAdmin() ? 'admin' : 'client' }})
            @else
                You're signed out.
            @endif
        </p>

        <div class="max-h-80 space-y-3 overflow-y-auto p-3">
            @foreach ([['Admin', 'shield', $testAdmins, 'bg-ink-900 text-brass-200'], ['Client', 'user', $testClients, 'bg-brass-100 text-brass-700']] as [$group, $icon, $accounts, $avatarClasses])
                <div>
                    <p class="flex items-center gap-1.5 px-1 pb-1.5 text-[11px] font-semibold tracking-[0.15em] text-ink-400 uppercase"><x-icon :name="$icon" class="size-3.5" /> {{ $group }}</p>
                    @forelse ($accounts as $account)
                        <form method="POST" action="{{ route('dev.switch-account', $account) }}">
                            @csrf
                            <button @disabled($current?->is($account)) class="flex w-full items-center gap-3 rounded-xl px-2 py-2 text-left transition hover:bg-ivory disabled:cursor-default disabled:bg-ivory">
                                <span class="grid size-8 shrink-0 place-items-center rounded-full text-xs font-semibold {{ $avatarClasses }}">{{ str($account->name)->substr(0, 1)->upper() }}</span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-semibold text-ink-900">{{ $account->name }}</span>
                                    <span class="block truncate text-xs text-ink-400">{{ $account->email }}</span>
                                </span>
                                @if ($current?->is($account))
                                    <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 ring-1 ring-emerald-100">Current</span>
                                @else
                                    <x-icon name="arrow-right" class="size-4 text-ink-300" />
                                @endif
                            </button>
                        </form>
                    @empty
                        <p class="px-1 text-xs text-ink-400">No {{ strtolower($group) }} accounts yet.</p>
                    @endforelse
                </div>
            @endforeach
        </div>

        @if ($current)
            <form method="POST" action="{{ route('logout') }}" class="border-t border-ink-100 p-2">
                @csrf
                <button class="flex w-full items-center justify-center gap-2 rounded-xl px-3 py-2 text-sm font-medium text-ink-600 transition hover:bg-ivory hover:text-ink-900"><x-icon name="logout" class="size-4" /> Sign out</button>
            </form>
        @endif
    </div>

    <button type="button" data-toggle="#dev-switcher-panel" aria-expanded="false" class="flex items-center gap-2 rounded-full bg-ink-950 py-2.5 pr-4 pl-3 text-sm font-semibold text-white shadow-lift ring-1 ring-white/10 transition hover:bg-ink-800">
        <span class="relative grid size-6 place-items-center rounded-full bg-brass-500"><x-icon name="users" class="size-3.5" /></span>
        Test accounts
    </button>
</div>
