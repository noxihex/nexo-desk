@extends('adminlte::page')

@section('title', config('app.name') . ' - Visão Geral')

@section('content_header')
<p style="font-size: 1.2em;">
    {{ config('app.name') }} <i class="fas fa-angle-right" style="font-size: 0.7em;"></i> Visão Geral
</p>
@stop

@role('analista|supervisor|administrador')
@include('layouts.notificahtml')
@endrole





@section('content')







@role('cliente')
<div class="row">
    <!-- Meus Tickets Abertos -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ app('App\Http\Controllers\VisaoGeralController')->contarMeusTicketsAbertosCliente() }}</h3>
                <p>Meus Tickets Abertos</p>
            </div>
            <div class="icon">
                <i class="fas fa-ticket-alt"></i>
            </div>
            <a href="#" class="small-box-footer" onclick="toggleTicketList('meusTicketsAbertosList', event)">
                Mais informações <i class="fas fa-arrow-circle-right"></i>
            </a>
            <div id="meusTicketsAbertosList" style="display: none; padding: 10px;">
                <p><strong>IDs:</strong>
                    @foreach (app('App\Http\Controllers\VisaoGeralController')->obterMeusTicketsAbertosCliente() as $ticketId)
                        <a href="{{ url('/tickets/cliente/' . $ticketId) }}" style="color: #fff; text-decoration: none;">
                            #{{ $ticketId }}
                        </a>{{ !$loop->last ? ', ' : '' }}
                    @endforeach
                </p>
            </div>
        </div>
    </div>

    <!-- Tickets Abertos em Minha Empresa -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ app('App\Http\Controllers\VisaoGeralController')->contarTicketsAbertosMinhaEmpresa() }}</h3>
                <p>Tickets Abertos em Minha Empresa</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
            <a href="#" class="small-box-footer" onclick="toggleTicketList('ticketsAbertosEmpresaList', event)">
                Mais informações <i class="fas fa-arrow-circle-right"></i>
            </a>
            <div id="ticketsAbertosEmpresaList" style="display: none; padding: 10px;">
                <p><strong>IDs:</strong>
                    @foreach (app('App\Http\Controllers\VisaoGeralController')->obterTicketsAbertosMinhaEmpresa() as $ticketId)
                        <a href="{{ url('/tickets/cliente/' . $ticketId) }}" style="color: #fff; text-decoration: none;">
                            #{{ $ticketId }}
                        </a>{{ !$loop->last ? ', ' : '' }}
                    @endforeach
                </p>
            </div>
        </div>
    </div>

    <!-- Tickets Pendente Cliente -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ app('App\Http\Controllers\VisaoGeralController')->contarTicketsPendenteCliente() }}</h3>
                <p>Tickets Aguardando Minha Resposta</p>
            </div>
            <div class="icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <a href="#" class="small-box-footer" onclick="toggleTicketList('ticketsPendenteClienteList', event)">
                Mais informações <i class="fas fa-arrow-circle-right"></i>
            </a>
            <div id="ticketsPendenteClienteList" style="display: none; padding: 10px;">
                <p><strong>IDs:</strong>
                    @foreach (app('App\Http\Controllers\VisaoGeralController')->obterTicketsPendenteCliente() as $ticketId)
                        <a href="{{ url('/tickets/cliente/' . $ticketId) }}" style="color: #fff; text-decoration: none;">
                            #{{ $ticketId }}
                        </a>{{ !$loop->last ? ', ' : '' }}
                    @endforeach
                </p>
            </div>
        </div>
    </div>

    <!-- Tickets Fechados em Minha Empresa -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-secondary">
            <div class="inner">
                <h3>{{ app('App\Http\Controllers\VisaoGeralController')->contarTicketsFechadosMinhaEmpresa() }}</h3>
                <p>Tickets Fechados em Minha Empresa</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <a href="#" class="small-box-footer" onclick="toggleTicketList('ticketsFechadosEmpresaList', event)">
                Mais informações <i class="fas fa-arrow-circle-right"></i>
            </a>
            <!-- <div id="ticketsFechadosEmpresaList" style="display: none; padding: 10px;">
                <p><strong>IDs:</strong>
                    @foreach (app('App\Http\Controllers\VisaoGeralController')->obterTicketsFechadosMinhaEmpresa() as $ticketId)
                        <a href="{{ url('/tickets/cliente/' . $ticketId) }}" style="color: #fff; text-decoration: none;">
                            #{{ $ticketId }}
                        </a>{{ !$loop->last ? ', ' : '' }}
                    @endforeach
                </p>
            </div> -->
        </div>
    </div>
</div>
@endrole






@role('analista|supervisor|administrador')
<div class="row">
    <!-- Meus Tickets Abertos -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ app('App\Http\Controllers\VisaoGeralController')->contarMeusTicketsAbertos() }}</h3>
                <p>Meus tickets abertos</p>
            </div>
            <div class="icon">
                <i class="fas fa-ticket-alt"></i>
            </div>
            <a href="{{ url('/tickets/my') }}" class="small-box-footer">
                Mais informações <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <!-- Tickets Abertos em Meu Grupo -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ app('App\Http\Controllers\VisaoGeralController')->contarTicketsAbertosGrupo() }}</h3>
                <p>Tickets abertos em meu grupo</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
            <a href="{{ url('/tickets?sort=created_at&setor_id=&grupo_id=' . Auth::user()->grupo_id) }}" class="small-box-footer">
                Mais informações <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <!-- Tickets Abertos em Meu Grupo sem Analista -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                @php
                    $ticketsSemAnalista = app('App\Http\Controllers\VisaoGeralController')->obterTicketsSemAnalista();
                @endphp
                <h3>{{ count($ticketsSemAnalista) }}</h3>
                <p>Tickets não assumidos em meu grupo</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-times"></i>
            </div>
            <a href="#" class="small-box-footer" onclick="toggleTicketList('ticketsSemAnalistaList', event)">
                Mais informações <i class="fas fa-arrow-circle-right"></i>
            </a>
            <div id="ticketsSemAnalistaList" style="display: none; padding: 10px;">
                <p><strong>IDs:</strong>
                    @foreach ($ticketsSemAnalista as $ticketId)
                        <a href="{{ url('/tickets/' . $ticketId) }}" style="color: #fff; text-decoration: none;">
                            #{{ $ticketId }}
                        </a>{{ !$loop->last ? ',' : '' }}
                    @endforeach
                </p>
            </div>
        </div>
    </div>

    <!-- Tickets que Requerem Minha Atenção -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                @php
                    $ticketsAtencao = app('App\Http\Controllers\VisaoGeralController')->obterTicketsAtencao();
                @endphp
                <h3>{{ count($ticketsAtencao) }}</h3>
                <p>Tickets que requerem minha atenção</p>
            </div>
            <div class="icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <a href="#" class="small-box-footer" onclick="toggleTicketList('ticketsAtencaoList', event)">
                Mais informações <i class="fas fa-arrow-circle-right"></i>
            </a>
            <div id="ticketsAtencaoList" style="display: none; padding: 10px;">
                <p><strong>IDs:</strong>
                    @foreach ($ticketsAtencao as $ticketId)
                        <a href="{{ url('/tickets/' . $ticketId) }}" style="color: #fff; text-decoration: none;">
                            #{{ $ticketId }}
                        </a>{{ !$loop->last ? ' ' : '' }}
                    @endforeach
                </p>
            </div>
        </div>
    </div>
</div>
@endrole

<hr class="simple-divider">

@role('supervisor|administrador')

<!-- Filtro de Ordenação e Seleção de Analista, Grupo, Setor -->
<div class="form-group">
    <form method="GET" action="{{ route('home') }}" class="row">
        <div class="col-md-3 col-12 d-flex align-items-center mb-2">
            <label for="order" class="mr-2">Ordenar por:</label>
            <select name="order" id="order" class="form-control form-control-sm" onchange="this.form.submit()">
                <option value="desc" {{ request('order') == 'desc' ? 'selected' : '' }}>Tickets mais novos</option>
                <option value="asc" {{ request('order') == 'asc' ? 'selected' : '' }}>Tickets mais antigos</option>
            </select>
        </div>

        @role('administrador')
        <div class="col-md-3 col-12 d-flex align-items-center mb-2">
            <label for="analista" class="mr-2">Filtrar Analista:</label>
            <select name="analista" id="analista" class="form-control form-control-sm" onchange="this.form.submit()">
                <option value="">Todos</option>
                @foreach($analistas as $analista)
                    <option value="{{ $analista->id }}" {{ request('analista') == $analista->id ? 'selected' : '' }}>{{ $analista->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3 col-12 d-flex align-items-center mb-2">
            <label for="grupo" class="mr-2">Filtrar Grupo:</label>
            <select name="grupo" id="grupo" class="form-control form-control-sm" onchange="this.form.submit()">
                <option value="">Todos</option>
                @foreach($grupos as $grupo)
                    <option value="{{ $grupo->id }}" {{ request('grupo') == $grupo->id ? 'selected' : '' }}>{{ $grupo->nome }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3 col-12 d-flex align-items-center mb-2">
            <label for="setor" class="mr-2">Filtrar Setor:</label>
            <select name="setor" id="setor" class="form-control form-control-sm" onchange="this.form.submit()">
                <option value="">Todos</option>
                @foreach($setores as $setor)
                    <option value="{{ $setor->id }}" {{ request('setor') == $setor->id ? 'selected' : '' }}>{{ $setor->nome }}</option>
                @endforeach
            </select>
        </div>
        @endrole
    </form>
</div>

<!-- Colunas Kanban -->
<div class="row">
    <!-- Coluna de Tickets Abertos -->
    <div class="col-lg-3 col-md-6 col-12 kanban-column mb-3">
        <div class="card">
            <div class="card-header bg-success sticky-header">
                <h3 class="card-title">Abertos ({{ $ticketsAbertos->count() }})</h3>
            </div>
            <div class="card-body p-2">
                @foreach($ticketsAbertos as $ticket)
                    <div class="card mb-2 p-2">
                        <div class="card-title font-weight-bold">Ticket #{{ $ticket->id }}</div>
                        <p><strong>Empresa:</strong> {{ $ticket->empresa->nome ?? '-' }}</p>
                        <p><strong>Assunto:</strong> {{ $ticket->assunto }}</p>
                        <p><strong>Categoria:</strong> {{ $ticket->categoria->nome ?? 'N/A' }}</p>
                        <p><strong>Analista:</strong> {{ $ticket->analista->name ?? '-' }}</p>
                        <div class="text-right">
                            <a href="{{ route('tickets.show', $ticket->id) }}" class="btn btn-sm btn-info" title="Visualizar">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Coluna de Tickets Pendente Cliente -->
    <div class="col-lg-3 col-md-6 col-12 kanban-column mb-3">
        <div class="card">
            <div class="card-header bg-primary sticky-header">
                <h3 class="card-title">Pendente Cliente ({{ $ticketsPendenteCliente->count() }})</h3>
            </div>
            <div class="card-body p-2">
                @foreach($ticketsPendenteCliente as $ticket)
                    <div class="card mb-2 p-2">
                        <div class="card-title font-weight-bold">Ticket #{{ $ticket->id }}</div>
                        <p><strong>Empresa:</strong> {{ $ticket->empresa->nome ?? '-' }}</p>
                        <p><strong>Assunto:</strong> {{ $ticket->assunto }}</p>
                        <p><strong>Categoria:</strong> {{ $ticket->categoria->nome ?? 'N/A' }}</p>
                        <p><strong>Analista:</strong> {{ $ticket->analista->name ?? '-' }}</p>
                        <div class="text-right">
                            <a href="{{ route('tickets.show', $ticket->id) }}" class="btn btn-sm btn-info" title="Visualizar">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Coluna de Tickets Pendente Analista -->
    <div class="col-lg-3 col-md-6 col-12 kanban-column mb-3">
        <div class="card">
            <div class="card-header bg-warning sticky-header">
                <h3 class="card-title">Pendente Analista ({{ $ticketsPendenteAnalista->count() }})</h3>
            </div>
            <div class="card-body p-2">
                @foreach($ticketsPendenteAnalista as $ticket)
                    <div class="card mb-2 p-2">
                        <div class="card-title font-weight-bold">Ticket #{{ $ticket->id }}</div>
                        <p><strong>Empresa:</strong> {{ $ticket->empresa->nome ?? '-' }}</p>
                        <p><strong>Assunto:</strong> {{ $ticket->assunto }}</p>
                        <p><strong>Categoria:</strong> {{ $ticket->categoria->nome ?? 'N/A' }}</p>
                        <p><strong>Analista:</strong> {{ $ticket->analista->name ?? '-' }}</p>
                        <div class="text-right">
                            <a href="{{ route('tickets.show', $ticket->id) }}" class="btn btn-sm btn-info" title="Visualizar">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Coluna de Tickets Fechados -->
    <div class="col-lg-3 col-md-6 col-12 kanban-column mb-3">
        <div class="card">
            <div class="card-header bg-secondary sticky-header">
                <h3 class="card-title">Fechados ({{ $ticketsFechados->count() }}+)</h3>
            </div>
            <div class="card-body p-2">
                @foreach($ticketsFechados as $ticket)
                    <div class="card mb-2 p-2">
                        <div class="card-title font-weight-bold">Ticket #{{ $ticket->id }}</div>
                        <p><strong>Empresa:</strong> {{ $ticket->empresa->nome ?? '-' }}</p>
                        <p><strong>Assunto:</strong> {{ $ticket->assunto }}</p>
                        <p><strong>Categoria:</strong> {{ $ticket->categoria->nome ?? 'N/A' }}</p>
                        <p><strong>Analista:</strong> {{ $ticket->analista->name ?? '-' }}</p>
                        <div class="text-right">
                            <a href="{{ route('tickets.show', $ticket->id) }}" class="btn btn-sm btn-info" title="Visualizar">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endrole





@stop




@section('css')
@role('analista|supervisor|administrador')
@include('layouts.notificacss')
@endrole
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

.simple-divider {
    border: none;
    height: 1px;
    background-color: #ddd;
    box-shadow: 0px 1px 3px rgba(0, 0, 0, 0.1);
    margin: 10px 0;
}

    /* Ajustes adicionais para melhorar a responsividade */
    @media (max-width: 767.98px) {
        .form-group > .row .col-md {
            flex-direction: column;
            align-items: flex-start;
        }
    }

    /* Resto do estilo já fornecido */
    .kanban-column {
        overflow-y: auto;
        max-height: 70vh;
        padding-bottom: 1rem;
        border-radius: 0 0 8px 8px;
    }
    .sticky-header {
        position: sticky;
        top: 0;
        z-index: 1;
    }
    .card .card-title {
        font-size: 1em;
        margin-bottom: 0.2rem;
    }
    .card-body p {
        margin-bottom: 0.1rem;
        font-size: 0.9em;
    }
    .kanban-column .card {
        margin-bottom: 0.4rem;
    }
    .kanban-column::-webkit-scrollbar {
        width: 8px;
    }
    .kanban-column::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    .kanban-column::-webkit-scrollbar-thumb {
        background-color: #888;
        border-radius: 10px;
    }
    .kanban-column::-webkit-scrollbar-thumb:hover {
        background-color: #555;
    }
    .kanban-column {
        scrollbar-width: thin;
        scrollbar-color: #888 #f1f1f1;
    }
</style>
@stop

@section('js')
@role('analista|supervisor|administrador')
@include('layouts.notificajs')
@endrole
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    function toggleTicketList(listId, event) {
        event.preventDefault();
        const ticketsList = document.getElementById(listId);
        ticketsList.style.display = ticketsList.style.display === 'none' ? 'block' : 'none';
    }
</script>
<script>
    function toggleTicketList(elementId, event) {
    event.preventDefault(); // Previne o comportamento padrão do link
    const element = document.getElementById(elementId);
    if (element.style.display === 'none') {
        element.style.display = 'block'; // Mostra a lista
    } else {
        element.style.display = 'none'; // Esconde a lista
    }
}

</script>
@endsection
