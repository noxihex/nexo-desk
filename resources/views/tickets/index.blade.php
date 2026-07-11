    @extends('adminlte::page')

    @section('title', config('app.name') . ' - Tickets')

    @section('content_header')
        <x-page-header title="Tickets" :breadcrumbs="['Tickets', 'Todos os tickets']" />

@php
    $activeFilterCount = collect([$setorId, $categoriaId, $empresaId])->filter()->count() + ($showClosed == '1' ? 1 : 0);
    $selectedSetor = $setores->firstWhere('id', $setorId);
    $selectedCategoria = $categorias->firstWhere('id', $categoriaId);
    $selectedEmpresa = $empresas->firstWhere('id', $empresaId);
@endphp

<div class="ticket-toolbar mt-3">
    <form action="{{ route('tickets.index') }}" method="GET" id="ticketFiltersForm">
        <div class="ticket-toolbar__main">
            <div class="ticket-search">
                <label for="ticketSearch" class="sr-only">Buscar por ID ou assunto</label>
                <i class="fas fa-search ticket-search__icon" aria-hidden="true"></i>
                <input id="ticketSearch" type="search" name="search" class="form-control"
                    placeholder="Buscar por ID ou assunto..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-primary ticket-search__submit">
                    <i class="fas fa-search" aria-hidden="true"></i>
                    <span class="ticket-search__submit-label">Buscar</span>
                </button>
            </div>

            <button class="btn btn-outline-secondary ticket-filter-toggle" type="button" data-toggle="collapse"
                data-target="#ticketAdvancedFilters" aria-expanded="{{ $activeFilterCount ? 'true' : 'false' }}"
                aria-controls="ticketAdvancedFilters">
                <i class="fas fa-sliders-h mr-1" aria-hidden="true"></i> Filtros
                @if($activeFilterCount)
                    <span class="badge badge-primary ml-1">{{ $activeFilterCount }}</span>
                @endif
                <i class="fas fa-chevron-down ticket-filter-toggle__chevron ml-2" aria-hidden="true"></i>
            </button>

            <div class="ticket-sort">
                <label for="sortOrder" class="sr-only">Ordenar tickets</label>
                <select id="sortOrder" name="sort" class="form-control" aria-label="Ordenar tickets">
                    <option value="created_at" {{ request('sort', 'created_at') == 'created_at' ? 'selected' : '' }}>Criados recentemente</option>
                    <option value="updated_at" {{ request('sort') == 'updated_at' ? 'selected' : '' }}>Modificados recentemente</option>
                    <option value="sla" {{ request('sort') == 'sla' ? 'selected' : '' }}>SLA mais atrasado</option>
                </select>
            </div>

            <a href="{{ route('tickets.create', ['return_to' => url()->full()]) }}" class="btn btn-success ticket-new-button">
                <i class="fas fa-plus-circle mr-1" aria-hidden="true"></i> Novo Ticket
            </a>
        </div>

        <div class="collapse {{ $activeFilterCount ? 'show' : '' }}" id="ticketAdvancedFilters">
            <div class="ticket-toolbar__advanced">
                <div class="ticket-filter-field">
                    <label for="filterSetor">Setor</label>
                    <select id="filterSetor" name="setor_id" class="form-control">
                        <option value="">Todos os setores</option>
                        @foreach($setores as $setor)
                            <option value="{{ $setor->id }}" {{ $setorId == $setor->id ? 'selected' : '' }}>{{ $setor->nome }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="ticket-filter-field">
                    <label for="filterCategoria">Categoria</label>
                    <select id="filterCategoria" name="categoria_id" class="form-control">
                        <option value="">Todas as categorias</option>
                        @foreach($categorias as $categoria)
                            <option value="{{ $categoria->id }}" {{ $categoriaId == $categoria->id ? 'selected' : '' }}>{{ $categoria->nome }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="ticket-filter-field">
                    <label for="filterEmpresa">Empresa</label>
                    <select id="filterEmpresa" name="empresa_id" class="form-control">
                        <option value="">Todas as empresas</option>
                        @foreach($empresas as $empresa)
                            <option value="{{ $empresa->id }}" {{ $empresaId == $empresa->id ? 'selected' : '' }}>{{ $empresa->nome }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="ticket-closed-filter">
                    <span class="ticket-closed-filter__label">Tickets fechados</span>
                    <div class="custom-control custom-switch">
                        <input type="hidden" name="showClosed" value="0">
                        <input type="checkbox" class="custom-control-input" id="showClosed" name="showClosed" value="1"
                            {{ $showClosed == '1' ? 'checked' : '' }}>
                        <label class="custom-control-label" for="showClosed">Incluir fechados</label>
                    </div>
                </div>

                <div class="ticket-filter-actions">
                    @if($activeFilterCount || request('search'))
                        <a href="{{ route('tickets.index') }}" class="btn btn-link">Limpar filtros</a>
                    @endif
                    <button type="submit" class="btn btn-primary">Aplicar filtros</button>
                </div>
            </div>
        </div>
    </form>

    @if($activeFilterCount)
        <div class="ticket-filter-chips" aria-label="Filtros ativos">
            <span class="ticket-filter-chips__title">Filtros ativos:</span>
            @if($selectedSetor)
                <a class="ticket-filter-chip" href="{{ route('tickets.index', array_merge(request()->except(['setor_id', 'page']))) }}">
                    Setor: {{ $selectedSetor->nome }} <i class="fas fa-times" aria-hidden="true"></i>
                </a>
            @endif
            @if($selectedCategoria)
                <a class="ticket-filter-chip" href="{{ route('tickets.index', array_merge(request()->except(['categoria_id', 'page']))) }}">
                    Categoria: {{ $selectedCategoria->nome }} <i class="fas fa-times" aria-hidden="true"></i>
                </a>
            @endif
            @if($selectedEmpresa)
                <a class="ticket-filter-chip" href="{{ route('tickets.index', array_merge(request()->except(['empresa_id', 'page']))) }}">
                    Empresa: {{ $selectedEmpresa->nome }} <i class="fas fa-times" aria-hidden="true"></i>
                </a>
            @endif
            @if($showClosed == '1')
                <a class="ticket-filter-chip" href="{{ route('tickets.index', array_merge(request()->except('page'), ['showClosed' => 0])) }}">
                    Incluindo fechados <i class="fas fa-times" aria-hidden="true"></i>
                </a>
            @endif
        </div>
    @endif
</div>






    @endsection






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


    <div class="d-flex justify-content-end">
        {{ $tickets->appends([
            'search' => request('search'), // Preserva o termo pesquisado
            'sort' => request('sort'), // Preserva a ordenação
            'showClosed' => request('showClosed'), // Preserva o filtro "Mostrar Fechados"
            'setor_id' => request('setor_id'), // Preserva o filtro de setor
            'categoria_id' => request('categoria_id'),
            'empresa_id' => request('empresa_id')
        ])->links() }}
    </div>
    @endsection

    @section('css')
        <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
        <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
        <style>
            .ticket-toolbar {
                background: var(--btx-surface);
                border: 1px solid var(--btx-border);
                border-radius: var(--btx-radius);
                box-shadow: var(--btx-shadow-sm);
                padding: .85rem;
            }
            .ticket-toolbar__main {
                align-items: center;
                display: grid;
                gap: .75rem;
                grid-template-columns: minmax(260px, 1fr) auto minmax(210px, auto) auto;
            }
            .ticket-search {
                display: flex;
                min-width: 0;
                position: relative;
            }
            .ticket-search__icon {
                color: var(--btx-text-muted);
                left: .9rem;
                pointer-events: none;
                position: absolute;
                top: 50%;
                transform: translateY(-50%);
                z-index: 2;
            }
            .ticket-search .form-control {
                border-bottom-right-radius: 0;
                border-top-right-radius: 0;
                padding-left: 2.5rem;
            }
            .ticket-search__submit {
                border-bottom-left-radius: 0;
                border-top-left-radius: 0;
            }
            .ticket-search__submit i { display: none; }
            .ticket-filter-toggle[aria-expanded="true"] .ticket-filter-toggle__chevron {
                transform: rotate(180deg);
            }
            .ticket-filter-toggle__chevron { transition: transform var(--btx-transition); }
            .ticket-toolbar__advanced {
                align-items: end;
                border-top: 1px solid var(--btx-border);
                display: grid;
                gap: 1rem;
                grid-template-columns: repeat(3, minmax(180px, 1fr)) auto auto;
                margin-top: .85rem;
                padding-top: .85rem;
            }
            .ticket-filter-field label,
            .ticket-closed-filter__label {
                display: block;
                font-size: .78rem;
                margin-bottom: .35rem;
                text-transform: uppercase;
            }
            .ticket-closed-filter { padding-bottom: .45rem; }
            .ticket-filter-actions {
                display: flex;
                gap: .4rem;
                justify-content: flex-end;
            }
            .ticket-filter-chips {
                align-items: center;
                border-top: 1px solid var(--btx-border);
                display: flex;
                flex-wrap: wrap;
                gap: .45rem;
                margin-top: .85rem;
                padding-top: .75rem;
            }
            .ticket-filter-chips__title {
                color: var(--btx-text-muted);
                font-size: .82rem;
                font-weight: 600;
            }
            .ticket-filter-chip {
                align-items: center;
                background: var(--btx-primary-soft);
                border: 1px solid var(--btx-border);
                border-radius: 999px;
                color: var(--btx-primary);
                display: inline-flex;
                font-size: .82rem;
                font-weight: 600;
                gap: .45rem;
                padding: .35rem .65rem;
            }
            .ticket-filter-chip:hover { text-decoration: none; }
            body.dark-mode .btn-outline-secondary {
                border-color: var(--btx-border-input);
                color: var(--btx-text);
            }
            @media (max-width: 991.98px) {
                .ticket-toolbar__main {
                    grid-template-columns: minmax(0, 1fr) auto auto;
                }
                .ticket-search { grid-column: 1 / -1; }
                .ticket-sort { min-width: 210px; }
                .ticket-toolbar__advanced { grid-template-columns: 1fr 1fr; }
                .ticket-filter-actions { align-self: end; }
            }
            @media (max-width: 575.98px) {
                .ticket-toolbar { padding: .75rem; }
                .ticket-toolbar__main {
                    grid-template-columns: 1fr 1fr;
                    gap: .6rem;
                }
                .ticket-search { grid-column: 1 / -1; }
                .ticket-search__submit {
                    min-width: 44px;
                }
                .ticket-search__submit i { display: inline-block; }
                .ticket-search__submit-label { display: none; }
                .ticket-filter-toggle,
                .ticket-new-button { width: 100%; }
                .ticket-sort { grid-column: 1 / -1; min-width: 0; }
                .ticket-toolbar__advanced { display: block; }
                .ticket-filter-field,
                .ticket-closed-filter { margin-bottom: 1rem; }
                .ticket-filter-actions {
                    border-top: 1px solid var(--btx-border);
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    padding-top: .75rem;
                }
                .ticket-filter-actions .btn { width: 100%; }
                .ticket-filter-chips__title { flex-basis: 100%; }
            }
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
            .ticket-list-card .card-body p {
                margin-bottom: 0.2rem;
            }
            .col-12.mb-1 {
                margin-bottom: 0.6rem;
            }
            @media (max-width: 991.98px) {
                .ticket-list-card__details { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            }
            @media (max-width: 575.98px) {
                .ticket-list-card__details { grid-template-columns: 1fr; }
                .ticket-list-card__footer { justify-content: stretch; }
                .ticket-list-card__actions { width: 100%; }
                .ticket-list-card__actions .btn { flex: 1 1 110px; }
            }
        </style>
    @endsection

    @section('js')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
        <script>
            $(document).ready(function() {
                $.fn.select2.defaults.set('language', {
                    noResults: function () {
                        return 'Nenhum resultado encontrado';
                    }
                });

                $('#filterSetor').select2({
                    allowClear: true,
                    placeholder: 'Todos os setores',
                    width: '100%'
                });

                $('#filterCategoria').select2({
                    allowClear: true,
                    placeholder: 'Todas as categorias',
                    width: '100%'
                });

                $('#filterEmpresa').select2({
                    allowClear: true,
                    placeholder: 'Todas as empresas',
                    width: '100%'
                });

                @if(session('success'))
                    toastr.success('{{ session('success') }}', 'Sucesso', {
                        closeButton: true,
                        progressBar: true,
                    });
                @endif
            });

            // Ordenação é frequente e continua com aplicação imediata no desktop e no mobile.
            document.getElementById('sortOrder').addEventListener('change', function () {
                document.getElementById('ticketFiltersForm').submit();
            });
        </script>
    @endsection
