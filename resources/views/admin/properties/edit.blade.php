<x-layouts.admin :title="$property->title" :subtitle="$property->engagements_count.' '.str('engagement')->plural($property->engagements_count).' · Listed '.$property->created_at->format('M j, Y')">
    <x-slot:actions>
        <a href="{{ route('admin.properties.index') }}" class="btn btn-ghost hidden sm:inline-flex"><x-icon name="arrow-left" class="size-4" /> Back</a>
        @if ($property->status->isPublic())
            <a href="{{ route('properties.show', $property) }}" target="_blank" class="btn btn-outline"><x-icon name="eye" class="size-4" /> <span class="hidden sm:inline">View</span></a>
        @endif
    </x-slot:actions>

    <form method="POST" action="{{ route('admin.properties.update', $property) }}" enctype="multipart/form-data" class="animate-fade-up">
        @csrf
        @method('PUT')
        @include('admin.properties._form')
    </form>

    {{-- Image action forms live outside the main form (HTML forms can't nest). --}}
    @foreach ($property->images as $image)
        <form id="cover-image-{{ $image->id }}" method="POST" action="{{ route('admin.properties.images.cover', [$property, $image]) }}" hidden>
            @csrf @method('PATCH')
        </form>
        <form id="delete-image-{{ $image->id }}" method="POST" action="{{ route('admin.properties.images.destroy', [$property, $image]) }}" data-confirm="Remove this photo?" hidden>
            @csrf @method('DELETE')
        </form>
    @endforeach

    <section class="mt-10 flex flex-col items-start justify-between gap-4 rounded-3xl border border-rose-200 bg-rose-50/50 p-6 sm:flex-row sm:items-center">
        <div>
            <h2 class="font-semibold text-rose-900">Delete this property</h2>
            <p class="mt-1 text-sm text-rose-700">This removes the listing, its photos and all {{ $property->engagements_count }} engagements. This cannot be undone.</p>
        </div>
        <form method="POST" action="{{ route('admin.properties.destroy', $property) }}" data-confirm="Delete “{{ $property->title }}” permanently?">
            @csrf @method('DELETE')
            <button class="btn btn-danger"><x-icon name="trash" class="size-4" /> Delete property</button>
        </form>
    </section>
</x-layouts.admin>
