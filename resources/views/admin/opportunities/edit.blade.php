<x-layouts.admin :title="$opportunity->title" :subtitle="$opportunity->category->label().' · '.$opportunity->enquiries_count.' '.str('enquiry')->plural($opportunity->enquiries_count).' · Added '.$opportunity->created_at->format('M j, Y')">
    <x-slot:actions>
        <a href="{{ route('admin.opportunities.index', ['category' => $opportunity->isLand() ? 'land_banking' : 'investments']) }}" class="btn btn-ghost hidden sm:inline-flex"><x-icon name="arrow-left" class="size-4" /> Back</a>
        @if ($opportunity->status->isPublic())
            <a href="{{ $opportunity->url() }}" target="_blank" class="btn btn-outline"><x-icon name="eye" class="size-4" /> <span class="hidden sm:inline">View</span></a>
        @endif
    </x-slot:actions>

    <form method="POST" action="{{ route('admin.opportunities.update', $opportunity) }}" enctype="multipart/form-data" class="animate-fade-up">
        @csrf
        @method('PUT')
        @include('admin.opportunities._form')
    </form>

    <section class="mt-10 flex flex-col items-start justify-between gap-4 rounded-3xl border border-rose-200 bg-rose-50/50 p-6 sm:flex-row sm:items-center">
        <div>
            <h2 class="font-semibold text-rose-900">Delete this opportunity</h2>
            <p class="mt-1 text-sm text-rose-700">Its enquiries are kept, but they will no longer link to it. This cannot be undone.</p>
        </div>
        <form method="POST" action="{{ route('admin.opportunities.destroy', $opportunity) }}" data-confirm="Delete “{{ $opportunity->title }}” permanently?">
            @csrf @method('DELETE')
            <button class="btn btn-danger"><x-icon name="trash" class="size-4" /> Delete opportunity</button>
        </form>
    </section>
</x-layouts.admin>
