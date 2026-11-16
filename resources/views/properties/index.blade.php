@php
    $listing = $filters['listing'] ?? null;
    $heading = match ($listing) {
        'sale' => 'Properties for sale',
        'rent' => 'Properties for rent',
        default => 'All properties',
    };
    $activeFilters = collect($filters)->filter(fn ($value) => filled($value))->except('listing')->count();
@endphp

<x-layouts.app :title="$heading">
    <section class="mx-auto max-w-7xl px-4 pt-10 sm:px-6 lg:px-8">
        <x-advert-slot :adverts="$adverts" at="header:before" :contained="false" class="mb-10" />

        <div class="flex flex-col justify-between gap-6 lg:flex-row lg:items-end">
            <div>
                <p class="eyebrow animate-fade-up">Browse listings</p>
                <h1 class="mt-3 animate-fade-up font-display text-5xl tracking-tight text-ink-950 [animation-delay:80ms]">{{ $heading }}</h1>
                <p class="mt-3 animate-fade-up text-ink-500 [animation-delay:140ms]">{{ $properties->total() }} {{ str('property')->plural($properties->total()) }} matching your search</p>
            </div>

            <div class="flex animate-fade-up gap-1 self-start rounded-full bg-white p-1 shadow-soft ring-1 ring-ink-100 [animation-delay:200ms] lg:self-auto">
                @foreach (['' => 'All', 'sale' => 'For sale', 'rent' => 'For rent'] as $value => $label)
                    <a href="{{ route('properties.index', array_filter([...$filters, 'listing' => $value, 'sort' => $sort])) }}" @class([
                        'rounded-full px-5 py-2 text-sm font-medium transition',
                        'bg-ink-900 text-white' => ($listing ?? '') === $value,
                        'text-ink-500 hover:text-ink-900' => ($listing ?? '') !== $value,
                    ])>{{ $label }}</a>
                @endforeach
            </div>
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('properties.index') }}" class="mt-10 animate-fade-up rounded-3xl bg-white p-3 shadow-soft ring-1 ring-ink-100 [animation-delay:260ms]">
            @if ($listing)
                <input type="hidden" name="listing" value="{{ $listing }}">
            @endif
            <div class="grid grid-cols-1 gap-2 md:grid-cols-2 lg:grid-cols-[1.5fr_1fr_1fr_1fr_auto]">
                <label class="flex items-center gap-3 rounded-2xl bg-ivory px-4 py-3 ring-1 ring-transparent focus-within:bg-white focus-within:ring-brass-300">
                    <x-icon name="search" class="size-4 text-ink-300" />
                    <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Area, street or name" class="w-full bg-transparent text-sm focus:outline-none">
                </label>
                <select name="city" class="rounded-2xl border-0 bg-ivory px-4 py-3 text-sm text-ink-800 ring-1 ring-transparent focus:bg-white focus:ring-brass-300 focus:outline-none">
                    <option value="">Any location</option>
                    @foreach ($cities as $city)
                        <option value="{{ $city }}" @selected(($filters['city'] ?? '') === $city)>{{ $city }}</option>
                    @endforeach
                </select>
                <select name="type" class="rounded-2xl border-0 bg-ivory px-4 py-3 text-sm text-ink-800 ring-1 ring-transparent focus:bg-white focus:ring-brass-300 focus:outline-none">
                    <option value="">Any type</option>
                    @foreach (\App\Enums\PropertyType::cases() as $type)
                        <option value="{{ $type->value }}" @selected(($filters['type'] ?? '') === $type->value)>{{ $type->label() }}</option>
                    @endforeach
                </select>
                <select name="beds" class="rounded-2xl border-0 bg-ivory px-4 py-3 text-sm text-ink-800 ring-1 ring-transparent focus:bg-white focus:ring-brass-300 focus:outline-none">
                    <option value="">Any bedrooms</option>
                    @foreach ([1, 2, 3, 4, 5] as $beds)
                        <option value="{{ $beds }}" @selected((string) ($filters['beds'] ?? '') === (string) $beds)>{{ $beds }}+ bedrooms</option>
                    @endforeach
                </select>
                <button class="btn btn-primary rounded-2xl py-3"><x-icon name="sliders" class="size-4" /> Apply</button>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 px-2 pt-3">
                <div class="flex flex-wrap items-center gap-2 text-sm">
                    <span class="text-ink-400">Price</span>
                    <input type="number" name="min_price" value="{{ $filters['min_price'] ?? '' }}" placeholder="Min" min="0" class="w-32 rounded-full border-0 bg-ivory px-4 py-1.5 text-sm ring-1 ring-ink-100 focus:ring-brass-300 focus:outline-none">
                    <span class="text-ink-300">–</span>
                    <input type="number" name="max_price" value="{{ $filters['max_price'] ?? '' }}" placeholder="Max" min="0" class="w-32 rounded-full border-0 bg-ivory px-4 py-1.5 text-sm ring-1 ring-ink-100 focus:ring-brass-300 focus:outline-none">
                    @if ($activeFilters > 0)
                        <a href="{{ route('properties.index', array_filter(['listing' => $listing])) }}" class="ml-2 inline-flex items-center gap-1 text-xs font-semibold text-brass-600 hover:text-brass-700"><x-icon name="x" class="size-3.5" /> Clear filters</a>
                    @endif
                </div>
                <label class="flex items-center gap-2 text-sm text-ink-400">
                    Sort
                    <select name="sort" onchange="this.form.requestSubmit()" class="rounded-full border-0 bg-ivory px-4 py-1.5 text-sm text-ink-800 ring-1 ring-ink-100 focus:ring-brass-300 focus:outline-none">
                        <option value="">Newest</option>
                        <option value="price_asc" @selected($sort === 'price_asc')>Price: low to high</option>
                        <option value="price_desc" @selected($sort === 'price_desc')>Price: high to low</option>
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
