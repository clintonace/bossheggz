<x-layouts.admin :title="$article->title" :subtitle="$article->statusLabel().' · '.$article->category->label()">
    <x-slot:actions>
        <a href="{{ route('admin.articles.index') }}" class="btn btn-ghost hidden sm:inline-flex"><x-icon name="arrow-left" class="size-4" /> Back</a>
        @if ($article->isPublished())
            <a href="{{ route('insights.show', $article) }}" target="_blank" class="btn btn-outline"><x-icon name="eye" class="size-4" /> <span class="hidden sm:inline">View</span></a>
        @endif
    </x-slot:actions>

    <form method="POST" action="{{ route('admin.articles.update', $article) }}" enctype="multipart/form-data" class="animate-fade-up">
        @csrf
        @method('PUT')
        @include('admin.articles._form')
    </form>

    <section class="mt-10 flex flex-col items-start justify-between gap-4 rounded-3xl border border-rose-200 bg-rose-50/50 p-6 sm:flex-row sm:items-center">
        <div>
            <h2 class="font-semibold text-rose-900">Delete this article</h2>
            <p class="mt-1 text-sm text-rose-700">It will be removed from the Insights page. This cannot be undone.</p>
        </div>
        <form method="POST" action="{{ route('admin.articles.destroy', $article) }}" data-confirm="Delete “{{ $article->title }}” permanently?">
            @csrf @method('DELETE')
            <button class="btn btn-danger"><x-icon name="trash" class="size-4" /> Delete article</button>
        </form>
    </section>
</x-layouts.admin>
