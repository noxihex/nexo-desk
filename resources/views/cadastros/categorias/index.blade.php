@extends('adminlte::page')

@section('title', config('app.name') . ' - Categorias')

@section('content_header')
    <p style="font-size: 1.2em;">
        Cadastros <i class="fas fa-angle-right" style="font-size: 0.7em;"></i> Categorias
    </p>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('categorias.create') }}" class="btn btn-success">
            <i class="fas fa-plus-circle"></i> Nova Categoria
        </a>
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

    <!-- Campo de busca -->
    <form action="{{ route('categorias.index') }}" method="GET">
        <div class="d-flex justify-content-start mb-3">
            <input type="search" name="search" class="form-control" placeholder="Pesquisar por nome..." value="{{ request('search') }}" style="max-width: 300px;">
            <button type="submit" class="btn btn-primary ml-2">
                <i class="fas fa-search"></i>
            </button>
        </div>
    </form>

    <div class="card">

        <div class="card-header">
            <h3 class="card-title">Lista de Categorias</h3>
        </div>

        <div class="card-body p-0">
            <table class="table table-striped" id="categoriaTable">
                <thead>
                    <tr>
                        <th>Nome da Categoria</th>
                        <th>Setores</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categorias as $categoria)
                        <tr>
                            <td>{{ $categoria->nome }}</td>
                            <td>{{ $categoria->setores->pluck('nome')->implode(', ') ?: 'Sem setor' }}</td>
                            <td class="text-center">
                                <a href="{{ route('categorias.edit', $categoria->id) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                @role('administrador')
                                <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteModal{{ $categoria->id }}">
                                    <i class="fas fa-trash"></i> Excluir
                                </button>
                                @endrole
                                <div class="modal fade" id="deleteModal{{ $categoria->id }}" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel{{ $categoria->id }}" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="deleteModalLabel{{ $categoria->id }}">Confirmar Exclusão</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                Tem certeza que deseja excluir a categoria <strong>{{ $categoria->nome }}</strong>?
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                                <form action="{{ route('categorias.destroy', $categoria->id) }}" method="POST" style="display:inline;">
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
                {{ $categorias->links() }}
            </div>
        </div>
    </div>
@endsection
