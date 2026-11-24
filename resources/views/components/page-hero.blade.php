@props(['eyebrow' => null, 'title' => null, 'description' => null, 'image' => null, 'section' => null])

{{--
    Dark hero panel with glass accents (stays dark in both colour modes).
    Pass a CMS hero section (App\Models\PageSection) to fill the text, image and buttons from Admin → Pages;
    anything in the slot is added after the section's buttons.
--}}
@php
    if ($section) {
        $eyebrow = $section->value('eyebrow');
        $title = $section->value('title');
        $description = $section->value('description');
        $image = $section->imageUrl('image');
        $primaryLabel = $section->value('primary_label');
        $secondaryLabel = $section->value('secondary_label');
    }
    $hasButtons = filled($primaryLabel ?? null) || filled($secondaryLabel ?? null) || $slot->isNotEmpty();
@endphp

<section class="mx-auto max-w-7xl px-4 pt-6 sm:px-6 lg:px-8">
    <div data-palette="normal" {{ $attributes->merge(['class' => 'relative isolate overflow-hidden rounded-[2.5rem] bg-ink-950 px-6 py-16 text-white shadow-lift sm:px-12 lg:px-16 lg:py-24']) }}>
        @if ($image)
            <img src="{{ $image }}" alt="" class="absolute inset-0 -z-10 size-full object-cover opacity-35" onerror="this.remove()">
            <div class="absolute inset-0 -z-10 bg-gradient-to-r from-ink-950 via-ink-950/85 to-ink-950/30"></div>
        @endif
        <div class="pointer-events-none absolute -top-32 -right-24 -z-10 size-[28rem] rounded-full bg-brass-500/25 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-40 left-1/4 -z-10 size-[24rem] rounded-full bg-ink-500/30 blur-3xl"></div>

        <div class="max-w-3xl">
            <div class="h-1 w-14 animate-fade-up rounded-full bg-brass-400"></div>
            @if ($eyebrow)
                <p class="mt-6 animate-fade-up text-xs font-semibold tracking-[0.2em] text-brass-300 uppercase [animation-delay:40ms]">{{ $eyebrow }}</p>
            @endif
            <h1 class="mt-4 animate-fade-up font-display text-4xl leading-[1.05] font-medium tracking-tight [animation-delay:80ms] sm:text-5xl lg:text-6xl">{{ $title }}</h1>
            @if ($description)
                <p class="mt-6 max-w-2xl animate-fade-up text-lg leading-relaxed text-ink-300 [animation-delay:140ms]">{{ $description }}</p>
            @endif
            @if ($hasButtons)
                <div class="mt-9 flex animate-fade-up flex-wrap gap-3 [animation-delay:200ms]">
                    @if (filled($primaryLabel ?? null))
                        <a href="{{ $section->value('primary_url') ?: route('contact') }}" class="btn btn-accent btn-lg">{{ $primaryLabel }}</a>
                    @endif
                    @if (filled($secondaryLabel ?? null))
                        <a href="{{ $section->value('secondary_url') ?: route('contact') }}" class="btn btn-lg bg-white/10 text-white ring-1 ring-white/25 backdrop-blur hover:bg-white/20">{{ $secondaryLabel }}</a>
                    @endif
                    {{ $slot }}
                </div>
            @endif
        </div>

        @isset($aside)
            <div class="mt-12 lg:absolute lg:right-12 lg:bottom-12 lg:mt-0 lg:w-80">{{ $aside }}</div>
        @endisset
    </div>
</section>
