<x-layouts.app :title="$content['meta']->value('title')" :description="$content['meta']->value('description')">
    <x-advert-slot :adverts="$adverts" at="hero:before" class="mt-6" />
    <x-page-hero :section="$content['hero']" />
    <x-advert-slot :adverts="$adverts" at="hero:after" class="mt-12" />

    <x-advert-slot :adverts="$adverts" at="opportunities:before" class="mt-20" />
    <section class="mx-auto mt-12 max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="no-scrollbar -mx-1 flex gap-2 overflow-x-auto px-1 pb-1" aria-label="Filter by category">
            <a href="{{ route('investments.index') }}" @class(['chip', 'chip-active' => ! $category, 'bg-white/50' => $category])>All</a>
            @foreach (\App\Enums\InvestmentCategory::cases() as $case)
                <a href="{{ route('investments.index', ['category' => $case->value]) }}" @class(['chip', 'chip-active' => $category === $case, 'bg-white/50' => $category !== $case])>{{ $case->label() }}</a>
            @endforeach
        </div>

        @if ($content['disclaimer']->isShown())
            <x-disclaimer class="mt-6"><span class="font-semibold text-ink-900">Please note:</span> {{ $content['disclaimer']->value('text') }}</x-disclaimer>
        @endif

        @if ($opportunities->isEmpty())
            <x-empty-state class="mt-10" icon="briefcase" title="No opportunities in this category yet" description="New opportunities are added regularly. Speak to an adviser about what is coming up.">
                <a href="{{ route('investments.index') }}" class="btn btn-primary">View all opportunities</a>
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

    <x-advert-slot :adverts="$adverts" at="how_it_works:before" class="mt-20" />
    {{-- How it works --}}
    @if ($content['how_it_works']->isShown())
    <section class="mx-auto mt-24 max-w-7xl px-4 sm:px-6 lg:px-8">
        <x-section-heading :section="$content['how_it_works']" size="md" data-reveal />
        <div class="mt-10 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($content['how_it_works']->list('steps') as ['icon' => $icon, 'title' => $step, 'text' => $copy])
                <div class="glass rounded-3xl p-6" data-reveal data-reveal-delay="{{ $loop->index * 90 }}">
                    <div class="flex items-center justify-between">
                        <span class="grid size-11 place-items-center rounded-2xl bg-ink-900 text-brass-300"><x-icon :name="$icon" /></span>
                        <span class="font-display text-3xl text-ink-200">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <h3 class="mt-5 font-display text-xl text-ink-900">{{ $step }}</h3>
                    @if (filled($copy))
                        <p class="mt-1.5 text-sm text-ink-500">{{ $copy }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </section>
    @endif
    <x-advert-slot :adverts="$adverts" at="how_it_works:after" class="mt-16" />

    <x-advert-slot :adverts="$adverts" at="checklist:before" class="mt-20" />
    {{-- What you'll find + enquiry --}}
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
        <x-enquiry-form :type="\App\Models\EnquiryType::Investment" :title="$checklist->value('form_title')" :description="$checklist->value('form_description')" :opportunities="$openOpportunities" whatsapp-text="Hello, I'd like to know more about your investment opportunities." data-reveal />
    </section>
    @endif
    <x-advert-slot :adverts="$adverts" at="checklist:after" class="mt-16" />

    <x-advert-slot :adverts="$adverts" at="cta:before" class="mt-20" />
    @if ($content['cta']->isShown())
        <x-cta-band class="mt-24" :title="$content['cta']->value('title')" :description="$content['cta']->value('description')" />
    @endif
    <x-advert-slot :adverts="$adverts" at="cta:after" class="mt-16" />
</x-layouts.app>
