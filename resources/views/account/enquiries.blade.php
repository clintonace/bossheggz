@php
    $query = fn (array $changes) => array_filter([...['area' => $area?->value, 'filter' => $filter === 'all' ? null : $filter], ...$changes]);
@endphp

<x-layouts.client :title="$area ? $area->label().' enquiries' : 'My enquiries'" subtitle="Your land banking, investment, property and adviser enquiries, and every update from our team.">
    <x-slot:actions>
        <a href="{{ $area?->newEnquiryUrl() ?? route('contact', ['type' => 'consultation']) }}" class="btn btn-primary hidden sm:inline-flex"><x-icon name="plus" class="size-4" /> New enquiry</a>
    </x-slot:actions>

    {{-- Interest areas --}}
    <div class="grid animate-fade-up grid-cols-2 gap-3 lg:grid-cols-5">
        <a href="{{ route('account.enquiries.index', $query(['area' => null])) }}" @class([
            'group relative isolate flex items-center gap-3 overflow-hidden rounded-2xl p-4 ring-1 backdrop-blur-xl transition',
            'bg-ink-900 text-white ring-ink-900 shadow-lift' => ! $area,
            'bg-white/70 text-ink-800 ring-ink-100 hover:bg-white hover:shadow-soft' => $area,
        ])>
            <span @class(['grid size-10 shrink-0 place-items-center rounded-xl ring-1', 'bg-white/10 text-brass-300 ring-white/15' => ! $area, 'bg-ink-50 text-ink-500 ring-ink-100' => $area])><x-icon name="inbox" class="size-5" /></span>
            <span class="min-w-0">
                <span class="block truncate text-sm font-semibold">All enquiries</span>
                <span @class(['text-xs', 'text-ink-300' => ! $area, 'text-ink-400' => $area])>{{ $total }} in total</span>
            </span>
        </a>
        @foreach (\App\Enums\InterestArea::cases() as $case)
            <a href="{{ route('account.enquiries.index', $query(['area' => $case->value])) }}" @class([
                'group relative isolate flex items-center gap-3 overflow-hidden rounded-2xl p-4 ring-1 backdrop-blur-xl transition',
                'bg-ink-900 text-white ring-ink-900 shadow-lift' => $area === $case,
                'bg-white/70 text-ink-800 ring-ink-100 hover:bg-white hover:shadow-soft' => $area !== $case,
            ])>
                <span @class(['grid size-10 shrink-0 place-items-center rounded-xl ring-1', 'bg-white/10 text-brass-300 ring-white/15' => $area === $case, $case->tileClasses() => $area !== $case])><x-icon :name="$case->icon()" class="size-5" /></span>
                <span class="min-w-0">
                    <span class="block truncate text-sm font-semibold">{{ $case->label() }}</span>
                    <span @class(['text-xs', 'text-ink-300' => $area === $case, 'text-ink-400' => $area !== $case])>{{ $areaCounts[$case->value] }} {{ str('enquiry')->plural($areaCounts[$case->value]) }}</span>
                </span>
            </a>
        @endforeach
    </div>

    {{-- Status tabs --}}
    <div class="mt-6 flex animate-fade-up flex-wrap gap-2 [animation-delay:60ms]">
        @foreach (['all' => 'All', 'open' => 'In progress', 'completed' => 'Completed'] as $key => $label)
            <a href="{{ route('account.enquiries.index', $query(['filter' => $key === 'all' ? null : $key])) }}" @class([
                'inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-medium transition',
                'bg-ink-900 text-white' => $filter === $key,
                'bg-white text-ink-600 ring-1 ring-ink-100 hover:text-ink-900' => $filter !== $key,
            ])>{{ $label }} <span class="text-xs opacity-60">{{ $counts[$key] }}</span></a>
        @endforeach
    </div>

    @if ($enquiries->isEmpty())
        <x-empty-state class="mt-8" :icon="$area?->icon() ?? 'message'"
            :title="$total === 0 ? 'You haven\'t sent an enquiry yet' : 'Nothing in this list'"
            :description="$total === 0 ? 'Ask about land banking, an investment opportunity or a property while signed in, and you can follow every update from our advisers right here.' : 'Try another area or tab to see the rest of your enquiries.'">
            <a href="{{ $area?->newEnquiryUrl() ?? route('contact', ['type' => 'consultation']) }}" class="btn btn-accent">Speak to an adviser</a>
            <a href="{{ $area?->browseUrl() ?? route('land-banking.index') }}" class="btn btn-outline">{{ $area?->browseLabel() ?? 'Explore land banking' }}</a>
        </x-empty-state>
    @else
        <div class="mt-6 space-y-4">
            @foreach ($enquiries as $enquiry)
                @php($enquiryArea = $enquiry->area())
                <a href="{{ route('account.enquiries.show', $enquiry) }}" class="group grid grid-cols-1 gap-5 rounded-3xl bg-white/80 p-4 shadow-soft ring-1 ring-ink-100 backdrop-blur-xl transition duration-300 hover:-translate-y-0.5 hover:bg-white hover:shadow-lift sm:p-5 md:grid-cols-[11rem_1fr]" data-reveal data-reveal-delay="{{ min($loop->index, 4) * 70 }}">
                    @if ($image = $enquiry->subjectImageUrl())
                        <x-property-image :src="$image" :alt="$enquiry->subject()" class="aspect-[16/10] rounded-2xl md:aspect-auto md:h-full" />
                    @else
                        <span class="relative isolate grid aspect-[16/10] place-items-center overflow-hidden rounded-2xl bg-gradient-to-br from-ink-900 to-ink-800 md:aspect-auto md:h-full" data-palette="normal">
                            <span class="absolute -top-8 -right-8 -z-10 size-28 rounded-full bg-brass-500/30 blur-2xl"></span>
                            <x-icon :name="$enquiryArea->icon()" class="size-9 text-brass-300" stroke="1.5" />
                        </span>
                    @endif
                    <div class="flex min-w-0 flex-col gap-4 py-1 md:pr-3">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="flex flex-wrap items-center gap-2 text-xs text-ink-400">
                                    <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 font-semibold ring-1 {{ $enquiryArea->tileClasses() }}"><x-icon :name="$enquiryArea->icon()" class="size-3" /> {{ $enquiryArea->label() }}</span>
                                    Sent {{ $enquiry->created_at->format('M j, Y') }}
                                </p>
                                <h2 class="mt-2 truncate font-display text-xl text-ink-900 group-hover:text-brass-700 sm:text-2xl">{{ $enquiry->subject() }}</h2>
                            </div>
                            <x-enquiry-status :status="$enquiry->status" />
                        </div>
                        <x-enquiry-tracker :status="$enquiry->status" compact />
                        <p class="flex items-start gap-2 text-sm text-ink-500">
                            <x-icon name="message" class="mt-0.5 size-4 shrink-0 text-brass-500" />
                            <span class="line-clamp-2">
                                @if ($enquiry->latestUpdate)
                                    <span class="font-medium text-ink-700">{{ $enquiry->latestUpdate->created_at->diffForHumans() }}:</span>
                                    {{ $enquiry->latestUpdate->message ?: $enquiry->latestUpdate->status->description() }}
                                @else
                                    {{ $enquiry->status->description() }}
                                @endif
                            </span>
                        </p>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</x-layouts.client>
