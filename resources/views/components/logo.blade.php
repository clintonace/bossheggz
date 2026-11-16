@props(['light' => false])

<a href="{{ route('home') }}" {{ $attributes->merge(['class' => 'group inline-flex items-center gap-2.5']) }}>
    <span @class([
        'grid size-9 place-items-center rounded-xl font-display text-lg font-semibold transition group-hover:rotate-[-6deg]',
        'bg-brass-500 text-white' => $light,
        'bg-ink-900 text-brass-300' => ! $light,
    ])>S</span>
    <span @class(['font-display text-xl font-semibold tracking-tight', 'text-white' => $light, 'text-ink-900' => ! $light])>
        {{ config('app.name') }}
    </span>
</a>
