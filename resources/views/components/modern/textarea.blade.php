@props([
    'name' => null,
    'label' => null,
    'description' => null,
    'placeholder' => null,
    'rows' => 4,
])

@php($fieldName = $name ?: $attributes->whereStartsWith('wire:model')->first())

<flux:field>
    @if($label)
        <flux:label>{{ $label }}</flux:label>
    @endif
    @if($description)
        <flux:description>{{ $description }}</flux:description>
    @endif
    <flux:textarea :name="$fieldName" :placeholder="$placeholder" :rows="$rows" {{ $attributes }} />
    @if($fieldName)
        <flux:error :name="$fieldName" />
    @endif
</flux:field>
