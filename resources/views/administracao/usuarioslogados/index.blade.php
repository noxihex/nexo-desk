@extends('adminlte::page')

@section('title', config('app.name') . ' - Usuários Logados')

@section('content_header')
<p style="font-size: 1.2em;">
    Administração <i class="fas fa-angle-right" style="font-size: 0.7em;"></i> Usuários logados
</p>
@endsection

@section('content')
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Sessões Ativas</h3>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Tipo</th>
                            <th>IP do Usuário</th>
                            <th>Navegador</th>
                            <th>Última Atividade</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sessions as $session)
                            <tr>
                                <!-- Nome do usuário -->
                                <td>{{ $session->name }}</td>

                                <!-- Tipo do usuário -->
                                <td>
                                    @if(in_array($session->role, ['cliente', 'clientedc']))
                                        <span class="badge badge-success">Cliente</span>
                                    @elseif(in_array($session->role, ['analista', 'supervisor', 'administrador']))
                                        <span class="badge badge-primary">Analista</span>
                                    @else
                                        <span class="badge badge-secondary">Desconhecido</span>
                                    @endif
                                </td>

                                <!-- IP do usuário -->
                                <td>{{ $session->ip_address ?? 'N/A' }}</td>

                                <!-- Navegador -->
                                <td>{{ $session->user_agent ?? 'N/A' }}</td>

                                <!-- Última Atividade -->
                                <td>{{ \Carbon\Carbon::createFromTimestamp($session->last_activity)->format('d/m/Y H:i:s') }}</td>

                                <!-- Ações -->
                                <td>
                                    <form action="{{ route('administracao.usuarioslogados.deslogar', $session->session_id) }}" method="POST" onsubmit="return confirm('Deseja deslogar este usuário?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="fas fa-sign-out-alt"></i> Deslogar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Nenhum usuário logado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
