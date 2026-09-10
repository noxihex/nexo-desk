<x-layouts.modern title="Minha conta" heading="Minha conta">
    <x-slot:navigation>
        @hasanyrole('analista|supervisor|administrador')
            <x-modern.staff.overview-link />
        @endhasanyrole
        <a href="{{ route('minhaconta.edit') }}" aria-current="page" class="block rounded-lg bg-blue-50 px-3 py-2 text-sm font-medium text-blue-700 dark:bg-blue-950 dark:text-blue-300">Minha conta</a>
        @hasanyrole('analista|supervisor|administrador')
            <div class="mt-6"><x-modern.tickets.links /></div>
        @endhasanyrole
        @hasanyrole('administrador|supervisor')
            <p class="mb-2 mt-6 px-3 text-xs font-semibold uppercase tracking-wider text-zinc-500">Cadastros</p>
            @foreach(['empresas' => 'Empresas', 'usuarios' => 'Usuários', 'categorias' => 'Categorias', 'setores' => 'Setores'] as $key => $label)
                <a href="{{ route($key.'.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100 dark:text-zinc-200 dark:hover:bg-zinc-800">{{ $label }}</a>
            @endforeach
            <x-modern.reports.links />
        @endhasanyrole
    </x-slot:navigation>
    <x-slot:actions>
        <x-modern.button :href="route('home')" variant="outline" icon="arrow-left">Voltar ao sistema</x-modern.button>
    </x-slot:actions>
    <livewire:modern.account.settings />
</x-layouts.modern>
