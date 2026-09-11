@props(['section' => null])

@php
    $linkClass = 'mb-1 block rounded-lg px-3 py-2 text-sm font-medium focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-500';
    $activeClass = 'bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300';
    $inactiveClass = 'text-zinc-700 hover:bg-zinc-100 dark:text-zinc-200 dark:hover:bg-zinc-800';
@endphp

<a
    href="{{ route('home') }}"
    @if($section === 'overview') aria-current="page" @endif
    @class([$linkClass, $activeClass => $section === 'overview', $inactiveClass => $section !== 'overview'])
>Visão geral</a>

<p class="mb-2 mt-6 px-3 text-xs font-semibold uppercase tracking-wider text-zinc-500">Tickets</p>
@foreach([
    'tickets-general' => ['tickets.index', 'Geral'],
    'tickets-create' => ['tickets.create', 'Criar ticket'],
    'tickets-mine' => ['tickets.my', 'Meus tickets'],
    'tickets-pending' => ['tickets.pendentes', 'Pendentes'],
] as $key => [$route, $label])
    <a href="{{ route($route) }}" @if($section === $key) aria-current="page" @endif
       @class([$linkClass, $activeClass => $section === $key, $inactiveClass => $section !== $key])>{{ $label }}</a>
@endforeach

@hasanyrole('supervisor|administrador')
    <p class="mb-2 mt-6 px-3 text-xs font-semibold uppercase tracking-wider text-zinc-500">Cadastros</p>
    @foreach([
        'cadastros-empresas' => ['empresas.index', 'Empresas'],
        'cadastros-usuarios' => ['usuarios.index', 'Usuários'],
        'cadastros-categorias' => ['categorias.index', 'Categorias'],
        'cadastros-setores' => ['setores.index', 'Setores'],
    ] as $key => [$route, $label])
        <a href="{{ route($route) }}" @if($section === $key) aria-current="page" @endif
           @class([$linkClass, $activeClass => $section === $key, $inactiveClass => $section !== $key])>{{ $label }}</a>
    @endforeach

    <p class="mb-2 mt-6 px-3 text-xs font-semibold uppercase tracking-wider text-zinc-500">Relatórios</p>
    @foreach([
        'reports-horas' => ['relatorios.horas', 'Por empresa'],
        'reports-analista' => ['relatorios.analista', 'Por analista'],
    ] as $key => [$route, $label])
        <a href="{{ route($route) }}" @if($section === $key) aria-current="page" @endif
           @class([$linkClass, $activeClass => $section === $key, $inactiveClass => $section !== $key])>{{ $label }}</a>
    @endforeach
@endhasanyrole

<x-modern.administration.links :section="str($section)->after('administration-')->toString()" />
