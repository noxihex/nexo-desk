@extends('adminlte::page')

@section('title', config('app.name') . ' - Empresas')

@section('content_header')

<p style="font-size: 1.2em;">
    Cadastros <i class="fas fa-angle-right" style="font-size: 0.7em;"></i> Empresas
</p>

<div class="d-flex justify-content-start">
    <a href="{{ route('empresas.create') }}" class="btn btn-success">
        <i class="fas fa-plus-circle"></i> Nova Empresa
    </a>
</div>
@endsection

@section('content')

    <form action="{{ route('empresas.index') }}" method="GET" class="mb-3">
        <div class="d-flex">
            <input type="search" name="search" class="form-control" placeholder="Pesquisar por nome..." value="{{ request('search') }}" style="max-width: 300px;">
            <button type="submit" class="btn btn-primary ml-2" aria-label="Pesquisar">
                <i class="fas fa-search"></i>
            </button>
        </div>
    </form>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Lista de Empresas</h3>
        </div>

        <div class="card-body p-0">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($empresas as $empresa)
                        <tr>
                            <td>{{ $empresa->nome }}</td>
                            <td class="text-center">
                                {{-- Botão Editar --}}
                                <a href="{{ route('empresas.edit', $empresa->id) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                @role('supervisor|administrador')
                                {{-- Botão Excluir (com modal de confirmação) --}}
                                <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteModal{{ $empresa->id }}">
                                    <i class="fas fa-trash"></i> Excluir
                                </button>
                                @endrole
                                {{-- Modal de Confirmação de Exclusão --}}
                                <div class="modal fade" id="deleteModal{{ $empresa->id }}" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel{{ $empresa->id }}" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="deleteModalLabel{{ $empresa->id }}">Confirmar Exclusão</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                Tem certeza que deseja excluir a empresa <strong>{{ $empresa->nome }}</strong>?
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                                <form action="{{ route('empresas.destroy', $empresa->id) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">Sim, Excluir</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            <div class="d-flex justify-content-end">
                {{-- Paginação, se necessário --}}
                {{ $empresas->links() }}
            </div>
        </div>
    </div>
@endsection

@section('css')
    <style>
        .card-title {
            font-weight: bold;
        }

        .table th, .table td {
            vertical-align: middle;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            line-height: 1.5;
        }

        .modal-title {
            font-weight: bold;
        }
    </style>
@endsection
