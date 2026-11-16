@props(['mode', 'links' => []])

@php($user = auth()->user())

<div class="relative">
    <button type="button" data-toggle="#profile-menu" aria-expanded="false" aria-label="Open profile menu" class="flex items-center gap-2 rounded-full py-1 pr-1 pl-1 ring-1 ring-ink-200 transition hover:bg-white sm:pr-3">
        <span class="grid size-8 place-items-center rounded-full bg-ink-900 text-xs font-semibold text-brass-200">{{ str($user->name)->substr(0, 1)->upper() }}</span>
        <span class="hidden max-w-28 truncate text-sm font-medium text-ink-800 sm:block">{{ str($user->name)->before(' ') }}</span>
        <x-icon name="chevron-down" class="hidden size-4 text-ink-400 sm:block" />
    </button>

    <div id="profile-menu" data-toggle-panel hidden class="absolute right-0 z-50 mt-3 w-72 animate-toast-in overflow-hidden rounded-2xl bg-white p-2 text-left shadow-lift ring-1 ring-ink-100">
        <div class="flex items-center gap-3 px-3 py-2.5">
            <span class="grid size-10 shrink-0 place-items-center rounded-full bg-ink-900 font-semibold text-brass-200">{{ str($user->name)->substr(0, 1)->upper() }}</span>
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-ink-900">{{ $user->name }}</p>
                <p class="truncate text-xs text-ink-400">{{ $user->email }}</p>
            </div>
            @if ($user->isAdmin())
                <span class="ml-auto rounded-full bg-brass-50 px-2 py-0.5 text-[10px] font-bold tracking-wider text-brass-700 uppercase ring-1 ring-brass-200">Admin</span>
            @endif
        </div>

        @if ($links)
            <div class="my-1 h-px bg-ink-100"></div>
            @foreach ($links as $link)
                <a href="{{ $link['url'] }}" @if ($link['external'] ?? false) target="_blank" @endif class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-ink-700 transition hover:bg-ivory hover:text-ink-900">
                    <x-icon :name="$link['icon']" class="size-4 text-ink-400" /> {{ $link['label'] }}
                    @if ($link['external'] ?? false)
                        <x-icon name="external" class="ml-auto size-3.5 text-ink-300" />
                    @endif
                </a>
            @endforeach
        @endif

        <div class="my-1 h-px bg-ink-100"></div>

        <form method="POST" action="{{ route('account.appearance.update') }}" data-appearance-form class="px-3 py-2.5">
            @csrf
            @method('PUT')
            <p class="text-xs font-semibold tracking-wide text-ink-400 uppercase">Appearance</p>
            <div class="mt-2 grid grid-cols-3 gap-1 rounded-xl bg-ink-50 p-1 ring-1 ring-ink-100" role="group" aria-label="Colour mode">
                @foreach (['light' => 'Light', 'dark' => 'Dark', 'system' => 'Auto'] as $option => $label)
                    <button type="submit" name="mode" value="{{ $option }}" data-appearance-option aria-pressed="{{ $mode === $option ? 'true' : 'false' }}" title="{{ $option === 'system' ? 'Match my device' : $label.' mode' }}" class="flex flex-col items-center gap-1 rounded-lg px-2 py-2 text-[11px] font-semibold text-ink-500 transition hover:text-ink-900 aria-pressed:bg-white aria-pressed:text-ink-900 aria-pressed:shadow-soft aria-pressed:ring-1 aria-pressed:ring-ink-100">
                        @if ($option === 'light')
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
                        @elseif ($option === 'dark')
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
                        @else
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="4" width="20" height="13" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
                        @endif
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </form>

        <div class="my-1 h-px bg-ink-100"></div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm text-ink-700 transition hover:bg-ivory hover:text-ink-900"><x-icon name="logout" class="size-4 text-ink-400" /> Sign out</button>
        </form>
    </div>
</div>
