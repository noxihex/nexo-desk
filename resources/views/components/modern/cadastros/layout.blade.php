@props(['title', 'section'])

<x-layouts.modern :title="$title" :heading="$title" :show-success="false">
    <x-slot:navigation>
        <div class="mb-6"><x-modern.tickets.links /></div>
        <p class="mb-3 px-3 text-xs font-semibold uppercase tracking-wider text-zinc-500">Cadastros</p>
        @foreach(['empresas' => 'Empresas', 'usuarios' => 'Usuários', 'categorias' => 'Categorias', 'setores' => 'Setores'] as $key => $label)
            <a href="{{ route($key.'.index') }}"
               @if($section === $key) aria-current="page" @endif
               @class([
                   'mb-1 block rounded-lg px-3 py-2 text-sm font-medium focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-500',
                   'bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300' => $section === $key,
                   'text-zinc-700 hover:bg-zinc-100 dark:text-zinc-200 dark:hover:bg-zinc-800' => $section !== $key,
               ])>{{ $label }}</a>
        @endforeach
        <x-modern.reports.links />
    </x-slot:navigation>
    <x-slot:actions>
        <x-modern.button :href="route('home')" variant="outline" icon="arrow-left">Voltar ao sistema</x-modern.button>
    </x-slot:actions>
    {{ $slot }}
</x-layouts.modern>
