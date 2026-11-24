@props(['advert', 'preview' => false])

@php
    $href = $preview ? $advert->link_url : route('adverts.click', $advert);
    $sponsoredLabel = 'Sponsored'.($advert->sponsor ? ' · '.$advert->sponsor : '');
    $variant = match (true) {
        $advert->isPopup() => 'popup',
        $advert->position === \App\Enums\AdvertPosition::Inside => 'card',
        default => 'banner',
    };
    $isHalf = $advert->width === \App\Enums\AdvertWidth::Half;
@endphp

@switch($variant)
    @case('popup')
        <a href="{{ $href }}" target="_blank" rel="sponsored noopener" data-advert-popup-link {{ $attributes->merge(['class' => 'group relative isolate block overflow-hidden rounded-[2rem] bg-white/80 text-left shadow-2xl ring-1 ring-white backdrop-blur-2xl backdrop-saturate-150']) }} aria-label="{{ $advert->title }} — {{ $advert->cta_label }} (opens in a new tab)">
            <div class="relative aspect-[16/10] overflow-hidden bg-gradient-to-br from-ink-800 to-ink-950">
                @if ($advert->image_url)
                    <img src="{{ $advert->image_url }}" alt="" class="size-full object-cover transition duration-700 ease-out group-hover:scale-105">
                @else
                    <div class="grid size-full place-items-center text-white/30"><x-icon name="megaphone" class="size-14" stroke="1.25" /></div>
                @endif
                <span class="absolute top-4 left-4 inline-flex items-center gap-1.5 rounded-full bg-white/70 px-3 py-1 text-[11px] font-semibold tracking-wider text-ink-700 uppercase shadow-soft ring-1 ring-white/80 backdrop-blur-md"><span class="size-1.5 rounded-full bg-brass-500"></span>Sponsored</span>
            </div>
            <div class="relative p-6 sm:p-8">
                <div class="pointer-events-none absolute -right-16 -bottom-20 -z-10 size-56 rounded-full bg-brass-200/50 blur-3xl" aria-hidden="true"></div>
                @if ($advert->sponsor)
                    <p class="text-xs font-medium text-ink-400">{{ $advert->sponsor }}</p>
                @endif
                <p class="mt-1 font-display text-2xl leading-tight text-ink-950 sm:text-3xl">{{ $advert->title }}</p>
                @if ($advert->body)
                    <p class="mt-2 text-sm leading-relaxed text-ink-500">{{ $advert->body }}</p>
                @endif
                <span class="btn btn-accent btn-lg mt-6 w-full">{{ $advert->cta_label }} <x-icon name="external" class="size-4" /></span>
            </div>
        </a>
        @break

    @case('card')
        <aside {{ $attributes->merge(['class' => 'glass-card group relative flex h-full flex-col overflow-hidden']) }} aria-label="Advertisement">
            <div class="relative aspect-[4/3] overflow-hidden bg-gradient-to-br from-ink-100 via-ivory to-brass-50">
                @if ($advert->image_url)
                    <img src="{{ $advert->image_url }}" alt="" loading="lazy" class="size-full object-cover transition duration-700 ease-out group-hover:scale-105">
                @else
                    <div class="grid size-full place-items-center text-ink-300"><x-icon name="megaphone" class="size-10" stroke="1.25" /></div>
                @endif
                <span class="absolute top-4 left-4 inline-flex items-center gap-1.5 rounded-full bg-white/70 px-3 py-1 text-[11px] font-semibold tracking-wider text-ink-700 uppercase shadow-soft ring-1 ring-white/80 backdrop-blur-md"><span class="size-1.5 rounded-full bg-brass-500"></span>Sponsored</span>
            </div>
            <div class="flex flex-1 flex-col gap-3 p-5 sm:p-6">
                @if ($advert->sponsor)
                    <p class="text-xs text-ink-400">{{ $advert->sponsor }}</p>
                @endif
                <h3 class="font-display text-xl leading-snug text-ink-900">{{ $advert->title }}</h3>
                @if ($advert->body)
                    <p class="line-clamp-3 text-sm leading-relaxed text-ink-500">{{ $advert->body }}</p>
                @endif
                <div class="mt-auto flex items-center justify-between border-t border-ink-100/70 pt-4">
                    <a href="{{ $href }}" target="_blank" rel="sponsored noopener" class="text-sm font-semibold text-ink-900 after:absolute after:inset-0">{{ $advert->cta_label }}</a>
                    <span class="grid size-9 place-items-center rounded-full bg-ink-50 text-ink-500 transition duration-300 group-hover:bg-brass-500 group-hover:text-white">
                        <x-icon name="external" class="size-4" />
                    </span>
                </div>
            </div>
        </aside>
        @break

    @default
        <aside data-palette="normal" {{ $attributes->merge(['class' => 'group relative isolate flex h-full overflow-hidden rounded-3xl bg-gradient-to-br from-ink-900 to-ink-950 text-white shadow-soft']) }} aria-label="Advertisement">
            @if ($advert->image_url)
                <img src="{{ $advert->image_url }}" alt="" loading="lazy" class="absolute inset-0 -z-10 size-full object-cover transition duration-1000 ease-out group-hover:scale-105">
                <div @class([
                    'absolute inset-0 -z-10',
                    'bg-gradient-to-t from-ink-950/90 via-ink-950/60 to-ink-950/10' => $isHalf,
                    'bg-gradient-to-r from-ink-950/90 via-ink-950/65 to-ink-950/10' => ! $isHalf,
                ])></div>
            @endif
            <div class="pointer-events-none absolute -top-24 -right-20 -z-10 size-72 rounded-full bg-brass-500/25 blur-3xl transition duration-700 group-hover:bg-brass-400/35" aria-hidden="true"></div>
            <div @class([
                'flex w-full flex-col justify-between gap-6',
                'min-h-64 items-start px-6 py-7 sm:px-8' => $isHalf,
                'items-start px-6 py-8 sm:px-10 sm:py-10 md:flex-row md:items-center' => ! $isHalf,
            ])>
                <div class="max-w-xl">
                    <p class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-[11px] font-semibold tracking-[0.18em] text-white/80 uppercase ring-1 ring-white/20 backdrop-blur-md"><span class="size-1.5 rounded-full bg-brass-400"></span>{{ $sponsoredLabel }}</p>
                    <p @class(['mt-3 font-display leading-tight', 'text-2xl' => $isHalf, 'text-2xl sm:text-3xl' => ! $isHalf])>{{ $advert->title }}</p>
                    @if ($advert->body)
                        <p class="mt-2 line-clamp-2 text-sm text-white/80">{{ $advert->body }}</p>
                    @endif
                </div>
                <a href="{{ $href }}" target="_blank" rel="sponsored noopener" @class(['btn shrink-0 bg-white/90 text-ink-900 shadow-soft ring-1 ring-white backdrop-blur hover:bg-white after:absolute after:inset-0', 'btn-lg' => ! $isHalf])>{{ $advert->cta_label }} <x-icon name="external" class="size-4" /></a>
            </div>
        </aside>
@endswitch
