<x-layouts.admin title="Insights" subtitle="Articles, guides and market updates.">
    <x-slot:actions>
        <a href="{{ route('admin.articles.create') }}" class="btn btn-primary"><x-icon name="plus" class="size-4" /> <span class="hidden sm:inline">New article</span></a>
    </x-slot:actions>

    <form method="GET" class="flex animate-fade-up flex-wrap items-center gap-3">
        <label class="flex min-w-64 flex-1 items-center gap-3 rounded-full bg-white px-4 py-2.5 ring-1 ring-ink-100 focus-within:ring-brass-300">
            <x-icon name="search" class="size-4 text-ink-300" />
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search title or author" class="w-full bg-transparent text-sm focus:outline-none">
        </label>
        <select name="category" onchange="this.form.requestSubmit()" class="rounded-full border-0 bg-white px-4 py-2.5 text-sm ring-1 ring-ink-100 focus:ring-brass-300 focus:outline-none">
            <option value="">All categories</option>
            @foreach (\App\Enums\ArticleCategory::cases() as $case)
                <option value="{{ $case->value }}" @selected(request('category') === $case->value)>{{ $case->label() }}</option>
            @endforeach
        </select>
        <select name="status" onchange="this.form.requestSubmit()" class="rounded-full border-0 bg-white px-4 py-2.5 text-sm ring-1 ring-ink-100 focus:ring-brass-300 focus:outline-none">
            <option value="">All statuses</option>
            @foreach (['published' => 'Published', 'scheduled' => 'Scheduled', 'draft' => 'Draft'] as $value => $label)
                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <button class="btn btn-outline">Filter</button>
    </form>

    @if ($articles->isEmpty())
        <x-empty-state class="mt-8" icon="book" title="No articles found" description="Write your first guide or market update for the Insights page.">
            <a href="{{ route('admin.articles.create') }}" class="btn btn-primary"><x-icon name="plus" class="size-4" /> New article</a>
        </x-empty-state>
    @else
        <div class="mt-6 animate-fade-up overflow-hidden rounded-3xl bg-white shadow-soft ring-1 ring-ink-100 [animation-delay:80ms]">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-ink-100 bg-ivory/60 text-xs tracking-wider text-ink-400 uppercase">
                        <tr>
                            <th class="px-6 py-4 font-semibold">Article</th>
                            <th class="px-4 py-4 font-semibold">Category</th>
                            <th class="px-4 py-4 font-semibold">Status</th>
                            <th class="px-4 py-4 font-semibold">Date</th>
                            <th class="px-6 py-4"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-50">
                        @foreach ($articles as $article)
                            <tr class="group transition hover:bg-ivory/60">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <x-property-image :src="$article->image_url" class="h-14 w-20 shrink-0 rounded-xl" />
                                        <div class="min-w-0">
                                            <a href="{{ route('admin.articles.edit', $article) }}" class="flex items-center gap-1.5 font-semibold text-ink-900 hover:text-brass-700">
                                                <span class="max-w-sm truncate">{{ $article->title }}</span>
                                                @if ($article->is_featured)
                                                    <x-icon name="star" class="size-3.5 fill-brass-400 text-brass-400" />
                                                @endif
                                            </a>
                                            <p class="mt-0.5 text-xs text-ink-400">By {{ $article->author_name }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-xs text-ink-500">{{ $article->category->label() }}</td>
                                <td class="px-4 py-4">
                                    <span @class([
                                        'pill',
                                        'bg-emerald-50 text-emerald-700 ring-emerald-200' => $article->isPublished(),
                                        'bg-violet-50 text-violet-700 ring-violet-200' => $article->isScheduled(),
                                        'bg-stone-100 text-stone-600 ring-stone-200' => ! $article->published_at,
                                    ])><span class="size-1.5 rounded-full bg-current opacity-70"></span> {{ $article->statusLabel() }}</span>
                                </td>
                                <td class="px-4 py-4 text-xs whitespace-nowrap text-ink-400">{{ $article->published_at?->format('M j, Y') ?? '—' }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-end gap-1">
                                        @if ($article->isPublished())
                                            <a href="{{ route('insights.show', $article) }}" target="_blank" class="grid size-9 place-items-center rounded-lg text-ink-400 hover:bg-ink-50 hover:text-ink-900" title="View on site"><x-icon name="eye" class="size-4" /></a>
                                        @endif
                                        <a href="{{ route('admin.articles.edit', $article) }}" class="grid size-9 place-items-center rounded-lg text-ink-400 hover:bg-ink-50 hover:text-ink-900" title="Edit"><x-icon name="pencil" class="size-4" /></a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-6">{{ $articles->links('pagination') }}</div>
    @endif
</x-layouts.admin>
