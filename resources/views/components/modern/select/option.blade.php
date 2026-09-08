@props([
    'value' => null,
    'selected' => false,
    'disabled' => false,
])

<flux:select.option
    :value="$value"
    :selected="$selected"
    :disabled="$disabled"
    {{ $attributes }}
>
    {{ $slot }}
</flux:select.option>
