@extends('adminlte::page')

@section('title', config('app.name') . ' - Relatório por Analista')

@section('content_header')
    <p style="font-size: 1.2em;">
        Relatórios <i class="fas fa-angle-right" style="font-size: 0.7em;"></i> Analista
    </p>
@endsection

@section('content')
    <!-- Filtro por Data e Analista -->
    <div class="card">
        <div class="card-body">
            <form action="{{ route('relatorios.analista') }}" method="GET" class="row">
                <!-- Campo de Data e Hora Início -->
                <div class="form-group col-md-4 col-12 mb-3">
                    <label for="data_inicio">Data e Hora Início:</label>
                    <input type="datetime-local" name="data_inicio" id="data_inicio" class="form-control" required
                           value="{{ request('data_inicio', date('Y-m-d\TH:i', strtotime('-1 day'))) }}">
                </div>

                <!-- Campo de Data e Hora Fim -->
                <div class="form-group col-md-4 col-12 mb-3">
                    <label for="data_fim">Data e Hora Fim:</label>
                    <input type="datetime-local" name="data_fim" id="data_fim" class="form-control" required
                           value="{{ request('data_fim', date('Y-m-d\TH:i')) }}">
                </div>

                <!-- Campo de Seleção do Analista -->
                <div class="form-group col-md-4 col-12 mb-3">
                    <label for="usuario_id">Analista:</label>
                    <select name="usuario_id" id="usuario_id" class="form-control" required>
                        <option value="">Selecione um analista</option>
                        @foreach($usuarios as $usuario)
                            <option value="{{ $usuario->id }}" {{ request('usuario_id') == $usuario->id ? 'selected' : '' }}>
                                {{ $usuario->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group col-12 text-right">
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row mt-4">
        <div class="col-12 col-md-4 mb-3">
            <div class="chart-container" style="position: relative; height: 250px;">
                <canvas id="donutChart"></canvas>
            </div>
        </div>
        <div class="col-12 col-md-8 mb-3">
            <div class="chart-container" style="position: relative; height: 250px;">
                <canvas id="lineChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Tabela de Resultados -->
    @if(!empty($tickets))
        <div class="card mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Tickets do analista</h3>
                <div class="ml-auto">
                    <button type="button" class="btn btn-secondary" onclick="window.print()">
                        <i class="fas fa-print"></i> Imprimir
                    </button>
                </div>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th class="d-none d-md-table-cell">ID</th> <!-- Oculto em telas pequenas -->
                            <th>Assunto</th>
                            <th class="d-none d-md-table-cell">Categoria</th> <!-- Oculto em telas pequenas -->
                            <th class="d-none d-md-table-cell">Empresa</th> <!-- Oculto em telas pequenas -->
                            <th>Status</th>
                            <th>SLA (%)</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tickets as $ticket)
                            @php
                                $slaTotal = $ticket->categoria->slatotal ?? 0;
                                if ($ticket->status === 'fechado') {
                                    $dataFinalizacao = $ticket->data_hora_finalizado ? \Carbon\Carbon::parse($ticket->data_hora_finalizado) : now();
                                    $tempoDecorrido = $dataFinalizacao->diffInMinutes(\Carbon\Carbon::parse($ticket->created_at));
                                } else {
                                    $tempoDecorrido = now()->diffInMinutes(\Carbon\Carbon::parse($ticket->created_at));
                                }
                                $percentualSLA = $slaTotal > 0 ? round(($tempoDecorrido / $slaTotal) * 100) : 0;
                                $barraCor = 'bg-success';
                                if ($percentualSLA >= 50 && $percentualSLA < 80) {
                                    $barraCor = 'bg-warning';
                                } elseif ($percentualSLA >= 80 && $percentualSLA < 100) {
                                    $barraCor = 'bg-orange';
                                } elseif ($percentualSLA >= 100) {
                                    $barraCor = 'bg-danger';
                                }
                            @endphp
                            <tr>
                                <td class="d-none d-md-table-cell">{{ $ticket->id }}</td> <!-- Oculto em telas pequenas -->
                                <td>{{ $ticket->assunto }}</td>
                                <td class="d-none d-md-table-cell">{{ $ticket->categoria->nome ?? 'N/A' }}</td> <!-- Oculto em telas pequenas -->
                                <td class="d-none d-md-table-cell">{{ $ticket->empresa->nome ?? 'N/A' }}</td> <!-- Oculto em telas pequenas -->
                                <td>
                                    <span class="badge badge-{{
                                        $ticket->status === 'fechado' ? 'secondary' :
                                        ($ticket->status === 'pendente cliente' ? 'primary' :
                                        ($ticket->status === 'pendente analista' ? 'warning' : 'success'))
                                    }}">
                                        {{ ucfirst($ticket->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="mr-2">{{ $percentualSLA }}%</span>
                                        <div class="progress" style="width: 80px;">
                                            <div class="progress-bar {{ $barraCor }} progress-bar-striped" role="progressbar"
                                                style="width: {{ min($percentualSLA, 100) }}%;"
                                                aria-valuenow="{{ $percentualSLA }}" aria-valuemin="0" aria-valuemax="100">
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('tickets.show', $ticket->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> Visualizar
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                </table>

            </div>

        </div>
        <div class="card-body pt-0">
            <div class="d-flex flex-column flex-md-row justify-content-between">
                <div>
                    <strong>Total de Tickets:</strong> {{ $totalTickets }}
                </div>
                <div>
                    <strong>Total de Tickets Abertos:</strong> {{ $totalTicketsAbertos }}
                </div>
                <div>
                    <strong>Total de Tickets Fechados:</strong> {{ $totalTicketsFechados }}
                </div>
            </div>
        </div>
    @endif
@endsection

@section('css')
<style>
    /* Ajuste de responsividade para progress-bar em telas pequenas */
    .progress-bar {
        min-width: 20px;
    }
    .chart-container {
        width: 100%;
        height: auto;
    }
</style>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Dados para o gráfico de rosquinha
        const donutData = {
            labels: ['Abertos', 'Fechados'],
            datasets: [{
                data: [{{ $totalTicketsAbertos }}, {{ $totalTicketsFechados }}],
                backgroundColor: ['#66BB6A', '#42A5F5'],
                hoverBackgroundColor: ['#4CAF50', '#2196F3'],
                borderColor: '#fff',
                borderWidth: 2
            }]
        };

        const donutConfig = {
            type: 'doughnut',
            data: donutData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        };

        const donutChart = new Chart(document.getElementById('donutChart').getContext('2d'), donutConfig);
        window.BtxTheme?.registerChart(donutChart);

        // Dados para o gráfico de linhas
        const lineData = {
            labels: @json($lineChartData['labels']), // Labels em formato ISO 8601
            datasets: [
                {
                    label: 'Criados',
                    data: @json($lineChartData['created']),
                    borderColor: 'rgba(129,199,132, 0.7)',
                    backgroundColor: 'rgba(129,199,132, 0.2)',
                    fill: true,
                    tension: 0.4,
                },
                {
                    label: 'Finalizados',
                    data: @json($lineChartData['finalized']),
                    borderColor: 'rgba(66,165,245, 0.7)',
                    backgroundColor: 'rgba(66,165,245, 0.2)',
                    fill: true,
                    tension: 0.4,
                },
                {
                    label: 'Transferidos',
                    data: @json($lineChartData['transferred']),
                    borderColor: 'rgba(156,39,176, 0.7)',
                    backgroundColor: 'rgba(156,39,176, 0.2)',
                    fill: true,
                    tension: 0.4,
                },
                {
                    label: 'Assumidos',
                    data: @json($lineChartData['assumed']),
                    borderColor: 'rgba(255, 193, 7, 0.7)',
                    backgroundColor: 'rgba(255, 193, 7, 0.2)',
                    fill: true,
                    tension: 0.4,
                }
            ]
        };

        const isHourly = @json($intervaloHoras) <= 24;

        const lineConfig = {
            type: 'line',
            data: lineData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        type: 'time', // Tipo "time" para usar labels como datas
                        time: {
                            unit: isHourly ? 'hour' : 'day', // Define a granularidade
                            tooltipFormat: isHourly ? 'dd/MM/yyyy HH:mm' : 'dd/MM/yyyy',
                            displayFormats: {
                                hour: 'HH:mm', // Exibe horas no eixo
                                day: 'dd/MM/yyyy' // Exibe dias no eixo
                            }
                        },
                        title: {
                            display: true,
                            text: 'Período (Tempo)'
                        },
                        ticks: {
                            autoSkip: false,
                            maxRotation: 0
                        }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Tickets'
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            title: function(tooltipItems) {
                                const date = tooltipItems[0].parsed.x; // Pega o valor do eixo X
                                return isHourly
                                    ? new Date(date).toLocaleString('pt-BR', { dateStyle: 'short', timeStyle: 'short' })
                                    : new Date(date).toLocaleDateString('pt-BR'); // Formato diário
                            }
                        }
                    }
                }
            }
        };

        const lineChart = new Chart(document.getElementById('lineChart').getContext('2d'), lineConfig);
        window.BtxTheme?.registerChart(lineChart);
    });
</script>
@endsection








