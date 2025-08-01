@extends('adminlte::page')

@section('title', config('app.name') . ' - Criar Categoria')

@section('content_header')
    <p style="font-size: 1.2em;">
        Cadastros <i class="fas fa-angle-right" style="font-size: 0.7em;"></i> Categorias <i class="fas fa-angle-right" style="font-size: 0.7em;"></i> Criar
    </p>
@endsection

@section('content')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Nova Categoria</h3>
        </div>

        <div class="card-body">
            <form action="{{ route('categorias.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="nome"><i class="fas fa-tag"></i> Nome da Categoria</label>
                    <input type="text" name="nome" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="prioridade"><i class="fas fa-exclamation-circle"></i> Prioridade</label>
                    <select name="prioridade" class="form-control">
                        <option value="Normal" selected>Normal</option>
                        <option value="Alta">Alta</option>
                        <option value="Baixa">Baixa</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="slatotal"><i class="fas fa-clock"></i> SLA Total (min)</label>
                    <input type="number" name="slatotal" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="slaupdate"><i class="fas fa-history"></i> SLA Update (min)</label>
                    <input type="number" name="slaupdate" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="setor_id"><i class="fas fa-building"></i> Setor</label>
                    <select name="setor_id" class="form-control">
                        <option value="">Selecione um setor</option>
                        @foreach($setores as $setor)
                            <option value="{{ $setor->id }}">{{ $setor->nome }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group d-flex justify-content-start">
                    <button type="submit" class="btn btn-success mr-2">
                        <i class="fas fa-save"></i> Criar Categoria
                    </button>
                    <a href="{{ route('categorias.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('css')
    <style>
        .form-group label {
            font-weight: 600;
        }
    </style>
@endsection
