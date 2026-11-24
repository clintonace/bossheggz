@php
    $isLandView = $category === \App\Enums\InvestmentCategory::LandBanking;
    $money = fn (?int $amount) => \App\Models\InvestmentOpportunity::money($amount) ?? '—';
@endphp

<x-layouts.admin :title="$isLandView ? 'Land Banking' : 'Investment opportunities'" :subtitle="$isLandView ? 'Land opportunities shown on the Land Banking page.' : 'Opportunities shown on the Investment Opportunities page.'">
    <x-slot:actions>
        <a href="{{ route('admin.opportunities.create', $isLandView ? ['category' => 'land_banking'] : []) }}" class="btn btn-primary"><x-icon name="plus" class="size-4" /> <span class="hidden sm:inline">{{ $isLandView ? 'New land opportunity' : 'New opportunity' }}</span></a>
    </x-slot:actions>

    <form method="GET" class="flex animate-fade-up flex-wrap items-center gap-3">
        <label class="flex min-w-64 flex-1 items-center gap-3 rounded-full bg-white px-4 py-2.5 ring-1 ring-ink-100 focus-within:ring-brass-300">
            <x-icon name="search" class="size-4 text-ink-300" />
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search title or location" class="w-full bg-transparent text-sm focus:outline-none">
        </label>
        <select name="category" onchange="this.form.requestSubmit()" class="rounded-full border-0 bg-white px-4 py-2.5 text-sm ring-1 ring-ink-100 focus:ring-brass-300 focus:outline-none">
            <option value="">All categories</option>
            <option value="investments" @selected(request('category') === 'investments')>All except land banking</option>
            @foreach (\App\Enums\InvestmentCategory::cases() as $case)
                <option value="{{ $case->value }}" @selected(request('category') === $case->value)>{{ $case->label() }}</option>
            @endforeach
        </select>
        <select name="status" onchange="this.form.requestSubmit()" class="rounded-full border-0 bg-white px-4 py-2.5 text-sm ring-1 ring-ink-100 focus:ring-brass-300 focus:outline-none">
            <option value="">All statuses</option>
            @foreach (\App\Enums\OpportunityStatus::cases() as $status)
                <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
        <button class="btn btn-outline">Filter</button>
    </form>

    @if ($opportunities->isEmpty())
        <x-empty-state class="mt-8" :icon="$isLandView ? 'leaf' : 'briefcase'" title="No opportunities found" description="Add an opportunity with its structure, documentation and risks to start receiving enquiries.">
            <a href="{{ route('admin.opportunities.create', $isLandView ? ['category' => 'land_banking'] : []) }}" class="btn btn-primary"><x-icon name="plus" class="size-4" /> New opportunity</a>
        </x-empty-state>
    @else
        <div class="mt-6 animate-fade-up overflow-hidden rounded-3xl bg-white shadow-soft ring-1 ring-ink-100 [animation-delay:80ms]">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-ink-100 bg-ivory/60 text-xs tracking-wider text-ink-400 uppercase">
                        <tr>
                            <th class="px-6 py-4 font-semibold">Opportunity</th>
                            <th class="px-4 py-4 font-semibold">{{ $isLandView ? 'Price from' : 'Required' }}</th>
                            <th class="px-4 py-4 font-semibold">Status</th>
                            <th class="px-4 py-4 font-semibold">Verification</th>
                            <th class="px-4 py-4 text-center font-semibold">Enquiries</th>
                            <th class="px-6 py-4"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-50">
                        @foreach ($opportunities as $opportunity)
                            <tr class="group transition hover:bg-ivory/60">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <x-property-image :src="$opportunity->image_url" class="h-14 w-20 shrink-0 rounded-xl" />
                                        <div class="min-w-0">
                                            <a href="{{ route('admin.opportunities.edit', $opportunity) }}" class="flex items-center gap-1.5 font-semibold text-ink-900 hover:text-brass-700">
                                                <span class="max-w-xs truncate">{{ $opportunity->title }}</span>
                                                @if ($opportunity->is_featured)
                                                    <x-icon name="star" class="size-3.5 fill-brass-400 text-brass-400" />
                                                @endif
                                            </a>
                                            <p class="mt-0.5 text-xs text-ink-400">{{ $opportunity->category->label() }} · {{ $opportunity->full_location }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 font-semibold whitespace-nowrap text-ink-900">{{ $money($opportunity->isLand() ? $opportunity->acquisition_price : $opportunity->required_investment) }}</td>
                                <td class="px-4 py-4"><x-status-badge :status="$opportunity->status" /></td>
                                <td class="px-4 py-4"><x-status-badge :status="$opportunity->verification_status" /></td>
                                <td class="px-4 py-4 text-center">
                                    <a href="{{ route('admin.enquiries.index', ['q' => $opportunity->title]) }}" class="inline-flex min-w-8 justify-center rounded-full bg-ink-50 px-2.5 py-1 text-xs font-semibold text-ink-700 hover:bg-ink-100">{{ $opportunity->enquiries_count }}</a>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-end gap-1">
                                        @if ($opportunity->status->isPublic())
                                            <a href="{{ $opportunity->url() }}" target="_blank" class="grid size-9 place-items-center rounded-lg text-ink-400 hover:bg-ink-50 hover:text-ink-900" title="View on site"><x-icon name="eye" class="size-4" /></a>
                                        @endif
                                        <a href="{{ route('admin.opportunities.edit', $opportunity) }}" class="grid size-9 place-items-center rounded-lg text-ink-400 hover:bg-ink-50 hover:text-ink-900" title="Edit"><x-icon name="pencil" class="size-4" /></a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-6">{{ $opportunities->links('pagination') }}</div>
    @endif
</x-layouts.admin>
