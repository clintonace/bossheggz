{{-- One row of a repeatable "items" field. Expects $field, $inputName, $errorKey, $id, $index and $row. --}}
<li data-repeater-item class="group/item rounded-2xl bg-white/80 p-4 shadow-xs ring-1 ring-ink-100 backdrop-blur transition hover:ring-brass-200">
    <div class="flex items-center justify-between gap-3">
        <span class="inline-flex items-center gap-2 text-xs font-bold tracking-wider text-brass-600 uppercase">
            <span class="grid size-6 place-items-center rounded-full bg-brass-50 ring-1 ring-brass-100" data-repeater-number>{{ is_int($index) ? $index + 1 : '' }}</span>
            {{ str($field['label'])->singular() }}
        </span>
        <div class="flex items-center gap-1 opacity-70 transition group-hover/item:opacity-100">
            <button type="button" data-repeater-up class="grid size-8 place-items-center rounded-lg text-ink-400 transition hover:bg-ink-50 hover:text-ink-800" aria-label="Move up"><x-icon name="chevron-down" class="size-4 rotate-180" /></button>
            <button type="button" data-repeater-down class="grid size-8 place-items-center rounded-lg text-ink-400 transition hover:bg-ink-50 hover:text-ink-800" aria-label="Move down"><x-icon name="chevron-down" class="size-4" /></button>
            <button type="button" data-repeater-remove class="grid size-8 place-items-center rounded-lg text-ink-400 transition hover:bg-rose-50 hover:text-rose-600" aria-label="Remove"><x-icon name="trash" class="size-4" /></button>
        </div>
    </div>

    <div class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2">
        @foreach ($field['fields'] as $subName => $subField)
            @include('admin.pages._field', [
                'field' => $subField,
                'inputName' => "{$inputName}[{$index}][{$subName}]",
                'errorKey' => "{$errorKey}.{$index}.{$subName}",
                'id' => "{$id}-{$index}-{$subName}",
                'value' => $row[$subName] ?? null,
            ])
        @endforeach
    </div>
</li>
