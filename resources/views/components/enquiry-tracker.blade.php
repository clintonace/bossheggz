@props(['status', 'compact' => false])

{{-- Client-facing progress of an enquiry: Received → Adviser in touch → Exploring options → Completed. --}}
@php($current = $status->step())

@if ($current === null)
    <div {{ $attributes->merge(['class' => 'flex items-center gap-2 rounded-xl bg-ink-50 px-4 py-3 text-sm text-ink-600 ring-1 ring-ink-100']) }}>
        <x-icon name="info" class="size-4 shrink-0" /> {{ $status->description() }}
    </div>
@else
    <ol {{ $attributes->merge(['class' => 'flex items-start']) }} aria-label="Enquiry progress">
        @foreach (\App\Enums\EnquiryStatus::clientSteps() as $step => $label)
            @php($reached = $step <= $current)
            <li class="relative flex flex-1 flex-col items-center text-center">
                @unless ($loop->first)
                    <span @class(['absolute top-3.5 right-1/2 h-0.5 w-full -translate-y-1/2', 'bg-brass-500' => $reached, 'bg-ink-100' => ! $reached])></span>
                @endunless
                <span @class([
                    'relative z-10 grid size-7 place-items-center rounded-full text-xs font-semibold ring-4 ring-white transition',
                    'bg-brass-500 text-white' => $reached,
                    'bg-ink-100 text-ink-400' => ! $reached,
                    'shadow-[0_0_0_6px_rgb(184_138_65/0.18)]' => $step === $current,
                ])>
                    @if ($step < $current || ($step === $current && $step === 4))
                        <x-icon name="check" class="size-3.5" stroke="2.5" />
                    @else
                        {{ $step }}
                    @endif
                </span>
                @unless ($compact)
                    <span @class(['mt-2 px-1 text-[11px] leading-tight font-medium sm:text-xs', 'text-ink-900' => $reached, 'text-ink-400' => ! $reached])>{{ $label }}</span>
                @endunless
            </li>
        @endforeach
    </ol>
@endif
