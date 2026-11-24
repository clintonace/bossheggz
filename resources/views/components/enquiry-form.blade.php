@props([
    'type' => \App\Models\EnquiryType::General,
    'types' => null,
    'title' => 'Send an Enquiry',
    'description' => null,
    'opportunities' => null,
    'selected' => null,
    'properties' => null,
    'selectedProperty' => null,
    'interest' => null,
    'whatsappText' => null,
])

@php
    $chooseType = $types !== null && $types->isNotEmpty();
    $currentType = $chooseType ? ($types->firstWhere('slug', old('type', $type)) ?? $types->first()) : \App\Models\EnquiryType::findBySlug($type);
    $whatsappNumbers = \App\Models\WhatsappNumber::options();
    $whatsappNumber = $currentType?->whatsappDigits() ?? \App\Models\WhatsappNumber::primaryNumber();
    $whatsappIntro = $whatsappText ?? 'Hello, I would like to make an enquiry.';
@endphp

{{-- Glass enquiry card. Posts to the shared enquiries endpoint and shows a thank-you state afterwards. --}}
<div id="enquire" {{ $attributes->merge(['class' => 'glass-strong scroll-mt-28 rounded-[2rem] p-6 sm:p-8']) }}>
    @if (session('enquiry_sent'))
        <div class="py-6 text-center">
            <span class="mx-auto grid size-14 place-items-center rounded-full bg-emerald-50 text-emerald-600 ring-8 ring-emerald-50/50"><x-icon name="check" class="size-6" stroke="2.5" /></span>
            <p class="mt-5 font-display text-2xl text-ink-900">Thank you!</p>
            <p class="mt-2 text-sm text-ink-500">Your enquiry has been received. An adviser will contact you at <span class="font-semibold text-ink-800">{{ session('enquiry_sent') }}</span> shortly.</p>
            <x-whatsapp-button class="btn-outline mt-6" :number="$whatsappNumber" :text="$whatsappText" label="Chat with us on WhatsApp" />
        </div>
    @else
        <h2 class="font-display text-2xl text-ink-900">{{ $title }}</h2>
        @if ($description)
            <p class="mt-1.5 text-sm text-ink-500">{{ $description }}</p>
        @endif

        <form method="POST" action="{{ route('enquiries.store') }}" class="mt-6 space-y-4">
            @csrf
            @if ($chooseType)
                <div>
                    <label for="enquiry-type" class="field-label">Enquiry type</label>
                    <select name="type" id="enquiry-type" class="field-input appearance-none">
                        @foreach ($types as $option)
                            <option value="{{ $option->slug }}" data-whatsapp="{{ $option->whatsappDigits() }}" @selected($currentType?->is($option))>{{ $option->name }}</option>
                        @endforeach
                    </select>
                    @error('type') <p class="field-error">{{ $message }}</p> @enderror
                </div>
            @else
                <input type="hidden" name="type" value="{{ $type }}">
            @endif

            @auth
                <div class="flex items-center gap-3 rounded-2xl bg-white/70 p-3 ring-1 ring-ink-100">
                    <span class="grid size-9 place-items-center rounded-full bg-ink-900 text-xs font-semibold text-brass-200">{{ str(auth()->user()->name)->substr(0, 1)->upper() }}</span>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-ink-900">{{ auth()->user()->name }}</p>
                        <p class="truncate text-xs text-ink-400">{{ auth()->user()->email }}</p>
                    </div>
                </div>
                <x-field name="phone" label="Telephone number" type="tel" :value="auth()->user()->phone" autocomplete="tel" />
            @else
                <x-field name="name" label="Full name" required autocomplete="name" />
                <x-field name="email" label="Email address" type="email" required autocomplete="email" />
                <x-field name="phone" label="Telephone number" type="tel" autocomplete="tel" />
            @endauth

            @if ($opportunities !== null)
                <div>
                    <label for="enquiry-opportunity" class="field-label">Opportunity of interest</label>
                    <select name="opportunity" id="enquiry-opportunity" class="field-input appearance-none">
                        <option value="">Not sure yet — advise me</option>
                        @foreach ($opportunities as $option)
                            <option value="{{ $option->slug }}" @selected(old('opportunity', $selected) === $option->slug)>{{ $option->title }}</option>
                        @endforeach
                    </select>
                    @error('opportunity') <p class="field-error">{{ $message }}</p> @enderror
                </div>
            @else
                @if ($properties !== null)
                    <div>
                        <label for="enquiry-property" class="field-label">Available property of interest</label>
                        <select name="property" id="enquiry-property" class="field-input appearance-none">
                            <option value="">None selected</option>
                            @foreach ($properties as $option)
                                <option value="{{ $option->slug }}" @selected(old('property', $selectedProperty) === $option->slug)>{{ $option->title }} — {{ $option->location }} · {{ $option->formatted_price }}</option>
                            @endforeach
                        </select>
                        @error('property') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                @endif
                <x-field name="interest" :label="$properties !== null ? 'Or describe what you are looking for' : 'Property or opportunity of interest (optional)'" :value="$interest" :placeholder="$properties !== null ? 'e.g. 3-bedroom flat in Lekki under ₦80m' : null" :hint="$properties !== null ? 'Choose a listed property, describe one in your own words, or both.' : null" />
            @endif

            <x-field name="message" label="Message" textarea rows="4" />

            <button class="btn btn-primary btn-lg w-full" data-loading-text="Sending…">Submit Enquiry</button>

            <div class="rounded-2xl bg-white/55 p-4 ring-1 ring-white/80">
                <p class="flex items-center gap-2 text-sm font-semibold text-ink-900"><x-icon name="whatsapp" class="size-4 text-emerald-600" /> Prefer WhatsApp?</p>
                <p class="mt-0.5 text-xs text-ink-400">We will open WhatsApp with the details above filled in.</p>
                @if ($whatsappNumbers->count() > 1)
                    <label for="enquiry-whatsapp-number" class="field-label mt-3">Send to</label>
                    <select id="enquiry-whatsapp-number" data-whatsapp-number class="field-input appearance-none">
                        @foreach ($whatsappNumbers as $option)
                            <option value="{{ $option->number }}" @selected($option->number === $whatsappNumber)>{{ $option->label }} · {{ $option->display }}</option>
                        @endforeach
                    </select>
                @endif
                <a href="{{ \App\Models\WhatsappNumber::linkTo($whatsappNumber, $whatsappIntro) }}" data-whatsapp-compose data-number="{{ $whatsappNumber }}" data-intro="{{ $whatsappIntro }}" target="_blank" rel="noopener" data-no-loader class="btn btn-outline mt-3 w-full">
                    <x-icon name="whatsapp" class="size-4" /> Send on WhatsApp
                </a>
            </div>

            <p class="flex items-start gap-2 text-xs text-ink-400"><x-icon name="lock" class="size-4 shrink-0 text-ink-300" /> We only use your details to respond to this enquiry.</p>
        </form>
    @endif
</div>
