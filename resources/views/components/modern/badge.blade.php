@props([
    'color' => null,
    'size' => null,
    'variant' => null,
    'rounded' => true,
])

<flux:badge
    :color="$color"
    :size="$size"
    :variant="$variant"
    :rounded="$rounded"
    {{ $attributes }}
>
    {{ $slot }}
</flux:badge>
