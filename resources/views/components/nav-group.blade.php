@props(['id', 'label', 'icon', 'links' => [], 'badge' => 0, 'open' => null])

{{--
    Sidebar dropdown used by the admin and client dashboards.
    Each link: ['label', 'url', 'active' => bool, 'badge' => ?int, 'action' => bool, 'icon' => ?string].
    "Action" links (add / create) sit under a dashed divider and show their icon.
    Opens automatically when one of its links is the current page.
--}}
@php
    $isCurrent = collect($links)->contains(fn (array $link) => $link['active'] ?? false);
    $isOpen = $open ?? $isCurrent;
@endphp

<div>
    <button type="button" data-toggle="#{{ $id }}" data-toggle-class="is-open" data-toggle-persist aria-controls="{{ $id }}" aria-expanded="{{ $isOpen ? 'true' : 'false' }}" @class([
        'group flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition duration-300',
        'text-ink-600 hover:bg-ivory/80 hover:text-ink-900',
        'aria-expanded:bg-gradient-to-r aria-expanded:from-white aria-expanded:to-white/60 aria-expanded:text-ink-900 aria-expanded:shadow-[inset_0_1px_0_rgb(255_255_255/0.9),0_6px_20px_-12px_rgb(18_24_37/0.35)] aria-expanded:ring-1 aria-expanded:ring-ink-100/80 aria-expanded:backdrop-blur',
    ])>
        <x-icon :name="$icon" @class([
            'size-5 transition duration-300',
            'text-brass-600' => $isCurrent,
            'text-ink-300 group-hover:text-ink-500 group-aria-expanded:text-brass-600' => ! $isCurrent,
        ]) />
        <span>{{ $label }}</span>
        @if ($isCurrent)
            <span class="size-1.5 rounded-full bg-brass-500" aria-hidden="true"></span>
        @endif
        @if ($badge > 0)
            <span class="ml-auto rounded-full bg-brass-500 px-2 py-0.5 text-[11px] font-bold text-white shadow-sm">{{ $badge }}</span>
        @endif
        <span @class(['grid size-6 place-items-center rounded-full transition duration-300 group-aria-expanded:rotate-180 group-aria-expanded:bg-ink-900/5', 'ml-auto' => $badge <= 0])>
            <x-icon name="chevron-down" class="size-4 text-ink-300 group-hover:text-ink-500" />
        </span>
    </button>

    <div id="{{ $id }}" @class(['nav-group-panel', 'is-open' => $isOpen])>
        <div>
            <div class="relative mt-1.5 mb-2 ml-3 overflow-hidden rounded-2xl bg-gradient-to-b from-white/80 via-white/55 to-white/35 p-1.5 shadow-[inset_0_1px_0_rgb(255_255_255/0.95),0_12px_32px_-16px_rgb(18_24_37/0.3)] ring-1 ring-ink-100/70 backdrop-blur-xl backdrop-saturate-150">
                <div class="pointer-events-none absolute -top-10 -right-8 size-24 rounded-full bg-brass-200/40 blur-2xl" aria-hidden="true"></div>
                <span class="pointer-events-none absolute top-4 bottom-4 left-[17px] w-px bg-gradient-to-b from-transparent via-ink-200 to-transparent" aria-hidden="true"></span>

                @foreach ($links as $link)
                    @php($linkActive = $link['active'] ?? false)
                    <a href="{{ $link['url'] }}" style="--i: {{ $loop->index }}" @if ($linkActive) aria-current="page" @endif @class([
                        'nav-group-link group/link relative flex items-center gap-2.5 rounded-xl py-2 pr-2.5 pl-8 text-[13px] transition duration-200',
                        'bg-ink-900 font-semibold text-white shadow-[0_6px_16px_-8px_rgb(18_24_37/0.6)]' => $linkActive,
                        'text-ink-500 hover:bg-white hover:text-ink-900 hover:shadow-[0_4px_12px_-8px_rgb(18_24_37/0.35)]' => ! $linkActive,
                        'mt-1 border-t border-dashed border-ink-100 pt-2.5' => ($link['action'] ?? false) && ! $loop->first && ! $linkActive,
                    ])>
                        <span @class([
                            'absolute left-[13px] size-[9px] rounded-full ring-2 transition duration-200',
                            'bg-brass-400 ring-ink-900' => $linkActive,
                            'bg-white ring-ink-200 group-hover/link:bg-brass-300 group-hover/link:ring-brass-200' => ! $linkActive,
                        ]) aria-hidden="true"></span>
                        @if (($link['action'] ?? false) && ($link['icon'] ?? null))
                            <x-icon :name="$link['icon']" @class(['size-4', 'text-brass-300' => $linkActive, 'text-ink-300 group-hover/link:text-brass-600' => ! $linkActive]) />
                        @endif
                        <span class="truncate">{{ $link['label'] }}</span>
                        @if (($link['badge'] ?? 0) > 0)
                            <span @class(['ml-auto rounded-full px-1.5 text-[10px] leading-4 font-bold', 'bg-white/15 text-white' => $linkActive, 'bg-brass-50 text-brass-700 ring-1 ring-brass-100' => ! $linkActive])>{{ $link['badge'] }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>
