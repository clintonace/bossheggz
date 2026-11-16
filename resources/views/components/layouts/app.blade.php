@props(['title' => null, 'description' => null, 'transparentHeader' => false])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-mode="{{ $colorMode }}" data-motion="{{ $siteTheme->animationsEnabled() ? 'full' : 'reduced' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ? $title.' · '.config('app.name') : config('app.name').' — Homes for sale & rent' }}</title>
        <meta name="description" content="{{ $description ?? 'Discover beautiful homes for sale and rent. Browse curated listings and engage with the properties you love.' }}">
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <x-site-theme :theme="$siteTheme" />
    </head>
    <body class="min-h-screen font-sans antialiased">
        <x-page-loader />
        <x-flash />
        @if ($popupAdvert)
            <x-advert-popup :advert="$popupAdvert" />
        @endif
        @if (config('listings.dev_account_switcher'))
            <x-dev-account-switcher />
        @endif

        <header data-sticky-header class="group/header sticky top-0 z-40 border-b border-transparent bg-ivory/80 backdrop-blur-xl transition-[border-color,box-shadow] duration-300 [&.is-scrolled]:border-ink-100 [&.is-scrolled]:shadow-[0_8px_30px_-12px_rgb(18_24_37/0.12)]">
            <div class="mx-auto flex h-18 max-w-7xl items-center justify-between gap-6 px-4 sm:px-6 lg:px-8">
                <x-logo />

                <nav class="hidden items-center gap-1 md:flex" aria-label="Main">
                    @php
                        $links = [
                            ['label' => 'Home', 'url' => route('home'), 'active' => request()->routeIs('home')],
                            ['label' => 'All properties', 'url' => route('properties.index'), 'active' => request()->routeIs('properties.*') && ! request('listing')],
                            ['label' => 'For sale', 'url' => route('properties.index', ['listing' => 'sale']), 'active' => request('listing') === 'sale'],
                            ['label' => 'For rent', 'url' => route('properties.index', ['listing' => 'rent']), 'active' => request('listing') === 'rent'],
                        ];
                    @endphp
                    @foreach ($links as $link)
                        <a href="{{ $link['url'] }}" @class([
                            'rounded-full px-4 py-2 text-sm font-medium transition',
                            'bg-white text-ink-900 shadow-soft ring-1 ring-ink-100' => $link['active'],
                            'text-ink-500 hover:text-ink-900' => ! $link['active'],
                        ])>{{ $link['label'] }}</a>
                    @endforeach
                </nav>

                <div class="flex items-center gap-2">
                    @auth
                        <x-notification-menu :notifications="$recentNotifications" :unread="$unreadNotificationsCount" />

                        <x-profile-menu :mode="$colorMode" :links="array_values(array_filter([
                            auth()->user()->isAdmin() ? ['label' => 'Admin dashboard', 'icon' => 'shield', 'url' => route('admin.dashboard')] : null,
                            ['label' => 'My dashboard', 'icon' => 'grid', 'url' => route('account.index')],
                            ['label' => 'My engagements', 'icon' => 'inbox', 'url' => route('account.engagements.index')],
                            ['label' => 'Profile & security', 'icon' => 'user', 'url' => route('account.profile.edit')],
                        ]))" />
                    @else
                        <a href="{{ route('login') }}" class="btn btn-ghost hidden sm:inline-flex">Sign in</a>
                        <a href="{{ route('register') }}" class="btn btn-primary">Create account</a>
                    @endauth

                    <button type="button" data-toggle="#mobile-nav" aria-expanded="false" class="grid size-10 place-items-center rounded-full text-ink-700 hover:bg-ink-100/70 md:hidden" aria-label="Open menu">
                        <x-icon name="menu" />
                    </button>
                </div>
            </div>

            <nav id="mobile-nav" data-toggle-panel hidden class="border-t border-ink-100 bg-ivory px-4 pt-2 pb-4 md:hidden" aria-label="Mobile">
                @foreach ($links as $link)
                    <a href="{{ $link['url'] }}" @class(['block rounded-xl px-4 py-3 text-sm font-medium', 'bg-white text-ink-900 shadow-soft' => $link['active'], 'text-ink-600' => ! $link['active']])>{{ $link['label'] }}</a>
                @endforeach
                @guest
                    <a href="{{ route('login') }}" class="block rounded-xl px-4 py-3 text-sm font-medium text-ink-600">Sign in</a>
                @endguest
            </nav>
        </header>

        <main>
            {{ $slot }}
        </main>

        <footer data-palette="{{ $siteTheme->footerIsDark() ? 'normal' : 'inverted' }}" class="mt-24 bg-ink-950 text-ink-300">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-12 md:grid-cols-12">
                    <div class="md:col-span-5">
                        <x-logo light />
                        <p class="mt-5 max-w-sm text-sm leading-relaxed text-ink-400">
                            Curated homes for sale and rent. Find a place you love, engage in one click, and follow every step until you get the keys.
                        </p>
                    </div>
                    <div class="md:col-span-3">
                        <p class="text-xs font-semibold tracking-[0.2em] text-brass-300 uppercase">Explore</p>
                        <ul class="mt-5 space-y-3 text-sm">
                            <li><a href="{{ route('properties.index', ['listing' => 'sale']) }}" class="transition hover:text-white">Properties for sale</a></li>
                            <li><a href="{{ route('properties.index', ['listing' => 'rent']) }}" class="transition hover:text-white">Properties for rent</a></li>
                            <li><a href="{{ route('properties.index') }}" class="transition hover:text-white">All listings</a></li>
                        </ul>
                    </div>
                    <div class="md:col-span-4">
                        <p class="text-xs font-semibold tracking-[0.2em] text-brass-300 uppercase">Contact</p>
                        <ul class="mt-5 space-y-3 text-sm">
                            <li><a href="mailto:{{ config('listings.contact_email') }}" class="inline-flex items-center gap-2 transition hover:text-white"><x-icon name="mail" class="size-4" /> {{ config('listings.contact_email') }}</a></li>
                            @if (config('listings.contact_phone'))
                                <li><a href="tel:{{ config('listings.contact_phone') }}" class="inline-flex items-center gap-2 transition hover:text-white"><x-icon name="phone" class="size-4" /> {{ config('listings.contact_phone') }}</a></li>
                            @endif
                        </ul>
                    </div>
                </div>
                <div class="mt-14 flex flex-col justify-between gap-4 border-t border-white/10 pt-8 text-xs text-ink-500 sm:flex-row">
                    <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                    <p>Crafted with care for people looking for home.</p>
                </div>
            </div>
        </footer>
    </body>
</html>
