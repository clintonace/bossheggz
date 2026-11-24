@php
    $presets = [
        'Everything' => collect(\App\Enums\DataSet::seedOrder())->map->value->all(),
        'Listings & content' => ['properties', 'investments', 'land_banking', 'articles', 'adverts'],
        'Land & investments' => ['investments', 'land_banking'],
        'Accounts & activity' => ['clients', 'engagements', 'enquiries'],
    ];
@endphp

<x-layouts.admin title="Seed demo data" subtitle="Choose what to add. Seeding only adds demo records that aren't there yet — your own data is never changed.">
    <x-slot:actions>
        <a href="{{ route('admin.data.index') }}" class="btn btn-ghost hidden sm:inline-flex"><x-icon name="arrow-left" class="size-4" /> Data manager</a>
    </x-slot:actions>

    <form method="POST" action="{{ route('admin.data.seed.store') }}" data-check-form class="max-w-6xl">
        @csrf

        <div class="animate-fade-up">@include('admin.data._toolbar')</div>
        @error('datasets') <p class="field-error mt-3">{{ $message }}</p> @enderror

        @foreach ($groups as $group => $dataSets)
            <section class="mt-8 animate-fade-up">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-[11px] font-semibold tracking-[0.2em] text-ink-400 uppercase">{{ $group }}</h2>
                    <button type="button" data-check-all="{{ str($group)->slug() }}" data-no-loader class="text-xs font-semibold text-brass-600 hover:text-brass-700">Select all</button>
                </div>
                <div class="mt-3 grid grid-cols-1 gap-3 md:grid-cols-2">
                    @foreach ($dataSets as $dataSet)
                        @include('admin.data._option', ['mode' => 'seed', 'count' => $counts[$dataSet->value]])
                    @endforeach
                </div>
            </section>
        @endforeach

        <div class="sticky bottom-4 z-20 mt-8">
            <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-white/80 px-5 py-3.5 shadow-lift ring-1 ring-white backdrop-blur-xl backdrop-saturate-150">
                <p class="text-sm text-ink-500">Demo photos load from the web; demo accounts get random passwords and never have admin access.</p>
                <button class="btn btn-primary" data-check-submit data-loading-text="Adding demo data…"><x-icon name="sparkles" class="size-4" /> Add selected demo data</button>
            </div>
        </div>
    </form>
</x-layouts.admin>
