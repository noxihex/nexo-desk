@extends('adminlte::page')

@section('title', 'BTXDesk - Gestão de Clientes')

@section('content_header')
    <p style="font-size: 1.2em;">
        Cadastros <i class="fas fa-angle-right" style="font-size: 0.7em;"></i> Clientes
    </p>
    <a href="{{ route('clientes.create') }}" class="btn btn-success">
        <i class="fas fa-plus-circle"></i> Novo Cliente
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
            <h3 class="card-title">Lista de Clientes</h3>
        </div>

        <!-- Adicionando a classe table-responsive -->
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="d-none d-md-table-cell">ID</th> <!-- Oculto em telas pequenas -->
                        <th>Nome</th>
                        <th class="d-none d-md-table-cell">Email</th> <!-- Oculto em telas pequenas -->
                        <th>Empresa</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td class="d-none d-md-table-cell">{{ $user->id }}</td> <!-- Oculto em telas pequenas -->
                            <td>{{ $user->name }}</td>
                            <td class="d-none d-md-table-cell">{{ $user->email }}</td> <!-- Oculto em telas pequenas -->

                            <!-- Exibe o nome da empresa associada ao cliente ou "Sem Empresa" -->
                            <td>
                                <span class="badge badge-primary" style="font-size: 0.9em;">
                                    {{ optional($user->empresa)->nome ?? 'Sem Empresa' }}
                                </span>
                            </td>

                            <!-- Ações: Editar e Ativar/Desativar -->
                            <td class="text-center">
                                <div class="d-flex justify-content-center">
                                    @role('supervisor|administrador')
                                    <a href="{{ route('clientes.edit', $user->id) }}" class="btn btn-sm btn-warning mr-1">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                    @endrole
                                    <form action="{{ route('clientes.deactivate', $user->id) }}" method="POST" style="display:inline-block;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $user->status ? 'btn-danger' : 'btn-success' }}" onclick="return confirm('Você tem certeza?')">
                                            <i class="fas {{ $user->status ? 'fa-ban' : 'fa-check' }}"></i>
                                            {{ $user->status ? 'Desativar' : 'Ativar' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Exibir os links de paginação -->
        <div class="card-footer">
            <div class="d-flex justify-content-end">
                {{ $users->links() }}
            </div>
        </div>
    </div>
@endsection
