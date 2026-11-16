@props(['name', 'label' => null, 'type' => 'text', 'value' => null, 'hint' => null, 'textarea' => false, 'bag' => 'default'])

@php($fieldErrors = $errors->getBag($bag))

<div {{ $attributes->only('class') }}>
    @if ($label)
        <label for="{{ $attributes->get('id', $name) }}" class="field-label">{{ $label }}</label>
    @endif

    @if ($textarea)
        <textarea name="{{ $name }}" id="{{ $attributes->get('id', $name) }}" {{ $attributes->except(['class', 'id'])->merge(['rows' => 4, 'class' => 'field-input resize-y']) }}>{{ old($name, $value) }}</textarea>
    @else
        <input type="{{ $type }}" name="{{ $name }}" id="{{ $attributes->get('id', $name) }}" value="{{ $type === 'password' ? '' : old($name, $value) }}" {{ $attributes->except(['class', 'id'])->merge(['class' => 'field-input']) }}>
    @endif

    @if ($hint && ! $fieldErrors->has($name))
        <p class="mt-1.5 text-xs text-ink-400">{{ $hint }}</p>
    @endif

    @if ($fieldErrors->has($name))
        <p class="field-error">{{ $fieldErrors->first($name) }}</p>
    @endif
</div>
