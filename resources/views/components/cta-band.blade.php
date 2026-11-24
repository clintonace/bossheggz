@props(['title' => 'Your Next Property Investment Starts Here.', 'description' => null])

<section {{ $attributes->merge(['class' => 'mx-auto max-w-7xl px-4 sm:px-6 lg:px-8']) }} data-reveal>
    <div class="relative isolate overflow-hidden rounded-[2.5rem] bg-gradient-to-br from-brass-300 via-brass-400 to-brass-500 px-6 py-14 text-center shadow-lift sm:px-12 sm:py-16">
        <div class="pointer-events-none absolute -top-24 -left-16 -z-10 size-80 rounded-full bg-white/30 blur-3xl"></div>
        <div class="pointer-events-none absolute -right-10 -bottom-28 -z-10 size-80 rounded-full bg-brass-700/30 blur-3xl"></div>
        <h2 class="mx-auto max-w-3xl font-display text-3xl tracking-tight text-ink-950 sm:text-4xl lg:text-5xl">{{ $title }}</h2>
        @if ($description)
            <p class="mx-auto mt-4 max-w-xl text-ink-800">{{ $description }}</p>
        @endif
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            @if ($slot->isNotEmpty())
                {{ $slot }}
            @else
                <a href="{{ route('contact', ['type' => 'consultation']) }}" class="btn btn-primary btn-lg">Speak to an Adviser <x-icon name="arrow-right" class="size-4" /></a>
                <a href="{{ route('properties.index') }}" class="btn btn-lg bg-white/40 text-ink-950 ring-1 ring-ink-950/20 backdrop-blur hover:bg-white/60">Browse Properties</a>
            @endif
        </div>
    </div>
</section>
