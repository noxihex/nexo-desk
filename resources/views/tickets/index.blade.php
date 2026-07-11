    @extends('adminlte::page')

    @section('title', config('app.name') . ' - Tickets')

    @section('content_header')
        <p style="font-size: 1.2em;">Tickets</p>

      <!-- Linha com os botões de ação, filtros de setor e grupo, e seleção de ordenação -->
<div class="row mt-2">
    <!-- Botão de Novo Ticket -->
    <div class="col-lg-auto col-md-12 mb-2">
        <a href="{{ route('tickets.create') }}" class="btn btn-success">
            <i class="fas fa-plus-circle"></i> Novo Ticket
        </a>
    </div>

    <!-- Filtros, pesquisa e ordenação alinhados à direita -->
    <div class="col-lg d-flex justify-content-end flex-wrap">
        <!-- Campo de Pesquisa -->
        <div class="col-lg-auto col-md-6 mb-2">
            <form action="{{ route('tickets.index') }}" method="GET" class="d-flex">
                <input type="text" name="search" class="form-control" placeholder="ID ou Assunto" value="{{ request('search') }}">
                <input type="hidden" name="showClosed" value="1"> <!-- Preserva o filtro -->
                <input type="hidden" name="sort" value="{{ request('sort') }}"> <!-- Preserva a ordenação -->
                <input type="hidden" name="setor_id" value="{{ request('setor_id') }}"> <!-- Preserva o filtro de setor -->
                <input type="hidden" name="grupo_id" value="{{ request('grupo_id') }}"> <!-- Preserva o filtro de grupo -->
                <button type="submit" class="btn btn-primary ml-2">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>

        <!-- Filtro de Setor -->
        <div class="col-lg-auto col-md-6 mb-2">
            <div class="d-flex align-items-center">
                <label for="filterSetor" class="mr-2">Setor:</label>
                <select id="filterSetor" class="form-control">
                    <option value="">Todos os Setores</option>
                    @foreach($setores as $setor)
                        <option value="{{ $setor->id }}" {{ $setorId == $setor->id ? 'selected' : '' }}>{{ $setor->nome }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Filtro de Grupo -->
        <div class="col-lg-auto col-md-6 mb-2">
            <div class="d-flex align-items-center">
                <label for="filterGrupo" class="mr-2">Grupo:</label>
                <select id="filterGrupo" class="form-control">
                    <option value="">Todos os Grupos</option>
                    @foreach($grupos as $grupo)
                        <option value="{{ $grupo->id }}" {{ $grupoId == $grupo->id ? 'selected' : '' }}>{{ $grupo->nome }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Ordenação -->
        <div class="col-lg-auto col-md-6 mb-2">
            <div class="d-flex align-items-center">
                <label for="sortOrder" class="mr-2">Ordenar:</label>
                <select id="sortOrder" class="form-control">
                    <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Criado recente</option>
                    <option value="updated_at" {{ request('sort') == 'updated_at' ? 'selected' : '' }}>Modificado recente</option>
                    <option value="sla" {{ request('sort') == 'sla' ? 'selected' : '' }}>SLA atrasado</option>
                </select>
            </div>
        </div>

        <!-- Botão Ocultar/Mostrar Fechados -->
        <div class="col-lg-auto col-md-6 mb-2">
            <a href="{{ route('tickets.index', [
    'showClosed' => $showClosed == '1' ? '0' : '1', // Alterna entre 1 e 0
    'search' => request('search'), // Preserva o termo pesquisado
    'sort' => request('sort') ?: 'created_at', // Define um valor padrão
    'setor_id' => request('setor_id'), // Preserva o filtro de setor
    'grupo_id' => request('grupo_id') // Preserva o filtro de grupo
]) }}"
class="btn {{ $showClosed == '1' ? 'btn-success' : 'btn-secondary' }}">
    <i class="fas {{ $showClosed == '1' ? 'fa-eye-slash' : 'fa-eye' }}"></i>
    {{ $showClosed == '1' ? 'Ocultar Fechados' : 'Mostrar Fechados' }}
</a>
        </div>
    </div>
</div>






    @endsection



    @include('layouts.notificahtml')



    @section('content')


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
                                <p>
                                    <strong>Criado por:</strong>
                                    {{ $ticket->user->name }}
                                    ({{ $ticket->user->hasRole(['supervisor', 'analista', 'administrador']) ? 'Analista' : 'Cliente' }})
                                </p>
                                <p class="mb-1"><strong>Data de Criação:</strong>
                                    {{ $ticket->created_at ? $ticket->created_at->format('d/m/Y H:i') : '-' }}
                                </p>
                                <p class="mb-1"><strong>Modificado:</strong>
                                    {{ $ticket->updated_at ? $ticket->updated_at->format('d/m/Y H:i') : '-' }}
                                </p>
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
                                    @role('administrador')
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


    <div class="d-flex justify-content-end">
        {{ $tickets->appends([
            'search' => request('search'), // Preserva o termo pesquisado
            'sort' => request('sort'), // Preserva a ordenação
            'showClosed' => request('showClosed'), // Preserva o filtro "Mostrar Fechados"
            'setor_id' => request('setor_id'), // Preserva o filtro de setor
            'grupo_id' => request('grupo_id') // Preserva o filtro de grupo
        ])->links() }}
    </div>
    @endsection

    @section('css')
    @include('layouts.notificacss')
        <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
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
        <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
        <script>
            $(document).ready(function() {
                @if(session('success'))
                    toastr.success('{{ session('success') }}', 'Sucesso', {
                        closeButton: true,
                        progressBar: true,
                    });
                @endif
            });

            // Redirecionamento com filtros e ordenação selecionados
            document.getElementById('sortOrder').addEventListener('change', function () {
                updateFilters();
            });
            document.getElementById('filterSetor').addEventListener('change', function () {
                updateFilters();
            });
            document.getElementById('filterGrupo').addEventListener('change', function () {
                updateFilters();
            });

            function updateFilters() {
                const selectedSort = document.getElementById('sortOrder').value;
                const selectedSetor = document.getElementById('filterSetor').value;
                const selectedGrupo = document.getElementById('filterGrupo').value;
                const url = new URL(window.location.href);
                url.searchParams.set('sort', selectedSort);
                url.searchParams.set('setor_id', selectedSetor);
                url.searchParams.set('grupo_id', selectedGrupo);
                window.location.href = url;
            }
        </script>
    @endsection
