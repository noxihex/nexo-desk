@props(['section' => null])

<p class="mb-3 px-3 text-xs font-semibold uppercase tracking-wider text-zinc-500">Tickets</p>
@foreach(['general' => ['tickets.index', 'Geral'], 'create' => ['tickets.create', 'Criar ticket'], 'mine' => ['tickets.my', 'Meus tickets'], 'pending' => ['tickets.pendentes', 'Pendentes']] as $key => [$route, $label])
    <a
        href="{{ route($route) }}"
        @if($section === $key) aria-current="page" @endif
        @class([
            'mb-1 block rounded-lg px-3 py-2 text-sm font-medium focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-500',
            'bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300' => $section === $key,
            'text-zinc-700 hover:bg-zinc-100 dark:text-zinc-200 dark:hover:bg-zinc-800' => $section !== $key,
        ])
    >{{ $label }}</a>
@endforeach
