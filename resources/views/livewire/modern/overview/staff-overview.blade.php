@php
    $metricCards = [
        ['key' => 'mine', 'label' => 'Meus tickets abertos', 'color' => 'green', 'icon' => 'ticket', 'href' => route('tickets.my')],
        ['key' => 'sector', 'label' => 'Tickets abertos em meu setor', 'color' => 'sky', 'icon' => 'user-group', 'href' => $userSectorId ? route('tickets.index', ['sort' => 'created_at', 'setor_id' => $userSectorId]) : null],
        ['key' => 'unassigned', 'label' => 'Tickets não assumidos em meu setor', 'color' => 'amber', 'icon' => 'user-minus', 'ids' => $metrics['unassignedIds']],
        ['key' => 'attention', 'label' => 'Tickets que requerem minha atenção', 'color' => 'red', 'icon' => 'exclamation-triangle', 'ids' => $metrics['attentionIds']],
    ];
    $metricStyles = [
        'green' => 'border-green-200 bg-green-50 text-green-950 dark:border-green-900 dark:bg-green-950/40 dark:text-green-100',
        'sky' => 'border-sky-200 bg-sky-50 text-sky-950 dark:border-sky-900 dark:bg-sky-950/40 dark:text-sky-100',
        'amber' => 'border-amber-200 bg-amber-50 text-amber-950 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-100',
        'red' => 'border-red-200 bg-red-50 text-red-950 dark:border-red-900 dark:bg-red-950/40 dark:text-red-100',
    ];
    $metricIconStyles = [
        'green' => 'bg-green-200 text-green-800 dark:bg-green-900 dark:text-green-200',
        'sky' => 'bg-sky-200 text-sky-800 dark:bg-sky-900 dark:text-sky-200',
        'amber' => 'bg-amber-200 text-amber-800 dark:bg-amber-900 dark:text-amber-200',
        'red' => 'bg-red-200 text-red-800 dark:bg-red-900 dark:text-red-200',
    ];
    $columns = [
        ['tickets' => $ticketsAbertos, 'title' => 'Abertos', 'color' => 'green'],
        ['tickets' => $ticketsPendenteCliente, 'title' => 'Pendente cliente', 'color' => 'blue'],
        ['tickets' => $ticketsPendenteAnalista, 'title' => 'Pendente analista', 'color' => 'amber'],
        ['tickets' => $ticketsFechados, 'title' => 'Fechados', 'color' => 'zinc'],
    ];
    $columnStyles = [
        'green' => 'border-green-200 bg-green-50 text-green-900 dark:border-green-900 dark:bg-green-950/40 dark:text-green-100',
        'blue' => 'border-blue-200 bg-blue-50 text-blue-900 dark:border-blue-900 dark:bg-blue-950/40 dark:text-blue-100',
        'amber' => 'border-amber-200 bg-amber-50 text-amber-900 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-100',
        'zinc' => 'border-zinc-300 bg-zinc-100 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100',
    ];
@endphp

<div class="space-y-6">
    <section aria-labelledby="overview-indicators">
        <h2 id="overview-indicators" class="sr-only">Indicadores dos tickets</h2>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach($metricCards as $metric)
                <article class="overflow-hidden rounded-xl border {{ $metricStyles[$metric['color']] }}">
                    <div class="flex items-start justify-between gap-4 p-5">
                        <div>
                            <p class="text-3xl font-semibold tabular-nums">{{ $metrics[$metric['key']] }}</p>
                            <h3 class="mt-1 text-sm font-medium leading-5">{{ $metric['label'] }}</h3>
                        </div>
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-full {{ $metricIconStyles[$metric['color']] }}">
                            <flux:icon :name="$metric['icon']" class="size-5" aria-hidden="true" />
                        </span>
                    </div>

                    @if($metric['href'] ?? null)
                        <a href="{{ $metric['href'] }}" class="flex items-center justify-between border-t border-current/10 px-5 py-3 text-sm font-medium hover:bg-black/5 focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-blue-500 dark:hover:bg-white/5">
                            Ver tickets <flux:icon.arrow-right class="size-4" aria-hidden="true" />
                        </a>
                    @elseif(isset($metric['ids']))
                        <details class="group border-t border-current/10">
                            <summary class="flex cursor-pointer list-none items-center justify-between px-5 py-3 text-sm font-medium hover:bg-black/5 focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-blue-500 dark:hover:bg-white/5">
                                <span>Ver tickets</span>
                                <flux:icon.chevron-down class="size-4 transition-transform group-open:rotate-180" aria-hidden="true" />
                            </summary>
                            <div class="flex flex-wrap gap-2 px-5 pb-4">
                                @forelse($metric['ids'] as $ticketId)
                                    <a href="{{ route('tickets.show', ['ticket' => $ticketId, 'return_to' => $returnUrl]) }}" class="rounded-md bg-white/70 px-2 py-1 text-sm font-semibold hover:underline focus-visible:outline-2 focus-visible:outline-blue-500 dark:bg-black/20">#{{ $ticketId }}</a>
                                @empty
                                    <p class="text-sm opacity-70">Nenhum ticket.</p>
                                @endforelse
                            </div>
                        </details>
                    @endif
                </article>
            @endforeach
        </div>
    </section>

    @if($manager)
        <x-modern.card size="sm">
            <div class="space-y-4">
                <div class="grid gap-4 {{ $administrator ? 'md:grid-cols-2 xl:grid-cols-4' : 'max-w-sm' }}">
                    <x-modern.select name="order" label="Ordenar por" :options="['desc' => 'Tickets mais novos', 'asc' => 'Tickets mais antigos']" wire:model.live="order" />
                    @if($administrator)
                        <x-modern.select name="analista" label="Analista" placeholder="Todos os analistas" :options="$analistas->pluck('name', 'id')->all()" wire:model.live="analista" searchable />
                        <x-modern.select name="setor" label="Setor" placeholder="Todos os setores" :options="$setores->pluck('nome', 'id')->all()" wire:model.live="setor" searchable />
                        <x-modern.select name="empresa" label="Empresa" placeholder="Todas as empresas" :options="$empresas->pluck('nome', 'id')->all()" wire:model.live="empresa" searchable />
                    @endif
                </div>
                @if($order !== 'desc' || $analista !== '' || $setor !== '' || $empresa !== '')
                    <div class="flex justify-end">
                        <x-modern.button type="button" variant="ghost" wire:click="clearFilters">Limpar filtros</x-modern.button>
                    </div>
                @endif
            </div>
        </x-modern.card>

        <section aria-labelledby="overview-board" class="space-y-3">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 id="overview-board" class="text-lg font-semibold text-zinc-950 dark:text-white">Tickets por status</h2>
                </div>
                <span wire:loading wire:target="order,analista,setor,empresa,clearFilters" class="text-sm text-zinc-500">Atualizando quadro...</span>
            </div>

            <div class="grid items-start gap-4 md:grid-cols-2 xl:grid-cols-4" aria-busy="false" wire:loading.attr="aria-busy">
                @foreach($columns as $column)
                    <section wire:key="overview-column-{{ $column['color'] }}" class="overflow-hidden rounded-xl border border-zinc-200 bg-zinc-100/70 dark:border-zinc-800 dark:bg-zinc-900/70" aria-labelledby="overview-column-{{ $column['color'] }}">
                        <header class="sticky top-0 z-10 flex items-center justify-between gap-3 border-b px-4 py-3 {{ $columnStyles[$column['color']] }}">
                            <h3 id="overview-column-{{ $column['color'] }}" class="font-semibold">{{ $column['title'] }}</h3>
                            <span class="rounded-full bg-white/70 px-2 py-0.5 text-xs font-semibold tabular-nums dark:bg-black/20">{{ $column['tickets']->count() }}</span>
                        </header>
                        <div class="max-h-[70vh] space-y-3 overflow-y-auto p-3">
                            @forelse($column['tickets'] as $ticket)
                                <article wire:key="overview-ticket-{{ $ticket->id }}" class="rounded-lg border border-zinc-200 bg-white p-3 shadow-xs dark:border-zinc-700 dark:bg-zinc-800">
                                    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Ticket #{{ $ticket->id }}</p>
                                    <h4 class="mt-1 break-words text-sm font-semibold text-zinc-950 dark:text-white">{{ $ticket->assunto }}</h4>
                                    <dl class="mt-3 space-y-1.5 text-xs">
                                        <div><dt class="inline font-medium text-zinc-500 dark:text-zinc-400">Empresa:</dt> <dd class="inline">{{ $ticket->empresa?->nome ?? 'Sem empresa' }}</dd></div>
                                        <div><dt class="inline font-medium text-zinc-500 dark:text-zinc-400">Categoria:</dt> <dd class="inline">{{ $ticket->categoria?->nome ?? 'Sem categoria' }}</dd></div>
                                        <div><dt class="inline font-medium text-zinc-500 dark:text-zinc-400">Analista:</dt> <dd class="inline">{{ $ticket->analista?->name ?? 'Não atribuído' }}</dd></div>
                                    </dl>
                                    <div class="mt-3 flex justify-end border-t border-zinc-200 pt-3 dark:border-zinc-700">
                                        <x-modern.button :href="route('tickets.show', ['ticket' => $ticket, 'return_to' => $returnUrl])" variant="filled" color="sky" size="sm" icon="eye">Detalhes</x-modern.button>
                                    </div>
                                </article>
                            @empty
                                <div class="rounded-lg border border-dashed border-zinc-300 px-3 py-8 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">Nenhum ticket.</div>
                            @endforelse
                        </div>
                    </section>
                @endforeach
            </div>
        </section>
    @endif
</div>
