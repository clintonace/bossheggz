@php($firstName = str(auth()->user()->name)->before(' '))

<x-layouts.client title="Overview" :subtitle="'Welcome back, '.$firstName.'. Here is where things stand.'">
    <x-slot:actions>
        <a href="{{ route('properties.index') }}" class="btn btn-primary hidden sm:inline-flex"><x-icon name="search" class="size-4" /> Browse properties</a>
    </x-slot:actions>

    {{-- Welcome banner --}}
    <section data-palette="normal" class="relative animate-fade-up overflow-hidden rounded-[2rem] bg-ink-950 px-6 py-8 text-white sm:px-10 sm:py-10">
        <div class="pointer-events-none absolute -top-24 -right-16 size-80 rounded-full bg-brass-500/25 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-32 left-1/3 size-72 rounded-full bg-brass-300/10 blur-3xl"></div>
        <div class="relative flex flex-col justify-between gap-8 lg:flex-row lg:items-center">
            <div class="max-w-xl">
                <p class="text-xs font-semibold tracking-[0.2em] text-brass-300 uppercase">{{ now()->format('l, F j') }}</p>
                <h2 class="mt-3 font-display text-3xl leading-tight sm:text-4xl">
                    @if ($stats['total'] === 0)
                        Let's find a place you'll love, <em class="text-brass-300">{{ $firstName }}.</em>
                    @elseif ($stats['open'] > 0)
                        You have {{ $stats['open'] }} {{ str('home')->plural($stats['open']) }} <em class="text-brass-300">in progress.</em>
                    @else
                        All your engagements are <em class="text-brass-300">wrapped up.</em>
                    @endif
                </h2>
                <p class="mt-3 text-sm leading-relaxed text-ink-300">
                    @if ($stats['total'] === 0)
                        Engage with any property and you'll be able to follow every step right here, with updates by email too.
                    @elseif ($stats['viewings'] > 0)
                        {{ $stats['viewings'] }} {{ str('viewing')->plural($stats['viewings']) }} scheduled. We'll notify you here and at {{ auth()->user()->email }} as things move forward.
                    @else
                        We'll notify you here and at {{ auth()->user()->email }} the moment anything changes.
                    @endif
                </p>
            </div>
            <div class="flex shrink-0 flex-wrap gap-3">
                <a href="{{ route('properties.index') }}" class="btn btn-accent btn-lg">Explore homes <x-icon name="arrow-right" class="size-4" /></a>
                @if ($stats['total'] > 0)
                    <a href="{{ route('account.engagements.index') }}" class="btn btn-lg bg-white/10 text-white ring-1 ring-white/15 hover:bg-white/15">My engagements</a>
                @endif
            </div>
        </div>
    </section>

    {{-- Stat tiles --}}
    <div class="mt-6 grid grid-cols-2 gap-3 sm:gap-5 xl:grid-cols-4">
        @foreach ([
            ['inbox', 'Engaged', $stats['total'], 'Properties you showed interest in', route('account.engagements.index'), 'bg-ink-900 text-brass-300'],
            ['trending', 'In progress', $stats['open'], 'Being handled by our team', route('account.engagements.index', ['filter' => 'open']), 'bg-brass-50 text-brass-600'],
            ['calendar', 'Viewings', $stats['viewings'], 'Scheduled property viewings', route('account.engagements.index', ['filter' => 'open']), 'bg-violet-50 text-violet-600'],
            ['check-circle', 'Completed', $stats['closed'], 'Deals closed successfully', route('account.engagements.index', ['filter' => 'completed']), 'bg-emerald-50 text-emerald-600'],
        ] as [$icon, $label, $value, $meta, $url, $iconClasses])
            <a href="{{ $url }}" class="group card animate-fade-up p-5 transition hover:-translate-y-0.5 hover:shadow-lift sm:p-6" style="animation-delay: {{ 80 + $loop->index * 60 }}ms">
                <div class="flex items-start justify-between">
                    <span class="grid size-11 place-items-center rounded-2xl {{ $iconClasses }}"><x-icon :name="$icon" /></span>
                    <x-icon name="arrow-right" class="size-4 -translate-x-1 text-ink-300 opacity-0 transition group-hover:translate-x-0 group-hover:opacity-100" />
                </div>
                <p class="mt-5 text-sm font-medium text-ink-500">{{ $label }}</p>
                <p class="mt-1 font-display text-4xl tracking-tight text-ink-950">{{ $value }}</p>
                <p class="mt-1.5 hidden text-xs text-ink-400 sm:block">{{ $meta }}</p>
            </a>
        @endforeach
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
        {{-- Active engagements --}}
        <section class="card animate-fade-up p-6 [animation-delay:320ms] xl:col-span-2">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="font-display text-xl text-ink-900">Active engagements</h2>
                    <p class="mt-1 text-sm text-ink-400">Where each of your homes is in the process</p>
                </div>
                @if ($stats['open'] > $activeEngagements->count())
                    <a href="{{ route('account.engagements.index', ['filter' => 'open']) }}" class="text-sm font-semibold text-brass-600 hover:text-brass-700">View all {{ $stats['open'] }}</a>
                @endif
            </div>

            @if ($activeEngagements->isEmpty())
                <div class="mt-6 flex flex-col items-center rounded-2xl border border-dashed border-ink-200 bg-ivory/60 px-6 py-12 text-center">
                    <span class="grid size-12 place-items-center rounded-2xl bg-white text-brass-500 shadow-soft ring-1 ring-ink-100"><x-icon name="home" /></span>
                    <p class="mt-4 font-display text-lg text-ink-900">Nothing in progress right now</p>
                    <p class="mt-1 max-w-sm text-sm text-ink-500">When you engage with a property, its progress will appear here step by step.</p>
                    <a href="{{ route('properties.index') }}" class="btn btn-primary btn-sm mt-5">Find a property</a>
                </div>
            @else
                <ul class="mt-6 space-y-4">
                    @foreach ($activeEngagements as $engagement)
                        <li>
                            <a href="{{ route('account.engagements.show', $engagement) }}" class="group grid grid-cols-1 gap-5 rounded-2xl p-3 ring-1 ring-ink-100 transition hover:bg-ivory/70 hover:ring-ink-200 sm:grid-cols-[9rem_1fr]">
                                <x-property-image :src="$engagement->property->cover_url" :alt="$engagement->property->title" class="aspect-[16/10] rounded-xl sm:aspect-auto sm:h-full" />
                                <div class="flex min-w-0 flex-col gap-4 py-1 sm:pr-2">
                                    <div class="flex flex-wrap items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <p class="truncate font-display text-lg text-ink-900">{{ $engagement->property->title }}</p>
                                            <p class="text-xs text-ink-400">{{ $engagement->property->formatted_price }} · {{ $engagement->property->location }}</p>
                                        </div>
                                        <x-status-badge :status="$engagement->status" />
                                    </div>
                                    <x-progress-tracker :status="$engagement->status" />
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        {{-- Recent activity --}}
        <section class="card animate-fade-up p-6 [animation-delay:380ms]">
            <div class="flex items-center justify-between">
                <h2 class="font-display text-xl text-ink-900">Recent activity</h2>
                <a href="{{ route('account.notifications.index') }}" class="text-sm font-semibold text-brass-600 hover:text-brass-700">Notifications</a>
            </div>

            @if ($activity->isEmpty())
                <p class="py-12 text-center text-sm text-ink-400">Your activity will show up here.</p>
            @else
                <ol class="relative mt-6 space-y-6 border-l border-ink-100 pl-6">
                    @foreach ($activity as $item)
                        <li class="relative">
                            <span @class([
                                'absolute top-1.5 -left-[29px] size-2.5 rounded-full ring-4 ring-white',
                                'bg-brass-500' => $item['status'],
                                'bg-ink-200' => ! $item['status'],
                            ])></span>
                            <a href="{{ route('account.engagements.show', $item['engagement']) }}" class="group block">
                                <div class="flex flex-wrap items-center gap-2">
                                    @if ($item['status'])
                                        <x-status-badge :status="$item['status']" />
                                    @else
                                        <span class="pill bg-ink-50 text-ink-500 ring-ink-100">Engaged</span>
                                    @endif
                                    <time class="text-[11px] text-ink-300">{{ $item['date']->diffForHumans() }}</time>
                                </div>
                                <p class="mt-1.5 truncate text-sm font-semibold text-ink-900 group-hover:text-brass-700">{{ $item['title'] }}</p>
                                <p class="mt-0.5 line-clamp-2 text-xs text-ink-500">{{ $item['body'] }}</p>
                            </a>
                        </li>
                    @endforeach
                </ol>
            @endif
        </section>
    </div>

    {{-- Recommendations --}}
    @if ($recommended->isNotEmpty())
        <section class="mt-10">
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end" data-reveal>
                <div>
                    <p class="eyebrow">Picked for you</p>
                    <h2 class="mt-2 font-display text-3xl tracking-tight text-ink-950">{{ $stats['total'] > 0 ? 'You might also like' : 'Start with these homes' }}</h2>
                </div>
                <a href="{{ route('properties.index') }}" class="btn btn-outline self-start sm:self-auto">View all properties <x-icon name="arrow-right" class="size-4" /></a>
            </div>
            <div class="mt-8 grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($recommended as $property)
                    <x-property-card :property="$property" data-reveal data-reveal-delay="{{ $loop->index * 90 }}" />
                @endforeach
            </div>
        </section>
    @endif
</x-layouts.client>
