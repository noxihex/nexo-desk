@extends('adminlte::page')

@section('title', config('app.name') . ' - Tickets do Cliente')

@section('content_header')
    <p style="font-size: 1.2em;">
        Tickets <i class="fas fa-angle-right" style="font-size: 0.7em;"></i> Meus tickets
    </p>
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

    <div class="mb-3 d-flex justify-content-between">
        <!-- Botão para alternar entre os tickets -->
        <a href="{{ route('tickets.cliente.index', ['viewCompanyTickets' => !$viewCompanyTickets]) }}"
           class="btn {{ $viewCompanyTickets ? 'btn-success' : 'btn-primary' }}">
            <i class="fas {{ $viewCompanyTickets ? 'fa-user' : 'fa-building' }}"></i>
            {{ $viewCompanyTickets ? 'Ver somente Meus Tickets' : 'Ver Tickets de minha empresa' }}
        </a>

        <!-- Botão para criar um novo ticket -->
        <a href="{{ route('tickets.cliente.create') }}" class="btn btn-success">
            <i class="fas fa-plus-circle"></i> Criar Novo Ticket
        </a>
    </div>

    @forelse ($tickets as $ticket)
        <div class="card mb-3" style="width: 100%;">
            <div class="card-header" style="background-color: #f8f9fa; color: #333; font-weight: bold;">
                Ticket #{{ $ticket->id }}
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Informações Básicas -->
                    <div class="col-md-4">
                        <p class="mb-1"><strong>Assunto:</strong> {{ $ticket->assunto }}</p>
                        <p class="mb-1"><strong>Criado em:</strong> {{ $ticket->created_at->format('d/m/Y H:i') }}</p>
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
                    <!-- Dados Adicionais -->
                    <div class="col-md-4">
                        <p>
                            <strong>Criado por:</strong>
                            {{ $ticket->user->name }}
                            ({{ $ticket->user->hasRole(['supervisor', 'analista', 'administrador']) ? 'Analista' : 'Cliente' }})
                        </p>
                        <p class="mb-1"><strong>Setor:</strong> {{ $ticket->setor ? $ticket->setor->nome : 'N/A' }}</p>
                        <p class="mb-1"><strong>Última Atualização:</strong> {{ $ticket->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>
            <div class="card-footer text-right">
                <a href="{{ route('tickets.cliente.show', $ticket->id) }}" class="btn btn-info btn-sm">
                    <i class="fas fa-eye"></i> Detalhes
                </a>
            </div>
        </div>
    @empty
        <div class="alert alert-warning text-center">
            Nenhum ticket encontrado.
        </div>
    @endforelse

    <div class="d-flex justify-content-center mt-3">
        <!-- Paginação -->
        {{ $tickets->appends(['viewCompanyTickets' => $viewCompanyTickets])->links() }}
    </div>
@endsection

@section('css')
    <style>
        a {
            margin: 2px;
        }
        .card {
            font-size: 16px;
            margin-bottom: 15px;
        }
        .card-header {
            font-size: 1.1em;
            padding: 8px 15px;
        }
        .card-body p {
            margin-bottom: 5px;
        }
        .card-footer {
            padding: 8px 15px;
        }
        .btn-info {
            background-color: #5bc0de;
            border-color: #46b8da;
        }
        .btn-info:hover {
            background-color: #31b0d5;
            border-color: #269abc;
        }
    </style>
@endsection
