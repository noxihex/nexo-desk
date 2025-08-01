@extends('adminlte::page')

@section('title', 'Instalação do Sistema - Criar Usuário')

@section('content_header')
    <p style="font-size: 1.2em;">
        Instalação <i class="fas fa-angle-right" style="font-size: 0.7em;"></i> Criar Primeiro Usuário
    </p>
@endsection

@section('content')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Preencha os dados para criar o primeiro usuário do sistema</h3>
        </div>

        <div class="card-body">
            <form action="{{ route('install.store') }}" method="POST">
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

                {{-- Permissão (Papel/Role) --}}
                <div class="form-group">
                    <label for="role"><i class="fas fa-shield-alt"></i> Permissão</label>
                    <select name="role" id="role" class="form-control @error('role') is-invalid @enderror">
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                        @endforeach
                    </select>
                    @error('role')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group d-flex justify-content-start">
                    {{-- Botão de Salvar --}}
                    <button type="submit" class="btn btn-success mr-2">
                        <i class="fas fa-save"></i> Criar Usuário
                    </button>
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
