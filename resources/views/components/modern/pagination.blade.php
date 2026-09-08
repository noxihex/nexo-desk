@props([
    'paginator',
    'scrollTo' => null,
])

<flux:pagination :paginator="$paginator" :scroll-to="$scrollTo" {{ $attributes }} />
