<x-layouts.app>
    <x-advert-slot :adverts="$adverts" at="hero:before" class="mt-6" />
    {{-- Hero --}}
    <section class="relative overflow-hidden">
        <div class="pointer-events-none absolute -top-40 -right-40 size-[38rem] rounded-full bg-brass-100/60 blur-3xl"></div>
        <div class="pointer-events-none absolute top-60 -left-40 size-[28rem] rounded-full bg-ink-100/60 blur-3xl"></div>

        <div class="relative mx-auto grid grid-cols-1 max-w-7xl items-center gap-14 px-4 pt-10 pb-20 sm:px-6 lg:grid-cols-12 lg:px-8 lg:pt-16 lg:pb-28">
            <div class="lg:col-span-6">
                <p class="eyebrow animate-fade-up">Curated homes · For sale &amp; rent</p>
                <h1 class="mt-5 animate-fade-up font-display text-5xl leading-[1.05] font-medium tracking-tight text-ink-950 [animation-delay:80ms] sm:text-6xl lg:text-7xl">
                    Find a place that<br>feels like <em class="text-brass-500">home.</em>
                </h1>
                <p class="mt-6 max-w-lg animate-fade-up text-lg leading-relaxed text-ink-500 [animation-delay:160ms]">
                    Explore beautiful properties, engage with the ones you love in a single click, and follow every step — from first call to the keys in your hand.
                </p>

                {{-- Search --}}
                <form action="{{ route('properties.index') }}" method="GET" class="mt-10 animate-fade-up rounded-3xl bg-white p-2 shadow-lift ring-1 ring-ink-100 [animation-delay:240ms]">
                    <div class="flex gap-1 p-1">
                        @foreach (['' => 'All', 'sale' => 'Buy', 'rent' => 'Rent'] as $value => $label)
                            <label class="cursor-pointer">
                                <input type="radio" name="listing" value="{{ $value }}" class="peer sr-only" @checked($value === '')>
                                <span class="block rounded-full px-4 py-1.5 text-sm font-medium text-ink-500 transition peer-checked:bg-ink-900 peer-checked:text-white hover:text-ink-900 peer-checked:hover:text-white">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    <div class="grid grid-cols-1 gap-2 p-1 sm:grid-cols-[1.4fr_1fr_auto]">
                        <label class="flex items-center gap-3 rounded-2xl bg-ivory px-4 py-3 ring-1 ring-transparent transition focus-within:bg-white focus-within:ring-brass-300">
                            <x-icon name="search" class="size-5 text-ink-300" />
                            <input type="text" name="q" placeholder="Search by area, street or name" class="w-full bg-transparent text-sm text-ink-900 placeholder:text-ink-300 focus:outline-none">
                        </label>
                        <label class="flex items-center gap-3 rounded-2xl bg-ivory px-4 py-3 ring-1 ring-transparent transition focus-within:bg-white focus-within:ring-brass-300">
                            <x-icon name="map-pin" class="size-5 text-ink-300" />
                            <select name="city" class="w-full appearance-none bg-transparent text-sm text-ink-900 focus:outline-none">
                                <option value="">Any location</option>
                                @foreach ($cities as $city)
                                    <option value="{{ $city }}">{{ $city }}</option>
                                @endforeach
                            </select>
                        </label>
                        <button class="btn btn-accent rounded-2xl px-7 py-3.5">
                            <x-icon name="search" class="size-4" /> Search
                        </button>
                    </div>
                </form>

                <dl class="mt-10 flex animate-fade-up gap-10 [animation-delay:320ms]">
                    @foreach ([['For sale', $stats['sale']], ['For rent', $stats['rent']], ['Locations', $stats['cities']]] as [$label, $value])
                        <div>
                            <dt class="text-xs font-medium tracking-wider text-ink-400 uppercase">{{ $label }}</dt>
                            <dd class="mt-1 font-display text-3xl text-ink-900">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>

            {{-- Collage --}}
            <div class="relative hidden lg:col-span-6 lg:block">
                @php($heroImages = $featured->concat($latest)->pluck('cover_url')->filter()->unique()->values())
                <div class="grid grid-cols-6 grid-rows-6 gap-4" style="height: 36rem">
                    <x-property-image :src="$heroImages->get(0)" class="col-span-4 row-span-4 animate-fade-up rounded-[2rem] shadow-lift [animation-delay:200ms]" />
                    <x-property-image :src="$heroImages->get(1)" class="col-span-2 row-span-3 animate-fade-up rounded-[2rem] shadow-soft [animation-delay:320ms]" />
                    <x-property-image :src="$heroImages->get(2)" class="col-span-2 row-span-3 animate-fade-up rounded-[2rem] shadow-soft [animation-delay:440ms]" />
                    <x-property-image :src="$heroImages->get(3)" class="col-span-4 row-span-2 animate-fade-up rounded-[2rem] shadow-soft [animation-delay:560ms]" />
                </div>

                <div class="absolute -bottom-6 -left-8 flex animate-fade-up items-center gap-4 rounded-2xl bg-white/95 p-4 pr-6 shadow-lift ring-1 ring-ink-100 backdrop-blur [animation-delay:700ms]">
                    <span class="grid size-11 place-items-center rounded-xl bg-emerald-50 text-emerald-600"><x-icon name="shield" /></span>
                    <div>
                        <p class="text-sm font-semibold text-ink-900">Verified listings</p>
                        <p class="text-xs text-ink-400">Every property reviewed by our team</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <x-advert-slot :adverts="$adverts" at="hero:after" class="mb-16" />

    <x-advert-slot :adverts="$adverts" at="featured:before" class="mb-16" />
    {{-- Featured --}}
    @if ($featured->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col justify-between gap-6 sm:flex-row sm:items-end" data-reveal>
                <div>
                    <p class="eyebrow">Handpicked</p>
                    <h2 class="mt-3 font-display text-4xl tracking-tight text-ink-950 sm:text-5xl">Featured properties</h2>
                </div>
                <a href="{{ route('properties.index') }}" class="btn btn-outline self-start sm:self-auto">View all <x-icon name="arrow-right" class="size-4" /></a>
            </div>
            <div class="mt-12 grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($featured as $property)
                    <x-property-card :property="$property" data-reveal data-reveal-delay="{{ $loop->index * 100 }}" />
                @endforeach
            </div>
        </section>
    @endif

    <x-advert-slot :adverts="$adverts" at="featured:after" class="mt-20" />

    <x-advert-slot :adverts="$adverts" at="how_it_works:before" class="mt-20" />
    {{-- How it works --}}
    <section class="mx-auto mt-28 max-w-7xl px-4 sm:px-6 lg:px-8">
        <div data-palette="normal" class="relative overflow-hidden rounded-[2.5rem] bg-ink-950 px-6 py-16 text-white sm:px-12 lg:px-16 lg:py-20">
            <div class="pointer-events-none absolute -top-24 -right-24 size-96 rounded-full bg-brass-500/20 blur-3xl"></div>
            <div class="relative grid grid-cols-1 gap-14 lg:grid-cols-12">
                <div class="lg:col-span-4" data-reveal>
                    <p class="text-xs font-semibold tracking-[0.2em] text-brass-300 uppercase">How it works</p>
                    <h2 class="mt-4 font-display text-4xl leading-tight tracking-tight">From first look to <em class="text-brass-300">keys in hand.</em></h2>
                    <p class="mt-5 text-ink-300">Engage as a guest with just your email, or create an account to follow your progress live and get every update by email.</p>
                </div>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-3 lg:col-span-8">
                    @foreach ([
                        ['search', 'Discover', 'Browse curated homes for sale and rent with rich photos and full details.'],
                        ['message', 'Engage', 'Like what you see? Engage in seconds — our team will reach out to you directly.'],
                        ['trending', 'Track progress', 'Signed-in engagers follow each step and receive updates in-app and by email.'],
                    ] as [$icon, $heading, $copy])
                        <div class="rounded-3xl bg-white/5 p-6 ring-1 ring-white/10 backdrop-blur transition hover:bg-white/10" data-reveal data-reveal-delay="{{ $loop->index * 120 }}">
                            <div class="flex items-center justify-between">
                                <span class="grid size-12 place-items-center rounded-2xl bg-brass-500/15 text-brass-300"><x-icon :name="$icon" /></span>
                                <span class="font-display text-3xl text-white/15">0{{ $loop->iteration }}</span>
                            </div>
                            <h3 class="mt-6 font-display text-xl">{{ $heading }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-ink-300">{{ $copy }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <x-advert-slot :adverts="$adverts" at="how_it_works:after" class="mt-20" />

    <x-advert-slot :adverts="$adverts" at="latest:before" class="mt-20" />
    {{-- Latest --}}
    <section class="mx-auto mt-28 max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col justify-between gap-6 sm:flex-row sm:items-end" data-reveal>
            <div>
                <p class="eyebrow">Just listed</p>
                <h2 class="mt-3 font-display text-4xl tracking-tight text-ink-950 sm:text-5xl">Latest on the market</h2>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('properties.index', ['listing' => 'sale']) }}" class="btn btn-outline">For sale</a>
                <a href="{{ route('properties.index', ['listing' => 'rent']) }}" class="btn btn-outline">For rent</a>
            </div>
        </div>

        @if ($latest->isEmpty())
            <x-empty-state class="mt-12" icon="home" title="New listings are on the way" description="Our team is preparing beautiful properties. Check back very soon." />
        @else
            <div class="mt-12 grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($latest as $property)
                    <x-property-card :property="$property" data-reveal data-reveal-delay="{{ ($loop->index % 3) * 100 }}" />
                @endforeach
            </div>
        @endif
    </section>

    <x-advert-slot :adverts="$adverts" at="latest:after" class="mt-20" />

    <x-advert-slot :adverts="$adverts" at="signup:before" class="mt-20" />
    {{-- CTA --}}
    @guest
        <section class="mx-auto mt-28 max-w-7xl px-4 sm:px-6 lg:px-8" data-reveal>
            <div class="grid grid-cols-1 items-center gap-10 rounded-[2.5rem] bg-gradient-to-br from-brass-100 via-sand to-ivory p-10 ring-1 ring-brass-200/60 sm:p-14 lg:grid-cols-2">
                <div>
                    <h2 class="font-display text-4xl tracking-tight text-ink-950">Never miss an update on a home you love.</h2>
                    <p class="mt-4 max-w-md text-ink-600">Create a free account to see every property you've engaged with, follow its progress step by step and get notified by email the moment anything changes.</p>
                </div>
                <div class="flex flex-wrap gap-3 lg:justify-end">
                    <a href="{{ route('register') }}" class="btn btn-primary btn-lg">Create free account <x-icon name="arrow-right" class="size-4" /></a>
                    <a href="{{ route('login') }}" class="btn btn-outline btn-lg">Sign in</a>
                </div>
            </div>
        </section>
    @endguest

    <x-advert-slot :adverts="$adverts" at="signup:after" class="mt-20" />
</x-layouts.app>
