<div class="space-y-6">
    @if($success)
        <x-modern.alert variant="success">{{ $success }}</x-modern.alert>
    @endif

    <x-modern.card size="sm">
        <form wire:submit="applySearch" class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <div class="min-w-0 flex-1">
                <x-modern.input name="search" type="search" label="Buscar ticket" placeholder="ID ou assunto" autocomplete="off" wire:model="search" />
            </div>
            <div class="flex justify-end gap-2">
                @if(trim($search) !== '')
                    <x-modern.button type="button" variant="ghost" wire:click="clearSearch">Limpar</x-modern.button>
                @endif
                <x-modern.button type="submit" icon="magnifying-glass">Buscar</x-modern.button>
            </div>
        </form>
    </x-modern.card>

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p role="status" class="text-sm text-zinc-500 dark:text-zinc-400">
            {{ $tickets->total() }} {{ $tickets->total() === 1 ? 'ticket encontrado' : 'tickets encontrados' }}.
            <span wire:loading wire:target="applySearch,clearSearch,toggleCompanyView,gotoPage,nextPage,previousPage">Atualizando lista...</span>
        </p>
        <div class="flex flex-wrap justify-end gap-2">
            @if($companyAvailable)
                <x-modern.button type="button" variant="filled" :color="$viewCompanyTickets ? 'green' : 'blue'" :icon="$viewCompanyTickets ? 'user' : 'building-office'" wire:click="toggleCompanyView">
                    {{ $viewCompanyTickets ? 'Ver somente meus tickets' : 'Ver tickets da minha empresa' }}
                </x-modern.button>
            @endif
            <x-modern.button :href="route('tickets.cliente.create', ['return_to' => $returnUrl])" variant="filled" color="green" icon="plus">Criar novo ticket</x-modern.button>
        </div>
    </div>

    <div class="space-y-4" aria-busy="false" wire:loading.attr="aria-busy">
        @forelse($tickets as $ticket)
            @php
                $statusColor = match ($ticket->status) {
                    'aberto' => 'green',
                    'pendente cliente' => 'blue',
                    'pendente analista' => 'yellow',
                    'fechado' => 'zinc',
                    default => 'zinc',
                };
            @endphp
            <x-modern.card wire:key="client-ticket-{{ $ticket->id }}" size="sm" class="overflow-hidden">
                <div class="flex flex-col gap-3">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Ticket #{{ $ticket->id }}</p>
                            <h2 class="mt-0.5 break-words text-base font-semibold text-zinc-950 dark:text-white">{{ $ticket->assunto }}</h2>
                        </div>
                        <x-modern.badge :color="$statusColor">{{ ucfirst($ticket->status) }}</x-modern.badge>
                    </div>

                    <dl class="grid gap-x-6 gap-y-2.5 text-sm sm:grid-cols-2 xl:grid-cols-4">
                        <div><dt class="font-medium text-zinc-500 dark:text-zinc-400">Criado por</dt><dd class="mt-0.5">{{ $ticket->user?->name ?? 'Usuário removido' }} ({{ $ticket->user?->hasAnyRole(['supervisor', 'analista', 'administrador']) ? 'Equipe' : 'Cliente' }})</dd></div>
                        <div><dt class="font-medium text-zinc-500 dark:text-zinc-400">Setor</dt><dd class="mt-0.5">{{ $ticket->setor?->nome ?? 'Sem setor' }}</dd></div>
                        <div><dt class="font-medium text-zinc-500 dark:text-zinc-400">Criado em</dt><dd class="mt-0.5">{{ $ticket->created_at?->format('d/m/Y - H:i') ?? '—' }}</dd></div>
                        <div><dt class="font-medium text-zinc-500 dark:text-zinc-400">Atualizado em</dt><dd class="mt-0.5">{{ $ticket->updated_at?->format('d/m/Y - H:i') ?? '—' }}</dd></div>
                    </dl>

                    <div class="flex justify-end border-t border-zinc-200 pt-3 dark:border-zinc-700">
                        <x-modern.button :href="route('tickets.cliente.show', ['id' => $ticket, 'return_to' => $returnUrl])" variant="filled" color="sky" size="sm" icon="eye">Detalhes</x-modern.button>
                    </div>
                </div>
            </x-modern.card>
        @empty
            <x-modern.card class="py-10 text-center">
                <p class="font-medium">Nenhum ticket encontrado.</p>
                @if(trim($search) !== '')
                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Tente ajustar ou limpar a busca.</p>
                @endif
            </x-modern.card>
        @endforelse
    </div>

    @if($tickets->hasPages())
        <x-modern.pagination :paginator="$tickets" scroll-to="body" />
    @endif
</div>
