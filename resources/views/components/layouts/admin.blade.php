@props(['title', 'subtitle' => null, 'actions' => null])

@php
    $nav = [
        ['label' => 'Dashboard', 'icon' => 'grid', 'url' => route('admin.dashboard'), 'active' => request()->routeIs('admin.dashboard')],
        ['label' => 'Properties', 'icon' => 'building', 'url' => route('admin.properties.index'), 'active' => request()->routeIs('admin.properties.*')],
        ['label' => 'Engagements', 'icon' => 'inbox', 'url' => route('admin.engagements.index'), 'active' => request()->routeIs('admin.engagements.*'), 'badge' => $newEngagementsCount],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-mode="{{ $colorMode }}" data-motion="{{ $siteTheme->animationsEnabled() ? 'full' : 'reduced' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex">
        <title>{{ $title }} · Admin · {{ config('app.name') }}</title>
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <x-site-theme :theme="$siteTheme" />
    </head>
    <body class="min-h-screen bg-ivory font-sans text-ink-800 antialiased">
        <x-page-loader />
        <x-flash />
        @if (config('listings.dev_account_switcher'))
            <x-dev-account-switcher />
        @endif

        {{-- Sidebar (designed light; follows light/dark mode unless pinned in the Site manager) --}}
        <aside id="admin-sidebar" data-toggle-panel @if ($sidebarPalette = $siteTheme->sidebarPalette()) data-palette="{{ $sidebarPalette }}" @endif class="fixed inset-y-0 left-0 z-50 hidden w-72 flex-col border-r border-ink-100 bg-white text-ink-800 shadow-2xl lg:flex lg:shadow-none [&.is-open]:flex">
            <div class="flex h-20 items-center justify-between px-6">
                <x-logo />
                <span class="rounded-full bg-brass-50 px-2 py-0.5 text-[10px] font-bold tracking-widest text-brass-700 uppercase ring-1 ring-brass-200">Admin</span>
            </div>

            <nav class="mt-2 flex-1 space-y-1 overflow-y-auto px-4" aria-label="Admin">
                <p class="px-3 pb-2 text-[11px] font-semibold tracking-[0.2em] text-ink-300 uppercase">Manage</p>
                @foreach ($nav as $item)
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

                {{-- Adverts manager dropdown --}}
                @php
                    $advertsOpen = request()->routeIs('admin.adverts.*');
                    $advertView = request()->routeIs('admin.adverts.index') ? request('view', 'all') : null;
                    $advertLinks = [
                        ['label' => 'All adverts', 'url' => route('admin.adverts.index'), 'active' => $advertView === 'all'],
                        ...collect(\App\Enums\AdvertPage::cases())->map(fn ($advertPage) => [
                            'label' => $advertPage->label(),
                            'url' => route('admin.adverts.index', ['view' => $advertPage->value]),
                            'active' => $advertView === $advertPage->value,
                        ])->all(),
                        ['label' => 'Pop-up adverts', 'url' => route('admin.adverts.index', ['view' => 'popup']), 'active' => $advertView === 'popup'],
                        ['label' => '+ New advert', 'url' => route('admin.adverts.create'), 'active' => request()->routeIs('admin.adverts.create')],
                    ];
                    $siteLinks = collect(\App\Enums\SiteArea::cases())->map(fn ($siteArea) => [
                        'label' => $siteArea->label(),
                        'url' => route('admin.site.edit', $siteArea),
                        'active' => request()->routeIs('admin.site.edit') && request()->route('area') === $siteArea,
                    ])->all();
                @endphp
                <x-admin-nav-group id="nav-adverts" label="Adverts" icon="megaphone" :open="$advertsOpen" :links="$advertLinks" />
                <x-admin-nav-group id="nav-site" label="Site manager" icon="sliders" :open="request()->routeIs('admin.site.*')" :links="$siteLinks" />

                <p class="px-3 pt-8 pb-2 text-[11px] font-semibold tracking-[0.2em] text-ink-300 uppercase">Quick actions</p>
                <a href="{{ route('admin.properties.create') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-ink-600 transition hover:bg-ivory hover:text-ink-900">
                    <x-icon name="plus" class="size-5 text-ink-300 group-hover:text-ink-500" /> New property
                </a>
                <a href="{{ route('home') }}" target="_blank" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-ink-600 transition hover:bg-ivory hover:text-ink-900">
                    <x-icon name="globe" class="size-5 text-ink-300 group-hover:text-ink-500" /> View website <x-icon name="external" class="ml-auto size-3.5 text-ink-300" />
                </a>
            </nav>

            <div class="m-4 flex items-center gap-3 rounded-2xl bg-ivory p-3 ring-1 ring-ink-100">
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
        </aside>

        <div class="lg:pl-72">
            {{-- Topbar --}}
            <header data-sticky-header class="sticky top-0 z-30 border-b border-transparent bg-ivory/85 backdrop-blur-xl transition [&.is-scrolled]:border-ink-100">
                <div class="flex h-20 items-center gap-4 px-4 sm:px-8">
                    <button type="button" data-toggle="#admin-sidebar" data-toggle-class="is-open" aria-expanded="false" class="grid size-10 place-items-center rounded-xl text-ink-700 ring-1 ring-ink-200 lg:hidden" aria-label="Open navigation"><x-icon name="menu" /></button>
                    <div class="min-w-0">
                        <h1 class="truncate font-display text-2xl tracking-tight text-ink-950 sm:text-3xl">{{ $title }}</h1>
                        @if ($subtitle)
                            <p class="hidden truncate text-sm text-ink-400 sm:block">{{ $subtitle }}</p>
                        @endif
                    </div>
                    <div class="ml-auto flex items-center gap-2">
                        {{ $actions }}
                        <x-notification-menu :notifications="$recentNotifications" :unread="$unreadNotificationsCount" />
                        <x-profile-menu :mode="$colorMode" :links="[
                            ['label' => 'Admin dashboard', 'icon' => 'grid', 'url' => route('admin.dashboard')],
                            ['label' => 'Site manager', 'icon' => 'sliders', 'url' => route('admin.site.edit', 'admin')],
                            ['label' => 'My client dashboard', 'icon' => 'user', 'url' => route('account.index')],
                            ['label' => 'View website', 'icon' => 'globe', 'url' => route('home'), 'external' => true],
                        ]" />
                    </div>
                </div>
            </header>

            <main class="px-4 pt-4 pb-16 sm:px-8">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
