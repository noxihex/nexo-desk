@extends('adminlte::page')

@section('title', config('app.name') . ' - Meus Tickets')

@section('content_header')
<x-page-header title="Meus tickets" :breadcrumbs="['Tickets', 'Meus tickets']" />

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
    @parent
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
                                <li><a href="{{ route('tickets.show', ['ticket' => $ticketId, 'return_to' => url()->full()]) }}">Ticket #{{ $ticketId }}</a></li>
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
                                <li><a href="{{ route('tickets.show', ['ticket' => $ticketId, 'return_to' => url()->full()]) }}">Ticket #{{ $ticketId }}</a></li>
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
            <div class="card ticket-list-card">
                <div class="card-header" style="padding: 0.5rem 1rem;">
                    <h5 class="card-title mb-0">Ticket <strong>#{{ $ticket->id }}</strong></h5>
                </div>
                <div class="card-body" style="padding: 0.5rem 1rem;">
                    <div class="ticket-list-card__details">
                        <div>
                            <p class="mb-1"><strong>Assunto:</strong> {{ $ticket->assunto }}</p>
                            <p class="mb-1"><strong>Categoria:</strong> {{ $ticket->categoria->nome ?? 'N/A' }}</p>
                        </div>

                        <div>
                            <p class="mb-1"><strong>Empresa:</strong> {{ $ticket->empresa ? $ticket->empresa->nome : '-' }}</p>
                            <p class="mb-1"><strong>Data de Criação:</strong>
                                {{ $ticket->created_at ? $ticket->created_at->format('d/m') : '-' }}
                            </p>
                        </div>

                        <div>
                            <p class="mb-1">
                                <strong>Criado por:</strong>
                                {{ $ticket->user->name }}
                                ({{ $ticket->user->hasRole(['supervisor', 'analista', 'administrador']) ? 'Analista' : 'Cliente' }})
                            </p>
                            <p class="mb-1"><strong>Prazo:</strong> {{ $ticket->prazo ? $ticket->prazo->format('d/m/Y') : '-' }}</p>
                        </div>

                        <div>
                            <p class="mb-1"><strong>Setor:</strong> {{ $ticket->setor ? $ticket->setor->nome : '-' }}</p>
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
                        </div>
                    </div>
                </div>
                <div class="card-footer ticket-list-card__footer">
                    <div class="ticket-list-card__actions">
                        <a href="{{ route('tickets.show', ['ticket' => $ticket->id, 'return_to' => url()->full()]) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i> Detalhes
                        </a>
                        @role('supervisor|administrador')
                        <a href="{{ route('tickets.edit', ['ticket' => $ticket->id, 'return_to' => url()->full()]) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        @endrole
                        @role('administrador')
                        <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteModal{{ $ticket->id }}">
                            <i class="fas fa-trash"></i> Excluir
                        </button>
                        @endrole
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
                        <input type="hidden" name="return_to" value="{{ url()->full() }}">
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
        .col-12.mb-1 {
            margin-bottom: 0.6rem;
        }
        .ticket-list-card__details {
            display: grid;
            gap: .75rem 1.25rem;
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
        .ticket-list-card__footer {
            display: flex;
            justify-content: flex-end;
            padding: .5rem 1rem;
        }
        .ticket-list-card__actions {
            display: flex;
            flex-wrap: wrap;
            gap: .4rem;
            justify-content: flex-end;
        }
        .ticket-list-card__actions .btn {
            min-width: 110px;
            text-align: center;
        }
        @media (max-width: 991.98px) {
            .ticket-list-card__details { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 575.98px) {
            .ticket-list-card__details { grid-template-columns: 1fr; }
            .ticket-list-card__footer,
            .ticket-list-card__actions { display: block; }
            .ticket-list-card__actions .btn { margin-bottom: .4rem; width: 100%; }
        }
    </style>
@endsection


@section('js')
@include('layouts.notificajs')
@endsection
