@props(['icon' => 'inbox', 'title', 'description' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center rounded-3xl border border-dashed border-ink-200 bg-white/60 px-6 py-16 text-center']) }}>
    <span class="grid size-14 place-items-center rounded-2xl bg-brass-50 text-brass-500 ring-1 ring-brass-100">
        <x-icon :name="$icon" class="size-6" />
    </span>
    <h3 class="mt-5 font-display text-xl text-ink-900">{{ $title }}</h3>
    @if ($description)
        <p class="mt-2 max-w-sm text-sm text-ink-500">{{ $description }}</p>
    @endif
    @if ($slot->isNotEmpty())
        <div class="mt-6">{{ $slot }}</div>
    @endif
</div>
