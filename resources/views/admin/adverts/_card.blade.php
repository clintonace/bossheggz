<article class="card flex animate-fade-up flex-col overflow-hidden">
    <div class="relative aspect-[16/7] overflow-hidden bg-gradient-to-br from-ink-100 via-ivory to-brass-50">
        @if ($advert->image_url)
            <img src="{{ $advert->image_url }}" alt="" loading="lazy" class="size-full object-cover">
        @else
            <div class="grid size-full place-items-center text-ink-300"><x-icon name="megaphone" class="size-8" /></div>
        @endif
        <span class="pill absolute top-3 left-3 bg-white/95 capitalize {{ $advert->stateBadgeClasses() }}"><span class="size-1.5 rounded-full bg-current"></span>{{ $advert->state() }}</span>
        @if ($advert->isPopup())
            <span class="absolute top-3 right-3 rounded-full bg-brass-500 px-2.5 py-1 text-[10px] font-bold tracking-wider text-white uppercase">Pop-up</span>
        @endif
    </div>
    <div class="flex flex-1 flex-col p-5">
        <p class="text-xs text-ink-400">{{ $advert->locationLabel() }}</p>
        <a href="{{ route('admin.adverts.edit', $advert) }}" class="mt-1 font-display text-lg leading-snug text-ink-900 hover:text-brass-700">{{ $advert->title }}</a>
        @if ($advert->sponsor)
            <p class="text-xs text-ink-400">{{ $advert->sponsor }}</p>
        @endif
        <dl class="mt-4 grid grid-cols-2 gap-3 text-xs">
            <div class="rounded-xl bg-ivory px-3 py-2">
                <dt class="text-ink-400">Clicks</dt>
                <dd class="mt-0.5 text-base font-semibold text-ink-900">{{ number_format($advert->clicks) }}</dd>
            </div>
            <div class="rounded-xl bg-ivory px-3 py-2">
                <dt class="text-ink-400">Runs</dt>
                <dd class="mt-0.5 font-semibold text-ink-900">
                    @if ($advert->starts_at || $advert->ends_at)
                        {{ $advert->starts_at?->format('M j') ?? 'Now' }} – {{ $advert->ends_at?->format('M j') ?? 'ongoing' }}
                    @else
                        Always
                    @endif
                </dd>
            </div>
        </dl>
        <div class="mt-5 flex items-center gap-2 border-t border-ink-100 pt-4">
            <a href="{{ route('admin.adverts.edit', $advert) }}" class="btn btn-outline btn-sm"><x-icon name="pencil" class="size-3.5" /> Edit</a>
            <form method="POST" action="{{ route('admin.adverts.toggle', $advert) }}">
                @csrf
                @method('PATCH')
                <button class="btn btn-ghost btn-sm"><x-icon :name="$advert->is_active ? 'pause' : 'play'" class="size-3.5" /> {{ $advert->is_active ? 'Pause' : 'Resume' }}</button>
            </form>
            <a href="{{ $advert->link_url }}" target="_blank" rel="noopener" class="ml-auto truncate text-xs text-ink-400 hover:text-ink-700" title="{{ $advert->link_url }}">{{ $advert->display_domain }}</a>
        </div>
    </div>
</article>
