<x-layouts.admin :title="$advert->title" :subtitle="$advert->locationLabel().' · '.number_format($advert->clicks).' '.str('click')->plural($advert->clicks)">
    <x-slot:actions>
        <a href="{{ route('admin.adverts.index', ['view' => $advert->isPopup() ? 'popup' : $advert->page]) }}" class="btn btn-ghost hidden sm:inline-flex"><x-icon name="arrow-left" class="size-4" /> Back</a>
        <form method="POST" action="{{ route('admin.adverts.toggle', $advert) }}">
            @csrf
            @method('PATCH')
            <button class="btn btn-outline"><x-icon :name="$advert->is_active ? 'pause' : 'play'" class="size-4" /> <span class="hidden sm:inline">{{ $advert->is_active ? 'Pause' : 'Resume' }}</span></button>
        </form>
    </x-slot:actions>

    {{-- Live preview --}}
    <section class="card mb-6 animate-fade-up p-6 sm:p-8">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-display text-xl text-ink-900">Preview</h2>
                <p class="mt-1 text-sm text-ink-400">{{ $advert->locationLabel() }}</p>
            </div>
            <span class="pill capitalize {{ $advert->stateBadgeClasses() }}"><span class="size-1.5 rounded-full bg-current"></span>{{ $advert->state() }}</span>
        </div>
        <div class="mt-6 rounded-2xl bg-ivory p-4 sm:p-6">
            @if ($advert->isPopup())
                <div class="mx-auto max-w-lg rounded-3xl bg-ink-950/75 p-6"><x-advert :advert="$advert" preview /></div>
            @else
                <div class="grid grid-cols-12 gap-6">
                    <div class="{{ $advert->position === \App\Enums\AdvertPosition::Inside ? 'col-span-12 sm:col-span-6 lg:col-span-4' : $advert->width->columnClasses() }}">
                        <x-advert :advert="$advert" preview />
                    </div>
                </div>
            @endif
        </div>
    </section>

    <form method="POST" action="{{ route('admin.adverts.update', $advert) }}" enctype="multipart/form-data" data-advert-form class="animate-fade-up [animation-delay:60ms]">
        @csrf
        @method('PUT')
        @include('admin.adverts._form')
    </form>

    <section class="mt-10 flex flex-col items-start justify-between gap-4 rounded-3xl border border-rose-200 bg-rose-50/50 p-6 sm:flex-row sm:items-center">
        <div>
            <h2 class="font-semibold text-rose-900">Delete this advert</h2>
            <p class="mt-1 text-sm text-rose-700">It will be removed from the website immediately. Pausing keeps it for later instead.</p>
        </div>
        <form method="POST" action="{{ route('admin.adverts.destroy', $advert) }}" data-confirm="Delete this advert permanently?">
            @csrf
            @method('DELETE')
            <button class="btn btn-danger"><x-icon name="trash" class="size-4" /> Delete advert</button>
        </form>
    </section>
</x-layouts.admin>
