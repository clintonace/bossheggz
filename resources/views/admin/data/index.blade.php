@php
    $total = array_sum(array_diff_key($counts, array_flip(['admins', 'page_content', 'appearance'])));
@endphp

<x-layouts.admin title="Data manager" subtitle="Fill the site with demo data to try things out, or clear data out — you choose exactly what.">
    <x-slot:actions>
        <a href="{{ route('admin.data.delete') }}" class="btn btn-outline hidden text-rose-600 sm:inline-flex"><x-icon name="trash" class="size-4" /> Delete data</a>
        <a href="{{ route('admin.data.seed') }}" class="btn btn-primary"><x-icon name="sparkles" class="size-4" /> Seed demo data</a>
    </x-slot:actions>

    <div class="grid animate-fade-up grid-cols-1 gap-4 lg:grid-cols-3">
        <div data-palette="normal" class="relative isolate overflow-hidden rounded-3xl bg-ink-950 p-6 text-white lg:col-span-2">
            <div class="absolute -top-20 -right-16 -z-10 size-64 rounded-full bg-brass-500/25 blur-3xl"></div>
            <p class="text-xs font-semibold tracking-[0.2em] text-brass-300 uppercase">In the database</p>
            <p class="mt-2 font-display text-4xl">{{ number_format($total) }} <span class="text-lg text-ink-300">records</span></p>
            <p class="mt-2 max-w-xl text-sm text-ink-300">Listings, opportunities, articles, adverts, accounts and activity. Seeding only adds what's missing, so it's safe to run again.</p>
            <div class="mt-5 flex flex-wrap gap-2">
                <a href="{{ route('admin.data.seed') }}" class="btn btn-accent btn-sm"><x-icon name="sparkles" class="size-4" /> Seed demo data</a>
                <a href="{{ route('admin.data.delete') }}" class="btn btn-sm bg-white/10 text-white ring-1 ring-white/15 hover:bg-white/15"><x-icon name="trash" class="size-4" /> Delete data</a>
            </div>
        </div>
        <div class="flex flex-col justify-between rounded-3xl bg-white/75 p-6 shadow-soft ring-1 ring-ink-100 backdrop-blur-xl">
            <div class="flex items-center gap-3">
                <span class="grid size-11 place-items-center rounded-xl bg-emerald-50 text-emerald-600 ring-1 ring-emerald-100"><x-icon name="shield" class="size-5" /></span>
                <div>
                    <p class="font-semibold text-ink-900">Admin accounts</p>
                    <p class="text-xs text-ink-400">Signed in as {{ auth()->user()->email }}</p>
                </div>
            </div>
            @if ($counts['admins'] === 0)
                <p class="mt-4 text-sm text-emerald-700">You are the only admin. Seeding never creates admin accounts.</p>
            @else
                <p class="mt-4 text-sm text-ink-600">There {{ $counts['admins'] === 1 ? 'is' : 'are' }} <span class="font-semibold">{{ $counts['admins'] }} other admin {{ str('account')->plural($counts['admins']) }}</span>. Keep just yours by deleting “Other admin accounts”.</p>
                <a href="{{ route('admin.data.delete', ['only' => 'admins']) }}" class="btn btn-outline btn-sm mt-4 self-start text-rose-600">Remove other admins</a>
            @endif
        </div>
    </div>

    @foreach ($groups as $group => $dataSets)
        <section class="mt-8 animate-fade-up">
            <h2 class="text-[11px] font-semibold tracking-[0.2em] text-ink-400 uppercase">{{ $group }}</h2>
            <div class="mt-3 grid grid-cols-1 gap-4 md:grid-cols-2 2xl:grid-cols-3">
                @foreach ($dataSets as $dataSet)
                    <article class="flex flex-col rounded-3xl bg-white/75 p-5 shadow-soft ring-1 ring-ink-100 backdrop-blur-xl transition hover:shadow-lift">
                        <div class="flex items-start justify-between gap-3">
                            <span class="grid size-11 place-items-center rounded-xl bg-ink-900 text-brass-300"><x-icon :name="$dataSet->icon()" class="size-5" /></span>
                            <span class="text-right">
                                <span class="block font-display text-3xl text-ink-950">{{ number_format($counts[$dataSet->value]) }}</span>
                                <span class="text-[11px] text-ink-400">{{ str($dataSet->noun())->plural($counts[$dataSet->value]) }}</span>
                            </span>
                        </div>
                        <h3 class="mt-4 font-semibold text-ink-900">{{ $dataSet->label() }}</h3>
                        <p class="mt-1 flex-1 text-xs leading-relaxed text-ink-500">{{ $dataSet->seedDescription() ?? $dataSet->deleteDescription() }}</p>
                        <div class="mt-4 flex gap-2 border-t border-ink-100/80 pt-4">
                            @if ($dataSet->isSeedable())
                                <a href="{{ route('admin.data.seed', ['only' => $dataSet->value]) }}" class="btn btn-outline btn-sm"><x-icon name="plus" class="size-3.5" /> Seed</a>
                            @endif
                            @if ($counts[$dataSet->value] > 0)
                                <a href="{{ route('admin.data.delete', ['only' => $dataSet->value]) }}" class="btn btn-ghost btn-sm text-rose-600 hover:bg-rose-50 hover:text-rose-700"><x-icon name="trash" class="size-3.5" /> {{ in_array($dataSet, [\App\Enums\DataSet::PageContent, \App\Enums\DataSet::Appearance], true) ? 'Reset' : 'Delete' }}</a>
                            @else
                                <span class="btn btn-ghost btn-sm cursor-default text-ink-300">Empty</span>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endforeach
</x-layouts.admin>
