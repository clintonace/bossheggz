<x-layouts.app :title="$article->title" :description="$article->excerpt">
    <article class="mx-auto max-w-4xl px-4 pt-8 sm:px-6 lg:px-8">
        <nav class="flex animate-fade-up items-center gap-2 text-sm text-ink-400" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="shrink-0 hover:text-ink-900">Home</a>
            <x-icon name="chevron-right" class="size-3.5" />
            <a href="{{ route('insights.index') }}" class="shrink-0 hover:text-ink-900">Insights</a>
            <x-icon name="chevron-right" class="size-3.5" />
            <a href="{{ route('insights.index', ['category' => $article->category->value]) }}" class="truncate hover:text-ink-900">{{ $article->category->label() }}</a>
        </nav>

        <header class="mt-8 animate-fade-up [animation-delay:60ms]">
            <span class="rounded-full bg-brass-50 px-2.5 py-1 text-[11px] font-semibold text-brass-700 ring-1 ring-brass-100">{{ $article->category->label() }}</span>
            <h1 class="mt-5 font-display text-4xl leading-tight tracking-tight text-ink-950 sm:text-5xl">{{ $article->title }}</h1>
            <p class="mt-5 text-xl leading-relaxed text-ink-500">{{ $article->excerpt }}</p>
            <div class="mt-6 flex items-center gap-3 text-sm text-ink-500">
                <span class="grid size-10 place-items-center rounded-full bg-ink-900 font-semibold text-brass-200">{{ str($article->author_name)->substr(0, 1)->upper() }}</span>
                <span><span class="font-semibold text-ink-900">{{ $article->author_name }}</span><span class="block text-xs text-ink-400">{{ $article->published_at->format('F j, Y') }} · {{ $article->reading_minutes }} min read</span></span>
            </div>
        </header>

        @if ($article->image_url)
            <x-property-image :src="$article->image_url" :alt="$article->title" class="mt-10 aspect-[16/9] animate-fade-up rounded-[2rem] shadow-lift [animation-delay:120ms]" />
        @endif

        <div class="glass mt-10 rounded-[2rem] p-6 sm:p-10">
            <div class="prose-article">
                @foreach (preg_split('/\R{2,}/', trim($article->body)) as $block)
                    @if (str_starts_with($block, '## '))
                        <h2 class="pt-4 font-display text-2xl text-ink-950">{{ substr($block, 3) }}</h2>
                    @else
                        <p>{!! nl2br(e($block)) !!}</p>
                    @endif
                @endforeach
            </div>
        </div>

        <div class="mt-8 flex flex-wrap items-center justify-between gap-4">
            <a href="{{ route('insights.index') }}" class="btn btn-ghost"><x-icon name="arrow-left" class="size-4" /> All insights</a>
            <x-whatsapp-button class="btn-outline" :text="$article->title.' — '.route('insights.show', $article)" label="Share on WhatsApp" />
        </div>
    </article>

    @if ($related->isNotEmpty())
        <section class="mx-auto mt-20 max-w-7xl px-4 sm:px-6 lg:px-8">
            <h2 class="font-display text-3xl tracking-tight text-ink-950" data-reveal>Keep reading</h2>
            <div class="mt-8 grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($related as $item)
                    <x-article-card :article="$item" data-reveal data-reveal-delay="{{ $loop->index * 100 }}" />
                @endforeach
            </div>
        </section>
    @endif

    <x-cta-band class="mt-24" title="Ready to act on what you have read?">
        <a href="{{ route('contact', ['type' => 'consultation']) }}" class="btn btn-primary btn-lg">Speak to an Adviser <x-icon name="arrow-right" class="size-4" /></a>
    </x-cta-band>
</x-layouts.app>
