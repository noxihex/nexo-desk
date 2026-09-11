@props(['title', 'section'])

    <x-layouts.modern :title="$title" :heading="$title">
        <x-slot:navigation>
            <x-modern.staff.navigation :section="'reports-'.$section" />
        </x-slot:navigation>
        {{ $slot }}
    </x-layouts.modern>
