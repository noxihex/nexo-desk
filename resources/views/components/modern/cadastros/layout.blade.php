@props(['title', 'section'])

<x-layouts.modern :title="$title" :heading="$title" :show-success="false">
    <x-slot:navigation>
        <x-modern.staff.navigation :section="'cadastros-'.$section" />
    </x-slot:navigation>
    {{ $slot }}
</x-layouts.modern>
