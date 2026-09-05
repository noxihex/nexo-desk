@extends('adminlte::page')
@section('title', config('app.name') . ' - Backup')

@section('content_header')
<p style="font-size: 1.2em;">
    Administração <i class="fas fa-angle-right" style="font-size: 0.7em;"></i> Backups
</p>
@endsection

@section('content')
    @if(session('success'))
        <script>
            toastr.success('{{ session('success') }}', 'Sucesso', { closeButton: true, progressBar: true });
        </script>
    @elseif(session('error'))
        <script>
            toastr.error('{{ session('error') }}', 'Erro', { closeButton: true, progressBar: true });
        </script>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Lista de Backups</h3>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Data e Hora</th>
                        <th>Status</th>
                        <th>Arquivo</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($backups as $backup)
                        <tr>
                            <td>{{ $backup->id }}</td>
                            <td>{{ \Carbon\Carbon::parse($backup->data_hora)->format('d/m/Y H:i:s') }}</td>
                            <td>
                                @if($backup->status == 'sucesso')
                                    <span class="badge badge-success">Sucesso</span>
                                @else
                                    <span class="badge badge-danger">Falha</span>
                                @endif
                            </td>
                            <td>{{ basename(str_replace('\\', '/', $backup->local_arquivo)) }}</td>
                            <td>
                                @if($backup->status == 'sucesso')
                                    <a href="{{ route('backup.download', $backup->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-download"></i> Baixar
                                    </a>
                                @else
                                    <button class="btn btn-sm btn-secondary" disabled>
                                        <i class="fas fa-download"></i> Indisponível
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Nenhum backup encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
