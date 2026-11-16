@props(['status'])

<span {{ $attributes->merge(['class' => 'pill '.$status->badgeClasses()]) }}>
    <span class="size-1.5 rounded-full bg-current opacity-70"></span>
    {{ $status->label() }}
</span>
