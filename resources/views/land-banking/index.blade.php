@php($controlClasses = 'field-input appearance-none bg-white/70 ring-white/80')

<x-layouts.app :title="$content['meta']->value('title')" :description="$content['meta']->value('description')">
    <x-advert-slot :adverts="$adverts" at="hero:before" class="mt-6" />
    <x-page-hero :section="$content['hero']" />
    <x-advert-slot :adverts="$adverts" at="hero:after" class="mt-12" />

    <x-advert-slot :adverts="$adverts" at="why:before" class="mt-20" />
    {{-- Why land banking --}}
    @if ($content['why']->isShown())
    <section class="mx-auto mt-20 max-w-7xl px-4 sm:px-6 lg:px-8">
        <x-section-heading :section="$content['why']" size="md" data-reveal />
        <div class="mt-10 grid grid-cols-1 gap-5 md:grid-cols-3">
            @foreach ($content['why']->list('reasons') as ['icon' => $icon, 'title' => $heading, 'text' => $copy])
                <div class="glass rounded-3xl p-7" data-reveal data-reveal-delay="{{ $loop->index * 100 }}">
                    <span class="grid size-12 place-items-center rounded-2xl bg-brass-50 text-brass-600 ring-1 ring-brass-100"><x-icon :name="$icon" /></span>
                    <h3 class="mt-5 font-display text-xl text-ink-900">{{ $heading }}</h3>
                    @if (filled($copy))
                        <p class="mt-2 text-sm leading-relaxed text-ink-500">{{ $copy }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </section>
    @endif
    <x-advert-slot :adverts="$adverts" at="why:after" class="mt-16" />

    <x-advert-slot :adverts="$adverts" at="opportunities:before" class="mt-20" />
    {{-- Filters + results --}}
    <section id="land-opportunities" class="mx-auto mt-20 max-w-7xl scroll-mt-28 px-4 sm:px-6 lg:px-8">
        <form method="GET" action="{{ route('land-banking.index') }}#land-opportunities" class="glass-strong grid grid-cols-1 items-end gap-3 rounded-3xl p-4 sm:grid-cols-2 lg:grid-cols-[repeat(4,1fr)_auto]">
            <div>
                <label for="land-state" class="field-label">State</label>
                <select name="state" id="land-state" class="{{ $controlClasses }}">
                    <option value="">All states</option>
                    @foreach ($states as $state)
                        <option value="{{ $state }}" @selected(($filters['state'] ?? '') === $state)>{{ $state }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="land-location" class="field-label">City or location</label>
                <input type="text" name="location" id="land-location" value="{{ $filters['location'] ?? '' }}" placeholder="e.g. Delta State" class="field-input bg-white/70 ring-white/80">
            </div>
            <div>
                <label for="land-size" class="field-label">Land size</label>
                <select name="size" id="land-size" class="{{ $controlClasses }}">
                    <option value="">Any size</option>
                    @foreach (\App\Models\InvestmentOpportunity::landSizeRanges() as $value => $range)
                        <option value="{{ $value }}" @selected(($filters['size'] ?? '') === $value)>{{ $range['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="land-price" class="field-label">Price range</label>
                <select name="price" id="land-price" class="{{ $controlClasses }}">
                    <option value="">Any price</option>
                    @foreach (\App\Models\InvestmentOpportunity::priceRanges() as $value => $range)
                        <option value="{{ $value }}" @selected(($filters['price'] ?? '') === $value)>{{ $range['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <button class="btn btn-primary rounded-xl py-3"><x-icon name="sliders" class="size-4" /> Filter</button>
        </form>

        @if ($content['disclaimer']->isShown())
            <x-disclaimer class="mt-6"><span class="font-semibold text-ink-900">Please note:</span> {{ $content['disclaimer']->value('text') }}</x-disclaimer>
        @endif

        @if ($opportunities->isEmpty())
            <x-empty-state class="mt-10" icon="leaf" title="No land opportunities match" description="Try widening your filters, or ask an adviser about land that is not yet listed.">
                <a href="{{ route('land-banking.index') }}" class="btn btn-primary">Clear filters</a>
            </x-empty-state>
        @else
            <div class="mt-10 grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($opportunities as $opportunity)
                    <x-opportunity-card :opportunity="$opportunity" data-reveal data-reveal-delay="{{ ($loop->index % 3) * 90 }}" />
                @endforeach
            </div>
            <div class="mt-12">{{ $opportunities->links('pagination') }}</div>
        @endif
    </section>
    <x-advert-slot :adverts="$adverts" at="opportunities:after" class="mt-16" />

    <x-advert-slot :adverts="$adverts" at="checklist:before" class="mt-20" />
    {{-- Know the land + enquiry --}}
    @if ($content['checklist']->isShown())
    @php($checklist = $content['checklist'])
    <section class="mx-auto mt-24 grid max-w-7xl grid-cols-1 gap-12 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
        <div data-reveal>
            <x-section-heading :section="$checklist" size="md" />
            <ul class="mt-8 divide-y divide-ink-100/80 border-y border-ink-100/80">
                @foreach ($checklist->list('items') as $item)
                    <li class="flex items-center gap-3 py-3.5 text-ink-700"><span class="grid size-6 place-items-center rounded-full bg-brass-50 text-brass-600"><x-icon name="check" class="size-3.5" stroke="2.5" /></span>{{ $item }}</li>
                @endforeach
            </ul>
        </div>
        <x-enquiry-form :type="\App\Models\EnquiryType::LandBanking" :title="$checklist->value('form_title')" :description="$checklist->value('form_description')" :opportunities="$openOpportunities" whatsapp-text="Hello, I'd like to know more about your land banking opportunities." data-reveal />
    </section>
    @endif
    <x-advert-slot :adverts="$adverts" at="checklist:after" class="mt-16" />

    <x-advert-slot :adverts="$adverts" at="cta:before" class="mt-20" />
    @if ($content['cta']->isShown())
        <x-cta-band class="mt-24" :title="$content['cta']->value('title')" :description="$content['cta']->value('description')" />
    @endif
    <x-advert-slot :adverts="$adverts" at="cta:after" class="mt-16" />
</x-layouts.app>
