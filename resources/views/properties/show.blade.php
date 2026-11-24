@php
    $images = $property->images->pluck('url')->values();
@endphp

<x-layouts.app :title="$property->title" :description="str($property->description)->limit(155)">
    <section class="mx-auto max-w-7xl px-4 pt-8 sm:px-6 lg:px-8">
        <x-advert-slot :adverts="$adverts" at="overview:before" :contained="false" class="mb-8" />

        {{-- Breadcrumb --}}
        <nav class="flex animate-fade-up items-center gap-2 text-sm text-ink-400" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="shrink-0 hover:text-ink-900">Home</a>
            <x-icon name="chevron-right" class="size-3.5" />
            <a href="{{ route('properties.index', ['listing' => $property->listing_type->value]) }}" class="shrink-0 whitespace-nowrap hover:text-ink-900">{{ $property->listing_type->label() }}</a>
            <x-icon name="chevron-right" class="size-3.5" />
            <span class="truncate text-ink-700">{{ $property->title }}</span>
        </nav>

        {{-- Title row --}}
        <div class="mt-6 flex flex-col justify-between gap-6 lg:flex-row lg:items-end">
            <div class="animate-fade-up [animation-delay:60ms]">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full bg-ink-900 px-3 py-1 text-[11px] font-semibold tracking-wider text-white uppercase">{{ $property->listing_type->label() }}</span>
                    <span class="rounded-full bg-white px-3 py-1 text-[11px] font-semibold tracking-wider text-ink-600 uppercase ring-1 ring-ink-100">{{ $property->property_type->label() }}</span>
                    <x-status-badge :status="$property->status" />
                    @if ($property->verification_status)
                        <x-status-badge :status="$property->verification_status" />
                    @endif
                </div>
                <h1 class="mt-4 max-w-3xl font-display text-4xl leading-tight tracking-tight text-ink-950 sm:text-5xl">{{ $property->title }}</h1>
                <p class="mt-3 flex items-center gap-2 text-ink-500"><x-icon name="map-pin" class="size-4 text-brass-500" /> {{ $property->address }}, {{ $property->location }}</p>
            </div>
            <div class="animate-fade-up text-left [animation-delay:120ms] lg:text-right">
                <p class="text-xs font-semibold tracking-wider text-ink-400 uppercase">{{ match ($property->listing_type) { \App\Enums\ListingType::Sale => 'Asking price', \App\Enums\ListingType::Shortlet => 'Nightly rate', \App\Enums\ListingType::Lease => 'Lease', default => 'Rent' } }}</p>
                <p class="mt-1 font-display text-4xl text-ink-950">{{ $property->formatted_price }}</p>
            </div>
        </div>

        {{-- Gallery --}}
        <div class="mt-8 animate-fade-up [animation-delay:180ms]" data-gallery='@json($images)' data-lightbox="#lightbox">
            <div class="group relative overflow-hidden rounded-[2rem] bg-ink-100 shadow-lift">
                @if ($images->isNotEmpty())
                    <button type="button" data-gallery-open class="block w-full cursor-zoom-in" aria-label="Open gallery">
                        <img data-gallery-main src="{{ $images->first() }}" alt="{{ $property->title }}" class="aspect-[16/9] w-full object-cover transition-opacity duration-300 lg:aspect-[21/9]">
                    </button>
                    @if ($images->count() > 1)
                        <button type="button" data-gallery-prev class="absolute top-1/2 left-4 grid size-12 -translate-y-1/2 place-items-center rounded-full bg-white/90 text-ink-900 opacity-0 shadow-soft backdrop-blur transition group-hover:opacity-100 hover:bg-white" aria-label="Previous photo"><x-icon name="chevron-left" /></button>
                        <button type="button" data-gallery-next class="absolute top-1/2 right-4 grid size-12 -translate-y-1/2 place-items-center rounded-full bg-white/90 text-ink-900 opacity-0 shadow-soft backdrop-blur transition group-hover:opacity-100 hover:bg-white" aria-label="Next photo"><x-icon name="chevron-right" /></button>
                    @endif
                    <div class="absolute right-4 bottom-4 flex items-center gap-2">
                        <span data-gallery-counter class="rounded-full bg-ink-950/70 px-3 py-1.5 text-xs font-semibold text-white backdrop-blur">1 / {{ $images->count() }}</span>
                        <button type="button" data-gallery-open class="inline-flex items-center gap-2 rounded-full bg-white/95 px-4 py-1.5 text-xs font-semibold text-ink-900 backdrop-blur hover:bg-white"><x-icon name="image" class="size-4" /> View photos</button>
                    </div>
                @else
                    <x-property-image class="aspect-[16/9] lg:aspect-[21/9]" />
                @endif
            </div>

            @if ($images->count() > 1)
                <div class="no-scrollbar mt-4 flex gap-3 overflow-x-auto pb-1">
                    @foreach ($images as $image)
                        <button type="button" data-gallery-thumb class="relative h-20 w-28 shrink-0 overflow-hidden rounded-xl ring-2 ring-transparent ring-offset-2 ring-offset-ivory transition hover:opacity-100 sm:h-24 sm:w-36" aria-label="Show photo {{ $loop->iteration }}">
                            <img src="{{ $image }}" alt="" loading="lazy" class="size-full object-cover">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <x-advert-slot :adverts="$adverts" at="overview:after" :contained="false" class="mt-12" />

        <div class="mt-14 grid grid-cols-1 gap-12 lg:grid-cols-12">
            {{-- Details --}}
            <div class="lg:col-span-7 xl:col-span-8">
                <x-advert-slot :adverts="$adverts" at="about:before" :contained="false" class="mb-10" />

                <dl class="grid grid-cols-2 gap-4 sm:grid-cols-4" data-reveal>
                    @foreach (array_filter([
                        ['bed', 'Bedrooms', $property->bedrooms],
                        ['bath', 'Bathrooms', $property->bathrooms],
                        ['area', 'Floor area', $property->area_sqm ? number_format($property->area_sqm).' m²' : null],
                        ['building', 'Type', $property->property_type->label()],
                    ], fn ($item) => filled($item[2])) as [$icon, $label, $value])
                        <div class="glass rounded-2xl p-5">
                            <x-icon :name="$icon" class="size-5 text-brass-500" />
                            <dt class="mt-4 text-xs font-medium tracking-wider text-ink-400 uppercase">{{ $label }}</dt>
                            <dd class="mt-1 font-display text-2xl text-ink-900">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>

                <div class="mt-12" data-reveal>
                    <h2 class="font-display text-3xl text-ink-950">About this property</h2>
                    <div class="mt-5 space-y-4 text-[17px] leading-relaxed text-ink-600">
                        @foreach (preg_split('/\R{2,}/', trim($property->description)) as $paragraph)
                            <p>{!! nl2br(e($paragraph)) !!}</p>
                        @endforeach
                    </div>
                </div>

                @if (! empty($property->features))
                    <div class="mt-12" data-reveal>
                        <h2 class="font-display text-3xl text-ink-950">Features &amp; amenities</h2>
                        <ul class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2">
                            @foreach ($property->features as $feature)
                                <li class="glass flex items-center gap-3 rounded-2xl px-4 py-3.5 text-sm text-ink-700">
                                    <span class="grid size-7 place-items-center rounded-full bg-brass-50 text-brass-600"><x-icon name="check" class="size-3.5" stroke="2.5" /></span>
                                    {{ $feature }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="glass mt-12 flex items-center gap-4 rounded-3xl p-6" data-reveal>
                    <span class="grid size-12 place-items-center rounded-2xl bg-ink-900 text-brass-300"><x-icon name="map-pin" /></span>
                    <div class="min-w-0">
                        <p class="text-xs font-medium tracking-wider text-ink-400 uppercase">Location</p>
                        <p class="mt-0.5 truncate font-medium text-ink-900">{{ $property->address }}, {{ $property->location }}</p>
                    </div>
                    <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($property->address.', '.$property->location) }}" target="_blank" rel="noopener" class="btn btn-outline btn-sm ml-auto">Map <x-icon name="external" class="size-3.5" /></a>
                </div>

                <x-advert-slot :adverts="$adverts" at="about:after" :contained="false" class="mt-10" />
            </div>

            {{-- Engage card --}}
            <aside class="lg:col-span-5 xl:col-span-4">
                <div id="engage" class="glass-strong sticky top-24 scroll-mt-28 overflow-hidden rounded-[2rem]">
                    <div class="bg-ink-950 px-7 py-6 text-white">
                        <p class="text-xs font-semibold tracking-[0.2em] text-brass-300 uppercase">Interested?</p>
                        <h2 class="mt-2 font-display text-2xl">Engage with this property</h2>
                        <p class="mt-1.5 text-sm text-ink-300">Our team will reach out to arrange a viewing and answer your questions.</p>
                    </div>

                    <div class="p-7">
                        @if (! $property->acceptsEngagements())
                            <div class="rounded-2xl bg-rose-50 p-5 text-sm text-rose-700 ring-1 ring-rose-100">
                                <p class="font-semibold">This property is {{ strtolower($property->status->label()) }}.</p>
                                <p class="mt-1 text-rose-600">It's no longer accepting new engagements, but there are more homes waiting for you.</p>
                            </div>
                            <a href="{{ route('properties.index') }}" class="btn btn-primary mt-5 w-full">Browse available properties</a>
                        @elseif ($existingEngagement)
                            <div class="flex items-center gap-3">
                                <span class="grid size-10 place-items-center rounded-full bg-emerald-50 text-emerald-600"><x-icon name="check-circle" /></span>
                                <div>
                                    <p class="font-semibold text-ink-900">You're engaged</p>
                                    <p class="text-xs text-ink-400">Since {{ $existingEngagement->created_at->format('M j, Y') }}</p>
                                </div>
                                <x-status-badge :status="$existingEngagement->status" class="ml-auto" />
                            </div>
                            <div class="mt-6"><x-progress-tracker :status="$existingEngagement->status" compact /></div>
                            <a href="{{ route('account.engagements.show', $existingEngagement) }}" class="btn btn-primary mt-6 w-full">View progress <x-icon name="arrow-right" class="size-4" /></a>
                        @elseif (session('engaged_email'))
                            <div class="text-center">
                                <span class="mx-auto grid size-14 place-items-center rounded-full bg-emerald-50 text-emerald-600 ring-8 ring-emerald-50/50"><x-icon name="check" class="size-6" stroke="2.5" /></span>
                                <p class="mt-5 font-display text-2xl text-ink-900">Thank you!</p>
                                <p class="mt-2 text-sm text-ink-500">We've received your interest. Our team will reach out to <span class="font-semibold text-ink-800">{{ session('engaged_email') }}</span> shortly.</p>
                            </div>
                            <div class="mt-6 rounded-2xl bg-ivory p-5 ring-1 ring-ink-100">
                                <p class="text-sm font-semibold text-ink-900">Want to track your progress?</p>
                                <p class="mt-1 text-xs text-ink-500">Create a free account to follow every step and get updates by email.</p>
                                <a href="{{ route('register', ['return' => route('properties.show', $property, false)]) }}" class="btn btn-accent btn-sm mt-4">Create account</a>
                            </div>
                        @else
                            <form method="POST" action="{{ route('properties.engage', $property) }}#engage" class="space-y-4">
                                @csrf
                                @auth
                                    <div class="flex items-center gap-3 rounded-2xl bg-ivory p-3 ring-1 ring-ink-100">
                                        <span class="grid size-9 place-items-center rounded-full bg-ink-900 text-xs font-semibold text-brass-200">{{ str(auth()->user()->name)->substr(0, 1)->upper() }}</span>
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-semibold text-ink-900">{{ auth()->user()->name }}</p>
                                            <p class="truncate text-xs text-ink-400">{{ auth()->user()->email }}</p>
                                        </div>
                                    </div>
                                    <x-field name="phone" label="Phone (optional)" type="tel" :value="auth()->user()->phone" placeholder="Best number to reach you" />
                                @else
                                    <x-field name="name" label="Full name" required autocomplete="name" placeholder="Jane Doe" />
                                    <x-field name="email" label="Email" type="email" required autocomplete="email" placeholder="you@example.com" />
                                    <x-field name="phone" label="Phone (optional)" type="tel" autocomplete="tel" placeholder="Best number to reach you" />
                                @endauth
                                <x-field name="message" label="Message (optional)" textarea rows="3" placeholder="I'd like to arrange a viewing…" />

                                <button class="btn btn-accent btn-lg w-full" data-loading-text="Sending…">
                                    <x-icon name="sparkles" class="size-4" /> Engage now
                                </button>

                                @auth
                                    <p class="flex items-start gap-2 text-xs text-ink-400"><x-icon name="bell" class="size-4 shrink-0 text-brass-500" /> You'll get progress updates in your account and by email.</p>
                                @else
                                    <p class="flex items-start gap-2 text-xs text-ink-400"><x-icon name="lock" class="size-4 shrink-0 text-ink-300" /> We only use your details to contact you about this property.</p>
                                @endauth
                            </form>

                            <x-whatsapp-button class="btn-outline mt-3 w-full" :text="'Hello, I am interested in '.$property->title.' ('.route('properties.show', $property).')'" />
                            <a href="{{ route('contact', ['type' => 'consultation', 'property' => $property->slug]) }}#enquire" class="mt-3 block text-center text-sm font-semibold text-brass-700 hover:text-brass-600">Speak to an adviser about this property</a>

                            @guest
                                <div class="mt-6 rounded-2xl bg-gradient-to-br from-brass-50 to-ivory p-5 ring-1 ring-brass-100">
                                    <p class="text-sm font-semibold text-ink-900">Track every step</p>
                                    <p class="mt-1 text-xs leading-relaxed text-ink-500">Sign in to see all your engaged properties, follow their progress and receive updates by email.</p>
                                    <div class="mt-4 flex gap-2">
                                        <a href="{{ route('login', ['return' => route('properties.show', $property, false)]) }}" class="btn btn-primary btn-sm">Sign in</a>
                                        <a href="{{ route('register', ['return' => route('properties.show', $property, false)]) }}" class="btn btn-outline btn-sm">Create account</a>
                                    </div>
                                </div>
                            @endguest
                        @endif
                    </div>
                </div>
            </aside>
        </div>
    </section>

    <x-advert-slot :adverts="$adverts" at="similar:before" class="mt-20" />

    {{-- Similar --}}
    @if ($similar->isNotEmpty())
        <section class="mx-auto mt-28 max-w-7xl px-4 sm:px-6 lg:px-8">
            <div data-reveal>
                <p class="eyebrow">You may also like</p>
                <h2 class="mt-3 font-display text-4xl tracking-tight text-ink-950">Similar properties</h2>
            </div>
            <div class="mt-10 grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($similar as $item)
                    <x-property-card :property="$item" data-reveal data-reveal-delay="{{ $loop->index * 100 }}" />
                @endforeach
            </div>
        </section>
    @endif

    <x-advert-slot :adverts="$adverts" at="similar:after" class="mt-20" />

    {{-- Mobile engage bar --}}
    @if ($property->acceptsEngagements() && ! $existingEngagement && ! session('engaged_email'))
        <div class="fixed inset-x-0 bottom-0 z-30 border-t border-ink-100 bg-white/90 px-4 py-3 backdrop-blur-xl lg:hidden">
            <div class="flex items-center justify-between gap-4">
                <div class="min-w-0">
                    <p class="truncate text-xs text-ink-400">{{ $property->listing_type->label() }}</p>
                    <p class="truncate font-semibold text-ink-900">{{ $property->formatted_price }}</p>
                </div>
                <a href="#engage" class="btn btn-accent"><x-icon name="sparkles" class="size-4" /> Engage now</a>
            </div>
        </div>
    @endif

    {{-- Lightbox --}}
    <div id="lightbox" hidden class="fixed inset-0 z-[70] flex items-center justify-center bg-ink-950/95 p-4 backdrop-blur-sm">
        <button type="button" data-lightbox-close class="absolute top-5 right-5 grid size-12 place-items-center rounded-full bg-white/10 text-white hover:bg-white/20" aria-label="Close"><x-icon name="x" /></button>
        <span data-lightbox-counter class="absolute top-8 left-1/2 -translate-x-1/2 text-sm font-medium text-white/70"></span>
        <button type="button" data-lightbox-prev class="absolute left-4 grid size-12 place-items-center rounded-full bg-white/10 text-white hover:bg-white/20 sm:left-8" aria-label="Previous"><x-icon name="chevron-left" /></button>
        <img data-lightbox-image src="" alt="{{ $property->title }}" class="max-h-[85vh] max-w-[90vw] rounded-2xl object-contain shadow-2xl">
        <button type="button" data-lightbox-next class="absolute right-4 grid size-12 place-items-center rounded-full bg-white/10 text-white hover:bg-white/20 sm:right-8" aria-label="Next"><x-icon name="chevron-right" /></button>
    </div>
</x-layouts.app>
