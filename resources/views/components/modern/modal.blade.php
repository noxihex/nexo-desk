@props([
    'name',
    'title' => null,
    'description' => null,
    'dismissible' => true,
])

@isset($trigger)
    <flux:modal.trigger :name="$name">
        {{ $trigger }}
    </flux:modal.trigger>
@endisset

<flux:modal :name="$name" :dismissible="$dismissible" {{ $attributes }}>
    <div class="space-y-6"
        @if($title)
            x-data
            x-init="$el.closest('dialog')?.setAttribute('aria-labelledby', @js('modern-modal-title-'.$name))"
        @endif
    >
        @if($title || $description)
            <div>
                @if($title)
                    <flux:heading :id="'modern-modal-title-'.$name" size="lg" level="2">{{ $title }}</flux:heading>
                @endif

                @if($description)
                    <flux:text class="mt-2">{{ $description }}</flux:text>
                @endif
            </div>
        @endif

        <div>{{ $slot }}</div>

        @isset($actions)
            <div class="flex flex-wrap justify-end gap-2">{{ $actions }}</div>
        @endisset
    </div>
</flux:modal>
