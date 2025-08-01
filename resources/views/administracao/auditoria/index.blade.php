@extends('adminlte::page')

@section('title', config('app.name') . ' - Auditoria')

@section('content_header')
<p style="font-size: 1.2em;">
    Administração <i class="fas fa-angle-right" style="font-size: 0.7em;"></i> Auditoria
</p>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Registro de alterações feitas:</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Data/Hora</th>
                            <th>Usuário</th>
                            <th>Ação</th>
                            <th>Objeto</th>
                            <th>Alterações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($audits as $audit)
                            <tr>
                                <td>{{ $audit->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $audit->user ? $audit->user->name : 'N/A' }}</td>
                                <td>{{ ucfirst($audit->event) }}</td>
                                <td>{{ class_basename($audit->auditable_type) }}</td>
                                <td>
                                    <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#auditModal{{ $audit->id }}">
                                        Ver
                                    </button>

                                    <!-- Modal para exibir as alterações -->
                                    <div class="modal fade" id="auditModal{{ $audit->id }}" tabindex="-1" role="dialog" aria-labelledby="auditModalLabel{{ $audit->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-lg" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="auditModalLabel{{ $audit->id }}">Detalhes da Alteração</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <h5>Valores Antigos</h5>
                                                    <pre>{{ json_encode($audit->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                    <h5>Novos Valores</h5>
                                                    <pre>{{ json_encode($audit->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Nenhum registro de auditoria encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $audits->links() }}
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@stop

@section('js')
    <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        $(document).ready(function() {
            @if(session('success'))
                toastr.success('{{ session('success') }}', 'Sucesso', { closeButton: true, progressBar: true });
            @endif
        });
    </script>
@stop
