<div class="space-y-6" wire:poll.60s>
    <x-modern.card size="sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="w-full sm:max-w-xs">
                <x-modern.select name="filter" label="Exibir" :options="['all' => 'Todas as notificações', 'unread' => 'Somente não lidas']" wire:model.live="filter" />
            </div>
            @if($unreadCount > 0)
                <x-modern.button type="button" variant="outline" icon="check" wire:click="markAllAsRead">Marcar todas como lidas</x-modern.button>
            @endif
        </div>
    </x-modern.card>

    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900" aria-busy="false" wire:loading.attr="aria-busy">
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
            <article wire:key="notification-page-{{ $notification->id }}" class="flex flex-col gap-3 border-b border-zinc-200 p-4 last:border-b-0 sm:flex-row sm:items-start dark:border-zinc-800 {{ $notification->read_at ? '' : 'bg-blue-50/40 dark:bg-blue-950/10' }}">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-full {{ $color }}"><flux:icon :name="$icon" class="size-5" aria-hidden="true" /></span>
                <div class="min-w-0 flex-1">
                    <div class="flex items-start gap-2">
                        <h2 class="flex-1 font-semibold text-zinc-950 dark:text-white">{{ $notification->data['title'] ?? 'Notificação' }}</h2>
                        @unless($notification->read_at)<span class="mt-2 size-2 shrink-0 rounded-full bg-blue-600" aria-label="Não lida"></span>@endunless
                    </div>
                    @if($notification->data['message'] ?? null)<p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">{{ $notification->data['message'] }}</p>@endif
                    @if($notification->data['summary'] ?? null)<p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $notification->data['summary'] }}</p>@endif
                    <time class="mt-2 block text-xs text-zinc-400" datetime="{{ $notification->created_at?->toIso8601String() }}">{{ $notification->created_at?->format('d/m/Y H:i') }}</time>
                </div>
                <div class="flex shrink-0 justify-end gap-2">
                    @unless($notification->read_at)
                        <x-modern.button type="button" variant="ghost" size="sm" wire:click="markAsRead('{{ $notification->id }}')">Marcar como lida</x-modern.button>
                    @endunless
                    @if($notification->data['ticket_id'] ?? null)
                        <x-modern.button type="button" variant="filled" color="sky" size="sm" icon="arrow-right" wire:click="openNotification('{{ $notification->id }}')">Abrir ticket</x-modern.button>
                    @endif
                </div>
            </article>
        @empty
            <div class="px-6 py-16 text-center">
                <flux:icon.bell-slash class="mx-auto size-9 text-zinc-400" aria-hidden="true" />
                <p class="mt-3 font-medium text-zinc-800 dark:text-zinc-200">Nenhuma notificação encontrada.</p>
                @if($filter === 'unread')<p class="mt-1 text-sm text-zinc-500">Você não possui notificações pendentes.</p>@endif
            </div>
        @endforelse
    </div>

    @if($notifications->hasPages())
        <x-modern.pagination :paginator="$notifications" scroll-to="body" />
    @endif
</div>
