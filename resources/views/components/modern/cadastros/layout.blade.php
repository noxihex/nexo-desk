@props(['title', 'section'])

<x-layouts.modern :title="$title" :heading="$title" :show-success="false">
    <x-slot:navigation>
        <x-modern.staff.navigation :section="'cadastros-'.$section" />
    </x-slot:navigation>
    <x-slot:actions>
        <x-modern.button :href="route('home')" variant="outline" icon="arrow-left">Voltar ao sistema</x-modern.button>
    </x-slot:actions>
    {{ $slot }}
</x-layouts.modern>
