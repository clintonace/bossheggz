@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex items-center justify-between gap-4">
        <p class="hidden text-sm text-ink-400 sm:block">
            Showing <span class="font-semibold text-ink-700">{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}</span> of <span class="font-semibold text-ink-700">{{ $paginator->total() }}</span>
        </p>
        <div class="flex items-center gap-1.5">
            @if ($paginator->onFirstPage())
                <span class="grid size-10 place-items-center rounded-full text-ink-200"><x-icon name="chevron-left" class="size-4" /></span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="grid size-10 place-items-center rounded-full bg-white text-ink-600 ring-1 ring-ink-100 transition hover:ring-ink-300" aria-label="Previous"><x-icon name="chevron-left" class="size-4" /></a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-2 text-sm text-ink-300">{{ $element }}</span>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="grid size-10 place-items-center rounded-full bg-ink-900 text-sm font-semibold text-white">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="grid size-10 place-items-center rounded-full text-sm font-medium text-ink-600 transition hover:bg-white hover:ring-1 hover:ring-ink-100">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="grid size-10 place-items-center rounded-full bg-white text-ink-600 ring-1 ring-ink-100 transition hover:ring-ink-300" aria-label="Next"><x-icon name="chevron-right" class="size-4" /></a>
            @else
                <span class="grid size-10 place-items-center rounded-full text-ink-200"><x-icon name="chevron-right" class="size-4" /></span>
            @endif
        </div>
    </nav>
@endif
