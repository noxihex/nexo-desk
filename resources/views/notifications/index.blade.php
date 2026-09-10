@if(auth()->user()->isStaff())
    <x-modern.staff.layout title="Notificações" :show-create-action="false">
        <livewire:modern.notifications.notification-index />
    </x-modern.staff.layout>
@else
    <x-modern.client-tickets.layout title="Notificações" section="">
        <livewire:modern.notifications.notification-index />
    </x-modern.client-tickets.layout>
@endif
