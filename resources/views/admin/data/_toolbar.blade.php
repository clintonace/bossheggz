{{-- Quick-select dropdown + per-group select all. Expects $presets (label => list of values) and $groups. --}}
<div class="flex flex-wrap items-center gap-3">
    <label for="quick-select" class="text-sm font-medium text-ink-500">Quick select</label>
    <div class="relative">
        <select id="quick-select" data-check-preset class="appearance-none rounded-full border-0 bg-white py-2.5 pr-10 pl-4 text-sm font-semibold text-ink-900 shadow-soft ring-1 ring-ink-100 focus:ring-brass-300 focus:outline-none">
            <option value="">Choose a selection…</option>
            @foreach ($presets as $label => $values)
                <option value="{{ implode(',', $values) }}">{{ $label }}</option>
            @endforeach
            <option value="none">Clear selection</option>
        </select>
        <x-icon name="chevron-down" class="pointer-events-none absolute top-1/2 right-3.5 size-4 -translate-y-1/2 text-ink-400" />
    </div>
    <span class="ml-auto rounded-full bg-white px-4 py-2 text-sm text-ink-500 ring-1 ring-ink-100"><span data-selected-count class="font-semibold text-ink-900">0</span> selected</span>
</div>
