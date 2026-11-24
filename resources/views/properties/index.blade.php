@php
    $listing = $filters['listing'] ?? null;
    $heading = \App\Enums\ListingType::tryFrom((string) $listing)?->heading() ?? $content['header']->value('title');
    $controlClasses = 'rounded-2xl border-0 bg-white/70 px-4 py-3 text-sm text-ink-800 ring-1 ring-white/80 focus:bg-white focus:ring-brass-300 focus:outline-none';
    $activeFilters = collect($filters)->filter(fn ($value) => filled($value))->except('listing')->count();
@endphp

<x-layouts.app :title="$content['meta']->value('title') ?: $heading" :description="$content['meta']->value('description')">
    <section class="mx-auto max-w-7xl px-4 pt-10 sm:px-6 lg:px-8">
        <x-advert-slot :adverts="$adverts" at="header:before" :contained="false" class="mb-10" />

        <div class="flex flex-col justify-between gap-6 lg:flex-row lg:items-end">
            <div>
                @if (filled($content['header']->value('eyebrow')))
                    <p class="eyebrow animate-fade-up">{{ $content['header']->value('eyebrow') }}</p>
                @endif
                <h1 class="mt-3 animate-fade-up font-display text-5xl tracking-tight text-ink-950 [animation-delay:80ms]">{{ $heading }}</h1>
                <p class="mt-3 animate-fade-up text-ink-500 [animation-delay:140ms]">{{ $properties->total() }} {{ str('property')->plural($properties->total()) }} matching your search</p>
            </div>

            <div class="glass no-scrollbar flex max-w-full animate-fade-up gap-1 self-start overflow-x-auto rounded-full p-1 [animation-delay:200ms] lg:self-auto">
                @foreach (['' => 'All', ...collect(\App\Enums\ListingType::cases())->mapWithKeys(fn ($type) => [$type->value => $type->label()])->all()] as $value => $label)
                    <a href="{{ route('properties.index', array_filter([...$filters, 'listing' => $value, 'sort' => $sort])) }}" @class([
                        'rounded-full px-5 py-2 text-sm font-medium whitespace-nowrap transition',
                        'bg-ink-900 text-white' => ($listing ?? '') === $value,
                        'text-ink-500 hover:text-ink-900' => ($listing ?? '') !== $value,
                    ])>{{ $label }}</a>
                @endforeach
            </div>
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('properties.index') }}" class="glass-strong mt-10 animate-fade-up rounded-3xl p-3 [animation-delay:260ms]">
            @if ($listing)
                <input type="hidden" name="listing" value="{{ $listing }}">
            @endif
            <div class="grid grid-cols-1 gap-2 md:grid-cols-2 lg:grid-cols-[1.4fr_1fr_1fr_1fr_auto]">
                <label class="flex items-center gap-3 rounded-2xl bg-white/70 px-4 py-3 ring-1 ring-white/80 focus-within:bg-white focus-within:ring-brass-300">
                    <x-icon name="search" class="size-4 text-ink-300" />
                    <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Area, street or name" aria-label="Search" class="w-full bg-transparent text-sm focus:outline-none">
                </label>
                <select name="state" aria-label="State" class="{{ $controlClasses }}">
                    <option value="">All states</option>
                    @foreach ($states as $state)
                        <option value="{{ $state }}" @selected(($filters['state'] ?? '') === $state)>{{ $state }}</option>
                    @endforeach
                </select>
                <select name="city" aria-label="City or location" class="{{ $controlClasses }}">
                    <option value="">Any city</option>
                    @foreach ($cities as $city)
                        <option value="{{ $city }}" @selected(($filters['city'] ?? '') === $city)>{{ $city }}</option>
                    @endforeach
                </select>
                <select name="type" aria-label="Property category" class="{{ $controlClasses }}">
                    <option value="">All categories</option>
                    @foreach (\App\Enums\PropertyType::cases() as $type)
                        <option value="{{ $type->value }}" @selected(($filters['type'] ?? '') === $type->value)>{{ $type->label() }}</option>
                    @endforeach
                </select>
                <button class="btn btn-primary rounded-2xl py-3"><x-icon name="sliders" class="size-4" /> Apply</button>
            </div>

            <div class="mt-2 grid grid-cols-2 gap-2 md:grid-cols-4">
                <select name="beds" aria-label="Bedrooms" class="{{ $controlClasses }}">
                    <option value="">Any bedrooms</option>
                    @foreach ([1, 2, 3, 4, 5] as $beds)
                        <option value="{{ $beds }}" @selected((string) ($filters['beds'] ?? '') === (string) $beds)>{{ $beds }}+ bedrooms</option>
                    @endforeach
                </select>
                <select name="size" aria-label="Property size" class="{{ $controlClasses }}">
                    <option value="">Any size</option>
                    @foreach (\App\Models\Property::sizeRanges() as $value => $range)
                        <option value="{{ $value }}" @selected(($filters['size'] ?? '') === $value)>{{ $range['label'] }}</option>
                    @endforeach
                </select>
                <select name="availability" aria-label="Availability" class="{{ $controlClasses }}">
                    <option value="">Any availability</option>
                    @foreach (\App\Enums\PropertyStatus::publicCases() as $status)
                        <option value="{{ $status->value }}" @selected(($filters['availability'] ?? '') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
                <div class="flex items-center gap-2 rounded-2xl bg-white/70 px-4 py-2 ring-1 ring-white/80">
                    <span class="text-sm text-ink-300">{{ config('listings.currency') }}</span>
                    <input type="number" name="min_price" value="{{ $filters['min_price'] ?? '' }}" placeholder="Min" min="0" aria-label="Minimum price" class="w-full min-w-0 bg-transparent text-sm focus:outline-none">
                    <span class="text-ink-300">–</span>
                    <input type="number" name="max_price" value="{{ $filters['max_price'] ?? '' }}" placeholder="Max" min="0" aria-label="Maximum price" class="w-full min-w-0 bg-transparent text-sm focus:outline-none">
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 px-2 pt-3">
                <div class="flex flex-wrap items-center gap-4 text-sm text-ink-600">
                    <label class="inline-flex cursor-pointer items-center gap-2">
                        <input type="checkbox" name="featured" value="1" class="size-4 accent-brass-500" @checked(filled($filters['featured'] ?? null)) onchange="this.form.requestSubmit()">
                        Featured listings only
                    </label>
                    <label class="inline-flex cursor-pointer items-center gap-2">
                        <input type="checkbox" name="verified" value="1" class="size-4 accent-brass-500" @checked(filled($filters['verified'] ?? null)) onchange="this.form.requestSubmit()">
                        Verified listings only
                    </label>
                    @if ($activeFilters > 0)
                        <a href="{{ route('properties.index', array_filter(['listing' => $listing])) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-brass-600 hover:text-brass-700"><x-icon name="x" class="size-3.5" /> Clear filters</a>
                    @endif
                </div>
                <label class="flex items-center gap-2 text-sm text-ink-400">
                    Sort by
                    <select name="sort" onchange="this.form.requestSubmit()" class="rounded-full border-0 bg-white/70 px-4 py-1.5 text-sm text-ink-800 ring-1 ring-white/80 focus:ring-brass-300 focus:outline-none">
                        <option value="">Newest listings</option>
                        <option value="price_asc" @selected($sort === 'price_asc')>Lowest price</option>
                        <option value="price_desc" @selected($sort === 'price_desc')>Highest price</option>
                    </select>
                </label>
            </div>
        </form>

        <x-advert-slot :adverts="$adverts" at="header:after" :contained="false" class="mt-10" />
        <x-advert-slot :adverts="$adverts" at="grid:before" :contained="false" class="mt-10" />

        @if ($properties->isEmpty())
            <x-empty-state class="mt-12" icon="search" title="No properties match your search" description="Try widening your filters or browsing all listings.">
                <a href="{{ route('properties.index') }}" class="btn btn-primary">View all properties</a>
            </x-empty-state>
        @else
            <div class="mt-12 grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                @php($shownGridAdverts = 0)
                @foreach ($properties as $property)
                    <x-property-card :property="$property" data-reveal data-reveal-delay="{{ ($loop->index % 3) * 90 }}" />
                    {{-- "Inside" adverts sit after every 3rd property (and at the end of a short page). --}}
                    @while (($loop->iteration % 3 === 0 || $loop->last) && $shownGridAdverts < min($gridAdverts->count(), $loop->last ? $gridAdverts->count() : intdiv($loop->iteration, 3)))
                        @php($gridAdvert = $gridAdverts[$shownGridAdverts])
                        @php($shownGridAdverts++)
                        <x-advert :advert="$gridAdvert" data-reveal data-reveal-delay="180" />
                    @endwhile
                @endforeach
            </div>

            <div class="mt-14">
                {{ $properties->links('pagination') }}
            </div>
        @endif

        <x-advert-slot :adverts="$adverts" at="grid:after" :contained="false" class="mt-14" />
    </section>
</x-layouts.app>
