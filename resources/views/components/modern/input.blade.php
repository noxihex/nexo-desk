@props([
    'name' => null,
    'label' => null,
    'type' => 'text',
    'description' => null,
    'placeholder' => null,
    'invalid' => null,
])

@php
    $fieldName = $name ?: $attributes->whereStartsWith('wire:model')->first();
    $invalid ??= $fieldName ? $errors->has($fieldName) : false;
@endphp

<flux:field>
    @if($label)
        <flux:label>{{ $label }}</flux:label>
    @endif

    @if($description)
        <flux:description>{{ $description }}</flux:description>
    @endif

    <flux:input
        :name="$fieldName"
        :type="$type"
        :placeholder="$placeholder"
        :invalid="$invalid"
        {{ $attributes }}
    />

    @if($fieldName)
        <flux:error :name="$fieldName" />
    @endif
</flux:field>
