@props(['name' => null, 'label' => null])

@php($fieldName = $name ?: $attributes->whereStartsWith('wire:model')->first())

<flux:field>
    <flux:checkbox :name="$fieldName" :label="$label" {{ $attributes }} />
    @if($fieldName)
        <flux:error :name="$fieldName" />
    @endif
</flux:field>
