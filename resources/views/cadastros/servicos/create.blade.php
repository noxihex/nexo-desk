@extends('adminlte::page')

@section('title', 'Adicionar Serviço')

@section('content_header')
    <p style="font-size: 1.2em;">
        <a href="{{ route('empresas.edit', $empresa->id) }}">Empresa: {{ $empresa->nome }}</a>
        <i class="fas fa-angle-right" style="font-size: 0.7em;"></i> Adicionar Serviço
    </p>
@endsection

@section('content')
    {{-- O formulário agora engloba todos os cards --}}
    <form action="{{ route('servicos.store', $empresa->id) }}" method="POST">
        @csrf

        {{-- CARD PRINCIPAL --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Novo Serviço para {{ $empresa->nome }}</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="nome">Nome do Serviço</label>
                    <input type="text" name="nome" id="nome" class="form-control @error('nome') is-invalid @enderror" value="{{ old('nome') }}" required>
                    @error('nome')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>
            </div>
        </div>

        {{-- CARD DE INFORMAÇÕES --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Informações Personalizadas</h3>
            </div>
            <div class="card-body">
                <p class="text-muted">Preencha até 5 campos personalizados e seus valores</p>
                @for ($i = 0; $i < 5; $i++)
                    <div class="form-row align-items-center mb-2">
                        <div class="col-md-5">
                            <input type="text" class="form-control" name="informacoes[{{ $i }}][campo]" value="{{ old('informacoes.'.$i.'.campo') }}" placeholder="Campo {{ $i + 1 }}">
                        </div>
                        <div class="col-md-7">
                            <input type="text" class="form-control" name="informacoes[{{ $i }}][valor]" value="{{ old('informacoes.'.$i.'.valor') }}" placeholder="Valor {{ $i + 1 }}">
                        </div>
                    </div>
                @endfor
            </div>
        </div>
        
        {{-- CARD DE QUESTIONÁRIO --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Questionário</h3>
            </div>
            <div class="card-body">
                <p class="text-muted">Preencha até 5 perguntas para o questionário</p>
                @for ($i = 0; $i < 5; $i++)
                    <div class="form-group">
                        <input type="text" class="form-control" name="questionario[]" value="{{ old('questionario.'.$i) }}" placeholder="Pergunta {{ $i + 1 }}">
                    </div>
                @endfor
            </div>
        </div>

        {{-- BOTÕES DE AÇÃO --}}
        <div class="form-group d-flex justify-content-start">
            <button type="submit" class="btn btn-success mr-2"><i class="fas fa-save"></i> Criar Serviço</button>
            <a href="{{ route('empresas.edit', $empresa->id) }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Voltar</a>
        </div>
    </form>
@endsection