@extends('adminlte::page')

@section('title', config('app.name') . ' - Contratos')

@section('content_header')
    <p style="font-size: 1.2em;">
        Cadastros <i class="fas fa-angle-right" style="font-size: 0.7em;"></i> Contratos
    </p>

    <div class="d-flex justify-content-start">
        @role('supervisor|administrador')
            <a href="{{ route('contratos.create') }}" class="btn btn-success">
                <i class="fas fa-plus-circle"></i> Novo Contrato
            </a>
        @endrole
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Lista de Contratos</h3>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Valor (R$)</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($contratos as $contrato)
                            <tr>
                                <td>{{ $contrato->id }}</td>
                                <td>{{ $contrato->nome }}</td>
                                <td>{{ number_format($contrato->valor, 2, ',', '.') }}</td>
                                <td class="text-center">
                                    <div class="actions-buttons-group">
                                        <a href="{{ route('contratos.edit', $contrato->id) }}" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i> Editar
                                        </a>
                                        @role('supervisor|administrador')
                                            <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteModal" data-id="{{ $contrato->id }}">
                                                <i class="fas fa-trash"></i> Excluir
                                            </button>
                                        @endrole
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Nenhum contrato encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end">
        {{ $contratos->links() }}
    </div>

    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirmar Exclusão</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Tem certeza de que deseja excluir este contrato?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <form id="deleteForm" method="POST" action="">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Sim, Excluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <style>
        .card-title { font-weight: bold; }
        .table th, .table td { vertical-align: middle; white-space: nowrap; }
        .actions-buttons-group { display: flex; justify-content: center; align-items: center; gap: 5px; }
        @media (max-width: 768px) { .actions-buttons-group { flex-direction: column; } }
    </style>
@stop

{{-- 
    =================================================================
    MUDANÇA PRINCIPAL AQUI: de @section('js') para @push('js')
    =================================================================
--}}
@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        // Script para o Modal de Exclusão
        $(document).ready(function() {
            $('#deleteModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var id = button.data('id');
                var action = '{{ route("contratos.destroy", ["contrato" => ":id"]) }}';
                action = action.replace(':id', id);
                var modal = $(this);
                modal.find('#deleteForm').attr('action', action);
            });

            // Script do Toastr para notificações de sucesso
            @if(session('success'))
                toastr.success('{{ session('success') }}', 'Sucesso', {
                    closeButton: true,
                    progressBar: true,
                });
            @endif
        });
    </script>
@endpush