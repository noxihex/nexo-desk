<div
    class="contents"
    wire:poll.60s="refreshNotifications"
    x-data="{ toastVisible: false, toastTimer: null }"
    x-on:notification-received.window="
        toastVisible = true;
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => toastVisible = false, 5000);
    "
>
    @php
        $bellLabel = $unreadCount > 0 ? "Notificações, {$unreadCount} não lidas" : 'Notificações';
    @endphp

    <flux:modal.trigger name="modern-notification-center">
        <button type="button" class="fixed bottom-4 start-4 z-[60] hidden size-10 shrink-0 items-center justify-center rounded-lg text-zinc-500 hover:bg-zinc-100 hover:text-zinc-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-500 lg:inline-flex dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-white" aria-label="{{ $bellLabel }}">
            <flux:icon.bell class="size-5" aria-hidden="true" />
            @if($unreadCount > 0)
                <span class="absolute -end-1 -top-1 flex min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-bold leading-5 text-white">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
            @endif
        </button>
    </flux:modal.trigger>

    <flux:modal.trigger name="modern-notification-center">
        <button type="button" class="fixed end-4 top-3 z-40 inline-flex size-10 items-center justify-center rounded-lg text-zinc-600 hover:bg-zinc-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-500 lg:hidden dark:text-zinc-300 dark:hover:bg-zinc-800" aria-label="{{ $bellLabel }}">
            <flux:icon.bell class="size-5" aria-hidden="true" />
            @if($unreadCount > 0)
                <span class="absolute -end-1 -top-1 flex min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-bold leading-5 text-white">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
            @endif
        </button>
    </flux:modal.trigger>

    <flux:modal name="modern-notification-center" variant="flyout" class="w-full max-w-md p-0!">
        <div class="flex min-h-dvh flex-col">
            <header class="border-b border-zinc-200 px-5 py-5 pe-14 dark:border-zinc-700">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <flux:heading size="lg" level="2">Notificações</flux:heading>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $unreadCount }} {{ $unreadCount === 1 ? 'não lida' : 'não lidas' }}</p>
                    </div>
                    @if($unreadCount > 0)
                        <button type="button" wire:click="markAllAsRead" class="text-sm font-medium text-blue-600 hover:underline focus-visible:outline-2 focus-visible:outline-blue-500 dark:text-blue-400">Marcar todas como lidas</button>
                    @endif
                </div>
            </header>

            <div class="min-h-0 flex-1 overflow-y-auto">
                @forelse($notifications as $notification)
                    @php
                        $event = $notification->data['event'] ?? '';
                        [$icon, $color] = match ($event) {
                            'novo_ticket' => ['plus-circle', 'text-green-600 bg-green-50 dark:text-green-300 dark:bg-green-950/50'],
                            'nova_mensagem' => ['chat-bubble-left-right', 'text-blue-600 bg-blue-50 dark:text-blue-300 dark:bg-blue-950/50'],
                            'ticket_resolvido' => ['check-circle', 'text-zinc-600 bg-zinc-100 dark:text-zinc-300 dark:bg-zinc-700'],
                            'sla' => ['exclamation-triangle', 'text-red-600 bg-red-50 dark:text-red-300 dark:bg-red-950/50'],
                            default => ['bell', 'text-amber-600 bg-amber-50 dark:text-amber-300 dark:bg-amber-950/50'],
                        };
                    @endphp
                    <button type="button" wire:key="notification-center-{{ $notification->id }}" wire:click="openNotification('{{ $notification->id }}')" class="flex w-full gap-3 border-b border-zinc-100 px-5 py-4 text-left hover:bg-zinc-50 focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-blue-500 dark:border-zinc-700 dark:hover:bg-zinc-700/50">
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-full {{ $color }}"><flux:icon :name="$icon" class="size-4" aria-hidden="true" /></span>
                        <span class="min-w-0 flex-1">
                            <span class="flex items-start gap-2">
                                <span class="flex-1 text-sm font-semibold text-zinc-950 dark:text-white">{{ $notification->data['title'] ?? 'Notificação' }}</span>
                                @unless($notification->read_at)<span class="mt-1.5 size-2 shrink-0 rounded-full bg-blue-600" aria-label="Não lida"></span>@endunless
                            </span>
                            @if($notification->data['message'] ?? null)<span class="mt-1 block text-sm leading-5 text-zinc-600 dark:text-zinc-300">{{ $notification->data['message'] }}</span>@endif
                            @if($notification->data['summary'] ?? null)<span class="mt-1 block truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $notification->data['summary'] }}</span>@endif
                            <time class="mt-2 block text-xs text-zinc-400" datetime="{{ $notification->created_at?->toIso8601String() }}">{{ $notification->created_at?->format('d/m/Y H:i') }}</time>
                        </span>
                    </button>
                @empty
                    <div class="px-6 py-16 text-center">
                        <flux:icon.bell-slash class="mx-auto size-8 text-zinc-400" aria-hidden="true" />
                        <p class="mt-3 text-sm font-medium text-zinc-700 dark:text-zinc-200">Nenhuma notificação.</p>
                    </div>
                @endforelse
            </div>

            <footer class="border-t border-zinc-200 p-4 dark:border-zinc-700">
                <x-modern.button :href="route('notifications.index')" variant="outline" class="w-full">Ver todas as notificações</x-modern.button>
            </footer>
        </div>
    </flux:modal>

    <div x-cloak x-show="toastVisible" x-transition class="fixed end-4 top-20 z-[70] max-w-sm rounded-xl border border-zinc-200 bg-white px-4 py-3 shadow-lg dark:border-zinc-700 dark:bg-zinc-800" role="status">
        <div class="flex items-center gap-3">
            <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-blue-50 text-blue-600 dark:bg-blue-950 dark:text-blue-300"><flux:icon.bell class="size-4" aria-hidden="true" /></span>
            <p class="text-sm font-medium text-zinc-900 dark:text-white">Você recebeu uma nova notificação.</p>
        </div>
    </div>
</div>
