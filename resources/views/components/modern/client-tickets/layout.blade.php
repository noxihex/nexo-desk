@props(['title', 'section'])

<x-layouts.modern :title="$title" :heading="$title">
    <x-slot:navigation>
        <x-modern.client.navigation :section="$section" />
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
