@props(['title', 'heading', 'subheading' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-mode="{{ $colorMode }}" data-motion="{{ $siteTheme->animationsEnabled() ? 'full' : 'reduced' }}" data-glass="{{ $siteTheme->glassStyle() }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title }} · {{ config('listings.company.name') }}</title>
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <x-site-theme :theme="$siteTheme" />
    </head>
    <body class="min-h-screen font-sans antialiased">
        <x-page-loader />
        <x-flash />
        @if (config('listings.dev_account_switcher'))
            <x-dev-account-switcher />
        @endif

        <div class="grid grid-cols-1 min-h-screen lg:grid-cols-2">
            <div data-palette="normal" class="relative hidden overflow-hidden bg-ink-950 lg:block">
                <img src="https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=1600&q=80" alt="" class="absolute inset-0 size-full object-cover opacity-60" onerror="this.remove()">
                <div class="absolute inset-0 bg-gradient-to-t from-ink-950 via-ink-950/40 to-ink-950/10"></div>
                <div class="relative flex h-full flex-col justify-between p-12">
                    <x-logo light />
                    <div class="max-w-md">
                        <p class="font-display text-4xl leading-tight text-white">"Every home has a story. <em class="text-brass-300">Let's start yours.</em>"</p>
                        <ul class="mt-8 space-y-3 text-sm text-ink-200">
                            <li class="flex items-center gap-3"><span class="grid size-7 place-items-center rounded-full bg-white/10 text-brass-300"><x-icon name="inbox" class="size-4" /></span> See every property you've engaged with</li>
                            <li class="flex items-center gap-3"><span class="grid size-7 place-items-center rounded-full bg-white/10 text-brass-300"><x-icon name="trending" class="size-4" /></span> Follow progress step by step</li>
                            <li class="flex items-center gap-3"><span class="grid size-7 place-items-center rounded-full bg-white/10 text-brass-300"><x-icon name="mail" class="size-4" /></span> Get updates in-app and by email</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="flex flex-col px-6 py-10 sm:px-12">
                <div class="lg:hidden"><x-logo /></div>
                <div class="m-auto w-full max-w-md py-12">
                    <h1 class="animate-fade-up font-display text-4xl tracking-tight text-ink-950">{{ $heading }}</h1>
                    @if ($subheading)
                        <p class="mt-3 animate-fade-up text-ink-500 [animation-delay:60ms]">{{ $subheading }}</p>
                    @endif
                    <div class="mt-10 animate-fade-up [animation-delay:120ms]">
                        {{ $slot }}
                    </div>
                </div>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 self-center text-sm text-ink-400 hover:text-ink-900"><x-icon name="arrow-left" class="size-4" /> Back to properties</a>
            </div>
        </div>
    </body>
</html>
