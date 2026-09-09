<div class="report-content space-y-6">
    <x-modern.card class="report-filters">
        <form wire:submit="applyFilters" class="space-y-5">
            <h2 class="text-lg font-semibold">Filtros do relatório</h2>
            <div class="grid gap-5 sm:grid-cols-2">
                <x-modern.input name="data_inicio" label="Data e hora de início" type="datetime-local" wire:model="data_inicio" required />
                <x-modern.input name="data_fim" label="Data e hora de fim" type="datetime-local" wire:model="data_fim" required />
                @if($company)
                    <x-modern.select name="empresa_id" label="Empresa" wire:model="empresa_id" :options="['' => 'Selecione uma empresa'] + $empresas->sortBy('nome')->pluck('nome', 'id')->all()" required />
                    <x-modern.select name="setor_id" label="Setor" wire:model="setor_id" :options="['' => 'Todos os setores'] + $setores->sortBy('nome')->pluck('nome', 'id')->all()" />
                @else
                    <x-modern.select name="usuario_id" label="Analista" wire:model="usuario_id" :options="['' => 'Selecione um analista'] + $usuarios->sortBy('name')->pluck('name', 'id')->all()" required />
                @endif
            </div>
            <div class="flex flex-wrap items-center justify-between gap-4">
                @if($company)<x-modern.checkbox name="ignore_open_tickets" label="Desconsiderar tickets abertos" wire:model="ignore_open_tickets" />@endif
                <x-modern.button type="submit" icon="funnel" wire:loading.attr="disabled">Gerar relatório</x-modern.button>
            </div>
            <p role="status" wire:loading wire:target="applyFilters" class="text-sm text-zinc-500">Gerando relatório...</p>
        </form>
    </x-modern.card>

    @if(!$generated)
        <x-modern.card class="py-12 text-center">
            <h2 class="text-lg font-semibold">Selecione os filtros para começar</h2>
            <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Informe o período e {{ $company ? 'a empresa' : 'o analista' }} para consultar os tickets.</p>
        </x-modern.card>
    @else
        @php
            $selection = $company ? $empresas->firstWhere('id', $applied['empresa_id'])?->nome : $usuarios->firstWhere('id', $applied['usuario_id'])?->name;
            $minutes = (int) collect($tickets)->sum('horas_gastas');
        @endphp
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold">{{ $selection ?? 'Registro selecionado' }}</h2>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ \Carbon\Carbon::parse($applied['data_inicio'])->format('d/m/Y H:i') }} até {{ \Carbon\Carbon::parse($applied['data_fim'])->format('d/m/Y H:i') }}</p>
                @if($company)
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $setores->firstWhere('id', $applied['setor_id'])?->nome ?? 'Todos os setores' }} · {{ $applied['ignore_open_tickets'] === '1' ? 'Somente tickets fechados no período' : 'Tickets criados ou fechados no período' }}</p>
                @endif
            </div>
            <x-modern.button variant="outline" icon="printer" x-on:click="window.print()" class="report-print-button">Imprimir</x-modern.button>
        </div>
        <div class="grid gap-4 sm:grid-cols-3">
            <x-modern.card><p class="text-sm text-zinc-500">Total de tickets</p><p class="mt-2 text-3xl font-semibold">{{ count($tickets) }}</p></x-modern.card>
            @if($company)
                <x-modern.card><p class="text-sm text-zinc-500">Horas gastas totais</p><p class="mt-2 text-3xl font-semibold">{{ intdiv($minutes, 60) }}h {{ $minutes % 60 }}m</p></x-modern.card>
            @else
                <x-modern.card><p class="text-sm text-zinc-500">Tickets abertos</p><p class="mt-2 text-3xl font-semibold">{{ $totalTicketsAbertos }}</p></x-modern.card>
                <x-modern.card><p class="text-sm text-zinc-500">Tickets fechados</p><p class="mt-2 text-3xl font-semibold">{{ $totalTicketsFechados }}</p></x-modern.card>
            @endif
        </div>
        @if($company)
            <x-modern.reports.chart :labels="$dataLabels" :series="[['label' => 'Finalizados', 'values' => $ticketsFechadosData], ['label' => 'Abertos', 'values' => $ticketsAbertosData]]" />
            <p class="text-sm text-zinc-500 dark:text-zinc-400">O gráfico mostra aberturas e fechamentos por dia para a empresa e o setor, independentemente da opção de desconsiderar tickets abertos na tabela.</p>
        @else
            <x-modern.reports.chart :labels="$lineChartData['labels']" :series="[['label' => 'Criados', 'values' => $lineChartData['created']], ['label' => 'Finalizados', 'values' => $lineChartData['finalized']], ['label' => 'Transferidos', 'values' => $lineChartData['transferred']], ['label' => 'Assumidos', 'values' => $lineChartData['assumed']]]" title="Atividades do analista" />
            <p class="text-sm text-zinc-500 dark:text-zinc-400">O gráfico contabiliza as ações realizadas pelo usuário. A tabela mostra os tickets atribuídos a ele e criados no período.</p>
            <x-modern.card class="flex flex-wrap items-center gap-6">
                @php
                    $closedPercent = $totalTickets ? 100 * $totalTicketsFechados / $totalTickets : 0;
                @endphp
                <svg viewBox="0 0 100 100" class="size-32 shrink-0" role="img" aria-label="Distribuição: {{ $totalTicketsAbertos }} abertos e {{ $totalTicketsFechados }} fechados">
                    <circle cx="50" cy="50" r="38" fill="none" stroke="{{ $totalTickets ? '#16a34a' : '#a1a1aa' }}" stroke-width="12" />
                    <circle cx="50" cy="50" r="38" fill="none" stroke="#2563eb" stroke-width="12" pathLength="100" stroke-dasharray="{{ $closedPercent }} {{ 100 - $closedPercent }}" transform="rotate(-90 50 50)" />
                </svg>
                <div><h2 class="text-lg font-semibold">Distribuição dos tickets</h2><p class="mt-2">{{ $totalTicketsAbertos }} abertos · {{ $totalTicketsFechados }} fechados</p></div>
            </x-modern.card>
        @endif
        <x-modern.card class="report-results overflow-hidden">
            <h2 class="mb-4 text-lg font-semibold">{{ $company ? 'Tickets do período' : 'Tickets do analista' }}</h2>
            <x-modern.table>
                <x-modern.table.columns>
                    <x-modern.table.column>ID</x-modern.table.column>
                    <x-modern.table.column>Assunto</x-modern.table.column>
                    <x-modern.table.column>Categoria</x-modern.table.column>
                    @if($company)
                        <x-modern.table.column>Criado</x-modern.table.column>
                        <x-modern.table.column>Finalizado</x-modern.table.column>
                        <x-modern.table.column>Horas gastas</x-modern.table.column>
                    @else
                        <x-modern.table.column>Empresa</x-modern.table.column>
                        <x-modern.table.column>Status</x-modern.table.column>
                        <x-modern.table.column>SLA (%)</x-modern.table.column>
                    @endif
                    <x-modern.table.column class="report-ticket-action">Ações</x-modern.table.column>
                </x-modern.table.columns>
                <x-modern.table.rows>
                    @forelse($tickets as $ticket)
                        <x-modern.table.row :key="$ticket->id">
                            <x-modern.table.cell>{{ $ticket->id }}</x-modern.table.cell>
                            <x-modern.table.cell class="max-w-xs whitespace-normal">{{ $ticket->assunto }}</x-modern.table.cell>
                            <x-modern.table.cell class="whitespace-normal">{{ $ticket->categoria?->nome ?? 'N/A' }}</x-modern.table.cell>
                            @if($company)
                                <x-modern.table.cell>{{ $ticket->created_at?->format('d/m/Y H:i') ?? 'N/A' }}</x-modern.table.cell>
                                <x-modern.table.cell>{{ $ticket->data_hora_finalizado ? \Carbon\Carbon::parse($ticket->data_hora_finalizado)->format('d/m/Y H:i') : 'N/A' }}</x-modern.table.cell>
                                <x-modern.table.cell>{{ intdiv((int) $ticket->horas_gastas, 60) }}h {{ (int) $ticket->horas_gastas % 60 }}m</x-modern.table.cell>
                            @else
                                @php
                                    $end = $ticket->status === 'fechado' && $ticket->data_hora_finalizado ? \Carbon\Carbon::parse($ticket->data_hora_finalizado) : now();
                                    $elapsed = \App\Support\ElapsedTime::wholeMinutes($ticket->created_at, $end);
                                    $sla = $ticket->categoria?->slatotal ?? 0;
                                    $percentage = $sla > 0 ? (int) round(100 * $elapsed / $sla) : 0;
                                @endphp
                                <x-modern.table.cell class="whitespace-normal">{{ $ticket->empresa?->nome ?? 'N/A' }}</x-modern.table.cell>
                                <x-modern.table.cell><x-modern.badge :color="match ($ticket->status) { 'fechado' => 'zinc', 'pendente cliente' => 'blue', 'pendente analista' => 'yellow', default => 'green' }">{{ ucfirst($ticket->status) }}</x-modern.badge></x-modern.table.cell>
                                <x-modern.table.cell><x-modern.badge :color="$percentage >= 100 ? 'red' : ($percentage >= 80 ? 'orange' : ($percentage >= 50 ? 'yellow' : 'green'))">{{ $percentage }}%</x-modern.badge></x-modern.table.cell>
                            @endif
                            <x-modern.table.cell class="report-ticket-action"><x-modern.button :href="route('tickets.show', $ticket->id)" variant="filled" color="sky" size="sm" icon="eye">Visualizar</x-modern.button></x-modern.table.cell>
                        </x-modern.table.row>
                    @empty
                        <x-modern.table.row><x-modern.table.cell colspan="7" class="py-10 text-center">Nenhum ticket encontrado para os filtros selecionados.</x-modern.table.cell></x-modern.table.row>
                    @endforelse
                </x-modern.table.rows>
            </x-modern.table>
        </x-modern.card>
    @endif
</div>
