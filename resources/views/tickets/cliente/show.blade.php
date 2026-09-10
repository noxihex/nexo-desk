<x-modern.client-tickets.layout :title="'Ticket #'.$ticketId" section="index">
    <livewire:modern.client-tickets.client-ticket-show :ticket-id="$ticketId" :return-url="$returnUrl" />
</x-modern.client-tickets.layout>
