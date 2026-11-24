@props(['area', 'theme'])

{{--
    Guided tour for an area (App\Enums\SiteArea::tourSteps()). Starts by itself on a visitor's first view
    when the Site manager has the tour switched on; any [data-tour-start] button replays it.
    Signed-in users are remembered on their account, guests in their browser.
--}}
@php
    $user = auth()->user();
    $autoStart = $theme->tourEnabled() && ! $user?->hasSeenTour($area);
@endphp

<div data-site-tour
    data-area="{{ $area->value }}"
    data-auto-start="{{ $autoStart ? '1' : '0' }}"
    data-guest="{{ $user ? '0' : '1' }}"
    @if ($user) data-complete-url="{{ route('tour.complete', $area) }}" @endif
    data-steps='@json($area->tourSteps())'
    hidden>
    <div data-tour-spotlight class="tour-spotlight" aria-hidden="true"></div>

    <div data-tour-card role="dialog" aria-modal="true" aria-labelledby="tour-title" aria-describedby="tour-body" class="tour-card fixed isolate z-[102] w-[min(22rem,calc(100vw-2rem))] overflow-hidden rounded-3xl bg-white/85 p-6 text-left shadow-lift ring-1 ring-white backdrop-blur-2xl backdrop-saturate-150">
        <div class="pointer-events-none absolute -top-16 -right-14 -z-10 size-40 rounded-full bg-brass-200/60 blur-3xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -bottom-20 -left-12 -z-10 size-40 rounded-full bg-ink-100/80 blur-3xl" aria-hidden="true"></div>

        <div class="flex items-center justify-between gap-3">
            <span class="inline-flex items-center gap-2 text-[11px] font-semibold tracking-[0.2em] text-brass-600 uppercase">
                <x-icon name="sparkles" class="size-3.5" /> <span data-tour-counter>Tour</span>
            </span>
            <button type="button" data-no-loader data-tour-close class="grid size-8 place-items-center rounded-full text-ink-400 transition hover:bg-ink-50 hover:text-ink-800" aria-label="Close tour"><x-icon name="x" class="size-4" /></button>
        </div>

        <h2 id="tour-title" data-tour-title class="mt-3 font-display text-2xl leading-tight text-ink-950"></h2>
        <p id="tour-body" data-tour-body class="mt-2 text-sm leading-relaxed text-ink-600"></p>

        <div class="mt-5 flex items-center gap-1.5" data-tour-dots aria-hidden="true"></div>

        <div class="mt-5 flex items-center justify-between gap-2">
            <button type="button" data-no-loader data-tour-skip class="text-xs font-semibold text-ink-400 transition hover:text-ink-800">Skip tour</button>
            <div class="flex items-center gap-2">
                <button type="button" data-no-loader data-tour-back class="btn btn-ghost btn-sm"><x-icon name="arrow-left" class="size-3.5" /> Back</button>
                <button type="button" data-no-loader data-tour-next class="btn btn-primary btn-sm"><span data-tour-next-label>Next</span> <x-icon name="arrow-right" class="size-3.5" /></button>
            </div>
        </div>
    </div>
</div>
