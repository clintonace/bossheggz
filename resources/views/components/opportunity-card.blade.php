@props(['opportunity'])

@php($money = fn (?int $amount) => \App\Models\InvestmentOpportunity::money($amount) ?? 'On request')

<article {{ $attributes->merge(['class' => 'glass-card group relative flex flex-col overflow-hidden']) }}>
    <x-property-image :src="$opportunity->image_url" :alt="$opportunity->title" class="aspect-[16/10]">
        <div class="absolute inset-x-0 top-0 flex flex-wrap items-start gap-2 p-4">
            <span class="rounded-full bg-ink-900/75 px-3 py-1 text-[11px] font-semibold tracking-wider text-white uppercase backdrop-blur-md">
                {{ $opportunity->isLand() && $opportunity->land_use ? $opportunity->land_use->label() : $opportunity->category->label() }}
            </span>
            <span @class([
                'rounded-full px-3 py-1 text-[11px] font-semibold backdrop-blur-md',
                'bg-white/85 text-emerald-700' => $opportunity->acceptsEnquiries(),
                'bg-white/85 text-rose-700' => ! $opportunity->acceptsEnquiries(),
            ])>{{ $opportunity->status->label() }}</span>
        </div>
    </x-property-image>

    <div class="flex flex-1 flex-col p-5 sm:p-6">
        <h3 class="font-display text-xl leading-snug text-ink-900">
            <a href="{{ $opportunity->url() }}" class="after:absolute after:inset-0">{{ $opportunity->title }}</a>
        </h3>
        <p class="mt-1.5 flex items-center gap-1.5 text-sm text-ink-400">
            <x-icon name="map-pin" class="size-3.5 shrink-0" />
            <span class="truncate">{{ $opportunity->full_location }}{{ $opportunity->isLand() && $opportunity->access ? ' · '.$opportunity->access : '' }}</span>
        </p>

        <dl class="mt-4">
            @if ($opportunity->isLand())
                <div class="detail-row"><dt class="shrink-0 text-ink-400">Land sizes</dt><dd class="text-right font-semibold text-ink-900">{{ $opportunity->land_sizes ?? '—' }}</dd></div>
                <div class="detail-row"><dt class="shrink-0 text-ink-400">Acquisition price</dt><dd class="text-right font-semibold text-ink-900">{{ $money($opportunity->acquisition_price) }}</dd></div>
                <div class="detail-row"><dt class="shrink-0 text-ink-400">Estate / development</dt><dd class="text-right font-semibold text-ink-900">{{ $opportunity->estate_status ?? '—' }}</dd></div>
                <div class="detail-row"><dt class="shrink-0 text-ink-400">Documentation</dt><dd class="text-right font-semibold text-ink-900">{{ $opportunity->documentation ?? '—' }}</dd></div>
            @else
                <div class="detail-row"><dt class="shrink-0 text-ink-400">Required investment</dt><dd class="text-right font-semibold text-ink-900">{{ $money($opportunity->required_investment) }}</dd></div>
                <div class="detail-row"><dt class="shrink-0 text-ink-400">Minimum participation</dt><dd class="text-right font-semibold text-ink-900">{{ $money($opportunity->minimum_participation) }}</dd></div>
                <div class="detail-row"><dt class="shrink-0 text-ink-400">Timeline</dt><dd class="text-right font-semibold text-ink-900">{{ $opportunity->timeline ?? '—' }}</dd></div>
                <div class="detail-row"><dt class="shrink-0 text-ink-400">Documentation</dt><dd class="text-right font-semibold text-ink-900">{{ $opportunity->documentation ?? '—' }}</dd></div>
            @endif
            <div class="detail-row"><dt class="shrink-0 text-ink-400">Verification</dt><dd><x-status-badge :status="$opportunity->verification_status" /></dd></div>
        </dl>

        @if ($opportunity->isLand() && $opportunity->rationale)
            <p class="mt-3 text-sm leading-relaxed text-ink-500">{{ $opportunity->rationale }}</p>
        @endif

        <div class="mt-auto flex items-center justify-between pt-5">
            <span class="text-sm font-semibold text-ink-900">View opportunity</span>
            <span class="grid size-9 place-items-center rounded-full bg-ink-900/5 text-ink-600 transition duration-300 group-hover:bg-ink-900 group-hover:text-white">
                <x-icon name="arrow-right" class="size-4" />
            </span>
        </div>
    </div>
</article>
