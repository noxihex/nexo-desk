@props([
    'title' => null,
    'description' => null,
    'variant' => 'outline',
    'size' => null,
])

<flux:card :variant="$variant" :size="$size" {{ $attributes }}>
    @if($title || $description || isset($actions))
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                @if($title)
                    <flux:heading size="lg">{{ $title }}</flux:heading>
                @endif

                @if($description)
                    <flux:text class="mt-1">{{ $description }}</flux:text>
                @endif
            </div>

            @isset($actions)
                <div class="flex shrink-0 items-center gap-2">{{ $actions }}</div>
            @endisset
        </div>
    @endif

    <div @class(['mt-6' => $title || $description || isset($actions)])>
        {{ $slot }}
    </div>

    @isset($footer)
        <div class="mt-6 border-t border-zinc-200 pt-4 dark:border-zinc-700">
            {{ $footer }}
        </div>
    @endisset
</flux:card>
