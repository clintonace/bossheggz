<div {{ $attributes->merge(['class' => 'glass flex gap-3 rounded-2xl border-l-4 border-brass-400 p-5 text-sm leading-relaxed text-ink-600']) }}>
    <x-icon name="info" class="mt-0.5 size-5 shrink-0 text-brass-500" />
    <p>{{ $slot }}</p>
</div>
