@php
    $messages = collect([
        'success' => ['icon' => 'check-circle', 'classes' => 'text-emerald-600'],
        'info' => ['icon' => 'info', 'classes' => 'text-sky-600'],
        'error' => ['icon' => 'alert', 'classes' => 'text-rose-600'],
    ])->filter(fn ($style, $key) => session()->has($key));
@endphp

@if ($messages->isNotEmpty())
    <div class="pointer-events-none fixed inset-x-0 top-20 z-[60] flex flex-col items-center gap-3 px-4 sm:items-end sm:px-6">
        @foreach ($messages as $key => $style)
            <div data-toast="7000" role="status" class="pointer-events-auto flex w-full max-w-sm animate-toast-in items-start gap-3 rounded-2xl bg-white/95 p-4 shadow-lift ring-1 ring-ink-100 backdrop-blur">
                <x-icon :name="$style['icon']" class="mt-0.5 {{ $style['classes'] }}" />
                <p class="flex-1 text-sm leading-relaxed text-ink-700">{{ session($key) }}</p>
                <button type="button" data-toast-close data-no-loader class="text-ink-300 transition hover:text-ink-700" aria-label="Dismiss">
                    <x-icon name="x" class="size-4" />
                </button>
            </div>
        @endforeach
    </div>
@endif
