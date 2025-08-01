@extends('adminlte::page')

@section('title', config('app.name') . ' - Relatório por empresa')

@section('content_header')
<p style="font-size: 1.2em;">
    Relatórios <i class="fas fa-angle-right" style="font-size: 0.7em;"></i> Por empresa
</p>
@endsection

@section('content')
    <!-- Exibe mensagens de sucesso -->
    @if(session('success'))
        <script>
            $(document).ready(function() {
                toastr.success('{{ session('success') }}', 'Sucesso', {
                    closeButton: true,
                    progressBar: true,
                });
            });
        </script>
    @endif


<!-- Filtros de Data e Empresa -->
<div class="card">
    <div class="card-body">
        <form action="{{ route('relatorios.horas') }}" method="GET" class="row">
            <!-- Data e Hora Início -->
            <div class="form-group col-md-3 col-12 mb-3">
                <label for="data_inicio">Data e Hora Início:</label>
                <input type="datetime-local" name="data_inicio" id="data_inicio" class="form-control" required
                       value="{{ request('data_inicio', date('Y-m-d\TH:i', strtotime('-1 month'))) }}">
            </div>

            <!-- Data e Hora Fim -->
            <div class="form-group col-md-3 col-12 mb-3">
                <label for="data_fim">Data e Hora Fim:</label>
                <input type="datetime-local" name="data_fim" id="data_fim" class="form-control" required
                       value="{{ request('data_fim', date('Y-m-d\TH:i')) }}">
            </div>

            <!-- Seleção de Empresa -->
            <div class="form-group col-md-3 col-12 mb-3">
                <label for="empresa_id">Empresa:</label>
                <select name="empresa_id" id="empresa_id" class="form-control" required>
                    <option value="">Selecione uma empresa</option>
                    @foreach($empresas as $empresa)
                        <option value="{{ $empresa->id }}" {{ request('empresa_id') == $empresa->id ? 'selected' : '' }}>
                            {{ $empresa->nome }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filtro por Setor -->
            <div class="form-group col-md-3 col-12 mb-3">
                <label for="setor_id">Setor:</label>
                <select name="setor_id" id="setor_id" class="form-control">
                    <option value="">Selecione um setor</option>
                    @foreach($setores as $setor)
                        <option value="{{ $setor->id }}" {{ request('setor_id') == $setor->id ? 'selected' : '' }}>
                            {{ $setor->nome }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Checkbox para Ignorar Tickets Abertos -->
            <div class="form-group col-md-3 col-12 d-flex align-items-center mb-3">
                <input type="hidden" name="ignore_open_tickets" value="0"> <!-- Valor padrão se desmarcado -->
                <label class="mr-2 mb-0">
                    <input type="checkbox" name="ignore_open_tickets" value="1"
                           {{ request('ignore_open_tickets', '1') == '1' ? 'checked' : '' }}>
                    Desconsiderar tickets abertos
                </label>
            </div>

            <div class="col-12 text-right">
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </div>
        </form>
    </div>
</div>


<!-- Gráfico de Tickets -->
@if(!empty($tickets))
    <div class="card mt-3">
        <div class="card-body p-0">
            <div class="chart-container" style="position: relative; width: 100%; height: 250px;">
                <canvas id="ticketsChart"></canvas>
            </div>
        </div>
    </div>
@endif


    <!-- Tabela de Resultados -->
    @if(!empty($tickets))
        <div class="card mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Tickets do período</h3>
                <button type="button" class="btn btn-secondary" onclick="window.print()">
                    <i class="fas fa-print"></i> Imprimir
                </button>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th class="d-none d-md-table-cell">ID</th> <!-- Oculto em telas pequenas -->
                            <th>Assunto</th>
                            <th class="d-none d-md-table-cell">Categoria</th> <!-- Oculto em telas pequenas -->
                            <th>Criado</th>
                            <th class="d-none d-md-table-cell">Finalizado</th> <!-- Oculto em telas pequenas -->
                            <th>Horas Gastas</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tickets as $ticket)
                            <tr>
                                <td class="d-none d-md-table-cell">{{ $ticket->id }}</td> <!-- Oculto em telas pequenas -->
                                <td>{{ $ticket->assunto }}</td>
                                <td class="d-none d-md-table-cell">{{ $ticket->categoria->nome ?? 'N/A' }}</td> <!-- Oculto em telas pequenas -->

                                <!-- Formata as datas para o padrão brasileiro -->
                                <td>{{ $ticket->created_at ? date('d/m/Y H:i', strtotime($ticket->created_at)) : 'N/A' }}</td>
                                <td class="d-none d-md-table-cell">{{ $ticket->data_hora_finalizado ? date('d/m/Y H:i', strtotime($ticket->data_hora_finalizado)) : 'N/A' }}</td>

                                <!-- Exibe horas e minutos gastos -->
                                @php
                                    $horas = intdiv($ticket->horas_gastas, 60);
                                    $minutos = $ticket->horas_gastas % 60;
                                @endphp
                                <td>{{ $horas > 0 ? $horas . 'h ' : '' }}{{ $minutos > 0 ? $minutos . 'm' : '' }}</td>
                                <td class="text-center">
                                    <a href="{{ route('tickets.show', $ticket->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> Visualizar
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <!-- Total de horas -->
                    @php
                        $totalMinutos = $tickets->sum('horas_gastas');
                        $totalHoras = intdiv($totalMinutos, 60);
                        $totalMinutosRestantes = $totalMinutos % 60;
                    @endphp

                </table>
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="d-flex flex-column flex-md-row justify-content-between">
                <div>
                    <p><strong>Total de Tickets:</strong> {{ $tickets->count() }}</p>
                    <p><strong>Horas gastas totais:</strong> {{ $totalHoras }}h {{ $totalMinutosRestantes }}m</p>
                </div>
            </div>
        </div>

    @endif
@endsection

@section('css')
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <style>
        .chart-container {
            width: 100%;
            height: auto;
        }
    </style>
@endsection
@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Recupera os dados do PHP
        const dataLabels = @json($dataLabels); // Garantir que são strings no formato ISO
        const ticketsAbertosData = @json($ticketsAbertosData);
        const ticketsFechadosData = @json($ticketsFechadosData);

        // Verifica se há labels e dados para renderizar o gráfico
        if (dataLabels.length === 0 || ticketsAbertosData.length === 0 || ticketsFechadosData.length === 0) {
            console.warn("Sem dados para exibir no gráfico.");
            return;
        }

        // Configuração do gráfico
        const ctx = document.getElementById('ticketsChart').getContext('2d');
        const ticketsChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: dataLabels, // As datas no eixo X
                datasets: [
                    {
                        label: 'Finalizados',
                        data: ticketsFechadosData, // Dados de tickets finalizados
                        borderColor: 'rgba(54, 162, 235, 1)', // Azul
                        backgroundColor: 'rgba(54, 162, 235, 0.2)', // Azul claro
                        fill: true,
                        borderWidth: 2,
                        tension: 0.3,
                    },
                    {
                        label: 'Abertos',
                        data: ticketsAbertosData, // Dados de tickets abertos
                        borderColor: 'rgba(75, 192, 192, 1)', // Verde
                        backgroundColor: 'rgba(75, 192, 192, 0.2)', // Verde claro
                        fill: true,
                        borderWidth: 2,
                        tension: 0.3,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        type: 'time', // Define o eixo X como baseado em tempo
                        time: {
                            unit: 'day', // Agrupamento por dia
                            tooltipFormat: 'dd/MM/yyyy', // Formato no tooltip
                            displayFormats: {
                                day: 'dd/MM/yyyy' // Formato na linha do gráfico
                            }
                        },
                        title: {
                            display: true,
                            text: 'Período (Tempo)' // Título do eixo X
                        }
                    },
                    y: {
                        beginAtZero: true, // Começa do zero
                        title: {
                            display: true,
                            text: 'Tickets' // Título do eixo Y
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top'
                    }
                }
            }
        });
    });
</script>
@endsection
