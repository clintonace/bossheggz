{{--
    Branded preloader for the public website only (layouts/app). Shown on the first page a visitor
    opens in a browser session; the inline script decides before the first paint, so returning
    navigations never flash it. app.js lifts it away once the page has loaded; a CSS fail-safe hides
    it after a few seconds even if JavaScript never runs. Switched on/off in Site manager → Landing page.
--}}
@php
    $brand = (string) config('listings.company.name', config('app.name'));
    $primaryWord = str($brand)->before(' ')->upper()->toString();
    $secondaryWords = str($brand)->after(str($brand)->before(' '))->trim()->toString();
@endphp

<script>
    try {
        if (/[?&]preloader=1/.test(location.search) || !sessionStorage.getItem('site-preloader-seen')) {
            document.documentElement.classList.add('is-preloading');
        }
    } catch (error) {
        document.documentElement.classList.add('is-preloading');
    }
</script>

<div data-site-preloader data-palette="normal" class="site-preloader" role="status" aria-live="polite" aria-label="Loading {{ $brand }}">
    <div class="preloader-glow preloader-glow-a" aria-hidden="true"></div>
    <div class="preloader-glow preloader-glow-b" aria-hidden="true"></div>
    <div class="preloader-grid" aria-hidden="true"></div>

    <div class="preloader-content">
        <div class="preloader-mark" aria-hidden="true">
            <svg viewBox="0 0 88 88" class="preloader-frame">
                <rect x="3" y="3" width="82" height="82" rx="24" pathLength="100" />
            </svg>
            <span class="preloader-initial">{{ str($brand)->substr(0, 1)->upper() }}</span>
            <span class="preloader-shine"></span>
        </div>

        <p class="preloader-word" aria-hidden="true">
            @foreach (mb_str_split($primaryWord) as $letter)
                <span class="preloader-letter" style="--i: {{ $loop->index }}">{{ $letter }}</span>
            @endforeach
        </p>
        @if ($secondaryWords !== '')
            <p class="preloader-sub" aria-hidden="true">{{ $secondaryWords }}</p>
        @endif

        <div class="preloader-bar" aria-hidden="true"><span></span></div>
        <p class="preloader-tagline" aria-hidden="true">{{ config('listings.company.tagline') }}</p>
    </div>
</div>
