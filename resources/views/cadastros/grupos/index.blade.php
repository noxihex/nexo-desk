@extends('adminlte::page')

@section('title', config('app.name') . ' - Grupos')

@section('content_header')

<p style="font-size: 1.2em;">
    Cadastros <i class="fas fa-angle-right" style="font-size: 0.7em;"></i> Grupos
</p>

    <div class="d-flex justify-content-start">
        @role('supervisor|administrador')
        <a href="{{ route('grupos.create') }}" class="btn btn-success">
            <i class="fas fa-plus-circle"></i> Novo Grupo
        @endrole
        </a>
    </div>
@endsection

@section('content')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Lista de Grupos</h3>
        </div>

        <div class="card-body p-0">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($grupos as $grupo)
                        <tr>
                            <td>{{ $grupo->id }}</td>
                            <td>{{ $grupo->nome }}</td>
                            <td class="text-center">
                                <a href="{{ route('grupos.edit', $grupo->id) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                @role('supervisor|administrador')
                                <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#modal-excluir-{{ $grupo->id }}">
                                    <i class="fas fa-trash"></i> Excluir
                                </button>
                                @endrole
                                <!-- Modal de Confirmação -->
                                <div class="modal fade" id="modal-excluir-{{ $grupo->id }}" tabindex="-1" role="dialog" aria-labelledby="modal-excluir-label-{{ $grupo->id }}" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="modal-excluir-label-{{ $grupo->id }}">Confirmação de Exclusão</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                Tem certeza que deseja excluir o grupo <strong>{{ $grupo->nome }}</strong>?
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                                <form action="{{ route('grupos.destroy', $grupo->id) }}" method="POST" style="display:inline-block;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">Sim, Excluir</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Fim Modal -->
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>



    </div>
    <div>
        <div class="d-flex justify-content-end">
            <!-- Paginação -->
            {{ $grupos->links() }}
        </div>
    </div>
@endsection

@section('css')
    {{-- Incluindo o CSS do Toastr --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <style>
        .card-title {
            font-weight: bold;
        }

        .table th, .table td {
            vertical-align: middle;
        }

        /* Estilizando os botões menores */
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            line-height: 1.5;
        }
    </style>
@endsection

@section('js')
    {{-- Incluindo o jQuery --}}

    {{-- Incluindo o JS do Toastr --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    {{-- Script do Toastr para exibir notificações de sucesso --}}
    <script>
        $(document).ready(function() {
            @if(session('success'))
                toastr.success('{{ session('success') }}', 'Sucesso', {
                    closeButton: true,
                    progressBar: true,
                });
            @endif
        });
    </script>
@endsection
