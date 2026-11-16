@props(['status', 'compact' => false])

@php
    $steps = \App\Enums\EngagementStatus::pipeline();
    $current = $status->step();
@endphp

@if ($status === \App\Enums\EngagementStatus::Cancelled)
    <div class="flex items-center gap-2 rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-700 ring-1 ring-rose-100">
        <x-icon name="x" class="size-4" /> {{ $status->description() }}
    </div>
@else
    <ol class="flex items-start" aria-label="Engagement progress">
        @foreach ($steps as $step)
            @php($reached = $step->step() <= $current)
            <li class="relative flex flex-1 flex-col items-center text-center">
                @unless ($loop->first)
                    <span @class([
                        'absolute top-3.5 right-1/2 h-0.5 w-full -translate-y-1/2',
                        'bg-brass-500' => $reached,
                        'bg-ink-100' => ! $reached,
                    ])></span>
                @endunless
                <span @class([
                    'relative z-10 grid size-7 place-items-center rounded-full text-xs font-semibold ring-4 ring-white transition',
                    'bg-brass-500 text-white' => $reached,
                    'bg-ink-100 text-ink-400' => ! $reached,
                    'shadow-[0_0_0_6px_rgb(184_138_65/0.18)]' => $step === $status,
                ])>
                    @if ($step->step() < $current || ($step === $status && $status === \App\Enums\EngagementStatus::Closed))
                        <x-icon name="check" class="size-3.5" stroke="2.5" />
                    @else
                        {{ $step->step() }}
                    @endif
                </span>
                @unless ($compact)
                    <span @class(['mt-2 px-1 text-[11px] leading-tight font-medium sm:text-xs', 'text-ink-900' => $reached, 'text-ink-400' => ! $reached])>
                        {{ $step->label() }}
                    </span>
                @endunless
            </li>
        @endforeach
    </ol>
@endif
