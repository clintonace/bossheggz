@php
    $area = $enquiry->area();
    $image = $enquiry->subjectImageUrl();
    $subjectUrl = $enquiry->subjectUrl();
    $opportunity = $enquiry->enquirable instanceof \App\Models\InvestmentOpportunity ? $enquiry->enquirable : null;
    $property = $enquiry->enquirable instanceof \App\Models\Property ? $enquiry->enquirable : null;
    $whatsappText = 'Hello, I am following up on my enquiry about '.$enquiry->subject().'.';
    $opportunityPrice = $opportunity ? \App\Models\InvestmentOpportunity::money($opportunity->isLand() ? $opportunity->acquisition_price : $opportunity->minimum_participation) : null;
@endphp

<x-layouts.client title="Enquiry progress" :subtitle="$enquiry->subject()">
    <x-slot:actions>
        <a href="{{ route('account.enquiries.index', ['area' => $area->value]) }}" class="btn btn-ghost hidden sm:inline-flex"><x-icon name="arrow-left" class="size-4" /> {{ $area->label() }}</a>
    </x-slot:actions>

    <section class="max-w-6xl">
        @if ($enquiry->status === \App\Enums\EnquiryStatus::Converted)
            <div class="mb-6 flex animate-fade-up items-center gap-4 rounded-3xl bg-gradient-to-r from-emerald-50 to-ivory p-5 ring-1 ring-emerald-100">
                <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-emerald-500 text-white"><x-icon name="check-circle" /></span>
                <div>
                    <p class="font-display text-xl text-ink-900">This enquiry is complete</p>
                    <p class="text-sm text-ink-500">Thank you for choosing us. Your adviser is always a message away.</p>
                </div>
            </div>
        @endif

        {{-- Summary --}}
        <div @class(['grid animate-fade-up grid-cols-1 gap-8 overflow-hidden rounded-[2rem] bg-white/80 p-5 shadow-soft ring-1 ring-ink-100 backdrop-blur-xl [animation-delay:60ms] sm:p-6', 'md:grid-cols-[18rem_1fr]' => true])>
            @if ($image)
                <x-property-image :src="$image" :alt="$enquiry->subject()" class="aspect-[4/3] rounded-2xl" />
            @else
                <span data-palette="normal" class="relative isolate grid aspect-[4/3] place-items-center overflow-hidden rounded-2xl bg-gradient-to-br from-ink-900 to-ink-800">
                    <span class="absolute -top-10 -right-10 -z-10 size-40 rounded-full bg-brass-500/30 blur-3xl"></span>
                    <x-icon :name="$area->icon()" class="size-14 text-brass-300" stroke="1.25" />
                </span>
            @endif
            <div class="flex flex-col py-1">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 {{ $area->tileClasses() }}"><x-icon :name="$area->icon()" class="size-3.5" /> {{ $area->label() }}</span>
                    <x-enquiry-status :status="$enquiry->status" />
                    <span class="text-xs text-ink-400">Sent {{ $enquiry->created_at->format('F j, Y') }}</span>
                </div>
                <h1 class="mt-3 font-display text-3xl tracking-tight text-ink-950 sm:text-4xl">{{ $enquiry->subject() }}</h1>
                @if ($opportunity)
                    <p class="mt-2 flex items-center gap-2 text-sm text-ink-500"><x-icon name="map-pin" class="size-4 text-brass-500" /> {{ $opportunity->full_location }}</p>
                @elseif ($property)
                    <p class="mt-2 flex items-center gap-2 text-sm text-ink-500"><x-icon name="map-pin" class="size-4 text-brass-500" /> {{ $property->location }}</p>
                @else
                    <p class="mt-2 text-sm text-ink-500">{{ $enquiry->typeLabel() }}</p>
                @endif
                <div class="mt-auto flex flex-wrap items-center justify-between gap-4 pt-6">
                    @if ($opportunityPrice)
                        <p class="font-display text-2xl text-ink-900">{{ $opportunityPrice }} <span class="text-sm font-normal text-ink-400">{{ $opportunity->isLand() ? 'acquisition price' : 'minimum participation' }}</span></p>
                    @elseif ($property)
                        <p class="font-display text-2xl text-ink-900">{{ $property->formatted_price }}</p>
                    @else
                        <span></span>
                    @endif
                    <div class="flex flex-wrap gap-2">
                        @if ($subjectUrl)
                            <a href="{{ $subjectUrl }}" class="btn btn-outline btn-sm">View {{ $opportunity ? 'opportunity' : 'listing' }} <x-icon name="arrow-right" class="size-3.5" /></a>
                        @endif
                        <x-whatsapp-button class="btn-primary btn-sm" label="Message adviser" :text="$whatsappText" :number="$enquiry->enquiryType?->whatsappNumber?->number" />
                    </div>
                </div>
            </div>
        </div>

        {{-- Progress --}}
        <div class="card mt-8 animate-fade-up p-6 [animation-delay:120ms] sm:p-8">
            <h2 class="font-display text-2xl text-ink-900">Progress</h2>
            <p class="mt-1 text-sm text-ink-500">{{ $enquiry->status->description() }}</p>
            <div class="mt-8"><x-enquiry-tracker :status="$enquiry->status" /></div>
        </div>

        <div class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-3">
            {{-- Timeline --}}
            <div class="card p-6 sm:p-8 lg:col-span-2" data-reveal>
                <h2 class="font-display text-2xl text-ink-900">Updates from our team</h2>
                <ol class="relative mt-8 space-y-8 border-l border-ink-100 pl-8">
                    @foreach ($enquiry->updates as $update)
                        <li class="relative">
                            <span class="absolute top-0.5 -left-[41px] grid size-5 place-items-center rounded-full bg-white ring-4 ring-white">
                                <span @class(['size-2.5 rounded-full', 'bg-brass-500' => $loop->first, 'bg-ink-200' => ! $loop->first])></span>
                            </span>
                            <div class="flex flex-wrap items-center gap-2">
                                <x-enquiry-status :status="$update->status" />
                                <time class="text-xs text-ink-400" datetime="{{ $update->created_at->toIso8601String() }}">{{ $update->created_at->format('M j, Y · g:i A') }}</time>
                            </div>
                            @if ($update->message)
                                <div class="mt-3 rounded-2xl bg-ivory px-4 py-3 ring-1 ring-ink-100">
                                    <p class="text-xs font-semibold tracking-wide text-brass-600 uppercase">Your adviser</p>
                                    <p class="mt-1 text-sm leading-relaxed text-ink-700">{{ $update->message }}</p>
                                </div>
                            @else
                                <p class="mt-2 text-sm text-ink-500">{{ $update->status->description() }}</p>
                            @endif
                        </li>
                    @endforeach
                    <li class="relative">
                        <span class="absolute top-0.5 -left-[41px] grid size-5 place-items-center rounded-full bg-white ring-4 ring-white">
                            <span @class(['size-2.5 rounded-full', 'bg-brass-500' => $enquiry->updates->isEmpty(), 'bg-ink-200' => $enquiry->updates->isNotEmpty()])></span>
                        </span>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="pill bg-ink-50 text-ink-500 ring-ink-100">You sent this enquiry</span>
                            <time class="text-xs text-ink-400">{{ $enquiry->created_at->format('M j, Y · g:i A') }}</time>
                        </div>
                        @if ($enquiry->updates->isEmpty())
                            <p class="mt-2 text-sm text-ink-500">An adviser will be in touch shortly. You'll get an email and a notification here as soon as there's an update.</p>
                        @endif
                    </li>
                </ol>
            </div>

            {{-- What you sent --}}
            <aside class="space-y-6" data-reveal>
                <div class="card p-6">
                    <h2 class="font-display text-xl text-ink-900">Your enquiry</h2>
                    <dl class="mt-5 space-y-4 text-sm">
                        <div>
                            <dt class="text-xs font-semibold tracking-wider text-ink-400 uppercase">Type</dt>
                            <dd class="mt-1 text-ink-800">{{ $enquiry->typeLabel() }}</dd>
                        </div>
                        @if ($enquiry->interest)
                            <div>
                                <dt class="text-xs font-semibold tracking-wider text-ink-400 uppercase">Looking for</dt>
                                <dd class="mt-1 text-ink-800">{{ $enquiry->interest }}</dd>
                            </div>
                        @endif
                        @if ($enquiry->message)
                            <div>
                                <dt class="text-xs font-semibold tracking-wider text-ink-400 uppercase">Your message</dt>
                                <dd class="mt-1 rounded-2xl bg-ivory px-4 py-3 leading-relaxed whitespace-pre-line text-ink-700 ring-1 ring-ink-100">{{ $enquiry->message }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-xs font-semibold tracking-wider text-ink-400 uppercase">We'll reply to</dt>
                            <dd class="mt-1 text-ink-800">{{ $enquiry->email }}@if ($enquiry->phone) · {{ $enquiry->phone }}@endif</dd>
                        </div>
                    </dl>
                </div>

                <div data-palette="normal" class="relative isolate overflow-hidden rounded-3xl bg-ink-950 p-6 text-white">
                    <div class="absolute -top-12 -right-10 -z-10 size-40 rounded-full bg-brass-500/30 blur-3xl"></div>
                    <p class="text-xs font-semibold tracking-[0.2em] text-brass-300 uppercase">More {{ strtolower($area->label()) }}</p>
                    <p class="mt-2 font-display text-xl">Keep exploring while we work on this.</p>
                    <div class="mt-5 flex flex-wrap gap-2">
                        <a href="{{ $area->browseUrl() }}" class="btn btn-accent btn-sm">{{ $area->browseLabel() }}</a>
                        <a href="{{ $area->newEnquiryUrl() }}" class="btn btn-sm bg-white/10 text-white ring-1 ring-white/15 hover:bg-white/15">New enquiry</a>
                    </div>
                </div>
            </aside>
        </div>
    </section>
</x-layouts.client>
