@props(['id', 'label', 'icon', 'open' => false, 'links' => []])

<div>
    <button type="button" data-toggle="#{{ $id }}" data-toggle-persist aria-expanded="{{ $open ? 'true' : 'false' }}" @class([
        'group flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition',
        'bg-ivory text-ink-900 ring-1 ring-ink-100' => $open,
        'text-ink-600 hover:bg-ivory hover:text-ink-900' => ! $open,
    ])>
        <x-icon :name="$icon" @class(['size-5', 'text-brass-600' => $open, 'text-ink-300 group-hover:text-ink-500' => ! $open]) />
        {{ $label }}
        <x-icon name="chevron-down" class="ml-auto size-4 text-ink-300 transition group-aria-expanded:rotate-180" />
    </button>
    <div id="{{ $id }}" @unless ($open) hidden @endunless class="mt-1 ml-5 space-y-0.5 border-l border-ink-100 pl-3">
        @foreach ($links as $link)
            <a href="{{ $link['url'] }}" @class([
                'block rounded-lg px-3 py-2 text-[13px] transition',
                'bg-ink-900 font-semibold text-white' => $link['active'],
                'text-ink-500 hover:bg-ivory hover:text-ink-900' => ! $link['active'],
            ])>{{ $link['label'] }}</a>
        @endforeach
    </div>
</div>
