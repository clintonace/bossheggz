{{-- One advert spot on the page map. Expects: $page, $sectionKey, $sectionLabel, $position, $slotAdverts --}}
<div class="rounded-2xl border border-dashed border-brass-300/70 bg-brass-50/40 p-4" data-admin-slot="{{ $sectionKey }}:{{ $position->value }}">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <p class="text-xs font-semibold tracking-wide text-brass-700 uppercase">
            {{ $position === \App\Enums\AdvertPosition::Inside ? 'Inside' : $position->label() }} · {{ $sectionLabel }}
            <span class="ml-1 font-normal text-ink-400 normal-case">{{ $slotAdverts->count() }} {{ str('advert')->plural($slotAdverts->count()) }}</span>
        </p>
        <a href="{{ route('admin.adverts.create', ['format' => 'inline', 'page' => $page->value, 'section' => $sectionKey, 'position' => $position->value]) }}" class="inline-flex items-center gap-1 rounded-full bg-white px-3 py-1 text-xs font-semibold text-ink-700 ring-1 ring-ink-200 transition hover:ring-brass-300"><x-icon name="plus" class="size-3.5" /> Add advert here</a>
    </div>

    @if ($slotAdverts->isNotEmpty())
        <div class="mt-3 grid grid-cols-12 gap-3">
            @foreach ($slotAdverts as $advert)
                <a href="{{ route('admin.adverts.edit', $advert) }}" @class([
                    'group relative isolate flex min-h-20 items-end overflow-hidden rounded-xl bg-ink-900 p-3 text-white ring-1 ring-ink-900/10 transition hover:ring-brass-400',
                    'col-span-12 sm:col-span-6 lg:col-span-4' => $position === \App\Enums\AdvertPosition::Inside,
                    $advert->width->columnClasses() => $position !== \App\Enums\AdvertPosition::Inside,
                ])>
                    @if ($advert->image_url)
                        <img src="{{ $advert->image_url }}" alt="" loading="lazy" class="absolute inset-0 -z-10 size-full object-cover opacity-50 transition group-hover:opacity-60">
                    @endif
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-sm font-semibold">{{ $advert->title }}</span>
                        <span class="text-[11px] text-white/70">{{ $position === \App\Enums\AdvertPosition::Inside ? 'Card' : ($advert->width === \App\Enums\AdvertWidth::Half ? '6 / 12' : '12 / 12') }} · order {{ $advert->sort_order }}</span>
                    </span>
                    <span class="pill shrink-0 bg-white/95 capitalize {{ $advert->stateBadgeClasses() }}">{{ $advert->state() }}</span>
                </a>
            @endforeach
        </div>
    @endif
</div>
