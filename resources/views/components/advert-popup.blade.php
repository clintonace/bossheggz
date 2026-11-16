@props(['advert'])

<div id="advert-popup" data-palette="normal" data-advert-popup="{{ $advert->id }}" data-seconds="{{ $advert->display_seconds }}" hidden role="dialog" aria-modal="true" aria-label="Advertisement" class="fixed inset-0 z-[90] flex items-center justify-center bg-ink-950/75 p-4 backdrop-blur-sm">
    <div class="relative w-full max-w-lg animate-toast-in">
        <x-advert :advert="$advert" />

        <div class="mx-6 mt-3 h-1 overflow-hidden rounded-full bg-white/20" aria-hidden="true">
            <div data-advert-popup-progress class="h-full w-full origin-left rounded-full bg-brass-400"></div>
        </div>
        <p class="mt-2 text-center text-xs text-white/60">Closes automatically in <span data-advert-popup-countdown>{{ $advert->display_seconds }}</span>s</p>

        <button type="button" data-advert-popup-close class="absolute -top-3 -right-3 grid size-11 place-items-center rounded-full bg-white text-ink-900 shadow-lift ring-1 ring-ink-100 transition hover:scale-105 hover:bg-ivory" aria-label="Close advert">
            <x-icon name="x" class="size-5" stroke="2.25" />
        </button>
    </div>
</div>
