@extends('adminlte::page')

@section('title', config('app.name') . ' - Editar Categoria')

@section('content_header')
    <p style="font-size: 1.2em;">
        Cadastros <i class="fas fa-angle-right" style="font-size: 0.7em;"></i> Categorias <i class="fas fa-angle-right" style="font-size: 0.7em;"></i> Editar
    </p>
@endsection

@section('content')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Editar Categoria: {{ $categoria->nome }}</h3>
        </div>

        <div class="card-body">
            <form action="{{ route('categorias.update', $categoria->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="nome"><i class="fas fa-tag"></i> Nome da Categoria</label>
                    <input type="text" name="nome" class="form-control" value="{{ $categoria->nome }}" required>
                </div>

                <div class="form-group">
                    <label for="prioridade"><i class="fas fa-exclamation-circle"></i> Prioridade</label>
                    <select name="prioridade" class="form-control">
                        <option value="Normal" {{ $categoria->prioridade == 'Normal' ? 'selected' : '' }}>Normal</option>
                        <option value="Alta" {{ $categoria->prioridade == 'Alta' ? 'selected' : '' }}>Alta</option>
                        <option value="Baixa" {{ $categoria->prioridade == 'Baixa' ? 'selected' : '' }}>Baixa</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="slatotal"><i class="fas fa-clock"></i> SLA Total (min)</label>
                    <input type="number" name="slatotal" class="form-control" value="{{ $categoria->slatotal }}" required>
                </div>

                <div class="form-group">
                    <label for="slaupdate"><i class="fas fa-history"></i> SLA Update (min)</label>
                    <input type="number" name="slaupdate" class="form-control" value="{{ $categoria->slaupdate }}" required>
                </div>

                <div class="form-group">
                    <label for="setor_id"><i class="fas fa-building"></i> Setor</label>
                    <select name="setor_id" class="form-control">
                        <option value="">Sem setor</option>
                        @foreach($setores as $setor)
                            <option value="{{ $setor->id }}" {{ $categoria->setor_id == $setor->id ? 'selected' : '' }}>
                                {{ $setor->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group d-flex justify-content-start">
                    <button type="submit" class="btn btn-success mr-2">
                        <i class="fas fa-save"></i> Salvar Alterações
                    </button>
                    <a href="{{ route('categorias.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
