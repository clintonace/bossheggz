<x-layouts.admin :title="$type->name" :subtitle="$type->enquiries_count.' '.str('enquiry')->plural($type->enquiries_count).($type->is_system ? ' · Built-in type' : '')">
    <x-slot:actions>
        <a href="{{ route('admin.enquiry-types.index') }}" class="btn btn-ghost"><x-icon name="arrow-left" class="size-4" /> Enquiry types</a>
    </x-slot:actions>

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('admin.enquiry-types.update', $type) }}" class="card animate-fade-up p-6 sm:p-8">
            @csrf
            @method('PUT')
            @include('admin.enquiry-types._form')
            <div class="mt-6 flex justify-end">
                <button class="btn btn-primary" data-loading-text="Saving…"><x-icon name="check" class="size-4" /> Save changes</button>
            </div>
        </form>

        @if ($type->isDeletable())
            <section class="mt-6 flex flex-col items-start justify-between gap-4 rounded-3xl border border-rose-200 bg-rose-50/50 p-6 sm:flex-row sm:items-center">
                <div>
                    <h2 class="font-semibold text-rose-900">Delete this type</h2>
                    <p class="mt-1 text-sm text-rose-700">It has no enquiries yet, so it can be removed completely.</p>
                </div>
                <form method="POST" action="{{ route('admin.enquiry-types.destroy', $type) }}" data-confirm="Delete “{{ $type->name }}”?">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger"><x-icon name="trash" class="size-4" /> Delete</button>
                </form>
            </section>
        @endif
    </div>
</x-layouts.admin>
