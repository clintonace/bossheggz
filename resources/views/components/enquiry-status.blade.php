@props(['status'])

{{-- Status pill in the client's words (e.g. "Adviser in touch"). --}}
<span {{ $attributes->merge(['class' => 'pill '.$status->badgeClasses()]) }}>
    <span class="size-1.5 rounded-full bg-current opacity-70"></span>
    {{ $status->clientLabel() }}
</span>
