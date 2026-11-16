<x-layouts.admin title="Properties" subtitle="Everything you have listed for sale or rent.">
    <x-slot:actions>
        <a href="{{ route('admin.properties.create') }}" class="btn btn-primary"><x-icon name="plus" class="size-4" /> <span class="hidden sm:inline">New property</span></a>
    </x-slot:actions>

    <form method="GET" class="flex animate-fade-up flex-wrap items-center gap-3">
        <label class="flex min-w-64 flex-1 items-center gap-3 rounded-full bg-white px-4 py-2.5 ring-1 ring-ink-100 focus-within:ring-brass-300">
            <x-icon name="search" class="size-4 text-ink-300" />
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search title, city or address" class="w-full bg-transparent text-sm focus:outline-none">
        </label>
        <select name="status" onchange="this.form.requestSubmit()" class="rounded-full border-0 bg-white px-4 py-2.5 text-sm ring-1 ring-ink-100 focus:ring-brass-300 focus:outline-none">
            <option value="">All statuses</option>
            @foreach (\App\Enums\PropertyStatus::cases() as $status)
                <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
        <select name="listing" onchange="this.form.requestSubmit()" class="rounded-full border-0 bg-white px-4 py-2.5 text-sm ring-1 ring-ink-100 focus:ring-brass-300 focus:outline-none">
            <option value="">Sale &amp; rent</option>
            @foreach (\App\Enums\ListingType::cases() as $type)
                <option value="{{ $type->value }}" @selected(request('listing') === $type->value)>{{ $type->label() }}</option>
            @endforeach
        </select>
        <button class="btn btn-outline">Filter</button>
    </form>

    @if ($properties->isEmpty())
        <x-empty-state class="mt-8" icon="building" title="No properties found" description="Create your first listing to start receiving engagements.">
            <a href="{{ route('admin.properties.create') }}" class="btn btn-primary"><x-icon name="plus" class="size-4" /> New property</a>
        </x-empty-state>
    @else
        <div class="mt-6 animate-fade-up overflow-hidden rounded-3xl bg-white shadow-soft ring-1 ring-ink-100 [animation-delay:80ms]">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-ink-100 bg-ivory/60 text-xs tracking-wider text-ink-400 uppercase">
                        <tr>
                            <th class="px-6 py-4 font-semibold">Property</th>
                            <th class="px-4 py-4 font-semibold">Price</th>
                            <th class="px-4 py-4 font-semibold">Status</th>
                            <th class="px-4 py-4 text-center font-semibold">Engagements</th>
                            <th class="px-4 py-4 font-semibold">Listed</th>
                            <th class="px-6 py-4"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-50">
                        @foreach ($properties as $property)
                            <tr class="group transition hover:bg-ivory/60">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <x-property-image :src="$property->cover_url" class="h-14 w-20 shrink-0 rounded-xl" />
                                        <div class="min-w-0">
                                            <a href="{{ route('admin.properties.edit', $property) }}" class="flex items-center gap-1.5 font-semibold text-ink-900 hover:text-brass-700">
                                                <span class="max-w-xs truncate">{{ $property->title }}</span>
                                                @if ($property->is_featured)
                                                    <x-icon name="star" class="size-3.5 fill-brass-400 text-brass-400" />
                                                @endif
                                            </a>
                                            <p class="mt-0.5 text-xs text-ink-400">{{ $property->listing_type->label() }} · {{ $property->property_type->label() }} · {{ $property->location }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 font-semibold whitespace-nowrap text-ink-900">{{ $property->formatted_price }}</td>
                                <td class="px-4 py-4"><x-status-badge :status="$property->status" /></td>
                                <td class="px-4 py-4 text-center">
                                    <a href="{{ route('admin.engagements.index', ['q' => $property->title]) }}" class="inline-flex min-w-8 justify-center rounded-full bg-ink-50 px-2.5 py-1 text-xs font-semibold text-ink-700 hover:bg-ink-100">{{ $property->engagements_count }}</a>
                                </td>
                                <td class="px-4 py-4 text-xs whitespace-nowrap text-ink-400">{{ $property->created_at->format('M j, Y') }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-end gap-1">
                                        @if ($property->status->isPublic())
                                            <a href="{{ route('properties.show', $property) }}" target="_blank" class="grid size-9 place-items-center rounded-lg text-ink-400 hover:bg-ink-50 hover:text-ink-900" title="View on site"><x-icon name="eye" class="size-4" /></a>
                                        @endif
                                        <a href="{{ route('admin.properties.edit', $property) }}" class="grid size-9 place-items-center rounded-lg text-ink-400 hover:bg-ink-50 hover:text-ink-900" title="Edit"><x-icon name="pencil" class="size-4" /></a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-6">{{ $properties->links('pagination') }}</div>
    @endif
</x-layouts.admin>
