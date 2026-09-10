@props(['title', 'section' => null, 'showCreateAction' => true])

<x-layouts.modern :title="$title" :heading="$title">
    <x-slot:navigation>
        <x-modern.staff.overview-link :active="$section === 'overview'" />
        <x-modern.tickets.links />

        @hasanyrole('supervisor|administrador')
            <p class="mb-2 mt-6 px-3 text-xs font-semibold uppercase tracking-wider text-zinc-500">Cadastros</p>
            @foreach(['empresas' => 'Empresas', 'usuarios' => 'Usuários', 'categorias' => 'Categorias', 'setores' => 'Setores'] as $route => $label)
                <a href="{{ route($route.'.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-500 dark:text-zinc-200 dark:hover:bg-zinc-800">{{ $label }}</a>
            @endforeach
            <x-modern.reports.links />
        @endhasanyrole
    </x-slot:navigation>

    @if($showCreateAction)
        <x-slot:actions>
            <x-modern.button :href="route('tickets.create', ['return_to' => route('home')])" variant="filled" color="green" icon="plus">Novo ticket</x-modern.button>
        </x-slot:actions>
    @endif

    {{ $slot }}
</x-layouts.modern>
