@php
    $metricCards = [
        ['key' => 'mine', 'label' => 'Meus tickets abertos', 'color' => 'green', 'icon' => 'ticket'],
        ['key' => 'company', 'label' => 'Tickets abertos em minha empresa', 'color' => 'sky', 'icon' => 'user-group'],
        ['key' => 'awaiting', 'label' => 'Tickets aguardando minha resposta', 'color' => 'amber', 'icon' => 'exclamation-triangle'],
        ['key' => 'closed', 'label' => 'Tickets fechados em minha empresa', 'color' => 'zinc', 'icon' => 'check-circle'],
    ];
    $metricStyles = [
        'green' => 'border-green-200 bg-green-50 text-green-950 dark:border-green-900 dark:bg-green-950/40 dark:text-green-100',
        'sky' => 'border-sky-200 bg-sky-50 text-sky-950 dark:border-sky-900 dark:bg-sky-950/40 dark:text-sky-100',
        'amber' => 'border-amber-200 bg-amber-50 text-amber-950 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-100',
        'zinc' => 'border-zinc-300 bg-zinc-100 text-zinc-950 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100',
    ];
    $metricIconStyles = [
        'green' => 'bg-green-200 text-green-800 dark:bg-green-900 dark:text-green-200',
        'sky' => 'bg-sky-200 text-sky-800 dark:bg-sky-900 dark:text-sky-200',
        'amber' => 'bg-amber-200 text-amber-800 dark:bg-amber-900 dark:text-amber-200',
        'zinc' => 'bg-zinc-200 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200',
    ];
@endphp

<div class="space-y-6">
    <section aria-labelledby="client-overview-indicators">
        <h2 id="client-overview-indicators" class="sr-only">Indicadores dos tickets</h2>
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

                    @if($metric['key'] === 'mine' || $companyAvailable)
                        <details class="group border-t border-current/10">
                            <summary class="flex cursor-pointer list-none items-center justify-between px-5 py-3 text-sm font-medium hover:bg-black/5 focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-blue-500 dark:hover:bg-white/5">
                                <span>Ver tickets</span>
                                <flux:icon.chevron-down class="size-4 transition-transform group-open:rotate-180" aria-hidden="true" />
                            </summary>
                            <div class="flex max-h-36 flex-wrap gap-2 overflow-y-auto px-5 pb-4">
                                @forelse($ticketIds[$metric['key']] as $ticketId)
                                    <a href="{{ route('tickets.cliente.show', ['id' => $ticketId, 'return_to' => route('home')]) }}" class="rounded-md bg-white/70 px-2 py-1 text-sm font-semibold hover:underline focus-visible:outline-2 focus-visible:outline-blue-500 dark:bg-black/20">#{{ $ticketId }}</a>
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

    <x-modern.card>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-base font-semibold text-zinc-950 dark:text-white">Precisa de atendimento?</h2>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Abra um novo ticket ou acompanhe todas as solicitações disponíveis para você.</p>
            </div>
            <div class="flex flex-wrap justify-end gap-2">
                <x-modern.button :href="route('tickets.cliente.index')" variant="outline" icon="ticket">Ver meus tickets</x-modern.button>
                <x-modern.button :href="route('tickets.cliente.create', ['return_to' => route('home')])" variant="filled" color="green" icon="plus">Criar ticket</x-modern.button>
            </div>
        </div>
    </x-modern.card>
</div>
