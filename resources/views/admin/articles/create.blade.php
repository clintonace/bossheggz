<x-layouts.admin title="New article" subtitle="Guides, market updates and educational content for the Insights page.">
    <x-slot:actions>
        <a href="{{ route('admin.articles.index') }}" class="btn btn-ghost"><x-icon name="arrow-left" class="size-4" /> Back</a>
    </x-slot:actions>

    <form method="POST" action="{{ route('admin.articles.store') }}" enctype="multipart/form-data" class="animate-fade-up">
        @csrf
        @include('admin.articles._form')
    </form>
</x-layouts.admin>
