<x-layouts.modern title="Minha conta" heading="Minha conta">
    <x-slot:navigation>
        @if(auth()->user()->hasRole('cliente'))
            <x-modern.client.navigation />
        @else
            <x-modern.staff.navigation />
        @endif
    </x-slot:navigation>
    <livewire:modern.account.settings />
</x-layouts.modern>
