@php
    $presets = [
        'Everything (keeps your admin account)' => collect(\App\Enums\DataSet::cases())->map->value->all(),
        'Listings & content' => ['properties', 'investments', 'land_banking', 'articles', 'adverts'],
        'Accounts & activity' => ['clients', 'engagements', 'enquiries', 'notifications'],
        'Activity only (engagements, enquiries, notifications)' => ['engagements', 'enquiries', 'notifications'],
        'Reset website settings' => ['page_content', 'appearance'],
        'Keep only my admin account' => ['admins'],
    ];
@endphp

<x-layouts.admin title="Delete data" subtitle="Choose what to remove. This can't be undone — your own admin account is always kept.">
    <x-slot:actions>
        <a href="{{ route('admin.data.index') }}" class="btn btn-ghost hidden sm:inline-flex"><x-icon name="arrow-left" class="size-4" /> Data manager</a>
    </x-slot:actions>

    <form method="POST" action="{{ route('admin.data.destroy') }}" data-check-form data-confirm="Permanently delete the selected data? This cannot be undone." class="max-w-6xl">
        @csrf
        @method('DELETE')

        <div class="flex animate-fade-up items-start gap-3 rounded-2xl bg-rose-50/80 p-4 text-sm text-rose-800 ring-1 ring-rose-100 backdrop-blur">
            <x-icon name="alert" class="mt-px size-5 shrink-0" />
            <p>Deleting removes records and their uploaded images for good. If you only want to tidy up demo content, you can always seed it again later from <a href="{{ route('admin.data.seed') }}" class="font-semibold underline">Seed demo data</a>.</p>
        </div>

        <div class="mt-6 animate-fade-up">@include('admin.data._toolbar')</div>
        @error('datasets') <p class="field-error mt-3">{{ $message }}</p> @enderror

        @foreach ($groups as $group => $dataSets)
            <section class="mt-8 animate-fade-up">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-[11px] font-semibold tracking-[0.2em] text-ink-400 uppercase">{{ $group }}</h2>
                    <button type="button" data-check-all="{{ str($group)->slug() }}" data-no-loader class="text-xs font-semibold text-rose-600 hover:text-rose-700">Select all</button>
                </div>
                <div class="mt-3 grid grid-cols-1 gap-3 md:grid-cols-2">
                    @foreach ($dataSets as $dataSet)
                        @include('admin.data._option', ['mode' => 'delete', 'count' => $counts[$dataSet->value]])
                    @endforeach
                </div>
            </section>
        @endforeach

        <div class="sticky bottom-4 z-20 mt-8">
            <div class="flex flex-wrap items-end justify-between gap-4 rounded-2xl bg-white/85 px-5 py-4 shadow-lift ring-1 ring-white backdrop-blur-xl backdrop-saturate-150">
                <div class="min-w-60 flex-1">
                    <label for="confirmation" class="field-label">Type DELETE to confirm</label>
                    <input type="text" name="confirmation" id="confirmation" autocomplete="off" spellcheck="false" data-confirm-word="DELETE" placeholder="DELETE" class="field-input max-w-xs font-mono tracking-widest uppercase">
                    @error('confirmation') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div class="flex flex-col items-end gap-1.5">
                    <button class="btn btn-danger" data-check-submit disabled data-loading-text="Deleting…"><x-icon name="trash" class="size-4" /> Delete selected data</button>
                    <p data-check-hint class="text-xs font-medium text-ink-400" aria-live="polite">Tick at least one item above to delete.</p>
                </div>
            </div>
        </div>
    </form>
</x-layouts.admin>
