@php
    $company = config('listings.company');
    $details = $content['details'];
    $whatsappMessage = $details->value('whatsapp_message') ?: "Hello, I'd like to speak to an adviser.";
@endphp

<x-layouts.app :title="$content['meta']->value('title')" :description="$content['meta']->value('description')">
    <x-page-hero :section="$content['hero']" />

    <section class="mx-auto mt-16 grid max-w-7xl grid-cols-1 gap-10 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
        <div class="space-y-4">
            <a href="tel:{{ $company['phone'] }}" class="glass flex items-center gap-4 rounded-3xl p-6 transition hover:bg-white/75" data-reveal>
                <span class="grid size-12 place-items-center rounded-2xl bg-ink-900 text-brass-300"><x-icon name="phone" /></span>
                <div>
                    <p class="text-xs font-semibold tracking-wider text-ink-400 uppercase">Telephone</p>
                    <p class="font-display text-xl text-ink-900">{{ $company['phone'] }}</p>
                </div>
            </a>
            <div class="glass rounded-3xl p-6" data-reveal>
                <p class="flex items-center gap-2 text-xs font-semibold tracking-wider text-ink-400 uppercase"><x-icon name="whatsapp" class="size-4 text-emerald-600" /> WhatsApp</p>
                <ul class="mt-3 divide-y divide-ink-100/80">
                    @forelse (\App\Models\WhatsappNumber::options() as $whatsappLine)
                        <li class="flex flex-wrap items-center justify-between gap-3 py-3 first:pt-0 last:pb-0">
                            <div>
                                <p class="font-display text-xl text-ink-900">{{ $whatsappLine->display }}</p>
                                <p class="text-xs text-ink-400">{{ $whatsappLine->label }}</p>
                            </div>
                            <x-whatsapp-button class="btn-primary btn-sm" :number="$whatsappLine->number" :text="$whatsappMessage" label="Chat" />
                        </li>
                    @empty
                        <li><x-whatsapp-button class="btn-primary" :text="$whatsappMessage" /></li>
                    @endforelse
                </ul>
            </div>
            <a href="mailto:{{ $company['email'] }}" class="glass flex items-center gap-4 rounded-3xl p-6 transition hover:bg-white/75" data-reveal>
                <span class="grid size-12 place-items-center rounded-2xl bg-brass-50 text-brass-600 ring-1 ring-brass-100"><x-icon name="mail" /></span>
                <div>
                    <p class="text-xs font-semibold tracking-wider text-ink-400 uppercase">Email</p>
                    <p class="font-display text-xl text-ink-900">{{ strtoupper($company['email']) }}</p>
                </div>
            </a>
            <a href="https://instagram.com/{{ $company['instagram'] }}" target="_blank" rel="noopener" data-no-loader class="glass flex items-center gap-4 rounded-3xl p-6 transition hover:bg-white/75" data-reveal>
                <span class="grid size-12 place-items-center rounded-2xl bg-brass-50 text-brass-600 ring-1 ring-brass-100"><x-icon name="instagram" /></span>
                <div>
                    <p class="text-xs font-semibold tracking-wider text-ink-400 uppercase">Instagram</p>
                    <p class="font-display text-xl text-ink-900">{{ '@'.$company['instagram'] }}</p>
                </div>
            </a>
            <div class="glass flex items-center gap-4 rounded-3xl p-6" data-reveal>
                <span class="grid size-12 place-items-center rounded-2xl bg-brass-50 text-brass-600 ring-1 ring-brass-100"><x-icon name="map-pin" /></span>
                <div>
                    <p class="text-xs font-semibold tracking-wider text-ink-400 uppercase">Office</p>
                    <p class="font-display text-xl text-ink-900">{{ $company['address'] ?: $details->value('office_fallback') }}</p>
                </div>
            </div>
            @if ($company['address'])
                <div class="glass overflow-hidden rounded-3xl p-2" data-reveal>
                    <iframe title="Office location" src="https://www.google.com/maps?q={{ urlencode($company['address']) }}&output=embed" class="h-64 w-full rounded-[1.25rem] border-0" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            @endif
        </div>

        <x-enquiry-form :type="$selectedType" :types="$types" :interest="$interest" :properties="$properties" :selected-property="$selectedProperty" :title="$content['form']->value('title')" :description="$content['form']->value('description')" :whatsapp-text="$whatsappMessage" class="self-start" />
    </section>
</x-layouts.app>
