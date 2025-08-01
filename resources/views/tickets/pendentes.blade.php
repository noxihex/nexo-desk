@extends('adminlte::page')

@section('title', 'Tickets Pendentes')

@section('content_header')
<p style="font-size: 1.2em;">
    Tickets <i class="fas fa-angle-right" style="font-size: 0.7em;"></i> Pendentes
</p>
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

@if(session('error'))
    <script>
        $(document).ready(function() {
            toastr.error('{{ session('error') }}', 'Erro', {
                closeButton: true,
                progressBar: true,
            });
        });
    </script>
@endif

<div class="row">
    @forelse($tickets as $ticket)
        <div class="col-12 mb-2">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center" style="padding: 0.5rem 1rem;">
                    <h5 class="card-title mb-0">Ticket <strong>#{{ $ticket->id }}</strong></h5>
                </div>
                <div class="card-body" style="padding: 0.5rem 1rem;">
                    <div class="row">
                        <!-- Informações Básicas -->
                        <div class="col-md-3">
                            <p class="mb-1"><strong>Assunto:</strong> {{ $ticket->assunto }}</p>
                            <p>
                                <strong>Criado por:</strong>
                                {{ $ticket->user->name }}
                                ({{ $ticket->user->hasRole(['supervisor', 'analista', 'administrador']) ? 'Analista' : 'Cliente' }})
                            </p>
                                <p class="mb-1"><strong>Empresa:</strong> {{ $ticket->empresa ? $ticket->empresa->nome : '-' }}</p>
                        </div>
                        <!-- Dados de Setor e Analista -->
                        <div class="col-md-3">
                            <p class="mb-1"><strong>Setor:</strong> {{ $ticket->setor->nome ?? 'N/A' }}</p>
                            <p class="mb-1"><strong>Criado em:</strong> {{ $ticket->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <!-- Datas -->
                        <div class="col-md-3">
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
                        <!-- Botões -->
                        <div class="col-md-3 text-right">
                            <a href="{{ route('tickets.show', $ticket->id) }}" class="btn btn-info btn-sm botao">
                                <i class="fas fa-eye"></i> Detalhes
                            </a>
                            @role('supervisor|administrador')
                            <a href="{{ route('tickets.edit', $ticket->id) }}" class="btn btn-warning btn-sm botao">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            @endrole
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-warning text-center">
                Nenhum ticket pendente encontrado.
            </div>
        </div>
    @endforelse
</div>

<div class="d-flex justify-content-end mt-3">
    {{ $tickets->links() }}
</div>
@endsection

@section('css')
@include('layouts.notificacss')
<style>
    .card {
        font-size: 0.9em;
        border: 1px solid #e3e6f0;
    }
    .card-header {
        background-color: #f8f9fa;
        font-size: 1em;
    }
    .card-body p {
        margin-bottom: 0.3rem;
    }
    .badge {
        font-size: 0.9em;
        padding: 0.4em 0.6em;
    }
    .botao {
        padding: 10px;
        font-size: 16px;
    }
</style>
@endsection

@section('js')
@include('layouts.notificajs')
@endsection
