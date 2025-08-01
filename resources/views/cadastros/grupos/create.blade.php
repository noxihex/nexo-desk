@extends('adminlte::page')

@section('title', config('app.name') . ' - Criar Grupo')

@section('content_header')
<p style="font-size: 1.2em;">
    Cadastros <i class="fas fa-angle-right" style="font-size: 0.7em;"></i> Grupos <i class="fas fa-angle-right" style="font-size: 0.7em;"></i> Criar
</p>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Preencha os dados para criar um novo grupo</h3>
        </div>

        <div class="card-body">
            <form action="{{ route('grupos.store') }}" method="POST">
                @csrf

                {{-- Nome do Grupo --}}
                <div class="form-group">
                    <label for="nome">
                        <i class="fas fa-users"></i> Nome do Grupo
                    </label>
                    <input type="text" name="nome" id="nome" class="form-control @error('nome') is-invalid @enderror" placeholder="Digite o nome do grupo" value="{{ old('nome') }}">

                    @error('nome')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group d-flex justify-content-start">
                    {{-- Botão de Salvar --}}
                    <button type="submit" class="btn btn-success mr-2">
                        <i class="fas fa-save"></i> Salvar
                    </button>

                    {{-- Botão de Voltar --}}
                    <a href="{{ route('grupos.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('css')
    {{-- Custom styles for this page --}}
    <style>
        .form-group label {
            font-weight: 600;
        }
        .form-group .btn {
            min-width: 120px; /* Define um tamanho mínimo para os botões */
        }
    </style>
@endsection
