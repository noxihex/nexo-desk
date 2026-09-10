<x-modern.tickets.layout :title="'Editar ticket #'.$ticket->id" section="general">
    @hasanyrole('supervisor|administrador')
        <livewire:modern.tickets.ticket-form :record-id="$ticket->id" :return-url="$returnUrl" />
    @else
        <x-modern.alert variant="danger" heading="Acesso restrito">Somente supervisores e administradores podem editar tickets pela interface.</x-modern.alert>
        <div class="mt-4"><x-modern.button :href="$returnUrl" variant="outline" icon="arrow-left">Voltar</x-modern.button></div>
    @endhasanyrole
</x-modern.tickets.layout>
