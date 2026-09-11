<x-layouts.modern title="Minha conta" heading="Minha conta">
    <x-slot:navigation>
        @if(auth()->user()->hasRole('cliente'))
            <x-modern.client.navigation />
        @else
            <x-modern.staff.navigation />
        @endif
    </x-slot:navigation>
    <x-slot:actions>
        <x-modern.button :href="route('home')" variant="outline" icon="arrow-left">Voltar ao sistema</x-modern.button>
    </x-slot:actions>
    <livewire:modern.account.settings />
</x-layouts.modern>
