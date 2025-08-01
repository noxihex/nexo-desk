@extends('adminlte::page')

@section('title', 'BTXDesk - Criar Setor')

@section('content_header')
    <p style="font-size: 1.2em;">
        Cadastros <i class="fas fa-angle-right" style="font-size: 0.7em;"></i> Setores <i class="fas fa-angle-right" style="font-size: 0.7em;"></i> Criar
    </p>
@endsection

@section('content')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Preencha os dados para criar um novo setor</h3>
        </div>

        <div class="card-body">
            <form action="{{ route('setores.store') }}" method="POST">
                @csrf

                {{-- Nome --}}
                <div class="form-group">
                    <label for="nome"><i class="fas fa-building"></i> Nome do Setor</label>
                    <input type="text" name="nome" id="nome" class="form-control @error('nome') is-invalid @enderror" value="{{ old('nome') }}" required>
                    @error('nome')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group d-flex justify-content-start">
                    {{-- Botão de Salvar --}}
                    <button type="submit" class="btn btn-success mr-2">
                        <i class="fas fa-save"></i> Criar Setor
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
