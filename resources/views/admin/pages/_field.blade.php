{{--
    One CMS field. Expects:
      $field     the field definition from config/page_content.php
      $inputName the form name, e.g. sections[hero][title]
      $errorKey  the validation key, e.g. sections.hero.title
      $value     the current value (raw, tokens not filled)
      $id        a unique id for the control
--}}
@php
    $type = $field['type'];
    $hint = $field['hint'] ?? null;
    $error = $errors->first($errorKey) ?: ($type === 'image' ? $errors->first(str_replace('sections.', 'uploads.', $errorKey)) : null);
    $isWide = in_array($type, ['textarea', 'lines', 'items', 'image'], true);
    $required = $field['required'] ?? false;
@endphp

<div @class(['sm:col-span-2' => $isWide])>
    @if ($type === 'items')
        @php($rows = is_array($value) ? array_values($value) : [])
        <div data-repeater data-max="{{ $field['max_items'] ?? 12 }}" class="rounded-2xl bg-ivory/60 p-4 ring-1 ring-ink-100 sm:p-5">
            <div class="flex items-center justify-between gap-3">
                <span class="field-label mb-0">{{ $field['label'] }}</span>
                <span class="text-xs text-ink-400"><span data-repeater-count>{{ count($rows) }}</span> / {{ $field['max_items'] ?? 12 }}</span>
            </div>
            {{-- Submitted when every row is removed, so the list can be emptied. --}}
            <input type="hidden" name="{{ $inputName }}" value="">

            <ol data-repeater-list class="mt-4 space-y-3">
                @foreach ($rows as $index => $row)
                    @include('admin.pages._item', ['index' => $index, 'row' => $row])
                @endforeach
            </ol>

            <template data-repeater-template>
                @include('admin.pages._item', ['index' => '__INDEX__', 'row' => []])
            </template>

            <button type="button" data-repeater-add class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-xl border border-dashed border-ink-200 bg-white/60 px-4 py-2.5 text-sm font-semibold text-ink-600 transition hover:border-brass-400 hover:bg-white hover:text-ink-900 disabled:cursor-not-allowed disabled:opacity-50">
                <x-icon name="plus" class="size-4" /> Add {{ str($field['label'])->singular()->lower() }}
            </button>
            @if ($error)
                <p class="field-error">{{ $error }}</p>
            @endif
        </div>
    @else
        <label for="{{ $id }}" class="field-label">
            {{ $field['label'] }}
            @if ($required)
                <span class="text-brass-600">*</span>
            @endif
        </label>

        @switch($type)
            @case('textarea')
                <textarea name="{{ $inputName }}" id="{{ $id }}" rows="{{ $field['rows'] ?? 3 }}" @if ($field['max'] ?? null) maxlength="{{ $field['max'] }}" @endif class="field-input resize-y">{{ $value }}</textarea>
                @break

            @case('lines')
                <textarea name="{{ $inputName }}" id="{{ $id }}" rows="{{ max(3, min(8, is_array($value) ? count($value) : 3)) }}" class="field-input resize-y leading-relaxed">{{ is_array($value) ? implode("\n", $value) : $value }}</textarea>
                @break

            @case('icon')
                <div class="flex items-center gap-2">
                    <span data-icon-preview class="grid size-11 shrink-0 place-items-center rounded-xl bg-ink-900 text-brass-300">
                        <x-icon :name="$value ?: 'sparkles'" class="size-5" />
                    </span>
                    <select name="{{ $inputName }}" id="{{ $id }}" data-icon-select class="field-input appearance-none">
                        @foreach (config('page_content.icons') as $iconName)
                            <option value="{{ $iconName }}" @selected(($value ?: 'sparkles') === $iconName)>{{ str($iconName)->replace('-', ' ')->ucfirst() }}</option>
                        @endforeach
                    </select>
                </div>
                @break

            @case('image')
                @php($imageUrl = \App\Models\PageSection::resolveImageUrl(is_string($value) ? $value : null))
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-[11rem_1fr]">
                    <div class="relative aspect-[4/3] overflow-hidden rounded-2xl bg-ink-50 ring-1 ring-ink-100">
                        @if ($imageUrl)
                            <img src="{{ $imageUrl }}" alt="" class="size-full object-cover" onerror="this.remove()">
                        @else
                            <span class="absolute inset-0 grid place-items-center text-xs text-ink-300"><x-icon name="image" class="size-6" /></span>
                        @endif
                        <div id="{{ $id }}-preview" hidden class="absolute inset-0 grid bg-ink-50 [&>figure]:aspect-auto [&>figure]:rounded-none [&>figure]:ring-0"></div>
                    </div>
                    <div class="space-y-3">
                        <input type="text" name="{{ $inputName }}" id="{{ $id }}" value="{{ $value }}" placeholder="https://… image link" class="field-input" spellcheck="false">
                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-dashed border-ink-200 bg-white/60 px-4 py-3 text-sm text-ink-500 transition hover:border-brass-400 hover:text-ink-800">
                            <x-icon name="upload" class="size-4 shrink-0" />
                            <span>Upload a new image <span class="text-xs text-ink-400">(JPG, PNG or WebP, up to 5 MB)</span></span>
                            <input type="file" name="{{ str_replace('sections[', 'uploads[', $inputName) }}" accept="image/*" data-image-preview="#{{ $id }}-preview" class="sr-only">
                        </label>
                        <p class="text-xs text-ink-400">{{ $hint ?? 'Paste an image link or upload a file. Clear the link to show no image.' }}</p>
                    </div>
                </div>
                @php($hint = null)
                @break

            @case('url')
                <input type="text" name="{{ $inputName }}" id="{{ $id }}" value="{{ $value }}" placeholder="/contact or https://…" class="field-input font-mono text-[13px]" spellcheck="false" @required($required)>
                @break

            @default
                <input type="text" name="{{ $inputName }}" id="{{ $id }}" value="{{ $value }}" placeholder="{{ $field['placeholder'] ?? '' }}" @if ($field['max'] ?? null) maxlength="{{ $field['max'] }}" @endif class="field-input" @required($required)>
        @endswitch

        @if ($error)
            <p class="field-error">{{ $error }}</p>
        @elseif ($hint)
            <p class="mt-1.5 text-xs text-ink-400">{{ $hint }}</p>
        @endif
    @endif
</div>
