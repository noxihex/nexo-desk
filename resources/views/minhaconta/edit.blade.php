@extends('adminlte::page')

@section('title', 'BTXDesk - Minha Conta')

@section('content_header')
    <h1>Minha Conta</h1>
@endsection

@role('analista|supervisor|administrador')
@include('layouts.notificahtml')
@endrole


@section('content')


    <!-- Formulário para editar nome e e-mail -->
    <div class="row">
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Informações do Perfil</h3>
                </div>
                <form action="{{ route('minhaconta.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Nome Completo</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="email">E-mail</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Formulário para alterar a senha -->
        <div class="col-md-6">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">Alterar Senha</h3>
                </div>
                <form action="{{ route('minhaconta.password') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="form-group">
                            <label for="current_password">Senha Atual</label>
                            <input type="password" class="form-control @error('current_password') is-invalid @enderror" id="current_password" name="current_password" required>
                            @error('current_password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="password">Nova Senha</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="password_confirmation">Confirme a Nova Senha</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-success">Alterar Senha</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('js')
@role('analista|supervisor|administrador')
@include('layouts.notificajs')
@endrole
    <!-- Incluindo JS do Toastr via CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        $(document).ready(function() {
            // Verifica se existe uma sessão de sucesso e exibe o Toastr verde
            @if(session('success'))
                toastr.success("{{ session('success') }}", 'Sucesso', {
                    closeButton: true,
                    progressBar: true,
                    positionClass: "toast-top-right",
                    timeOut: "5000"
                });
            @endif

            // Verifica se existe uma sessão de erro e exibe o Toastr vermelho
            @if(session('error'))
                toastr.error("{{ session('error') }}", 'Erro', {
                    closeButton: true,
                    progressBar: true,
                    positionClass: "toast-top-right",
                    timeOut: "5000"
                });
            @endif

            // Exibe erros de validação, se houver
            @if($errors->any())
                @foreach ($errors->all() as $error)
                    toastr.error("{{ $error }}", 'Erro', {
                        closeButton: true,
                        progressBar: true,
                        positionClass: "toast-top-right",
                        timeOut: "5000"
                    });
                @endforeach
            @endif
        });
    </script>
@endsection



@section('css')
@role('analista|supervisor|administrador')
@include('layouts.notificacss')
@endrole
<style>
     /* Exibe texto completo em telas maiores */
 .badge-text-full {
        display: inline;
    }
    .badge-text-compact {
        display: none;
    }

    /* Em telas menores, exibe o texto compacto */
    @media (max-width: 576px) {
        .badge-text-full {
            display: none;
        }
        .badge-text-compact {
            display: inline;
        }
    }
</style>
    <!-- Incluindo CSS do Toastr via CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endsection





