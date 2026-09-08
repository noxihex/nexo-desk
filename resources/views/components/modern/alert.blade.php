@props(['variant' => 'info', 'heading' => null])

<flux:callout :variant="$variant" :heading="$heading" {{ $attributes->merge(['role' => $variant === 'danger' ? 'alert' : 'status']) }}>
    {{ $slot }}
</flux:callout>
