<x-layouts.admin title="Dashboard" :subtitle="'Good '.(now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening')).', '.str(auth()->user()->name)->before(' ').'. Here is what is happening today.'">
    <x-slot:actions>
        <a href="{{ route('admin.properties.create') }}" class="btn btn-primary hidden sm:inline-flex"><x-icon name="plus" class="size-4" /> New property</a>
    </x-slot:actions>

    {{-- Stat tiles --}}
    <div class="grid grid-cols-2 gap-3 sm:gap-5 xl:grid-cols-4">
        @foreach ([
            ['building', 'Properties', $stats['properties'], "{$stats['live']} live · {$stats['sale']} sale · {$stats['rent']} rent", route('admin.properties.index'), 'bg-ink-900 text-brass-300'],
            ['sparkles', 'New engagements', $stats['new'], 'Waiting for first contact', route('admin.engagements.index', ['status' => 'new']), 'bg-brass-500 text-white'],
            ['trending', 'Open pipeline', $stats['open'], "{$stats['engagements']} engagements all time", route('admin.engagements.index'), 'bg-sky-50 text-sky-600'],
            ['check-circle', 'Closed this month', $stats['closed_this_month'], "{$stats['users']} registered engagers", route('admin.engagements.index', ['status' => 'closed']), 'bg-emerald-50 text-emerald-600'],
        ] as [$icon, $label, $value, $meta, $url, $iconClasses])
            <a href="{{ $url }}" class="group card animate-fade-up p-6 transition hover:-translate-y-0.5 hover:shadow-lift" style="animation-delay: {{ $loop->index * 60 }}ms">
                <div class="flex items-start justify-between">
                    <span class="grid size-11 place-items-center rounded-2xl {{ $iconClasses }}"><x-icon :name="$icon" /></span>
                    <x-icon name="arrow-right" class="size-4 -translate-x-1 text-ink-300 opacity-0 transition group-hover:translate-x-0 group-hover:opacity-100" />
                </div>
                <p class="mt-6 text-sm font-medium text-ink-500">{{ $label }}</p>
                <p class="mt-1 font-display text-4xl tracking-tight text-ink-950">{{ number_format($value) }}</p>
                <p class="mt-2 text-xs text-ink-400">{{ $meta }}</p>
            </a>
        @endforeach
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
        {{-- Engagements over time --}}
        <section class="card animate-fade-up p-6 [animation-delay:240ms] xl:col-span-2">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h2 class="font-display text-xl text-ink-900">Engagements, last 14 days</h2>
                    <p class="mt-1 text-sm text-ink-400">New engagements received per day</p>
                </div>
                <p class="text-right">
                    <span class="font-display text-3xl text-ink-950">{{ $chartTotal }}</span>
                    <span class="block text-xs text-ink-400">total in period</span>
                </p>
            </div>

            <div class="relative mt-8 h-56">
                {{-- Recessive gridlines --}}
                <div class="pointer-events-none absolute inset-0 flex flex-col justify-between" aria-hidden="true">
                    @foreach ([$chartMax, (int) ceil($chartMax / 2), 0] as $tick)
                        <div class="flex items-center gap-3">
                            <span class="w-6 text-right text-[11px] text-ink-300 tabular-nums">{{ $tick }}</span>
                            <span @class(['h-px flex-1', 'bg-ink-200' => $tick === 0, 'border-t border-dashed border-ink-100' => $tick !== 0])></span>
                        </div>
                    @endforeach
                </div>

                <div class="absolute inset-y-0 right-0 left-9 flex items-end gap-[2px] pt-[7px] pb-[7px]" aria-hidden="true">
                    @foreach ($chart as $day)
                        <div class="group relative flex h-full flex-1 items-end justify-center">
                            <div class="absolute inset-0 rounded-md transition group-hover:bg-ink-50"></div>
                            <div class="relative w-full max-w-7 rounded-t-[4px] bg-brass-500 transition group-hover:bg-brass-600" style="height: {{ $day['count'] ? max(4, $day['count'] / $chartMax * 100) : 0 }}%"></div>
                            <div class="pointer-events-none absolute bottom-full left-1/2 z-10 mb-2 -translate-x-1/2 rounded-lg bg-ink-950 px-2.5 py-1.5 text-xs whitespace-nowrap text-white opacity-0 shadow-lift transition group-hover:opacity-100" style="bottom: calc({{ $day['count'] / $chartMax * 100 }}% + 4px)">
                                <span class="font-semibold">{{ $day['count'] }}</span> {{ str('engagement')->plural($day['count']) }}
                                <span class="block text-[10px] text-ink-400">{{ $day['date']->format('D, M j') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="mt-3 ml-9 flex justify-between text-[11px] text-ink-400" aria-hidden="true">
                <span>{{ $chart->first()['label'] }}</span>
                <span>{{ $chart->get(7)['label'] }}</span>
                <span>Today</span>
            </div>

            <table class="sr-only">
                <caption>Engagements per day, last 14 days</caption>
                <thead><tr><th>Date</th><th>Engagements</th></tr></thead>
                <tbody>
                    @foreach ($chart as $day)
                        <tr><td>{{ $day['date']->format('M j, Y') }}</td><td>{{ $day['count'] }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        {{-- Pipeline --}}
        <section class="card animate-fade-up p-6 [animation-delay:300ms]">
            <h2 class="font-display text-xl text-ink-900">Pipeline</h2>
            <p class="mt-1 text-sm text-ink-400">Engagements by stage</p>
            @php($pipelineMax = max(1, $statusCounts->max() ?? 0))
            <ul class="mt-6 space-y-4">
                @foreach (\App\Enums\EngagementStatus::cases() as $status)
                    @php($count = (int) $statusCounts->get($status->value, 0))
                    <li>
                        <a href="{{ route('admin.engagements.index', ['status' => $status->value]) }}" class="group block" title="{{ $count }} {{ $status->label() }}">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-ink-600 group-hover:text-ink-900">{{ $status->label() }}</span>
                                <span class="font-semibold text-ink-900 tabular-nums">{{ $count }}</span>
                            </div>
                            <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-ink-50">
                                <div class="h-full rounded-full bg-brass-500 transition group-hover:bg-brass-600" style="width: {{ $count / $pipelineMax * 100 }}%"></div>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
        {{-- Recent engagements --}}
        <section class="card animate-fade-up overflow-hidden [animation-delay:360ms] xl:col-span-2">
            <div class="flex items-center justify-between px-6 pt-6">
                <h2 class="font-display text-xl text-ink-900">Recent engagements</h2>
                <a href="{{ route('admin.engagements.index') }}" class="text-sm font-semibold text-brass-600 hover:text-brass-700">View all</a>
            </div>
            @if ($recentEngagements->isEmpty())
                <p class="px-6 py-14 text-center text-sm text-ink-400">No engagements yet. They will appear here as soon as visitors engage with a property.</p>
            @else
                <ul class="mt-4 divide-y divide-ink-50">
                    @foreach ($recentEngagements as $engagement)
                        <li>
                            <a href="{{ route('admin.engagements.show', $engagement) }}" class="flex items-center gap-4 px-6 py-4 transition hover:bg-ivory">
                                <x-property-image :src="$engagement->property->cover_url" class="size-12 shrink-0 rounded-xl" />
                                <div class="min-w-0 flex-1">
                                    <p class="flex items-center gap-2 truncate text-sm font-semibold text-ink-900">
                                        {{ $engagement->name }}
                                        @if ($engagement->isGuest())
                                            <span class="rounded-md bg-ink-50 px-1.5 py-0.5 text-[10px] font-semibold tracking-wide text-ink-500 uppercase">Guest</span>
                                        @else
                                            <span class="rounded-md bg-brass-50 px-1.5 py-0.5 text-[10px] font-semibold tracking-wide text-brass-700 uppercase">Member</span>
                                        @endif
                                    </p>
                                    <p class="truncate text-xs text-ink-400">{{ $engagement->property->title }}</p>
                                </div>
                                <x-status-badge :status="$engagement->status" class="hidden sm:inline-flex" />
                                <span class="w-20 text-right text-xs text-ink-400">{{ $engagement->created_at->diffForHumans(short: true) }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        {{-- Top properties --}}
        <section class="card animate-fade-up p-6 [animation-delay:420ms]">
            <h2 class="font-display text-xl text-ink-900">Most engaged</h2>
            <p class="mt-1 text-sm text-ink-400">Properties attracting the most interest</p>
            @if ($topProperties->isEmpty())
                <p class="py-10 text-center text-sm text-ink-400">No engagement data yet.</p>
            @else
                <ol class="mt-6 space-y-4">
                    @foreach ($topProperties as $property)
                        <li>
                            <a href="{{ route('admin.properties.edit', $property) }}" class="group flex items-center gap-3">
                                <span class="w-5 font-display text-lg text-ink-300">{{ $loop->iteration }}</span>
                                <x-property-image :src="$property->cover_url" class="size-11 shrink-0 rounded-xl" />
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-semibold text-ink-900 group-hover:text-brass-700">{{ $property->title }}</span>
                                    <span class="block text-xs text-ink-400">{{ $property->formatted_price }}</span>
                                </span>
                                <span class="rounded-full bg-ink-50 px-2.5 py-1 text-xs font-semibold text-ink-700 tabular-nums">{{ $property->engagements_count }}</span>
                            </a>
                        </li>
                    @endforeach
                </ol>
            @endif
        </section>
    </div>
</x-layouts.admin>
