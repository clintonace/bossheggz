@php
    $overview = $content['overview'];
    $mission = $content['mission'];
    $growth = $content['growth'];
    $overviewImage = $overview->imageUrl('image');
@endphp

<x-layouts.app :title="$content['meta']->value('title')" :description="$content['meta']->value('description')">
    <x-page-hero :section="$content['hero']" />

    @if ($overview->isShown())
    <section @class(['mx-auto mt-20 grid max-w-7xl grid-cols-1 items-center gap-12 px-4 sm:px-6 lg:px-8', 'lg:grid-cols-2' => $overviewImage])>
        <div data-reveal>
            <x-section-heading :section="$overview" size="md" />
            @foreach ($overview->paragraphs('body') as $paragraph)
                <p class="{{ $loop->first ? 'mt-5' : 'mt-4' }} text-lg leading-relaxed text-ink-600">{{ $paragraph }}</p>
            @endforeach
        </div>
        @if ($overviewImage)
            <div class="relative" data-reveal>
                <x-property-image :src="$overviewImage" alt="" class="aspect-[4/3] rounded-[2rem] shadow-lift" />
                @if (filled($overview->value('badge_title')))
                    <div class="glass-strong absolute -bottom-6 left-6 flex items-center gap-4 rounded-2xl p-4 pr-6">
                        <span class="grid size-11 place-items-center rounded-xl bg-emerald-50 text-emerald-600"><x-icon name="shield" /></span>
                        <div>
                            <p class="text-sm font-semibold text-ink-900">{{ $overview->value('badge_title') }}</p>
                            @if (filled($overview->value('badge_text')))
                                <p class="text-xs text-ink-400">{{ $overview->value('badge_text') }}</p>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </section>
    @endif

    @if ($mission->isShown())
    <section class="mx-auto mt-24 grid max-w-7xl grid-cols-1 gap-6 px-4 sm:px-6 md:grid-cols-2 lg:px-8">
        <div class="glass-strong rounded-[2rem] p-8 sm:p-10" data-reveal>
            <p class="eyebrow">Mission</p>
            <p class="mt-4 font-display text-2xl leading-snug text-ink-900">{{ $mission->value('mission') }}</p>
        </div>
        <div class="glass-strong rounded-[2rem] p-8 sm:p-10" data-reveal data-reveal-delay="100">
            <p class="eyebrow">Vision</p>
            <p class="mt-4 font-display text-2xl leading-snug text-ink-900">{{ $mission->value('vision') }}</p>
        </div>
    </section>
    @endif

    @if ($content['values']->isShown())
    <section class="mx-auto mt-24 max-w-7xl px-4 sm:px-6 lg:px-8">
        <x-section-heading :section="$content['values']" size="md" data-reveal />
        <div class="mt-10 grid grid-cols-2 gap-4 md:grid-cols-3">
            @foreach ($content['values']->list('values') as ['icon' => $icon, 'title' => $value])
                <div class="glass flex items-center gap-4 rounded-2xl border-t-2 border-brass-400 p-5" data-reveal data-reveal-delay="{{ ($loop->index % 3) * 80 }}">
                    <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-brass-50 text-brass-600"><x-icon :name="$icon" class="size-5" /></span>
                    <span class="font-display text-lg text-ink-900">{{ $value }}</span>
                </div>
            @endforeach
        </div>
    </section>
    @endif

    @if ($growth->isShown())
    <section class="mx-auto mt-24 max-w-7xl px-4 sm:px-6 lg:px-8">
        <div data-palette="normal" class="relative isolate overflow-hidden rounded-[2.5rem] bg-ink-950 px-6 py-16 text-white sm:px-12 lg:px-16">
            <div class="pointer-events-none absolute -top-24 -right-24 -z-10 size-96 rounded-full bg-brass-500/20 blur-3xl"></div>
            @if (filled($growth->value('eyebrow')))
                <p class="text-xs font-semibold tracking-[0.2em] text-brass-300 uppercase" data-reveal>{{ $growth->value('eyebrow') }}</p>
            @endif
            <h2 class="mt-4 font-display text-4xl tracking-tight" data-reveal>{{ $growth->value('title') }}</h2>
            <div class="mt-10 grid grid-cols-1 gap-5 md:grid-cols-3">
                @foreach ($growth->list('phases') as ['label' => $phase, 'title' => $heading, 'points' => $items])
                    <div class="glass-dark rounded-3xl p-6" data-reveal data-reveal-delay="{{ ($loop->index % 3) * 110 }}">
                        @if (filled($phase))
                            <p class="text-xs font-bold tracking-wider text-brass-300 uppercase">{{ $phase }}</p>
                        @endif
                        <h3 class="mt-2 font-display text-xl">{{ $heading }}</h3>
                        <ul class="mt-5 space-y-2.5 text-sm text-ink-300">
                            @foreach ($items as $item)
                                <li class="flex items-center gap-2.5"><span class="size-1.5 rounded-full bg-brass-400"></span>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    @if ($content['cta']->isShown())
        <x-cta-band class="mt-24" :title="$content['cta']->value('title')" :description="$content['cta']->value('description')" />
    @endif
</x-layouts.app>
