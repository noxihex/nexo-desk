@extends('adminlte::page')

@section('title', 'BTXDesk - Gestão de Usuários')

@section('content_header')
    <p style="font-size: 1.2em;">
        Cadastros <i class="fas fa-angle-right" style="font-size: 0.7em;"></i> Usuários
    </p>
    <a href="{{ route('usuarios.create') }}" class="btn btn-success">
        <i class="fas fa-plus-circle"></i> Novo Usuário
    </a>
@endsection

@section('content')
    @if(session('success'))
        <script>
            $(document).ready(function() {
                toastr.success('{{ session('success') }}', 'Sucesso', {
                    closeButton: true,
                    progressBar: true,
                });
            });
        </script>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Lista de Usuários</h3>
        </div>

        <!-- Adicionando a classe table-responsive -->
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="d-none d-md-table-cell">ID</th> <!-- Oculto em telas pequenas -->
                        <th>Nome</th>
                        <th class="d-none d-md-table-cell">Email</th> <!-- Oculto em telas pequenas -->
                        <th>Permissão</th>
                        <th>Grupo</th>
                        <th>Setor</th>
                        <th class="d-none d-md-table-cell">Status</th> <!-- Oculto em telas pequenas -->
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td class="d-none d-md-table-cell">{{ $user->id }}</td> <!-- Oculto em telas pequenas -->
                            <td>{{ $user->name }}</td>
                            <td class="d-none d-md-table-cell">{{ $user->email }}</td> <!-- Oculto em telas pequenas -->

                            <!-- Exibe a permissão -->
                            <td>
                                <span class="badge badge-info" style="font-size: 0.9em;">
                                    {{ ucfirst($user->roles->pluck('name')->first()) }}
                                </span>
                            </td>

                            <!-- Exibe o grupo -->
                            <td>
                                <span class="badge badge-primary" style="font-size: 0.9em;">
                                    {{ optional($user->grupo)->nome ?? 'Sem Grupo' }}
                                </span>
                            </td>

                            <!-- Exibe o setor -->
                            <td>
                                <span class="badge badge-secondary" style="font-size: 0.9em;">
                                    {{ optional($user->setor)->nome ?? 'Sem Setor' }}
                                </span>
                            </td>

                            <!-- Exibe o status -->
                            <td class="text-center">
                                <span class="badge {{ $user->status ? 'badge-success' : 'badge-danger' }}" style="font-size: 0.9em;">
                                    {{ $user->status ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>

                            <!-- Ações -->
                            <td class="text-center">
                                <div class="d-flex justify-content-center">
                                    @role('administrador')
                                    <a href="{{ route('usuarios.edit', $user->id) }}" class="btn btn-sm btn-warning mr-1">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                    @endrole
                                    <form action="{{ route('usuarios.deactivate', $user->id) }}" method="POST" style="display:inline-block;">
                                        @csrf
                                        @role('administrador|supervisor')
                                        <button type="submit" class="btn btn-sm {{ $user->status ? 'btn-danger' : 'btn-success' }}" onclick="return confirm('Você tem certeza?')">
                                            <i class="fas {{ $user->status ? 'fa-ban' : 'fa-check' }}"></i>
                                            {{ $user->status ? 'Desativar' : 'Ativar' }}
                                        </button>
                                        @endrole
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            <div class="d-flex justify-content-end">
                {{ $users->links() }}
            </div>
        </div>
    </div>
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <style>
        .card-title {
            font-weight: bold;
        }

        .table th, .table td {
            vertical-align: middle;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            line-height: 1.5;
        }

        /* Estilos para badges */
        .badge-info {
            background-color: #17a2b8;
        }

        .badge-primary {
            background-color: #007bff;
        }

        .badge-secondary {
            background-color: #6c757d;
        }

        .badge-success {
            background-color: #28a745;
        }

        .badge-danger {
            background-color: #dc3545;
        }
    </style>
@endsection

@section('js')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    @if(session('success'))
        <script>
            $(document).ready(function() {
                toastr.success('{{ session('success') }}', 'Sucesso', {
                    closeButton: true,
                    progressBar: true,
                });
            });
        </script>
    @endif
    @if(session('error'))
        <script>
            $(document).ready(function() {
                toastr.error('{{ session('error') }}', 'Erro', {
                    closeButton: true,
                    progressBar: true,
                });
            });
        </script>
    @endif
@endsection
