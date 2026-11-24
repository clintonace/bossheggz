@props(['section', 'size' => 'lg'])

{{-- Small gold heading + large title for a CMS-managed page section (App\Models\PageSection). --}}
<div {{ $attributes }}>
    @if (filled($section->value('eyebrow')))
        <p class="eyebrow">{{ $section->value('eyebrow') }}</p>
    @endif
    <h2 @class([
        'mt-3 font-display text-4xl tracking-tight text-ink-950',
        'sm:text-5xl' => $size === 'lg',
    ])>{{ $section->value('title') }}</h2>
</div>
