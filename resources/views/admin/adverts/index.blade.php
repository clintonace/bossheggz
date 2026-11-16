@php
    $viewOptions = ['all' => 'All adverts', ...collect(\App\Enums\AdvertPage::cases())->mapWithKeys(fn ($advertPage) => [$advertPage->value => $advertPage->label()])->all(), 'popup' => 'Pop-up adverts'];
    $pageUrl = match ($page) {
        \App\Enums\AdvertPage::Home => route('home'),
        \App\Enums\AdvertPage::Listings, \App\Enums\AdvertPage::Property => route('properties.index'),
        default => null,
    };
@endphp

<x-layouts.admin title="Adverts" subtitle="Promote partners, products and offers across the website.">
    <x-slot:actions>
        <a href="{{ route('admin.adverts.create', $view === 'popup' ? ['format' => 'popup'] : []) }}" class="btn btn-primary"><x-icon name="plus" class="size-4" /> <span class="hidden sm:inline">{{ $view === 'popup' ? 'New pop-up' : 'New advert' }}</span></a>
    </x-slot:actions>

    <div class="flex animate-fade-up flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <form method="GET" action="{{ route('admin.adverts.index') }}" class="flex items-center gap-3">
            <label for="advert-view" class="text-sm font-medium text-ink-500">Show adverts for</label>
            <div class="relative">
                <select id="advert-view" name="view" onchange="this.form.requestSubmit()" class="appearance-none rounded-full border-0 bg-white py-2.5 pr-10 pl-4 text-sm font-semibold text-ink-900 shadow-soft ring-1 ring-ink-100 focus:ring-brass-300 focus:outline-none">
                    @foreach ($viewOptions as $value => $label)
                        <option value="{{ $value }}" @selected($view === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <x-icon name="chevron-down" class="pointer-events-none absolute top-1/2 right-3.5 size-4 -translate-y-1/2 text-ink-400" />
            </div>
        </form>

        <dl class="flex gap-2 text-sm">
            @foreach ([['Live', $liveCount], ['Total', $totalCount], ['Clicks', number_format($totalClicks)]] as [$label, $value])
                <div class="rounded-full bg-white px-4 py-2 ring-1 ring-ink-100"><dt class="inline text-ink-400">{{ $label }}</dt> <dd class="inline font-semibold text-ink-900">{{ $value }}</dd></div>
            @endforeach
        </dl>
    </div>

    @if ($page)
        {{-- Page map --}}
        <section class="card mt-6 animate-fade-up p-5 [animation-delay:60ms] sm:p-8">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 class="font-display text-2xl text-ink-900">{{ $page->label() }} layout</h2>
                    <p class="mt-1 max-w-2xl text-sm text-ink-500">The page's sections from top to bottom. Each dashed box is a spot where adverts can go — add as many as you like. Half-width adverts (6/12) sit side by side; full-width (12/12) take the whole row.</p>
                </div>
                @if ($pageUrl)
                    <a href="{{ $pageUrl }}" target="_blank" class="btn btn-outline btn-sm"><x-icon name="eye" class="size-3.5" /> View page</a>
                @endif
            </div>

            <ol class="mt-8 space-y-3">
                @foreach ($page->sections() as $sectionKey => $section)
                    @foreach ([\App\Enums\AdvertPosition::Before] as $position)
                        @if (in_array($position, $section['positions'], true))
                            <li>@include('admin.adverts._slot', ['sectionLabel' => $section['label'], 'slotAdverts' => $slots->get("{$sectionKey}:{$position->value}", collect())])</li>
                        @endif
                    @endforeach

                    <li class="rounded-2xl bg-ink-50 px-5 py-5 ring-1 ring-ink-100">
                        <div class="flex items-center gap-3">
                            <span class="grid size-8 place-items-center rounded-lg bg-white text-xs font-semibold text-ink-400 ring-1 ring-ink-100">{{ $loop->iteration }}</span>
                            <p class="font-semibold text-ink-700">{{ $section['label'] }}</p>
                            <span class="hidden flex-1 space-y-1.5 sm:block" aria-hidden="true"><span class="ml-auto block h-1.5 w-2/3 rounded-full bg-ink-100"></span><span class="ml-auto block h-1.5 w-1/3 rounded-full bg-ink-100"></span></span>
                        </div>
                        @if (in_array(\App\Enums\AdvertPosition::Inside, $section['positions'], true))
                            <div class="mt-4">
                                @include('admin.adverts._slot', ['position' => \App\Enums\AdvertPosition::Inside, 'sectionLabel' => $section['label'], 'slotAdverts' => $slots->get("{$sectionKey}:inside", collect())])
                                <p class="mt-2 text-xs text-ink-400">“Inside” adverts appear as cards after every 3rd property (up to 2 per page).</p>
                            </div>
                        @endif
                    </li>

                    @if (in_array(\App\Enums\AdvertPosition::After, $section['positions'], true))
                        <li>@include('admin.adverts._slot', ['position' => \App\Enums\AdvertPosition::After, 'sectionLabel' => $section['label'], 'slotAdverts' => $slots->get("{$sectionKey}:after", collect())])</li>
                    @endif
                @endforeach
            </ol>
        </section>
    @else
        @if ($view === 'popup')
            <div class="mt-6 flex animate-fade-up gap-3 rounded-2xl bg-brass-50 p-4 text-sm text-brass-800 ring-1 ring-brass-100">
                <x-icon name="info" class="size-5 shrink-0" />
                <p>Pop-up (major) adverts cover the screen for the number of seconds you set, <span class="font-semibold">once per visit</span>. Clicking anywhere on the advert opens the advertiser's site; only the ✕ button closes it. If several pop-ups target the same page, one is chosen at random.</p>
            </div>
        @endif

        @if ($adverts->isEmpty())
            <x-empty-state class="mt-6" icon="megaphone" :title="$view === 'popup' ? 'No pop-up adverts yet' : 'No adverts yet'" description="Create an advert to promote a partner business, product, event or offer.">
                <a href="{{ route('admin.adverts.create', $view === 'popup' ? ['format' => 'popup'] : []) }}" class="btn btn-primary"><x-icon name="plus" class="size-4" /> {{ $view === 'popup' ? 'New pop-up' : 'New advert' }}</a>
            </x-empty-state>
        @else
            <div class="mt-6 grid grid-cols-1 gap-5 md:grid-cols-2 2xl:grid-cols-3">
                @foreach ($adverts as $advert)
                    @include('admin.adverts._card')
                @endforeach
            </div>
        @endif
    @endif
</x-layouts.admin>
