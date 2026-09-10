@props(['active' => false])

<a
    href="{{ route('home') }}"
    @if($active) aria-current="page" @endif
    @class([
        'mb-6 block rounded-lg px-3 py-2 text-sm font-medium focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-500',
        'bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300' => $active,
        'text-zinc-700 hover:bg-zinc-100 dark:text-zinc-200 dark:hover:bg-zinc-800' => ! $active,
    ])
>Visão geral</a>
