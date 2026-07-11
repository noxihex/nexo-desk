@extends('adminlte::page')

@section('title', config('app.name') . ' - Setores')

@section('content_header')
    <p style="font-size: 1.2em;">
        Cadastros <i class="fas fa-angle-right" style="font-size: 0.7em;"></i> Setores
    </p>
    @role('supervisor|administrador')
    <a href="{{ route('setores.create') }}" class="btn btn-success">
        <i class="fas fa-plus-circle"></i> Novo Setor
    </a>
    @endrole
@endsection

@section('content')

    <form action="{{ route('setores.index') }}" method="GET" class="mb-3">
        <div class="d-flex">
            <input type="search" name="search" class="form-control" placeholder="Pesquisar por nome..." value="{{ request('search') }}" style="max-width: 300px;">
            <button type="submit" class="btn btn-primary ml-2" aria-label="Pesquisar">
                <i class="fas fa-search"></i>
            </button>
        </div>
    </form>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Lista de Setores</h3>
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
                    @foreach($setores as $setor)
                        <tr>
                            <td>{{ $setor->nome }}</td>
                            <td class="text-center">
                                <a href="{{ route('setores.edit', $setor->id) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                <form action="{{ route('setores.destroy', $setor->id) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    @role('supervisor|administrador')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Você tem certeza?')">
                                        <i class="fas fa-trash"></i> Excluir
                                    </button>
                                    @endrole
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            <div class="d-flex justify-content-end">
                {{-- Paginação (se necessário) --}}
                {{-- $setores->links() --}}
            </div>
        </div>
    </div>
@endsection
