@props(['adverts', 'at', 'contained' => true])

@php($slotAdverts = $adverts->get($at, collect()))

@if ($slotAdverts->isNotEmpty())
    <div {{ $attributes->class(['mx-auto max-w-7xl px-4 sm:px-6 lg:px-8' => $contained]) }} data-advert-slot="{{ $at }}">
        <div class="grid grid-cols-12 gap-6">
            @foreach ($slotAdverts as $advert)
                <div class="{{ $advert->width->columnClasses() }}" data-reveal data-reveal-delay="{{ $loop->index * 90 }}">
                    <x-advert :advert="$advert" />
                </div>
            @endforeach
        </div>
    </div>
@endif
