@props(['title', 'section' => null, 'showCreateAction' => true])

<x-layouts.modern :title="$title" :heading="$title">
    <x-slot:navigation>
        <x-modern.staff.navigation :section="$section" />
    </x-slot:navigation>

    @if($showCreateAction)
        <x-slot:actions>
            <x-modern.button :href="route('tickets.create', ['return_to' => route('home')])" variant="filled" color="green" icon="plus">Novo ticket</x-modern.button>
        </x-slot:actions>
    @endif

    {{ $slot }}
</x-layouts.modern>
