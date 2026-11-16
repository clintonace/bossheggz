@props(['src' => null, 'alt' => ''])

<div {{ $attributes->merge(['class' => 'relative overflow-hidden bg-gradient-to-br from-sand via-brass-50 to-ink-100']) }}>
    <div class="absolute inset-0 grid place-items-center text-brass-400/70">
        <x-icon name="home" class="size-10" stroke="1.25" />
    </div>
    @if ($src)
        <img src="{{ $src }}" alt="{{ $alt }}" loading="lazy" onerror="this.remove()" class="relative size-full object-cover transition duration-700 ease-out group-hover:scale-105">
    @endif
    {{ $slot }}
</div>
