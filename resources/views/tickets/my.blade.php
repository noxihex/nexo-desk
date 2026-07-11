@extends('adminlte::page')

@section('title', config('app.name') . ' - Meus Tickets')

@section('content_header')
<p style="font-size: 1.2em;">
    Tickets <i class="fas fa-angle-right" style="font-size: 0.7em;"></i> Meus tickets
</p>

    <!-- Linha com o botão de ocultar/mostrar fechados -->
    <div class="d-flex justify-content-end align-items-center mt-2">
        <!-- Botão Ocultar/Mostrar Fechados -->
        <a href="{{ route('tickets.my', ['sort' => request('sort'), 'showClosed' => $showClosed == '1' ? '0' : '1']) }}"
           id="toggleClosed"
           class="btn {{ $showClosed == '1' ? 'btn-success' : 'btn-secondary' }}">
            <i class="fas {{ $showClosed == '1' ? 'fa-eye-slash' : 'fa-eye' }}"></i>
            {{ $showClosed == '1' ? 'Ocultar Fechados' : 'Mostrar Fechados' }}
        </a>
    </div>
@endsection

@include('layouts.notificahtml')

@section('content_top_nav_right')
    @role('analista|supervisor|administrador')
        @php
            $ticketsAtencao = app('App\Http\Controllers\VisaoGeralController')->obterTicketsAtencao();
            $totalTicketsAtencao = count($ticketsAtencao);

            $ticketsSemAnalista = app('App\Http\Controllers\VisaoGeralController')->obterTicketsSemAnalista();
            $totalTicketsSemAnalista = count($ticketsSemAnalista);
        @endphp

        <!-- Alerta Amarelo - Tickets em Meu Grupo Aguardando Atendimento -->
        @if ($totalTicketsSemAnalista > 0)
            <li class="nav-item">
                <a href="#" class="nav-link" data-toggle="modal" data-target="#ticketsSemAnalistaModal">
                    <span class="badge badge-warning">
                        <span class="badge-text-full">{{ $totalTicketsSemAnalista }} Tickets em seu grupo aguardando atendimento</span>
                        <span class="badge-text-compact">({{ $totalTicketsSemAnalista }}) Aguardando atendimento</span>
                    </span>
                </a>
            </li>
        @endif

        <!-- Alerta Vermelho - Tickets que Requerem Atenção -->
        @if ($totalTicketsAtencao > 0)
            <li class="nav-item">
                <a href="#" class="nav-link" data-toggle="modal" data-target="#ticketsAtencaoModal">
                    <span class="badge badge-danger">
                        <span class="badge-text-full">{{ $totalTicketsAtencao }} Tickets precisam de sua atenção</span>
                        <span class="badge-text-compact">({{ $totalTicketsAtencao }}) Requer atenção</span>
                    </span>
                </a>
            </li>
        @endif
    @endrole
@endsection




@section('content')

@role('analista|supervisor|administrador')
    @if ($totalTicketsAtencao > 0)
        <!-- Modal para Tickets que Requerem Atenção -->
        <div class="modal fade" id="ticketsAtencaoModal" tabindex="-1" role="dialog" aria-labelledby="ticketsAtencaoLabel" aria-hidden="true">
            <div class="modal-dialog modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="ticketsAtencaoLabel">Tickets que Requerem Atenção</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <ul>
                            @foreach ($ticketsAtencao as $ticketId)
                                <li><a href="{{ url('/tickets/' . $ticketId) }}">Ticket #{{ $ticketId }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($totalTicketsSemAnalista > 0)
        <!-- Modal para Tickets em Meu Grupo Aguardando Atendimento -->
        <div class="modal fade" id="ticketsSemAnalistaModal" tabindex="-1" role="dialog" aria-labelledby="ticketsSemAnalistaLabel" aria-hidden="true">
            <div class="modal-dialog modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="ticketsSemAnalistaLabel">Tickets Aguardando Atendimento</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <ul>
                            @foreach ($ticketsSemAnalista as $ticketId)
                                <li><a href="{{ url('/tickets/' . $ticketId) }}">Ticket #{{ $ticketId }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endrole



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

<div class="row">
    @foreach($tickets as $ticket)
        <div class="col-12 mb-1">
            <div class="card">
                <div class="card-header" style="padding: 0.5rem 1rem;">
                    <h5 class="card-title mb-0">Ticket <strong>#{{ $ticket->id }}</strong></h5>
                </div>
                <div class="card-body" style="padding: 0.5rem 1rem;">
                    <div class="row">
                        <!-- Primeira coluna: Assunto e Categoria -->
                        <div class="col-md-2">
                            <p class="mb-1"><strong>Assunto:</strong> {{ $ticket->assunto }}</p>
                            <p class="mb-1"><strong>Categoria:</strong> {{ $ticket->categoria->nome ?? 'N/A' }}</p>
                        </div>

                        <!-- Segunda coluna: Cliente e Empresa -->
                        <div class="col-md-2">
                            <p class="mb-1"><strong>Contato:</strong> {{ $ticket->cliente ? $ticket->cliente->name : '-' }}</p>
                            <p class="mb-1"><strong>Empresa:</strong> {{ $ticket->empresa ? $ticket->empresa->nome : '-' }}</p>
                        </div>

                        <!-- Terceira coluna: Criado Por, Data de Criação e Data de Modificação -->
                        <div class="col-md-2">
                            <p class="mb-1"><strong>Criado Por:</strong> {{ $ticket->user->name }}</p>
                            <p class="mb-1"><strong>Data de Criação:</strong> {{ $ticket->created_at->format('d/m/Y H:i') }}</p>
                            <p class="mb-1"><strong>Modificado:</strong> {{ $ticket->updated_at->format('d/m/Y H:i') }}</p>
                        </div>

                        <!-- Quarta coluna: Grupo, Setor e Analista -->
                        <div class="col-md-2">
                            <p class="mb-1"><strong>Grupo:</strong> {{ $ticket->grupo ? $ticket->grupo->nome : '-' }}</p>
                            <p class="mb-1"><strong>Setor:</strong> {{ $ticket->setor ? $ticket->setor->nome : '-' }}</p>
                            <p class="mb-1"><strong>Atribuído ao Analista:</strong> {{ $ticket->analista ? $ticket->analista->name : '-' }}</p>
                        </div>

                        <!-- Quinta coluna: Status, Origem, SLA e Botões de Ação -->
                        <div class="col-md-4 d-flex justify-content-between align-items-start">
                            <div>
                                <p class="mb-1"><strong>Status:</strong>
                                    <span class="badge
                                        @if($ticket->status == 'aberto') badge-success
                                        @elseif($ticket->status == 'pendente cliente') badge-primary
                                        @elseif($ticket->status == 'pendente analista') badge-warning
                                        @elseif($ticket->status == 'fechado') badge-secondary
                                        @endif">
                                        {{ ucfirst($ticket->status) }}
                                    </span>
                                </p>
                                <p class="mb-1"><strong>Prazo:</strong> {{ $ticket->prazo ? $ticket->prazo->format('d/m/Y') : '-' }}</p>

                                <!-- Cálculo de SLA -->
@php
if ($ticket->status === 'fechado') {
    $dataFinalizacao = $ticket->data_hora_finalizado ? \Carbon\Carbon::parse($ticket->data_hora_finalizado) : \Carbon\Carbon::parse($ticket->updated_at);
    $minutosDecorridos = $dataFinalizacao->diffInMinutes(\Carbon\Carbon::parse($ticket->created_at));
} else {
    $minutosDecorridos = now()->diffInMinutes(\Carbon\Carbon::parse($ticket->created_at));
}

$slaTotal = $ticket->categoria->slatotal ?? 0;
$slaPercentual = $slaTotal > 0 ? ($minutosDecorridos / $slaTotal) * 100 : 0;
$barraPercentual = min(100, $slaPercentual); // Limita a largura da barra a 100%
@endphp

<div class="d-flex align-items-center mb-1">
<p class="mb-0"><strong>SLA:</strong></p>
<div class="progress ml-2" style="width: 80px; height: 15px;">
    <div class="progress-bar @if($slaPercentual < 50) bg-success @elseif($slaPercentual < 100) bg-warning @else bg-danger @endif"
        role="progressbar"
        style="width: {{ floor($barraPercentual) }}%;"
        aria-valuenow="{{ floor($barraPercentual) }}"
        aria-valuemin="0"
        aria-valuemax="100">
    </div>
</div>
<span class="ml-2 font-weight-bold">{{ floor($slaPercentual) }}%</span>
</div>

                            </div>

                            <!-- Botões de ação alinhados à direita -->
                            <div class="d-flex flex-column button-container ml-3">
                                <a href="{{ route('tickets.show', $ticket->id) }}" class="btn btn-sm btn-info mb-1">
                                    <i class="fas fa-eye"></i> Detalhes
                                </a>
                                @role('supervisor|administrador')
                                <a href="{{ route('tickets.edit', $ticket->id) }}" class="btn btn-sm btn-warning mb-1">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                @endrole
                                @role('supervisor|administrador')
                                <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteModal{{ $ticket->id }}">
                                    <i class="fas fa-trash"></i> Excluir
                                </button>
                                @endrole
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>


<!-- Modais de Confirmação de Exclusão para Cada Ticket -->
@foreach($tickets as $ticket)
    <div class="modal fade" id="deleteModal{{ $ticket->id }}" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel{{ $ticket->id }}" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Exclusão</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Tem certeza que deseja excluir o ticket <strong>{{ $ticket->assunto }}</strong>?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <form action="{{ route('tickets.destroy', $ticket->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Sim, Excluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach
</div>

<div class="d-flex justify-content-end">
    {{ $tickets->appends(['sort' => request('sort'), 'showClosed' => request('showClosed')])->links() }}
</div>
@endsection

@section('css')
@include('layouts.notificacss')
    <style>
         /* Exibe texto completo em telas maiores */
 .badge-text-full {
        display: inline;
    }
    .badge-text-compact {
        display: none;
    }

    /* Em telas menores, exibe o texto compacto */
    @media (max-width: 576px) {
        .badge-text-full {
            display: none;
        }
        .badge-text-compact {
            display: inline;
        }
    }
        .card-body p {
            margin-bottom: 0.2rem;
        }
        .col-12.mb-1 {
            margin-bottom: 0.6rem;
        }
        .button-container .btn {
            min-width: 110px;
            text-align: center;
        }
    </style>
@endsection


@section('js')
@include('layouts.notificajs')
@endsection
