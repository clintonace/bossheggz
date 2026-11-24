@php
    $money = fn (?int $amount) => \App\Models\InvestmentOpportunity::money($amount) ?? 'On request';
    $isLand = $opportunity->isLand();
    $backUrl = $isLand ? route('land-banking.index') : route('investments.index');
    $mapQuery = $opportunity->full_location;
@endphp

<x-layouts.app :title="$opportunity->title" :description="$opportunity->summary">
    <section class="mx-auto max-w-7xl px-4 pt-8 sm:px-6 lg:px-8">
        <nav class="flex animate-fade-up items-center gap-2 text-sm text-ink-400" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="shrink-0 hover:text-ink-900">Home</a>
            <x-icon name="chevron-right" class="size-3.5" />
            <a href="{{ $backUrl }}" class="shrink-0 whitespace-nowrap hover:text-ink-900">{{ $isLand ? 'Land Banking' : 'Investment Opportunities' }}</a>
            <x-icon name="chevron-right" class="size-3.5" />
            <span class="truncate text-ink-700">{{ $opportunity->title }}</span>
        </nav>

        <div class="mt-6 grid grid-cols-1 gap-10 lg:grid-cols-12">
            <div class="lg:col-span-7 xl:col-span-8">
                <div class="flex animate-fade-up flex-wrap items-center gap-2">
                    <span class="rounded-full bg-ink-900 px-3 py-1 text-[11px] font-semibold tracking-wider text-white uppercase">{{ $opportunity->category->label() }}</span>
                    @if ($isLand && $opportunity->land_use)
                        <span class="rounded-full bg-white/70 px-3 py-1 text-[11px] font-semibold tracking-wider text-ink-600 uppercase ring-1 ring-ink-100">{{ $opportunity->land_use->label() }}</span>
                    @endif
                    <x-status-badge :status="$opportunity->status" />
                    <x-status-badge :status="$opportunity->verification_status" />
                </div>
                <h1 class="mt-4 animate-fade-up font-display text-4xl leading-tight tracking-tight text-ink-950 [animation-delay:60ms] sm:text-5xl">{{ $opportunity->title }}</h1>
                <p class="mt-3 flex animate-fade-up items-center gap-2 text-ink-500 [animation-delay:100ms]"><x-icon name="map-pin" class="size-4 text-brass-500" /> {{ $opportunity->full_location }}</p>

                <x-property-image :src="$opportunity->image_url" :alt="$opportunity->title" class="mt-8 aspect-[16/9] animate-fade-up rounded-[2rem] shadow-lift [animation-delay:160ms]" />

                {{-- Key figures --}}
                <dl class="mt-8 grid grid-cols-2 gap-4 lg:grid-cols-4" data-reveal>
                    @foreach (array_filter($isLand ? [
                        ['Acquisition price', $money($opportunity->acquisition_price)],
                        ['Land sizes', $opportunity->land_sizes],
                        ['Documentation', $opportunity->documentation],
                        ['Access', $opportunity->access],
                    ] : [
                        ['Required investment', $money($opportunity->required_investment)],
                        ['Minimum participation', $money($opportunity->minimum_participation)],
                        ['Timeline', $opportunity->timeline],
                        ['Documentation', $opportunity->documentation],
                    ], fn ($item) => filled($item[1])) as [$label, $value])
                        <div class="glass rounded-2xl p-5">
                            <dt class="text-xs font-medium tracking-wider text-ink-400 uppercase">{{ $label }}</dt>
                            <dd class="mt-1.5 font-display text-xl leading-snug text-ink-900">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>

                <div class="mt-12" data-reveal>
                    <h2 class="font-display text-3xl text-ink-950">About this opportunity</h2>
                    <p class="mt-4 text-lg leading-relaxed text-ink-700">{{ $opportunity->summary }}</p>
                    <div class="prose-article mt-5">
                        @foreach (preg_split('/\R{2,}/', trim($opportunity->description)) as $paragraph)
                            <p>{!! nl2br(e($paragraph)) !!}</p>
                        @endforeach
                    </div>
                </div>

                @if ($isLand)
                    <div class="mt-12 grid grid-cols-1 gap-5 sm:grid-cols-2" data-reveal>
                        @if ($opportunity->estate_status)
                            <div class="glass rounded-3xl p-6">
                                <x-icon name="building" class="size-5 text-brass-500" />
                                <h3 class="mt-3 font-display text-xl text-ink-900">Estate / development</h3>
                                <p class="mt-1.5 text-sm text-ink-600">{{ $opportunity->estate_status }}</p>
                            </div>
                        @endif
                        @if ($opportunity->rationale)
                            <div class="glass rounded-3xl p-6">
                                <x-icon name="trending" class="size-5 text-brass-500" />
                                <h3 class="mt-3 font-display text-xl text-ink-900">Investment rationale</h3>
                                <p class="mt-1.5 text-sm text-ink-600">{{ $opportunity->rationale }}</p>
                            </div>
                        @endif
                    </div>
                @endif

                @if ($opportunity->structure)
                    <div class="mt-12" data-reveal>
                        <h2 class="font-display text-3xl text-ink-950">Investment structure</h2>
                        <div class="prose-article mt-4">
                            @foreach (preg_split('/\R{2,}/', trim($opportunity->structure)) as $paragraph)
                                <p>{!! nl2br(e($paragraph)) !!}</p>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($opportunity->risks)
                    <div class="mt-12 rounded-3xl border border-amber-200/70 bg-amber-50/70 p-6 backdrop-blur sm:p-8" data-reveal>
                        <h2 class="flex items-center gap-2 font-display text-2xl text-amber-900"><x-icon name="alert" class="size-5" /> Relevant risks</h2>
                        <div class="mt-3 space-y-3 text-sm leading-relaxed text-amber-900/90">
                            @foreach (preg_split('/\R{2,}/', trim($opportunity->risks)) as $paragraph)
                                <p>{!! nl2br(e($paragraph)) !!}</p>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="mt-12" data-reveal>
                    <h2 class="font-display text-3xl text-ink-950">Location and accessibility</h2>
                    <div class="glass mt-5 overflow-hidden rounded-3xl p-2">
                        <iframe title="Map of {{ $mapQuery }}" src="https://www.google.com/maps?q={{ urlencode($mapQuery) }}&output=embed" class="h-72 w-full rounded-[1.25rem] border-0" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>

                <x-disclaimer class="mt-10">Any figures shown are projections, not guaranteed returns. Review the documentation and risks, and speak to an adviser before making any commitment.</x-disclaimer>
            </div>

            <aside class="lg:col-span-5 xl:col-span-4">
                <div class="sticky top-24 space-y-5">
                    @if ($opportunity->acceptsEnquiries())
                        <x-enquiry-form :type="$isLand ? \App\Models\EnquiryType::LandBanking : \App\Models\EnquiryType::Investment" title="Express Interest" :description="'Interested in '.$opportunity->title.'? An adviser will reach out with full details.'" :opportunities="collect([$opportunity])" :selected="$opportunity->slug" :whatsapp-text="'Hello, I am interested in '.$opportunity->title.' ('.$opportunity->url().')'" />
                    @else
                        <div class="glass-strong rounded-[2rem] p-7">
                            <p class="font-display text-2xl text-ink-900">This opportunity is closed</p>
                            <p class="mt-2 text-sm text-ink-500">It is no longer open to new participants, but similar opportunities may be available.</p>
                            <a href="{{ $backUrl }}" class="btn btn-primary mt-5 w-full">See open opportunities</a>
                            <x-whatsapp-button class="btn-outline mt-3 w-full" text="Hello, I'd like to hear about upcoming opportunities." />
                        </div>
                    @endif
                </div>
            </aside>
        </div>
    </section>

    @if ($related->isNotEmpty())
        <section class="mx-auto mt-24 max-w-7xl px-4 sm:px-6 lg:px-8">
            <div data-reveal>
                <p class="eyebrow">More like this</p>
                <h2 class="mt-3 font-display text-4xl tracking-tight text-ink-950">Related opportunities</h2>
            </div>
            <div class="mt-10 grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($related as $item)
                    <x-opportunity-card :opportunity="$item" data-reveal data-reveal-delay="{{ $loop->index * 100 }}" />
                @endforeach
            </div>
        </section>
    @endif

    <x-cta-band class="mt-24" />
</x-layouts.app>
