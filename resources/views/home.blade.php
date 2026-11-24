@php
    $hero = $content['hero'];
    $section = fn (string $key) => $content[$key];
@endphp

<x-layouts.app :title="$content['meta']->value('title')" :description="$content['meta']->value('description')">
    <x-advert-slot :adverts="$adverts" at="hero:before" class="mt-6" />
    {{-- Hero --}}
    <section class="relative overflow-hidden">
        <div class="pointer-events-none absolute -top-40 -right-40 size-[38rem] rounded-full bg-brass-100/60 blur-3xl"></div>
        <div class="pointer-events-none absolute top-60 -left-40 size-[28rem] rounded-full bg-ink-100/60 blur-3xl"></div>

        <div class="relative mx-auto grid grid-cols-1 max-w-7xl items-center gap-14 px-4 pt-10 pb-20 sm:px-6 lg:grid-cols-12 lg:px-8 lg:pt-16 lg:pb-28">
            <div class="lg:col-span-6">
                @if (filled($hero->value('eyebrow')))
                    <p class="eyebrow animate-fade-up">{{ $hero->value('eyebrow') }}</p>
                @endif
                <h1 class="mt-5 animate-fade-up font-display text-5xl leading-[1.05] font-medium tracking-tight text-ink-950 [animation-delay:80ms] sm:text-6xl lg:text-7xl">
                    {{ $hero->value('title') }}@if (filled($hero->value('title_highlight')))<br><em class="text-brass-500">{{ $hero->value('title_highlight') }}</em>@endif
                </h1>
                @if (filled($hero->value('description')))
                    <p class="mt-6 max-w-lg animate-fade-up text-lg leading-relaxed text-ink-500 [animation-delay:160ms]">{{ $hero->value('description') }}</p>
                @endif
                @if (filled($hero->value('primary_label')) || filled($hero->value('secondary_label')))
                    <div class="mt-8 flex animate-fade-up flex-wrap gap-3 [animation-delay:200ms]">
                        @if (filled($hero->value('primary_label')))
                            <a href="{{ $hero->value('primary_url') ?: route('properties.index') }}" class="btn btn-primary btn-lg">{{ $hero->value('primary_label') }} <x-icon name="arrow-right" class="size-4" /></a>
                        @endif
                        @if (filled($hero->value('secondary_label')))
                            <a href="{{ $hero->value('secondary_url') ?: route('investments.index') }}" class="btn btn-lg bg-white/60 text-ink-900 ring-1 ring-white/80 backdrop-blur hover:bg-white/80">{{ $hero->value('secondary_label') }}</a>
                        @endif
                    </div>
                @endif

                {{-- Search --}}
                <form action="{{ route('properties.index') }}" method="GET" data-tour="property-search" class="glass-strong mt-10 animate-fade-up rounded-3xl p-2 [animation-delay:240ms]">
                    <div class="flex flex-wrap gap-1 p-1">
                        @foreach (['' => 'All', 'sale' => 'Buy', 'rent' => 'Rent', 'shortlet' => 'Shortlet', 'lease' => 'Lease'] as $value => $label)
                            <label class="cursor-pointer">
                                <input type="radio" name="listing" value="{{ $value }}" class="peer sr-only" @checked($value === '')>
                                <span class="block rounded-full px-4 py-1.5 text-sm font-medium text-ink-500 transition peer-checked:bg-ink-900 peer-checked:text-white hover:text-ink-900 peer-checked:hover:text-white">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    <div class="grid grid-cols-1 gap-2 p-1 sm:grid-cols-2">
                        <label class="flex items-center gap-3 rounded-2xl bg-white/70 px-4 py-3 ring-1 ring-white/80 transition focus-within:bg-white focus-within:ring-brass-300">
                            <x-icon name="map-pin" class="size-5 text-ink-300" />
                            <input type="text" name="q" placeholder="State or city" aria-label="Location" class="w-full bg-transparent text-sm text-ink-900 placeholder:text-ink-300 focus:outline-none">
                        </label>
                        <label class="flex items-center gap-3 rounded-2xl bg-white/70 px-4 py-3 ring-1 ring-white/80 transition focus-within:bg-white focus-within:ring-brass-300">
                            <x-icon name="building" class="size-5 text-ink-300" />
                            <select name="type" aria-label="Property category" class="w-full appearance-none bg-transparent text-sm text-ink-900 focus:outline-none">
                                <option value="">All categories</option>
                                @foreach (\App\Enums\PropertyType::cases() as $propertyType)
                                    <option value="{{ $propertyType->value }}">{{ $propertyType->label() }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                    <div class="grid grid-cols-2 gap-2 p-1 sm:grid-cols-[1fr_1fr_auto]">
                        <label class="flex items-center gap-2 rounded-2xl bg-white/70 px-4 py-3 ring-1 ring-white/80 transition focus-within:bg-white focus-within:ring-brass-300">
                            <span class="text-sm text-ink-300">{{ config('listings.currency') }}</span>
                            <input type="number" name="min_price" min="0" placeholder="Min price" aria-label="Minimum price" class="w-full bg-transparent text-sm text-ink-900 placeholder:text-ink-300 focus:outline-none">
                        </label>
                        <label class="flex items-center gap-2 rounded-2xl bg-white/70 px-4 py-3 ring-1 ring-white/80 transition focus-within:bg-white focus-within:ring-brass-300">
                            <span class="text-sm text-ink-300">{{ config('listings.currency') }}</span>
                            <input type="number" name="max_price" min="0" placeholder="Max price" aria-label="Maximum price" class="w-full bg-transparent text-sm text-ink-900 placeholder:text-ink-300 focus:outline-none">
                        </label>
                        <button class="btn btn-accent col-span-2 rounded-2xl px-7 py-3.5 sm:col-span-1">
                            <x-icon name="search" class="size-4" /> Search
                        </button>
                    </div>
                </form>

                <dl class="mt-10 flex animate-fade-up gap-10 [animation-delay:320ms]">
                    @foreach ([['For sale', $stats['sale']], ['For rent', $stats['rent']], ['Locations', $stats['cities']]] as [$label, $value])
                        <div>
                            <dt class="text-xs font-medium tracking-wider text-ink-400 uppercase">{{ $label }}</dt>
                            <dd class="mt-1 font-display text-3xl text-ink-900">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>

            {{-- Collage --}}
            <div class="relative hidden lg:col-span-6 lg:block">
                @php($heroImages = $featured->concat($latest)->pluck('cover_url')->filter()->unique()->values())
                <div class="grid grid-cols-6 grid-rows-6 gap-4" style="height: 36rem">
                    <x-property-image :src="$heroImages->get(0)" class="col-span-4 row-span-4 animate-fade-up rounded-[2rem] shadow-lift [animation-delay:200ms]" />
                    <x-property-image :src="$heroImages->get(1)" class="col-span-2 row-span-3 animate-fade-up rounded-[2rem] shadow-soft [animation-delay:320ms]" />
                    <x-property-image :src="$heroImages->get(2)" class="col-span-2 row-span-3 animate-fade-up rounded-[2rem] shadow-soft [animation-delay:440ms]" />
                    <x-property-image :src="$heroImages->get(3)" class="col-span-4 row-span-2 animate-fade-up rounded-[2rem] shadow-soft [animation-delay:560ms]" />
                </div>

                @if (filled($hero->value('badge_title')))
                    <div class="glass-strong absolute -bottom-6 -left-8 flex animate-fade-up items-center gap-4 rounded-2xl p-4 pr-6 [animation-delay:700ms]">
                        <span class="grid size-11 place-items-center rounded-xl bg-emerald-50 text-emerald-600"><x-icon name="shield" /></span>
                        <div>
                            <p class="text-sm font-semibold text-ink-900">{{ $hero->value('badge_title') }}</p>
                            @if (filled($hero->value('badge_text')))
                                <p class="text-xs text-ink-400">{{ $hero->value('badge_text') }}</p>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <x-advert-slot :adverts="$adverts" at="hero:after" class="mb-16" />

    <x-advert-slot :adverts="$adverts" at="featured:before" class="mb-16" />
    {{-- Featured --}}
    @if ($featured->isNotEmpty() && $section('featured')->isShown())
        <section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col justify-between gap-6 sm:flex-row sm:items-end" data-reveal>
                <x-section-heading :section="$section('featured')" />
                @if (filled($section('featured')->value('button_label')))
                    <a href="{{ route('properties.index') }}" class="btn btn-outline self-start sm:self-auto">{{ $section('featured')->value('button_label') }} <x-icon name="arrow-right" class="size-4" /></a>
                @endif
            </div>
            <div class="mt-12 grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($featured as $property)
                    <x-property-card :property="$property" data-reveal data-reveal-delay="{{ $loop->index * 100 }}" />
                @endforeach
            </div>
        </section>
    @endif

    <x-advert-slot :adverts="$adverts" at="featured:after" class="mt-20" />

    <x-advert-slot :adverts="$adverts" at="services:before" class="mt-20" />
    {{-- Services --}}
    @if ($section('services')->isShown())
    <section class="mx-auto mt-28 max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col justify-between gap-6 sm:flex-row sm:items-end" data-reveal>
            <x-section-heading :section="$section('services')" />
            @if (filled($section('services')->value('button_label')))
                <a href="{{ route('services') }}" class="btn btn-outline self-start sm:self-auto">{{ $section('services')->value('button_label') }} <x-icon name="arrow-right" class="size-4" /></a>
            @endif
        </div>
        <div class="mt-12 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach (\App\Enums\Service::cases() as $service)
                <a href="{{ $service->url() }}" class="glass-card group flex gap-4 p-6" data-reveal data-reveal-delay="{{ ($loop->index % 3) * 80 }}">
                    <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-ink-900 text-brass-300 transition group-hover:bg-brass-500 group-hover:text-white"><x-icon :name="$service->icon()" /></span>
                    <span>
                        <span class="text-xs font-bold text-brass-600">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="mt-0.5 block font-display text-lg leading-snug text-ink-900">{{ $service->label() }}</span>
                        <span class="mt-1 block text-sm text-ink-500">{{ $service->summary() }}</span>
                    </span>
                </a>
            @endforeach
        </div>
    </section>
    @endif
    <x-advert-slot :adverts="$adverts" at="services:after" class="mt-20" />

    <x-advert-slot :adverts="$adverts" at="how_it_works:before" class="mt-20" />
    {{-- How it works --}}
    @if ($section('how_it_works')->isShown())
    @php($howItWorks = $section('how_it_works'))
    <section class="mx-auto mt-28 max-w-7xl px-4 sm:px-6 lg:px-8">
        <div data-palette="normal" class="relative overflow-hidden rounded-[2.5rem] bg-ink-950 px-6 py-16 text-white sm:px-12 lg:px-16 lg:py-20">
            <div class="pointer-events-none absolute -top-24 -right-24 size-96 rounded-full bg-brass-500/20 blur-3xl"></div>
            <div class="relative grid grid-cols-1 gap-14 lg:grid-cols-12">
                <div class="lg:col-span-4" data-reveal>
                    @if (filled($howItWorks->value('eyebrow')))
                        <p class="text-xs font-semibold tracking-[0.2em] text-brass-300 uppercase">{{ $howItWorks->value('eyebrow') }}</p>
                    @endif
                    <h2 class="mt-4 font-display text-4xl leading-tight tracking-tight">{{ $howItWorks->value('title') }} @if (filled($howItWorks->value('title_highlight')))<em class="text-brass-300">{{ $howItWorks->value('title_highlight') }}</em>@endif</h2>
                    @if (filled($howItWorks->value('description')))
                        <p class="mt-5 text-ink-300">{{ $howItWorks->value('description') }}</p>
                    @endif
                </div>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-3 lg:col-span-8">
                    @foreach ($howItWorks->list('steps') as ['icon' => $icon, 'title' => $heading, 'text' => $copy])
                        <div class="rounded-3xl bg-white/5 p-6 ring-1 ring-white/10 backdrop-blur transition hover:bg-white/10" data-reveal data-reveal-delay="{{ $loop->index * 120 }}">
                            <div class="flex items-center justify-between">
                                <span class="grid size-12 place-items-center rounded-2xl bg-brass-500/15 text-brass-300"><x-icon :name="$icon" /></span>
                                <span class="font-display text-3xl text-white/15">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            </div>
                            <h3 class="mt-6 font-display text-xl">{{ $heading }}</h3>
                            @if (filled($copy))
                                <p class="mt-2 text-sm leading-relaxed text-ink-300">{{ $copy }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    @endif

    <x-advert-slot :adverts="$adverts" at="how_it_works:after" class="mt-20" />

    <x-advert-slot :adverts="$adverts" at="guarantee:before" class="mt-20" />
    {{-- Guarantee --}}
    @if ($section('guarantee')->isShown())
    @php($guarantee = $section('guarantee'))
    <section class="mx-auto mt-28 max-w-7xl px-4 sm:px-6 lg:px-8">
        <div data-palette="normal" class="relative isolate overflow-hidden rounded-[2.5rem] bg-ink-950 px-6 py-16 text-white sm:px-12 lg:px-16 lg:py-20">
            <div class="pointer-events-none absolute -bottom-32 -left-24 -z-10 size-96 rounded-full bg-brass-500/20 blur-3xl"></div>
            <div class="pointer-events-none absolute -top-24 right-10 -z-10 size-80 rounded-full bg-ink-500/30 blur-3xl"></div>
            <div class="grid grid-cols-1 gap-12 lg:grid-cols-12">
                <div class="lg:col-span-4" data-reveal>
                    @if (filled($guarantee->value('eyebrow')))
                        <p class="text-xs font-semibold tracking-[0.2em] text-brass-300 uppercase">{{ $guarantee->value('eyebrow') }}</p>
                    @endif
                    <h2 class="mt-4 font-display text-4xl leading-tight tracking-tight">{{ $guarantee->value('title') }} @if (filled($guarantee->value('title_highlight')))<em class="text-brass-300">{{ $guarantee->value('title_highlight') }}</em>@endif</h2>
                    @if (filled($guarantee->value('description')))
                        <p class="mt-5 text-ink-300">{{ $guarantee->value('description') }}</p>
                    @endif
                    @if (filled($guarantee->value('note')))
                        <p class="mt-8 text-xs text-ink-400">{{ $guarantee->value('note') }}</p>
                    @endif
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:col-span-8">
                    @foreach ($guarantee->list('promises') as ['icon' => $icon, 'title' => $promise])
                        <div class="glass-dark flex items-center gap-4 rounded-2xl p-5 transition hover:bg-white/10" data-reveal data-reveal-delay="{{ ($loop->index % 2) * 100 }}">
                            <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-brass-500/15 text-brass-300"><x-icon :name="$icon" /></span>
                            <span>
                                <span class="block text-xs font-bold text-brass-300">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <span class="block font-medium">{{ $promise }}</span>
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    @endif
    <x-advert-slot :adverts="$adverts" at="guarantee:after" class="mt-20" />

    <x-advert-slot :adverts="$adverts" at="investments:before" class="mt-20" />
    {{-- Investment opportunities --}}
    @if ($section('investments')->isShown())
    <section class="mx-auto mt-28 max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col justify-between gap-6 sm:flex-row sm:items-end" data-reveal>
            <x-section-heading :section="$section('investments')" />
            @if (filled($section('investments')->value('button_label')))
                <a href="{{ route('investments.index') }}" class="btn btn-primary self-start sm:self-auto">{{ $section('investments')->value('button_label') }} <x-icon name="arrow-right" class="size-4" /></a>
            @endif
        </div>
        <div class="no-scrollbar mt-10 flex gap-3 overflow-x-auto pb-1 lg:grid lg:grid-cols-5" data-reveal>
            @foreach ([
                [\App\Enums\InvestmentCategory::StrategicLandAcquisition, 'Strategic land acquisition', 'map-pin'],
                [\App\Enums\InvestmentCategory::LandBanking, 'Land banking', 'leaf'],
                [\App\Enums\InvestmentCategory::PropertyInvestment, 'Property investment opportunities', 'building'],
                [\App\Enums\InvestmentCategory::JointVenture, 'Joint venture opportunities', 'handshake'],
                [\App\Enums\InvestmentCategory::RentalIncome, 'Rental income opportunities', 'key'],
            ] as [$investmentCategory, $label, $icon])
                <a href="{{ $investmentCategory === \App\Enums\InvestmentCategory::LandBanking ? route('land-banking.index') : route('investments.index', ['category' => $investmentCategory->value]) }}" class="glass-card group flex min-w-56 items-center gap-3 p-5 lg:min-w-0">
                    <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-brass-50 text-brass-600 ring-1 ring-brass-100"><x-icon :name="$icon" class="size-5" /></span>
                    <span class="text-sm leading-snug font-semibold text-ink-900">{{ $label }}</span>
                </a>
            @endforeach
        </div>
        @if ($opportunities->isNotEmpty())
            <div class="mt-8 grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($opportunities as $opportunity)
                    <x-opportunity-card :opportunity="$opportunity" data-reveal data-reveal-delay="{{ $loop->index * 100 }}" />
                @endforeach
            </div>
        @endif
        @if (filled($section('investments')->value('disclaimer')))
            <x-disclaimer class="mt-8" data-reveal>{{ $section('investments')->value('disclaimer') }}</x-disclaimer>
        @endif
    </section>
    @endif
    <x-advert-slot :adverts="$adverts" at="investments:after" class="mt-20" />

    <x-advert-slot :adverts="$adverts" at="latest:before" class="mt-20" />
    {{-- Latest --}}
    @if ($section('latest')->isShown())
    <section class="mx-auto mt-28 max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col justify-between gap-6 sm:flex-row sm:items-end" data-reveal>
            <x-section-heading :section="$section('latest')" />
            <div class="flex gap-2">
                <a href="{{ route('properties.index', ['listing' => 'sale']) }}" class="btn btn-outline">For sale</a>
                <a href="{{ route('properties.index', ['listing' => 'rent']) }}" class="btn btn-outline">For rent</a>
            </div>
        </div>

        @if ($latest->isEmpty())
            <x-empty-state class="mt-12" icon="home" :title="$section('latest')->value('empty_title') ?: 'New listings are on the way'" :description="$section('latest')->value('empty_text')" />
        @else
            <div class="mt-12 grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($latest as $property)
                    <x-property-card :property="$property" data-reveal data-reveal-delay="{{ ($loop->index % 3) * 100 }}" />
                @endforeach
            </div>
        @endif
    </section>
    @endif

    <x-advert-slot :adverts="$adverts" at="latest:after" class="mt-20" />

    <x-advert-slot :adverts="$adverts" at="insights:before" class="mt-20" />
    {{-- Insights --}}
    @if ($articles->isNotEmpty() && $section('insights')->isShown())
        <section class="mx-auto mt-28 max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col justify-between gap-6 sm:flex-row sm:items-end" data-reveal>
                <x-section-heading :section="$section('insights')" />
                @if (filled($section('insights')->value('button_label')))
                    <a href="{{ route('insights.index') }}" class="btn btn-outline self-start sm:self-auto">{{ $section('insights')->value('button_label') }} <x-icon name="arrow-right" class="size-4" /></a>
                @endif
            </div>
            <div class="mt-12 grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($articles as $article)
                    <x-article-card :article="$article" data-reveal data-reveal-delay="{{ $loop->index * 100 }}" />
                @endforeach
            </div>
        </section>
    @endif
    <x-advert-slot :adverts="$adverts" at="insights:after" class="mt-20" />

    <x-advert-slot :adverts="$adverts" at="cta:before" class="mt-20" />
    @if ($section('cta')->isShown())
        <x-cta-band class="mt-28" :title="$section('cta')->value('title')" :description="$section('cta')->value('description')" />
    @endif
    <x-advert-slot :adverts="$adverts" at="cta:after" class="mt-20" />

    <x-advert-slot :adverts="$adverts" at="signup:before" class="mt-20" />
    {{-- CTA --}}
    @if (auth()->guest() && $section('signup')->isShown())
        <section class="mx-auto mt-28 max-w-7xl px-4 sm:px-6 lg:px-8" data-reveal>
            <div class="glass-strong grid grid-cols-1 items-center gap-10 rounded-[2.5rem] p-10 sm:p-14 lg:grid-cols-2">
                <div>
                    <h2 class="font-display text-4xl tracking-tight text-ink-950">{{ $section('signup')->value('title') }}</h2>
                    @if (filled($section('signup')->value('description')))
                        <p class="mt-4 max-w-md text-ink-600">{{ $section('signup')->value('description') }}</p>
                    @endif
                </div>
                <div class="flex flex-wrap gap-3 lg:justify-end">
                    <a href="{{ route('register') }}" class="btn btn-primary btn-lg">Create free account <x-icon name="arrow-right" class="size-4" /></a>
                    <a href="{{ route('login') }}" class="btn btn-outline btn-lg">Sign in</a>
                </div>
            </div>
        </section>
    @endif

    <x-advert-slot :adverts="$adverts" at="signup:after" class="mt-20" />
</x-layouts.app>
