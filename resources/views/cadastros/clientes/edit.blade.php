@extends('adminlte::page')

@section('title', config('app.name') . ' - Editar Contato')

@section('content_header')
    <p style="font-size: 1.2em;">
        Cadastros <i class="fas fa-angle-right" style="font-size: 0.7em;"></i> Empresas <i class="fas fa-angle-right" style="font-size: 0.7em;"></i> Contatos
    </p>
@endsection

@section('content')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Editar Contato: {{ $user->name }}</h3>
        </div>

        <div class="card-body">
            <form action="{{ route('clientes.update', $user->id) }}" method="POST"> <!-- Rota clientes.update -->
                @csrf
                @method('PUT')

                {{-- Nome --}}
                <div class="form-group">
                    <label for="name"><i class="fas fa-user"></i> Nome</label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                    @error('name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                {{-- Email --}}
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required  @role('analista') disabled @endrole >
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                {{-- Senha --}}
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Senha (Deixe em branco se não quiser mudar)</label>
                    <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror">
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                {{-- Confirmação de Senha --}}
                <div class="form-group">
                    <label for="password_confirmation"><i class="fas fa-lock"></i> Confirmar Senha</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror">
                    @error('password_confirmation')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label><i class="fas fa-building"></i> Empresa</label>
                    <p class="form-control-plaintext mb-0 empresa-selecionada">{{ $user->empresa ? $user->empresa->nome : 'Sem empresa' }}</p>
                    <input type="hidden" name="empresa_id" value="{{ $empresaId }}">
                    @error('empresa_id')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>


                <div class="form-group d-flex justify-content-start">
                    <button type="submit" class="btn btn-success mr-2">
                        <i class="fas fa-save"></i> Atualizar Contato
                    </button>

                    @if($empresaId)
                    <a href="{{ route('empresas.edit', $empresaId) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                @else
                    <a href="{{ route('clientes.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                @endif
                </div>
            </form>
        </div>
    </div>
@endsection

@section('css')
    <style>
        .empresa-selecionada {
            color: inherit;
        }
    </style>
@endsection
