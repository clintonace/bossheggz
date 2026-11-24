@props(['title', 'subtitle' => null, 'actions' => null])

@php
    $engagementFilter = request()->routeIs('account.engagements.index') ? request('filter', 'all') : null;
    $notificationFilter = request()->routeIs('account.notifications.index') ? request('filter', 'all') : null;
    $nav = [
        ['label' => 'Overview', 'icon' => 'grid', 'url' => route('account.index'), 'active' => request()->routeIs('account.index')],
        [
            'group' => 'nav-my-engagements', 'label' => 'My engagements', 'icon' => 'inbox', 'badge' => $openEngagementsCount,
            'links' => [
                ['label' => 'All engagements', 'icon' => 'inbox', 'url' => route('account.engagements.index'), 'active' => $engagementFilter === 'all' || request()->routeIs('account.engagements.show')],
                ['label' => 'In progress', 'icon' => 'trending', 'url' => route('account.engagements.index', ['filter' => 'open']), 'active' => $engagementFilter === 'open', 'badge' => $openEngagementsCount],
                ['label' => 'Completed', 'icon' => 'check-circle', 'url' => route('account.engagements.index', ['filter' => 'completed']), 'active' => $engagementFilter === 'completed'],
                ['label' => 'Engage with a property', 'icon' => 'plus', 'action' => true, 'url' => route('properties.index'), 'active' => false],
            ],
        ],
        [
            'group' => 'nav-my-notifications', 'label' => 'Notifications', 'icon' => 'bell', 'badge' => $unreadNotificationsCount,
            'links' => [
                ['label' => 'All notifications', 'icon' => 'bell', 'url' => route('account.notifications.index'), 'active' => $notificationFilter === 'all'],
                ['label' => 'Unread', 'icon' => 'sparkles', 'url' => route('account.notifications.index', ['filter' => 'unread']), 'active' => $notificationFilter === 'unread', 'badge' => $unreadNotificationsCount],
            ],
        ],
        ['label' => 'Profile & security', 'icon' => 'user', 'url' => route('account.profile.edit'), 'active' => request()->routeIs('account.profile.*')],
    ];
    $explore = [
        ['label' => 'All properties', 'icon' => 'search', 'url' => route('properties.index')],
        ['label' => 'For sale', 'icon' => 'tag', 'url' => route('properties.index', ['listing' => 'sale'])],
        ['label' => 'For rent', 'icon' => 'key', 'url' => route('properties.index', ['listing' => 'rent'])],
        ['label' => 'Shortlets', 'icon' => 'calendar', 'url' => route('properties.index', ['listing' => 'shortlet'])],
        ['label' => 'Investment opportunities', 'icon' => 'briefcase', 'url' => route('investments.index')],
        ['label' => 'Land banking', 'icon' => 'leaf', 'url' => route('land-banking.index')],
        ['label' => 'Speak to an adviser', 'icon' => 'message', 'action' => true, 'url' => route('contact', ['type' => 'consultation'])],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-mode="{{ $colorMode }}" data-motion="{{ $siteTheme->animationsEnabled() ? 'full' : 'reduced' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex">
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

        {{-- Sidebar --}}
        <aside id="client-sidebar" data-toggle-panel @if ($sidebarPalette = $siteTheme->sidebarPalette()) data-palette="{{ $sidebarPalette }}" @endif class="fixed inset-y-0 left-0 z-50 hidden w-72 flex-col border-r border-ink-100 bg-white text-ink-800 shadow-2xl lg:flex lg:shadow-none [&.is-open]:flex">
            <div class="pointer-events-none absolute inset-0 -z-10 overflow-hidden" aria-hidden="true">
                <div class="absolute -top-24 -left-20 size-72 rounded-full bg-brass-100/70 blur-3xl"></div>
                <div class="absolute top-1/2 -right-28 size-72 rounded-full bg-ink-100/80 blur-3xl"></div>
            </div>
            <div class="flex h-20 items-center px-6">
                <x-logo />
            </div>

            <nav class="mt-2 flex-1 space-y-1 overflow-y-auto px-4" aria-label="Account">
                <p class="px-3 pb-2 text-[11px] font-semibold tracking-[0.2em] text-ink-300 uppercase">Your space</p>
                @foreach ($nav as $item)
                    @if (isset($item['group']))
                        <x-nav-group :id="$item['group']" :label="$item['label']" :icon="$item['icon']" :links="$item['links']" :badge="$item['badge'] ?? 0" />
                        @continue
                    @endif
                    <a href="{{ $item['url'] }}" @class([
                        'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition',
                        'bg-ink-900 text-white shadow-soft' => $item['active'],
                        'text-ink-600 hover:bg-ivory hover:text-ink-900' => ! $item['active'],
                    ])>
                        <x-icon :name="$item['icon']" @class(['size-5', 'text-brass-300' => $item['active'], 'text-ink-300 group-hover:text-ink-500' => ! $item['active']]) />
                        {{ $item['label'] }}
                        @if (($item['badge'] ?? 0) > 0)
                            <span @class([
                                'ml-auto rounded-full px-2 py-0.5 text-[11px] font-bold',
                                'bg-brass-500 text-white' => ! $item['active'],
                                'bg-white/15 text-white' => $item['active'],
                            ])>{{ $item['badge'] }}</span>
                        @endif
                    </a>
                @endforeach

                <p class="px-3 pt-8 pb-2 text-[11px] font-semibold tracking-[0.2em] text-ink-300 uppercase">Explore</p>
                <x-nav-group id="nav-explore" label="Browse" icon="search" :links="collect($explore)->map(fn (array $link) => [...$link, 'active' => false])->all()" />

                @if (auth()->user()->isAdmin())
                    <p class="px-3 pt-8 pb-2 text-[11px] font-semibold tracking-[0.2em] text-ink-300 uppercase">Admin</p>
                    <a href="{{ route('admin.dashboard') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-ink-600 transition hover:bg-ivory hover:text-ink-900">
                        <x-icon name="shield" class="size-5 text-ink-300 group-hover:text-ink-500" /> Admin dashboard
                    </a>
                @endif
            </nav>

            <div class="m-4 space-y-3">
                <a href="{{ route('properties.index') }}" class="block rounded-2xl bg-gradient-to-br from-brass-100 via-sand to-ivory p-4 ring-1 ring-brass-200/60 transition hover:ring-brass-300">
                    <p class="font-display text-lg leading-tight text-ink-900">Find your next home</p>
                    <p class="mt-1 text-xs text-ink-500">New listings are added every week.</p>
                    <span class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-brass-700">Browse now <x-icon name="arrow-right" class="size-3.5" /></span>
                </a>
                <div class="flex items-center gap-3 rounded-2xl bg-ivory p-3 ring-1 ring-ink-100">
                    <span class="grid size-10 shrink-0 place-items-center rounded-full bg-ink-900 font-semibold text-brass-200">{{ str(auth()->user()->name)->substr(0, 1)->upper() }}</span>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-ink-900">{{ auth()->user()->name }}</p>
                        <p class="truncate text-xs text-ink-400">{{ auth()->user()->email }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="ml-auto">
                        @csrf
                        <button class="grid size-8 place-items-center rounded-lg text-ink-400 transition hover:bg-white hover:text-ink-900" aria-label="Sign out" title="Sign out"><x-icon name="logout" class="size-4" /></button>
                    </form>
                </div>
            </div>
        </aside>

        <div class="lg:pl-72">
            {{-- Topbar --}}
            <header data-sticky-header class="sticky top-0 z-30 border-b border-transparent bg-ivory/85 backdrop-blur-xl transition [&.is-scrolled]:border-ink-100">
                <div class="flex h-20 items-center gap-4 px-4 sm:px-8">
                    <button type="button" data-toggle="#client-sidebar" data-toggle-class="is-open" aria-expanded="false" class="grid size-10 shrink-0 place-items-center rounded-xl text-ink-700 ring-1 ring-ink-200 lg:hidden" aria-label="Open navigation"><x-icon name="menu" /></button>
                    <div class="min-w-0">
                        <h1 class="truncate font-display text-2xl tracking-tight text-ink-950 sm:text-3xl">{{ $title }}</h1>
                        @if ($subtitle)
                            <p class="hidden truncate text-sm text-ink-400 sm:block">{{ $subtitle }}</p>
                        @endif
                    </div>
                    <div class="ml-auto flex items-center gap-2">
                        {{ $actions }}
                        <x-notification-menu :notifications="$recentNotifications" :unread="$unreadNotificationsCount" />
                        <x-profile-menu :mode="$colorMode" :links="array_values(array_filter([
                            auth()->user()->isAdmin() ? ['label' => 'Admin dashboard', 'icon' => 'shield', 'url' => route('admin.dashboard')] : null,
                            ['label' => 'Overview', 'icon' => 'grid', 'url' => route('account.index')],
                            ['label' => 'My engagements', 'icon' => 'inbox', 'url' => route('account.engagements.index')],
                            ['label' => 'Profile & security', 'icon' => 'user', 'url' => route('account.profile.edit')],
                            ['label' => 'Browse properties', 'icon' => 'search', 'url' => route('properties.index')],
                        ]))" />
                    </div>
                </div>
            </header>

            <main class="px-4 pt-4 pb-16 sm:px-8">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
