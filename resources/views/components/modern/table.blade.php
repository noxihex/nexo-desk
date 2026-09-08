@props([
    'paginator' => null,
    'bleed' => false,
])

<flux:table :paginate="$paginator" :bleed="$bleed" {{ $attributes }}>
    {{ $slot }}
</flux:table>
