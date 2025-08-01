@extends('adminlte::page')

@section('title', config('app.name') . ' - Criar Contato')

@section('content_header')
    <p style="font-size: 1.2em;">
        Cadastros <i class="fas fa-angle-right" style="font-size: 0.7em;"></i> Empresas <i class="fas fa-angle-right" style="font-size: 0.7em;"></i> Contato
    </p>
@endsection

@section('content')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Preencha os dados para criar um novo contato</h3>
        </div>

        <div class="card-body">
            <form action="{{ route('clientes.store') }}" method="POST"> <!-- Rota clientes.store -->
                @csrf

                {{-- Nome --}}
                <div class="form-group">
                    <label for="name"><i class="fas fa-user"></i> Nome</label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                    @error('name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                {{-- Email --}}
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                {{-- Senha --}}
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Senha</label>
                    <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required>
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                {{-- Confirmação de Senha --}}
                <div class="form-group">
                    <label for="password_confirmation"><i class="fas fa-lock"></i> Confirmar Senha</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" required>
                    @error('password_confirmation')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                {{-- Tipo de Cliente (Permissão) --}}
                <div class="form-group">
                    <label for="role"><i class="fas fa-shield-alt"></i> Tipo de Central do Cliente</label>
                    <select name="role" id="role" class="form-control @error('role') is-invalid @enderror" @role('analista|supervisor') disabled @endrole >
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}">
                                {{ $role->name == 'cliente' ? 'Cliente HelpDesk' : 'Cliente DataCenter' }}
                            </option>
                        @endforeach
                    </select>
                    @error('role')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="empresa_id"><i class="fas fa-building"></i> Empresa</label>
                    <select name="empresa_id_disabled" id="empresa_id_disabled" class="form-control" @role('analista|supervisor') disabled @endrole >
                        <option value="">Sem Empresa</option> {{-- Permite a opção "Sem Empresa" --}}
                        @foreach($empresas as $empresa)
                            <option value="{{ $empresa->id }}" {{ $empresa->id == $empresaId ? 'selected' : '' }}>
                                {{ $empresa->nome }}
                            </option>
                        @endforeach
                    </select>
                    {{-- Campo oculto para garantir envio do valor ao backend --}}

                    <input type="hidden" name="empresa_id" value="{{ $empresaId }}">

                </div>


                <div class="form-group d-flex justify-content-start">
                    <button type="submit" class="btn btn-success mr-2">
                        <i class="fas fa-save"></i> Criar contato
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
