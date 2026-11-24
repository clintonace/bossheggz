@php
    $management = $content['management'];
    $shortletSection = $content['shortlet'];
@endphp

<x-layouts.app :title="$content['meta']->value('title')" :description="$content['meta']->value('description')">
    <x-advert-slot :adverts="$adverts" at="hero:before" class="mt-6" />
    <x-page-hero :section="$content['hero']">
        <x-whatsapp-button class="btn-lg bg-white/10 text-white ring-1 ring-white/25 backdrop-blur hover:bg-white/20" :text="$content['hero']->value('whatsapp_message') ?: 'Hello, I\'d like to know more about your services.'" />
    </x-page-hero>
    <x-advert-slot :adverts="$adverts" at="hero:after" class="mt-12" />

    <x-advert-slot :adverts="$adverts" at="list:before" class="mt-20" />
    <section class="mx-auto mt-20 max-w-7xl px-4 sm:px-6 lg:px-8">
        <x-section-heading :section="$content['list']" size="md" data-reveal />
        <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach (\App\Enums\Service::cases() as $service)
                <div class="glass-card flex flex-col p-7" data-reveal data-reveal-delay="{{ ($loop->index % 3) * 90 }}">
                    <div class="flex items-center justify-between">
                        <span class="grid size-12 place-items-center rounded-2xl bg-ink-900 text-brass-300"><x-icon :name="$service->icon()" /></span>
                        <span class="font-display text-3xl text-ink-200">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <h3 class="mt-6 font-display text-xl text-ink-900">{{ $service->label() }}</h3>
                    <p class="mt-2 flex-1 text-sm leading-relaxed text-ink-500">{{ $service->description() }}</p>
                    <a href="{{ $service->url() }}" class="mt-5 inline-flex items-center gap-1.5 self-start border-b-2 border-brass-400 pb-0.5 text-sm font-semibold text-ink-900 hover:text-brass-700">Learn more <x-icon name="arrow-right" class="size-3.5" /></a>
                </div>
            @endforeach
        </div>
    </section>
    <x-advert-slot :adverts="$adverts" at="list:after" class="mt-16" />

    <x-advert-slot :adverts="$adverts" at="management:before" class="mt-20" />
    @if ($management->isShown() || $shortletSection->isShown())
    <section id="property-management" @class(['mx-auto mt-24 grid max-w-7xl scroll-mt-28 grid-cols-1 gap-12 px-4 sm:px-6 lg:px-8', 'lg:grid-cols-2' => $management->isShown() && $shortletSection->isShown()])>
        @if ($management->isShown())
        <div data-reveal>
            <x-section-heading :section="$management" size="md" />
            <ul class="mt-8 grid grid-cols-1 gap-3 sm:grid-cols-2">
                @foreach ($management->list('items') as $item)
                    <li class="glass flex items-center gap-3 rounded-2xl px-4 py-3.5 text-sm text-ink-700">
                        <span class="grid size-7 place-items-center rounded-full bg-brass-50 text-brass-600"><x-icon name="check" class="size-3.5" stroke="2.5" /></span>{{ $item }}
                    </li>
                @endforeach
            </ul>
            @if (filled($management->value('button_label')))
                <a href="{{ route('contact', ['type' => 'list_property']) }}" class="btn btn-primary btn-lg mt-8">{{ $management->value('button_label') }}</a>
            @endif
        </div>
        @endif

        @if ($shortletSection->isShown())
        <div data-reveal>
            <x-section-heading :section="$shortletSection" size="md" />
            @if ($shortlet)
                <div class="glass-strong mt-8 overflow-hidden rounded-[2rem]">
                    <x-property-image :src="$shortlet->cover_url" :alt="$shortlet->title" class="aspect-[16/9]" />
                    <div class="p-6 sm:p-7">
                        <span class="rounded-full bg-ink-900 px-3 py-1 text-[11px] font-semibold tracking-wider text-white uppercase">Shortlet</span>
                        <h3 class="mt-3 font-display text-2xl text-ink-900">{{ $shortlet->title }}</h3>
                        <p class="mt-1 text-sm text-ink-400">{{ $shortlet->location }}</p>
                        <dl class="mt-4">
                            @if ($shortlet->bedrooms)
                                <div class="detail-row"><dt class="text-ink-400">Bedrooms</dt><dd class="font-semibold text-ink-900">{{ $shortlet->bedrooms }}</dd></div>
                            @endif
                            @if (! empty($shortlet->features))
                                <div class="detail-row"><dt class="text-ink-400">Facilities</dt><dd class="text-right font-semibold text-ink-900">{{ collect($shortlet->features)->take(3)->implode(', ') }}</dd></div>
                            @endif
                            <div class="detail-row"><dt class="text-ink-400">Nightly rate</dt><dd class="font-semibold text-ink-900">{{ $shortlet->formatted_price }}</dd></div>
                            <div class="detail-row"><dt class="text-ink-400">Availability</dt><dd class="font-semibold text-ink-900">{{ $shortlet->status->label() }}</dd></div>
                        </dl>
                        <div class="mt-6 flex flex-wrap gap-3">
                            <a href="{{ route('properties.show', $shortlet) }}#engage" class="btn btn-primary">Booking Enquiry</a>
                            <x-whatsapp-button class="btn-outline" label="WhatsApp" :text="'Hello, I would like to book '.$shortlet->title.'.'" />
                        </div>
                    </div>
                </div>
            @else
                <div class="glass-strong mt-8 rounded-[2rem] p-8">
                    <span class="grid size-12 place-items-center rounded-2xl bg-brass-50 text-brass-600 ring-1 ring-brass-100"><x-icon name="calendar" /></span>
                    <p class="mt-5 font-display text-2xl text-ink-900">{{ $shortletSection->value('empty_title') }}</p>
                    @if (filled($shortletSection->value('empty_text')))
                        <p class="mt-2 text-sm text-ink-500">{{ $shortletSection->value('empty_text') }}</p>
                    @endif
                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="{{ route('contact', ['type' => 'shortlet_booking']) }}" class="btn btn-primary">Booking Enquiry</a>
                        <x-whatsapp-button class="btn-outline" label="WhatsApp" text="Hello, I'd like to book a shortlet." />
                    </div>
                </div>
            @endif
        </div>
        @endif
    </section>
    @endif
    <x-advert-slot :adverts="$adverts" at="management:after" class="mt-16" />

    <x-advert-slot :adverts="$adverts" at="cta:before" class="mt-20" />
    @if ($content['cta']->isShown())
        <x-cta-band class="mt-24" :title="$content['cta']->value('title')" :description="$content['cta']->value('description')" />
    @endif
    <x-advert-slot :adverts="$adverts" at="cta:after" class="mt-16" />
</x-layouts.app>
