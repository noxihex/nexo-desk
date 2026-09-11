@php
    $general = $section === \App\Actions\Tickets\TicketListing::GENERAL;
    $mine = $section === \App\Actions\Tickets\TicketListing::MINE;
    $activeFilterCount = collect([$setor_id, $categoria_id, $empresa_id])->filter(fn ($value) => $value !== '')->count() + ($showClosed ? 1 : 0);
@endphp

<div class="space-y-6">
    @if($success)
        <x-modern.alert variant="success">{{ $success }}</x-modern.alert>
    @endif

    @if($general)
        <x-modern.card size="sm" class="report-filters">
            <form wire:submit="applyFilters" class="space-y-3">
                <div class="grid gap-4 lg:grid-cols-[minmax(16rem,2fr)_minmax(12rem,1fr)_auto] lg:items-end">
                    <x-modern.input
                        name="search"
                        type="search"
                        label="Buscar ticket"
                        placeholder="ID ou assunto"
                        autocomplete="off"
                        wire:model="search"
                    />
                    <x-modern.select
                        name="sort"
                        label="Ordenar por"
                        :options="['created_at' => 'Criados recentemente', 'updated_at' => 'Modificados recentemente', 'sla' => 'SLA mais atrasado']"
                        wire:model="sort"
                    />
                    <x-modern.button type="submit" icon="magnifying-glass">Buscar</x-modern.button>
                </div>

                <details class="group border-t border-zinc-200 pt-3 dark:border-zinc-700" @if($activeFilterCount) open @endif>
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-3 rounded-lg py-1 text-sm font-medium text-zinc-700 outline-none hover:text-zinc-950 focus-visible:ring-2 focus-visible:ring-blue-500 dark:text-zinc-300 dark:hover:text-white">
                        <span>Filtros adicionais</span>
                        <span class="flex items-center gap-2">
                            @if($activeFilterCount)
                                <x-modern.badge color="blue">{{ $activeFilterCount }} {{ $activeFilterCount === 1 ? 'ativo' : 'ativos' }}</x-modern.badge>
                            @endif
                            <flux:icon.chevron-down class="size-4 transition-transform group-open:rotate-180" />
                        </span>
                    </summary>

                    <div class="space-y-4 pt-4">
                        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                            <x-modern.select name="setor_id" label="Setor" placeholder="Todos os setores" :options="$setores->pluck('nome', 'id')->all()" wire:model="setor_id" />
                            <x-modern.select name="categoria_id" label="Categoria" placeholder="Todas as categorias" :options="$categorias->pluck('nome', 'id')->all()" wire:model="categoria_id" />
                            <x-modern.select name="empresa_id" label="Empresa" placeholder="Todas as empresas" :options="$empresas->pluck('nome', 'id')->all()" wire:model="empresa_id" />
                            <div class="flex items-end pb-2">
                                <x-modern.checkbox name="showClosed" label="Incluir tickets fechados" wire:model="showClosed" />
                            </div>
                        </div>

                        @if($activeFilterCount || trim($search) !== '' || $sort !== 'created_at')
                            <div class="flex justify-end">
                                <x-modern.button type="button" variant="ghost" wire:click="clearFilters">Limpar filtros</x-modern.button>
                            </div>
                        @endif
                    </div>
                </details>
            </form>
        </x-modern.card>
    @elseif($mine)
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-zinc-600 dark:text-zinc-400">Tickets atribuídos a você.</p>
            <x-modern.checkbox name="showClosed" label="Incluir tickets fechados" wire:model.live="showClosed" />
        </div>
    @else
        <p class="text-sm text-zinc-600 dark:text-zinc-400">Tickets abertos por clientes que aguardam classificação pela equipe.</p>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-3">
        <p role="status" class="text-sm text-zinc-500 dark:text-zinc-400">
            {{ $tickets->total() }} {{ $tickets->total() === 1 ? 'ticket encontrado' : 'tickets encontrados' }}.
            <span wire:loading wire:target="applyFilters, clearFilters, showClosed, gotoPage, nextPage, previousPage">Atualizando lista...</span>
        </p>
        @if($general)
            <x-modern.button :href="route('tickets.create', ['return_to' => $returnUrl])" variant="filled" color="green" icon="plus">Novo ticket</x-modern.button>
        @endif
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
            <x-modern.card wire:key="ticket-{{ $ticket->id }}" size="sm" class="overflow-hidden">
                <div class="flex flex-col gap-3">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Ticket #{{ $ticket->id }}</p>
                            <h2 class="mt-0.5 break-words text-base font-semibold text-zinc-950 dark:text-white">{{ $ticket->assunto }}</h2>
                        </div>
                        <x-modern.badge :color="$statusColor">{{ ucfirst($ticket->status) }}</x-modern.badge>
                    </div>

                    <dl class="grid gap-x-6 gap-y-2.5 text-sm sm:grid-cols-2 xl:grid-cols-4">
                        <div><dt class="font-medium text-zinc-500 dark:text-zinc-400">Categoria</dt><dd class="mt-0.5">{{ $ticket->categoria?->nome ?? 'Sem categoria' }}</dd></div>
                        <div><dt class="font-medium text-zinc-500 dark:text-zinc-400">Empresa</dt><dd class="mt-0.5">{{ $ticket->empresa?->nome ?? 'Sem empresa' }}</dd></div>
                        <div><dt class="font-medium text-zinc-500 dark:text-zinc-400">Setor</dt><dd class="mt-0.5">{{ $ticket->setor?->nome ?? 'Sem setor' }}</dd></div>
                        <div><dt class="font-medium text-zinc-500 dark:text-zinc-400">Criado em</dt><dd class="mt-0.5">{{ $ticket->created_at?->format('d/m/Y - H:i') ?? '—' }}</dd></div>
                        <div class="sm:col-span-2 xl:col-span-4"><dt class="font-medium text-zinc-500 dark:text-zinc-400">Criado por</dt><dd class="mt-0.5">{{ $ticket->user?->name ?? 'Usuário removido' }} ({{ $ticket->user?->hasAnyRole(['supervisor', 'analista', 'administrador']) ? 'Equipe' : 'Cliente' }})</dd></div>
                    </dl>

                    <div class="flex flex-wrap justify-end gap-2 border-t border-zinc-200 pt-3 dark:border-zinc-700">
                        <x-modern.button :href="route('tickets.show', ['ticket' => $ticket, 'return_to' => $returnUrl])" variant="filled" color="sky" size="sm" icon="eye">Detalhes</x-modern.button>
                        @if($manager)
                            <x-modern.button :href="route('tickets.edit', ['ticket' => $ticket, 'return_to' => $returnUrl])" variant="filled" color="amber" size="sm" icon="pencil-square">Editar</x-modern.button>
                        @endif
                        @if($administrator)
                            <x-modern.button variant="filled" color="red" size="sm" icon="trash" wire:click="confirmDelete({{ $ticket->id }})" :aria-label="'Excluir ticket '.$ticket->id">Excluir</x-modern.button>
                        @endif
                    </div>
                </div>
            </x-modern.card>
        @empty
            <x-modern.card class="py-10 text-center">
                <p class="font-medium">{{ $section === \App\Actions\Tickets\TicketListing::PENDING ? 'Nenhum ticket pendente encontrado.' : 'Nenhum ticket encontrado.' }}</p>
                @if($general && ($activeFilterCount || trim($search) !== ''))
                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Tente ajustar ou limpar os filtros.</p>
                @endif
            </x-modern.card>
        @endforelse
    </div>

    @if($tickets->hasPages())
        <x-modern.pagination :paginator="$tickets" scroll-to="body" />
    @endif

    @if($administrator)
        <x-modern.modal name="delete-ticket" wire:model.self="showDelete" title="Confirmar exclusão" class="w-full max-w-md">
            <form wire:submit="delete" class="space-y-5">
                <p class="text-sm leading-6">Deseja excluir o ticket <strong>#{{ $deleteId }} — {{ $deleteSubject }}</strong>?</p>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">As mensagens e os anexos relacionados também serão excluídos. Esta ação não pode ser desfeita.</p>
                <div class="flex flex-wrap justify-end gap-2">
                    <x-modern.button variant="outline" autofocus x-on:click="$flux.modal('delete-ticket').close()">Cancelar</x-modern.button>
                    <x-modern.button type="submit" variant="filled" color="red" wire:loading.attr="disabled">Confirmar exclusão</x-modern.button>
                </div>
            </form>
        </x-modern.modal>
    @endif
</div>
