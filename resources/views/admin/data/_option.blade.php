{{-- One selectable dataset. Expects $dataSet, $count, $mode ('seed' or 'delete') and $selected (list of values). --}}
@php
    $isDelete = $mode === 'delete';
    $requires = $isDelete ? [] : $dataSet->requires();
@endphp

<label class="group/option relative block cursor-pointer">
    <input type="checkbox" name="datasets[]" value="{{ $dataSet->value }}" data-group="{{ str($dataSet->group())->slug() }}" class="peer sr-only" @checked(in_array($dataSet->value, old('datasets', $selected), true))>
    <span @class([
        'flex h-full items-start gap-4 rounded-2xl bg-white/75 p-4 ring-1 ring-ink-100 backdrop-blur-xl transition sm:p-5',
        'peer-checked:bg-white peer-checked:shadow-soft peer-checked:ring-2',
        'peer-checked:ring-rose-400' => $isDelete,
        'peer-checked:ring-brass-500' => ! $isDelete,
        'peer-focus-visible:ring-2 peer-focus-visible:ring-brass-400',
        'opacity-60' => $isDelete && $count === 0,
    ])>
        <span @class([
            'grid size-11 shrink-0 place-items-center rounded-xl ring-1 transition',
            'bg-rose-50 text-rose-600 ring-rose-100' => $isDelete,
            'bg-ink-900 text-brass-300 ring-ink-900' => ! $isDelete,
        ])><x-icon :name="$dataSet->icon()" class="size-5" /></span>
        <span class="min-w-0 flex-1">
            <span class="flex flex-wrap items-center gap-2">
                <span class="font-semibold text-ink-900">{{ $dataSet->label() }}</span>
                <span class="rounded-full bg-ink-50 px-2 py-0.5 text-[11px] font-semibold text-ink-500 ring-1 ring-ink-100">{{ number_format($count) }} now</span>
            </span>
            <span class="mt-1 block text-xs leading-relaxed text-ink-500">{{ $isDelete ? $dataSet->deleteDescription() : $dataSet->seedDescription() }}</span>
            @if ($requires)
                <span class="mt-2 inline-flex items-center gap-1 text-[11px] font-medium text-brass-700"><x-icon name="info" class="size-3.5" /> Also adds {{ collect($requires)->map->label()->implode(' and ') }} if missing</span>
            @endif
        </span>
        <span @class([
            'mt-0.5 grid size-6 shrink-0 place-items-center rounded-full ring-1 ring-ink-200 text-transparent transition',
            'group-has-checked/option:bg-rose-500 group-has-checked/option:text-white group-has-checked/option:ring-rose-500' => $isDelete,
            'group-has-checked/option:bg-brass-500 group-has-checked/option:text-white group-has-checked/option:ring-brass-500' => ! $isDelete,
        ])><x-icon name="check" class="size-3.5" stroke="3" /></span>
    </span>
</label>
