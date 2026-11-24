@props(['light' => false])

@php
    $brand = (string) config('listings.company.name', config('app.name'));
    $primaryWord = str($brand)->before(' ')->toString();
    $secondaryWords = str($brand)->after($primaryWord)->trim()->toString();
@endphp

<a href="{{ route('home') }}" {{ $attributes->merge(['class' => 'group inline-flex items-center gap-2.5']) }} aria-label="{{ $brand }} home">
    <span @class([
        'grid size-9 shrink-0 place-items-center rounded-xl font-display text-lg font-semibold transition group-hover:rotate-[-6deg]',
        'bg-brass-500 text-white' => $light,
        'bg-ink-900 text-brass-300' => ! $light,
    ])>{{ str($brand)->substr(0, 1)->upper() }}</span>
    <span class="leading-none">
        <span @class(['block font-display text-lg font-semibold tracking-[0.08em]', 'text-white' => $light, 'text-ink-900' => ! $light])>{{ $primaryWord }}</span>
        @if ($secondaryWords !== '')
            <span @class(['mt-0.5 block text-[9px] font-semibold tracking-[0.45em] uppercase', 'text-brass-300' => $light, 'text-brass-600' => ! $light])>{{ $secondaryWords }}</span>
        @endif
    </span>
</a>
