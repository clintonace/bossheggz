@php($isLand = $opportunity->isLand())

<x-layouts.admin :title="$isLand ? 'New land opportunity' : 'New investment opportunity'" subtitle="Present the structure, timeline, documentation and risks clearly.">
    <x-slot:actions>
        <a href="{{ route('admin.opportunities.index', ['category' => $isLand ? 'land_banking' : 'investments']) }}" class="btn btn-ghost"><x-icon name="arrow-left" class="size-4" /> Back</a>
    </x-slot:actions>

    <form method="POST" action="{{ route('admin.opportunities.store') }}" enctype="multipart/form-data" class="animate-fade-up">
        @csrf
        @include('admin.opportunities._form')
    </form>
</x-layouts.admin>
