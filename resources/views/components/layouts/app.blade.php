@props(['title' => null, 'description' => null, 'transparentHeader' => false])

@php
    $company = config('listings.company');
    $links = [
        ['label' => 'Home', 'url' => route('home'), 'active' => request()->routeIs('home')],
        ['label' => 'Properties', 'url' => route('properties.index'), 'active' => request()->routeIs('properties.*')],
        ['label' => 'Investment Opportunities', 'url' => route('investments.index'), 'active' => request()->routeIs('investments.*')],
        ['label' => 'Land Banking', 'url' => route('land-banking.index'), 'active' => request()->routeIs('land-banking.*')],
        ['label' => 'Services', 'url' => route('services'), 'active' => request()->routeIs('services')],
        ['label' => 'Insights', 'url' => route('insights.index'), 'active' => request()->routeIs('insights.*')],
        ['label' => 'About Us', 'url' => route('about'), 'active' => request()->routeIs('about')],
        ['label' => 'Contact Us', 'url' => route('contact'), 'active' => request()->routeIs('contact')],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-mode="{{ $colorMode }}" data-motion="{{ $siteTheme->animationsEnabled() ? 'full' : 'reduced' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ? $title.' · '.$company['name'] : $company['name'].' — '.$company['tagline'] }}</title>
        <meta name="description" content="{{ $description ?? 'Discover verified property opportunities, make informed real estate decisions and build lasting wealth with '.$company['name'].'.' }}">
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <x-site-theme :theme="$siteTheme" />
    </head>
    <body class="min-h-screen font-sans antialiased">
        {{-- Ambient colour behind the glass surfaces --}}
        <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden" aria-hidden="true">
            <div class="absolute -top-48 -left-40 size-[42rem] rounded-full bg-brass-200/45 blur-[120px]"></div>
            <div class="absolute top-1/3 -right-48 size-[38rem] rounded-full bg-ink-200/50 blur-[120px]"></div>
            <div class="absolute -bottom-48 left-1/4 size-[34rem] rounded-full bg-brass-100/60 blur-[120px]"></div>
        </div>

        <x-page-loader />
        <x-flash />
        @if ($popupAdvert)
            <x-advert-popup :advert="$popupAdvert" />
        @endif
        @if (config('listings.dev_account_switcher'))
            <x-dev-account-switcher />
        @endif

        {{-- Contact strip --}}
        <div data-palette="normal" class="hidden bg-ink-950 text-xs text-ink-300 md:block">
            <div class="mx-auto flex h-10 max-w-7xl items-center justify-between gap-6 px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-5">
                    <a href="tel:{{ $company['phone'] }}" class="inline-flex items-center gap-1.5 transition hover:text-white"><x-icon name="phone" class="size-3.5" /> {{ $company['phone'] }}</a>
                    <a href="mailto:{{ $company['email'] }}" class="inline-flex items-center gap-1.5 transition hover:text-white"><x-icon name="mail" class="size-3.5" /> {{ strtoupper($company['email']) }}</a>
                    <a href="https://instagram.com/{{ $company['instagram'] }}" target="_blank" rel="noopener" data-no-loader class="hidden items-center gap-1.5 transition hover:text-white lg:inline-flex"><x-icon name="instagram" class="size-3.5" /> {{ '@'.$company['instagram'] }}</a>
                </div>
                <div class="flex items-center gap-5">
                    <span class="hidden text-ink-400 lg:inline">{{ $company['tagline'] }}</span>
                    <a href="{{ route('contact', ['type' => 'list_property']) }}" class="inline-flex items-center gap-1.5 font-semibold text-brass-300 transition hover:text-brass-200"><x-icon name="plus" class="size-3.5" /> List Your Property</a>
                </div>
            </div>
        </div>

        <header data-sticky-header class="group/header sticky top-0 z-40 border-b border-white/40 bg-ivory/55 backdrop-blur-2xl backdrop-saturate-150 transition-[border-color,box-shadow,background-color] duration-300 [&.is-scrolled]:border-ink-100/70 [&.is-scrolled]:bg-ivory/70 [&.is-scrolled]:shadow-[0_8px_30px_-12px_rgb(18_24_37/0.14)]">
            <div class="mx-auto flex h-18 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                <x-logo />

                <nav class="hidden items-center gap-0.5 xl:flex" aria-label="Main">
                    @foreach ($links as $link)
                        <a href="{{ $link['url'] }}" @class([
                            'rounded-full px-3 py-2 text-[13px] font-medium whitespace-nowrap transition',
                            'bg-white/80 text-ink-900 shadow-soft ring-1 ring-white' => $link['active'],
                            'text-ink-500 hover:bg-white/50 hover:text-ink-900' => ! $link['active'],
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
                    @endauth

                    <a href="{{ route('contact', ['type' => 'consultation']) }}" class="btn btn-primary hidden sm:inline-flex">Speak to an Adviser</a>

                    <button type="button" data-toggle="#mobile-nav" aria-expanded="false" class="grid size-10 place-items-center rounded-full text-ink-700 hover:bg-white/60 xl:hidden" aria-label="Open menu">
                        <x-icon name="menu" />
                    </button>
                </div>
            </div>

            <nav id="mobile-nav" data-toggle-panel hidden class="border-t border-white/50 bg-ivory/85 px-4 pt-2 pb-4 backdrop-blur-2xl xl:hidden" aria-label="Mobile">
                <div class="mx-auto grid max-w-7xl grid-cols-1 gap-1 sm:grid-cols-2">
                    @foreach ($links as $link)
                        <a href="{{ $link['url'] }}" @class(['block rounded-xl px-4 py-3 text-sm font-medium', 'bg-white/80 text-ink-900 shadow-soft' => $link['active'], 'text-ink-600' => ! $link['active']])>{{ $link['label'] }}</a>
                    @endforeach
                </div>
                <div class="mx-auto mt-3 flex max-w-7xl flex-wrap gap-2 border-t border-ink-100/70 pt-4">
                    <a href="{{ route('contact', ['type' => 'consultation']) }}" class="btn btn-primary">Speak to an Adviser</a>
                    <a href="{{ route('contact', ['type' => 'list_property']) }}" class="btn btn-outline">List Your Property</a>
                    @guest
                        <a href="{{ route('login') }}" class="btn btn-ghost">Sign in</a>
                        <a href="{{ route('register') }}" class="btn btn-ghost">Create account</a>
                    @endguest
                </div>
            </nav>
        </header>

        <main>
            {{ $slot }}
        </main>

        <footer data-palette="{{ $siteTheme->footerIsDark() ? 'normal' : 'inverted' }}" class="mt-24 bg-ink-950 text-ink-300">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-12 md:grid-cols-12">
                    <div class="md:col-span-4">
                        <x-logo light />
                        <p class="mt-5 text-sm font-semibold tracking-wide text-white">{{ $company['tagline'] }}</p>
                        <p class="mt-3 max-w-sm text-sm leading-relaxed text-ink-400">
                            Real estate brokerage and investment advisory — connecting individuals and investors with verified, strategic property opportunities.
                        </p>
                        <x-whatsapp-button class="mt-6 bg-white/10 text-white ring-1 ring-white/15 backdrop-blur hover:bg-white/15" />
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-xs font-semibold tracking-[0.2em] text-brass-300 uppercase">Explore</p>
                        <ul class="mt-5 space-y-3 text-sm">
                            <li><a href="{{ route('properties.index', ['listing' => 'sale']) }}" class="transition hover:text-white">Properties for sale</a></li>
                            <li><a href="{{ route('properties.index', ['listing' => 'rent']) }}" class="transition hover:text-white">Properties for rent</a></li>
                            <li><a href="{{ route('properties.index', ['listing' => 'shortlet']) }}" class="transition hover:text-white">Shortlets</a></li>
                            <li><a href="{{ route('properties.index') }}" class="transition hover:text-white">All listings</a></li>
                        </ul>
                    </div>
                    <div class="md:col-span-3">
                        <p class="text-xs font-semibold tracking-[0.2em] text-brass-300 uppercase">Company</p>
                        <ul class="mt-5 space-y-3 text-sm">
                            <li><a href="{{ route('investments.index') }}" class="transition hover:text-white">Investment opportunities</a></li>
                            <li><a href="{{ route('land-banking.index') }}" class="transition hover:text-white">Land banking</a></li>
                            <li><a href="{{ route('services') }}" class="transition hover:text-white">Services</a></li>
                            <li><a href="{{ route('insights.index') }}" class="transition hover:text-white">Insights</a></li>
                            <li><a href="{{ route('about') }}" class="transition hover:text-white">About us</a></li>
                        </ul>
                    </div>
                    <div class="md:col-span-3">
                        <p class="text-xs font-semibold tracking-[0.2em] text-brass-300 uppercase">Contact</p>
                        <ul class="mt-5 space-y-3 text-sm">
                            <li><a href="tel:{{ $company['phone'] }}" class="inline-flex items-center gap-2 transition hover:text-white"><x-icon name="phone" class="size-4" /> Tel: {{ $company['phone'] }}</a></li>
                            <li><a href="{{ \App\Models\WhatsappNumber::linkTo(\App\Models\WhatsappNumber::primaryNumber()) }}" target="_blank" rel="noopener" data-no-loader class="inline-flex items-center gap-2 transition hover:text-white"><x-icon name="whatsapp" class="size-4" /> WhatsApp: +{{ \App\Models\WhatsappNumber::primaryNumber() }}</a></li>
                            <li><a href="mailto:{{ $company['email'] }}" class="inline-flex items-center gap-2 transition hover:text-white"><x-icon name="mail" class="size-4" /> {{ strtoupper($company['email']) }}</a></li>
                            <li><a href="https://instagram.com/{{ $company['instagram'] }}" target="_blank" rel="noopener" data-no-loader class="inline-flex items-center gap-2 transition hover:text-white"><x-icon name="instagram" class="size-4" /> {{ '@'.$company['instagram'] }}</a></li>
                            @if ($company['address'])
                                <li class="inline-flex items-start gap-2"><x-icon name="map-pin" class="mt-0.5 size-4" /> {{ $company['address'] }}</li>
                            @endif
                        </ul>
                    </div>
                </div>
                <div class="mt-14 flex flex-col justify-between gap-4 border-t border-white/10 pt-8 text-xs text-ink-500 sm:flex-row">
                    <p>&copy; {{ date('Y') }} {{ $company['legal_name'] }}. All rights reserved.</p>
                    <p>{{ $company['tagline'] }}</p>
                </div>
            </div>
        </footer>
    </body>
</html>
