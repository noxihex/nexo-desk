@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'base',
    'icon' => null,
    'href' => null,
    'disabled' => false,
    'loading' => null,
])

@php
    if ($href && $disabled) {
        $attributes = $attributes
            ->merge(['aria-disabled' => 'true', 'tabindex' => '-1'])
            ->class('pointer-events-none opacity-50');
    }
@endphp

<flux:button
    :type="$type"
    :variant="$variant"
    :size="$size"
    :icon="$icon"
    :href="$href"
    :disabled="$href ? false : $disabled"
    :loading="$loading"
    {{ $attributes }}
>
    {{ $slot }}
</flux:button>
