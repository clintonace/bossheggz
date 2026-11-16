<x-layouts.admin title="New advert" subtitle="Promote a partner, service or offer on the website.">
    <x-slot:actions>
        <a href="{{ route('admin.adverts.index') }}" class="btn btn-ghost"><x-icon name="arrow-left" class="size-4" /> Back</a>
    </x-slot:actions>

    <form method="POST" action="{{ route('admin.adverts.store') }}" enctype="multipart/form-data" data-advert-form class="animate-fade-up">
        @csrf
        @include('admin.adverts._form')
    </form>
</x-layouts.admin>
