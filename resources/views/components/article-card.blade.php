@props(['article'])

<article {{ $attributes->merge(['class' => 'glass-card group relative flex flex-col overflow-hidden']) }}>
    <x-property-image :src="$article->image_url" :alt="$article->title" class="aspect-[16/10]" />
    <div class="flex flex-1 flex-col p-5 sm:p-6">
        <span class="self-start rounded-full bg-brass-50 px-2.5 py-1 text-[11px] font-semibold text-brass-700 ring-1 ring-brass-100">{{ $article->category->label() }}</span>
        <h3 class="mt-3 font-display text-xl leading-snug text-ink-900">
            <a href="{{ route('insights.show', $article) }}" class="after:absolute after:inset-0">{{ $article->title }}</a>
        </h3>
        <p class="mt-2 line-clamp-2 text-sm text-ink-500">{{ $article->excerpt }}</p>
        <p class="mt-auto pt-4 text-xs text-ink-400">{{ $article->author_name }} · {{ $article->published_at?->format('M j, Y') }} · {{ $article->reading_minutes }} min read</p>
    </div>
</article>
