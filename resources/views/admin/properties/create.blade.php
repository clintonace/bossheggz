<x-layouts.admin title="New property" subtitle="Add a home for sale or rent.">
    <x-slot:actions>
        <a href="{{ route('admin.properties.index') }}" class="btn btn-ghost"><x-icon name="arrow-left" class="size-4" /> Back</a>
    </x-slot:actions>

    <form method="POST" action="{{ route('admin.properties.store') }}" enctype="multipart/form-data" class="animate-fade-up">
        @csrf
        @include('admin.properties._form')
    </form>
</x-layouts.admin>
