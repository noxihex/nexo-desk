@props([
    'name' => null,
    'label' => null,
    'description' => null,
    'placeholder' => null,
    'options' => [],
    'selected' => null,
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

    <flux:select
        :name="$fieldName"
        :placeholder="$placeholder"
        :invalid="$invalid"
        {{ $attributes }}
    >
        @foreach($options as $value => $optionLabel)
            <x-modern.select.option :value="$value" :selected="(string) $selected === (string) $value">
                {{ $optionLabel }}
            </x-modern.select.option>
        @endforeach

        {{ $slot }}
    </flux:select>

    @if($fieldName)
        <flux:error :name="$fieldName" />
    @endif
</flux:field>
