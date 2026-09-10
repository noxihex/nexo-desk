<x-modern.tickets.layout :title="'Ticket #'.$ticket->id" section="general">
    <livewire:modern.tickets.ticket-show :ticket-id="$ticket->id" :return-url="$returnUrl" />
</x-modern.tickets.layout>
