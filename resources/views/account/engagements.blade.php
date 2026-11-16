<x-layouts.client title="My engagements" subtitle="Every property you've engaged with and how it's progressing.">
    <x-slot:actions>
        <a href="{{ route('properties.index') }}" class="btn btn-primary hidden sm:inline-flex"><x-icon name="search" class="size-4" /> Find more</a>
    </x-slot:actions>

    <div class="flex animate-fade-up gap-2">
        @foreach (['all' => 'All', 'open' => 'In progress', 'completed' => 'Completed'] as $key => $label)
            <a href="{{ route('account.engagements.index', $key === 'all' ? [] : ['filter' => $key]) }}" @class([
                'inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-medium transition',
                'bg-ink-900 text-white' => $filter === $key,
                'bg-white text-ink-600 ring-1 ring-ink-100 hover:text-ink-900' => $filter !== $key,
            ])>{{ $label }} <span class="text-xs opacity-60">{{ $counts[$key] }}</span></a>
        @endforeach
    </div>

    @if ($engagements->isEmpty())
        <x-empty-state class="mt-8" icon="home" :title="$counts['all'] === 0 ? 'You haven\'t engaged with any property yet' : 'Nothing in this list'" :description="$counts['all'] === 0 ? 'When you find a home you love, click “Engage now” and you\'ll be able to follow its progress right here.' : 'Try another tab to see the rest of your engagements.'">
            <a href="{{ route('properties.index') }}" class="btn btn-accent">Browse properties</a>
        </x-empty-state>
    @else
        <div class="mt-6 space-y-5">
            @foreach ($engagements as $engagement)
                <a href="{{ route('account.engagements.show', $engagement) }}" class="group grid grid-cols-1 gap-6 rounded-3xl bg-white p-4 shadow-soft ring-1 ring-ink-100 transition duration-300 hover:-translate-y-0.5 hover:shadow-lift sm:p-5 md:grid-cols-[14rem_1fr]" data-reveal data-reveal-delay="{{ min($loop->index, 4) * 70 }}">
                    <x-property-image :src="$engagement->property->cover_url" :alt="$engagement->property->title" class="aspect-[4/3] rounded-2xl md:aspect-auto md:h-full" />
                    <div class="flex min-w-0 flex-col gap-5 py-1 md:pr-3">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-xs text-ink-400">{{ $engagement->property->listing_type->label() }} · {{ $engagement->property->location }}</p>
                                <h2 class="mt-1 font-display text-2xl text-ink-900">{{ $engagement->property->title }}</h2>
                                <p class="mt-1 text-sm font-semibold text-ink-700">{{ $engagement->property->formatted_price }}</p>
                            </div>
                            <x-status-badge :status="$engagement->status" />
                        </div>
                        <x-progress-tracker :status="$engagement->status" />
                        <div class="flex items-center justify-between border-t border-ink-100 pt-4 text-xs text-ink-400">
                            <span class="inline-flex items-center gap-1.5"><x-icon name="clock" class="size-3.5" />
                                @if ($latest = $engagement->updates->first())
                                    Last update {{ $latest->created_at->diffForHumans() }}
                                @else
                                    Engaged {{ $engagement->created_at->diffForHumans() }}
                                @endif
                            </span>
                            <span class="inline-flex items-center gap-1 font-semibold text-ink-700 transition group-hover:gap-2">View details <x-icon name="arrow-right" class="size-3.5" /></span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</x-layouts.client>
