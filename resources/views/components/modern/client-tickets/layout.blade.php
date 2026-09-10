@props(['title', 'section'])

<x-layouts.modern :title="$title" :heading="$title">
    <x-slot:navigation>
        <a
            href="{{ route('home') }}"
            @if($section === 'overview') aria-current="page" @endif
            @class([
                'mb-6 block rounded-lg px-3 py-2 text-sm font-medium focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-500',
                'bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300' => $section === 'overview',
                'text-zinc-700 hover:bg-zinc-100 dark:text-zinc-200 dark:hover:bg-zinc-800' => $section !== 'overview',
            ])
        >Visão geral</a>

        <p class="mb-3 px-3 text-xs font-semibold uppercase tracking-wider text-zinc-500">Tickets</p>
        @foreach(['create' => ['tickets.cliente.create', 'Criar ticket'], 'index' => ['tickets.cliente.index', 'Meus tickets']] as $key => [$route, $label])
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
    </x-slot:navigation>

    <x-slot:actions>
        @if($section === 'overview')
            <x-modern.button :href="route('tickets.cliente.create', ['return_to' => route('home')])" variant="filled" color="green" icon="plus">Novo ticket</x-modern.button>
        @else
            <x-modern.button :href="route('home')" variant="outline" icon="arrow-left">Voltar ao sistema</x-modern.button>
        @endif
    </x-slot:actions>

    {{ $slot }}
</x-layouts.modern>
