<x-layouts.admin title="Engagements" subtitle="Everyone who has shown interest in your properties.">
    {{-- Status tabs --}}
    <div class="no-scrollbar -mx-4 flex animate-fade-up gap-2 overflow-x-auto px-4 pb-1 sm:mx-0 sm:px-0">
        <a href="{{ route('admin.engagements.index', request()->except(['status', 'page'])) }}" @class([
            'inline-flex shrink-0 items-center gap-2 rounded-full px-4 py-2 text-sm font-medium transition',
            'bg-ink-900 text-white' => ! $status,
            'bg-white text-ink-600 ring-1 ring-ink-100 hover:text-ink-900' => $status,
        ])>All <span class="text-xs opacity-60">{{ $total }}</span></a>
        @foreach (\App\Enums\EngagementStatus::cases() as $case)
            <a href="{{ route('admin.engagements.index', [...request()->except(['status', 'page']), 'status' => $case->value]) }}" @class([
                'inline-flex shrink-0 items-center gap-2 rounded-full px-4 py-2 text-sm font-medium transition',
                'bg-ink-900 text-white' => $status === $case,
                'bg-white text-ink-600 ring-1 ring-ink-100 hover:text-ink-900' => $status !== $case,
            ])>{{ $case->label() }} <span class="text-xs opacity-60">{{ (int) $statusCounts->get($case->value, 0) }}</span></a>
        @endforeach
    </div>

    <form method="GET" class="mt-5 flex animate-fade-up flex-wrap items-center gap-3 [animation-delay:60ms]">
        @if ($status)
            <input type="hidden" name="status" value="{{ $status->value }}">
        @endif
        <label class="flex min-w-64 flex-1 items-center gap-3 rounded-full bg-white px-4 py-2.5 ring-1 ring-ink-100 focus-within:ring-brass-300">
            <x-icon name="search" class="size-4 text-ink-300" />
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search name, email or property" class="w-full bg-transparent text-sm focus:outline-none">
        </label>
        <select name="audience" onchange="this.form.requestSubmit()" class="rounded-full border-0 bg-white px-4 py-2.5 text-sm ring-1 ring-ink-100 focus:ring-brass-300 focus:outline-none">
            <option value="">Guests &amp; members</option>
            <option value="guest" @selected(request('audience') === 'guest')>Guests only (manual follow-up)</option>
            <option value="registered" @selected(request('audience') === 'registered')>Members only (auto updates)</option>
        </select>
        <button class="btn btn-outline">Search</button>
    </form>

    @if ($engagements->isEmpty())
        <x-empty-state class="mt-8" icon="inbox" title="No engagements here" description="When visitors engage with a property, they will show up in this list." />
    @else
        <div class="mt-6 animate-fade-up overflow-hidden rounded-3xl bg-white shadow-soft ring-1 ring-ink-100 [animation-delay:120ms]">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-ink-100 bg-ivory/60 text-xs tracking-wider text-ink-400 uppercase">
                        <tr>
                            <th class="px-6 py-4 font-semibold">Engager</th>
                            <th class="px-4 py-4 font-semibold">Property</th>
                            <th class="px-4 py-4 font-semibold">Stage</th>
                            <th class="px-4 py-4 font-semibold">Received</th>
                            <th class="px-6 py-4"><span class="sr-only">Open</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-50">
                        @foreach ($engagements as $engagement)
                            <tr class="group relative transition hover:bg-ivory/60">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <span @class(['grid size-10 shrink-0 place-items-center rounded-full text-sm font-semibold', 'bg-ink-100 text-ink-600' => $engagement->isGuest(), 'bg-ink-900 text-brass-200' => ! $engagement->isGuest()])>{{ str($engagement->name)->substr(0, 1)->upper() }}</span>
                                        <div class="min-w-0">
                                            <a href="{{ route('admin.engagements.show', $engagement) }}" class="flex items-center gap-2 font-semibold text-ink-900 after:absolute after:inset-0">
                                                {{ $engagement->name }}
                                                @if ($engagement->isGuest())
                                                    <span class="rounded-md bg-ink-50 px-1.5 py-0.5 text-[10px] font-semibold tracking-wide text-ink-500 uppercase">Guest</span>
                                                @else
                                                    <span class="rounded-md bg-brass-50 px-1.5 py-0.5 text-[10px] font-semibold tracking-wide text-brass-700 uppercase">Member</span>
                                                @endif
                                            </a>
                                            <p class="truncate text-xs text-ink-400">{{ $engagement->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <x-property-image :src="$engagement->property->cover_url" class="size-10 shrink-0 rounded-lg" />
                                        <span class="max-w-56 truncate text-ink-700">{{ $engagement->property->title }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-4"><x-status-badge :status="$engagement->status" /></td>
                                <td class="px-4 py-4 text-xs whitespace-nowrap text-ink-400">{{ $engagement->created_at->format('M j, Y') }}<span class="block">{{ $engagement->created_at->diffForHumans() }}</span></td>
                                <td class="px-6 py-4 text-right"><x-icon name="chevron-right" class="inline size-4 text-ink-300 transition group-hover:translate-x-0.5 group-hover:text-ink-700" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-6">{{ $engagements->links('pagination') }}</div>
    @endif
</x-layouts.admin>
