@props(['property'])

<article {{ $attributes->merge(['class' => 'group relative flex flex-col overflow-hidden rounded-3xl bg-white shadow-soft ring-1 ring-ink-100 transition duration-500 hover:-translate-y-1 hover:shadow-lift']) }}>
    <x-property-image :src="$property->cover_url" :alt="$property->title" class="aspect-[4/3]">
        <div class="absolute inset-x-0 top-0 flex items-start justify-between p-4">
            <span @class([
                'rounded-full px-3 py-1 text-[11px] font-semibold tracking-wider uppercase backdrop-blur-md',
                'bg-ink-900/80 text-white' => $property->listing_type === \App\Enums\ListingType::Sale,
                'bg-white/90 text-ink-900' => $property->listing_type === \App\Enums\ListingType::Rent,
            ])>{{ $property->listing_type->label() }}</span>

            @if ($property->status !== \App\Enums\PropertyStatus::Published)
                <x-status-badge :status="$property->status" class="bg-white/95 backdrop-blur" />
            @elseif ($property->is_featured)
                <span class="inline-flex items-center gap-1 rounded-full bg-brass-500/95 px-2.5 py-1 text-[11px] font-semibold text-white backdrop-blur">
                    <x-icon name="star" class="size-3" /> Featured
                </span>
            @endif
        </div>
        <div class="absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-ink-950/50 to-transparent opacity-0 transition duration-500 group-hover:opacity-100"></div>
    </x-property-image>

    <div class="flex flex-1 flex-col gap-3 p-5 sm:p-6">
        <div class="flex items-center gap-1.5 text-xs text-ink-400">
            <x-icon name="map-pin" class="size-3.5" />
            <span class="truncate">{{ $property->location }}</span>
            <span class="text-ink-200">•</span>
            <span>{{ $property->property_type->label() }}</span>
        </div>

        <h3 class="font-display text-xl leading-snug text-ink-900">
            <a href="{{ route('properties.show', $property) }}" class="after:absolute after:inset-0">{{ $property->title }}</a>
        </h3>

        <div class="mt-auto flex items-center gap-4 text-sm text-ink-500">
            @if ($property->bedrooms)
                <span class="inline-flex items-center gap-1.5"><x-icon name="bed" class="size-4 text-ink-300" />{{ $property->bedrooms }}</span>
            @endif
            @if ($property->bathrooms)
                <span class="inline-flex items-center gap-1.5"><x-icon name="bath" class="size-4 text-ink-300" />{{ $property->bathrooms }}</span>
            @endif
            @if ($property->area_sqm)
                <span class="inline-flex items-center gap-1.5"><x-icon name="area" class="size-4 text-ink-300" />{{ number_format($property->area_sqm) }} m²</span>
            @endif
        </div>

        <div class="flex items-center justify-between border-t border-ink-100 pt-4">
            <p class="text-lg font-semibold tracking-tight text-ink-900">{{ $property->formatted_price }}</p>
            <span class="grid size-9 place-items-center rounded-full bg-ink-50 text-ink-500 transition duration-300 group-hover:bg-ink-900 group-hover:text-white">
                <x-icon name="arrow-right" class="size-4" />
            </span>
        </div>
    </div>
</article>
