<x-layouts.admin title="Enquiries" subtitle="Contact, investment, land banking and consultation requests from the website.">
    <x-slot:actions>
        <a href="{{ route('admin.enquiry-types.index') }}" class="btn btn-ghost hidden lg:inline-flex"><x-icon name="sliders" class="size-4" /> Types</a>
        <a href="{{ route('admin.whatsapp-numbers.index') }}" class="btn btn-ghost hidden lg:inline-flex"><x-icon name="whatsapp" class="size-4" /> WhatsApp</a>
        <a href="{{ route('admin.enquiries.export', request()->only(['q', 'status', 'type'])) }}" data-no-loader class="btn btn-outline"><x-icon name="download" class="size-4" /> <span class="hidden sm:inline">Export CSV</span></a>
    </x-slot:actions>

    {{-- Status tabs --}}
    <div class="no-scrollbar flex animate-fade-up gap-1 overflow-x-auto rounded-full bg-white p-1 shadow-soft ring-1 ring-ink-100">
        <a href="{{ route('admin.enquiries.index', request()->except(['status', 'page'])) }}" @class(['rounded-full px-4 py-2 text-sm font-medium whitespace-nowrap transition', 'bg-ink-900 text-white' => ! request('status'), 'text-ink-500 hover:text-ink-900' => request('status')])>
            All <span class="ml-1 text-xs opacity-60">{{ $statusCounts->sum() }}</span>
        </a>
        @foreach (\App\Enums\EnquiryStatus::cases() as $status)
            <a href="{{ route('admin.enquiries.index', [...request()->except(['status', 'page']), 'status' => $status->value]) }}" @class(['rounded-full px-4 py-2 text-sm font-medium whitespace-nowrap transition', 'bg-ink-900 text-white' => request('status') === $status->value, 'text-ink-500 hover:text-ink-900' => request('status') !== $status->value])>
                {{ $status->label() }} <span class="ml-1 text-xs opacity-60">{{ (int) $statusCounts->get($status->value, 0) }}</span>
            </a>
        @endforeach
    </div>

    <form method="GET" class="mt-4 flex animate-fade-up flex-wrap items-center gap-3">
        @if (request('status'))
            <input type="hidden" name="status" value="{{ request('status') }}">
        @endif
        <label class="flex min-w-64 flex-1 items-center gap-3 rounded-full bg-white px-4 py-2.5 ring-1 ring-ink-100 focus-within:ring-brass-300">
            <x-icon name="search" class="size-4 text-ink-300" />
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search name, email or interest" class="w-full bg-transparent text-sm focus:outline-none">
        </label>
        <select name="type" onchange="this.form.requestSubmit()" class="rounded-full border-0 bg-white px-4 py-2.5 text-sm ring-1 ring-ink-100 focus:ring-brass-300 focus:outline-none">
            <option value="">All types</option>
            @foreach ($types as $type)
                <option value="{{ $type->slug }}" @selected(request('type') === $type->slug)>{{ $type->name }}</option>
            @endforeach
        </select>
        <button class="btn btn-outline">Filter</button>
    </form>

    @if ($enquiries->isEmpty())
        <x-empty-state class="mt-8" icon="mail" title="No enquiries found" description="Enquiries sent from the contact, investment and land banking pages will appear here." />
    @else
        <div class="mt-6 animate-fade-up overflow-hidden rounded-3xl bg-white shadow-soft ring-1 ring-ink-100 [animation-delay:80ms]">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-ink-100 bg-ivory/60 text-xs tracking-wider text-ink-400 uppercase">
                        <tr>
                            <th class="px-6 py-4 font-semibold">Enquirer</th>
                            <th class="px-4 py-4 font-semibold">Interest</th>
                            <th class="px-4 py-4 font-semibold">Type</th>
                            <th class="px-4 py-4 font-semibold">Status</th>
                            <th class="px-6 py-4 font-semibold">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-50">
                        @foreach ($enquiries as $enquiry)
                            <tr class="group relative transition hover:bg-ivory/60">
                                <td class="px-6 py-4">
                                    <a href="{{ route('admin.enquiries.show', $enquiry) }}" class="font-semibold text-ink-900 after:absolute after:inset-0 hover:text-brass-700">{{ $enquiry->name }}</a>
                                    <p class="mt-0.5 text-xs text-ink-400">{{ $enquiry->email }}{{ $enquiry->phone ? ' · '.$enquiry->phone : '' }}</p>
                                </td>
                                <td class="max-w-xs truncate px-4 py-4 text-ink-700">{{ $enquiry->subject() }}</td>
                                <td class="px-4 py-4 text-xs whitespace-nowrap text-ink-500">{{ $enquiry->typeLabel() }}</td>
                                <td class="px-4 py-4"><x-status-badge :status="$enquiry->status" /></td>
                                <td class="px-6 py-4 text-xs whitespace-nowrap text-ink-400">{{ $enquiry->created_at->format('M j, Y') }}<span class="block">{{ $enquiry->created_at->format('g:i A') }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-6">{{ $enquiries->links('pagination') }}</div>
    @endif
</x-layouts.admin>
