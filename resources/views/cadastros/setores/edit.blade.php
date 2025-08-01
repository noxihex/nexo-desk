@extends('adminlte::page')

@section('title', config('app.name') . ' - Editar Setor')

@section('content_header')
    <p style="font-size: 1.2em;">
        Cadastros <i class="fas fa-angle-right" style="font-size: 0.7em;"></i> Setores <i class="fas fa-angle-right" style="font-size: 0.7em;"></i> Editar
    </p>
@endsection

@section('content')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Editar Setor: {{ $setor->nome }}</h3>
        </div>

        <div class="card-body">
            <form action="{{ route('setores.update', $setor->id) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Nome do Setor --}}
                <div class="form-group">
                    <label for="nome"><i class="fas fa-building"></i> Nome do Setor</label>
                    <input type="text" name="nome" id="nome" class="form-control @error('nome') is-invalid @enderror" value="{{ old('nome', $setor->nome) }}" required>
                    @error('nome')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group d-flex justify-content-start">
                    {{-- Botão de Atualizar --}}
                    <button type="submit" class="btn btn-success mr-2">
                        <i class="fas fa-save"></i> Atualizar Setor
                    </button>

                    {{-- Botão de Voltar --}}
                    <a href="{{ route('setores.index') }}" class="btn btn-secondary">
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
