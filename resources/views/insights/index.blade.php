<x-layouts.app :title="$content['meta']->value('title')" :description="$content['meta']->value('description')">
    <x-page-hero :section="$content['hero']" />

    <section class="mx-auto mt-12 max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="no-scrollbar -mx-1 flex gap-2 overflow-x-auto px-1 pb-1" aria-label="Filter by category">
            <a href="{{ route('insights.index') }}" @class(['chip', 'chip-active' => ! $category, 'bg-white/50' => $category])>All</a>
            @foreach (\App\Enums\ArticleCategory::cases() as $case)
                <a href="{{ route('insights.index', ['category' => $case->value]) }}" @class(['chip', 'chip-active' => $category === $case, 'bg-white/50' => $category !== $case])>{{ $case->label() }}</a>
            @endforeach
        </div>

        @if ($featured)
            <article class="glass-strong group relative mt-10 grid grid-cols-1 overflow-hidden rounded-[2rem] lg:grid-cols-[1.2fr_1fr]" data-reveal>
                <x-property-image :src="$featured->image_url" :alt="$featured->title" class="aspect-[16/10] lg:aspect-auto lg:min-h-96" />
                <div class="flex flex-col justify-center p-8 sm:p-10">
                    <span class="self-start rounded-full bg-brass-50 px-2.5 py-1 text-[11px] font-semibold text-brass-700 ring-1 ring-brass-100">{{ $featured->category->label() }}</span>
                    <h2 class="mt-4 font-display text-3xl leading-tight text-ink-950 sm:text-4xl">
                        <a href="{{ route('insights.show', $featured) }}" class="after:absolute after:inset-0">{{ $featured->title }}</a>
                    </h2>
                    <p class="mt-3 text-sm text-ink-400">{{ $featured->author_name }} · {{ $featured->published_at->format('M j, Y') }} · {{ $featured->reading_minutes }} min read</p>
                    <p class="mt-4 text-ink-600">{{ $featured->excerpt }}</p>
                    <span class="btn btn-primary mt-7 self-start">Read Article <x-icon name="arrow-right" class="size-4" /></span>
                </div>
            </article>
        @endif

        @if ($articles->isEmpty() && ! $featured)
            <x-empty-state class="mt-10" icon="book" title="No articles here yet" description="Guides and market updates are on the way — check back soon.">
                @if ($category)
                    <a href="{{ route('insights.index') }}" class="btn btn-primary">View all insights</a>
                @endif
            </x-empty-state>
        @else
            <div class="mt-10 grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($articles as $article)
                    <x-article-card :article="$article" data-reveal data-reveal-delay="{{ ($loop->index % 3) * 90 }}" />
                @endforeach
            </div>
            <div class="mt-12">{{ $articles->links('pagination') }}</div>
        @endif
    </section>

    @if ($content['cta']->isShown())
        <x-cta-band class="mt-24" :title="$content['cta']->value('title')" :description="$content['cta']->value('description')">
            <a href="{{ route('contact', ['type' => 'consultation']) }}" class="btn btn-primary btn-lg">Speak to an Adviser <x-icon name="arrow-right" class="size-4" /></a>
        </x-cta-band>
    @endif
</x-layouts.app>
