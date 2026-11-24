@props(['text' => null, 'label' => 'Chat on WhatsApp', 'number' => null])

<a href="{{ \App\Models\WhatsappNumber::linkTo($number ?: \App\Models\WhatsappNumber::primaryNumber(), $text) }}" target="_blank" rel="noopener" data-no-loader {{ $attributes->merge(['class' => 'btn']) }}>
    <x-icon name="whatsapp" class="size-4" /> {{ $label }}
</a>
