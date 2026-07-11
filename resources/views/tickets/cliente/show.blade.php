@extends('adminlte::page')

@section('title', config('app.name') . ' - Detalhes do Ticket')

@section('content_header')
<p style="font-size: 1.2em;">
    Tickets <i class="fas fa-angle-right" style="font-size: 0.7em;"></i> Detalhes
</p>
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

<!-- Card com detalhes do ticket -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Detalhes do Ticket <strong>#{{ $ticket->id }}</strong></h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <p><strong>ID:</strong> {{ $ticket->id }}</p>
                <p><strong>Assunto:</strong> {{ $ticket->assunto }}</p>
                <p><strong>Setor:</strong> {{ $ticket->setor->nome ?? 'N/A' }}</p>
            </div>
            <div class="col-md-4">
                <p>
                    <strong>Criado por:</strong>
                    {{ $ticket->user->name }}
                    ({{ $ticket->user->hasRole(['supervisor', 'analista', 'administrador']) ? 'Analista' : 'Cliente' }})
                </p>
                <p><strong>Empresa:</strong> {{ $ticket->empresa->nome ?? 'N/A' }}</p>
                <p><strong>Data de Criação:</strong> {{ $ticket->created_at->format('d/m/Y H:i') }}</p>
            </div>
            <div class="col-md-4">
                <p><strong>Status:</strong>
                    <span class="badge
                        @if($ticket->status == 'aberto') badge-success
                        @elseif($ticket->status == 'pendente cliente') badge-primary
                        @elseif($ticket->status == 'pendente analista') badge-warning
                        @elseif($ticket->status == 'fechado') badge-secondary
                        @endif">
                        {{ ucfirst($ticket->status) }}
                    </span>
                </p>
                <p><strong>Última Atualização:</strong> {{ $ticket->updated_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        <div class="mt-3">
            <p><strong>Descrição:</strong></p>
<div style="white-space: pre-line;">{{ $ticket->descricao }}</div>
        </div>

        @if($ticket->attachments->isNotEmpty())
            <div class="mt-3">
                <h5><i class="fas fa-paperclip"></i> Anexos:</h5>
                <ul>
                    @foreach($ticket->attachments as $attachment)
                        <li>
                            <a href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank">
                                {{ basename($attachment->file_path) }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
    <div class="card-footer">
        <div class="d-flex">
            <a href="{{ route('tickets.cliente.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>

        <div class="d-flex justify-content-end">
            @if($ticket->status !== 'fechado' && $ticket->categoria) <!-- Só exibe o botão se o ticket não estiver fechado e tiver categoria -->
                {{-- Temporariamente oculto para clientes; remova `d-none` para voltar a exibi-lo. --}}
                <button type="button" class="btn btn-primary btn-sm d-none" data-toggle="modal" data-target="#finalizeModal">
                    <i class="fas fa-check-circle"></i> Finalizar
                </button>
            @elseif(!$ticket->categoria)
                <p>
                    <!-- Caso eu queira exibir algo -->
                </p>
            @endif
        </div>

    </div>
</div>

<!-- Card para mensagens -->
<div class="card mt-3">
    <div class="card-header">
        <h5 class="card-title"><i class="fas fa-comments"></i> Mensagens</h5>
    </div>
    <div class="card-body" id="mensagensContainer" style="max-height: 500px; overflow-y: auto;">
        <div class="timeline">
            @if($ticket->mensagens->isNotEmpty())
                @foreach($ticket->mensagens as $mensagem)
                    <div>
                        <!-- Ícone da mensagem -->
                        <i class="fas fa-user-circle bg-{{ $mensagem->user->hasRole(['administrador', 'analista', 'supervisor']) ? 'blue' : 'purple' }}"></i>
                        <div class="timeline-item">
                            <!-- Data e hora -->
                            <span class="time"><i class="fas fa-clock"></i> {{ $mensagem->created_at->diffForHumans() }}</span>
                            <!-- Nome do usuário -->
                            <h3 class="timeline-header"><strong>{{ $mensagem->user->name }}</strong> ({{ $mensagem->user->hasRole(['administrador', 'analista', 'supervisor']) ? 'Analista' : 'Cliente' }})</h3>
                            <!-- Conteúdo da mensagem -->
                            <div class="timeline-body">
                                {!! nl2br(e($mensagem->descricao)) !!}
                                @if($mensagem->attachments->isNotEmpty())
                                    <hr>
                                    <strong>Anexos:</strong>
                                    <ul>
                                        @foreach($mensagem->attachments as $attachment)
                                            <li>
                                                <a href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank">
                                                    {{ basename($attachment->file_path) }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <p class="text-muted">Nenhuma mensagem ainda.</p>
            @endif
        </div>
    </div>
    @if($ticket->status !== 'fechado')
    <div class="card-footer">
        <form action="{{ route('tickets.cliente.mensagens.store', $ticket->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="descricao">Enviar nova mensagem:</label>
                <textarea name="descricao" id="descricao" class="form-control" rows="3" placeholder="Digite sua mensagem aqui..." required></textarea>
            </div>
            <div class="form-group">
                <button type="button" class="btn btn-info btn-sm" onclick="addAttachmentField()">
                    <i class="fas fa-paperclip"></i> Inserir Anexo
                </button>
                <small class="form-text text-muted">Você pode adicionar até 5 anexos, máximo 5MB cada.</small>
                <div id="attachmentFields" style="margin-top: 10px;"></div>
            </div>
            <button type="submit" class="btn btn-success btn-sm">
                <i class="fas fa-paper-plane"></i> Enviar Mensagem
            </button>
        </form>
    </div>
@endif

</div>


<div class="modal fade" id="finalizeModal" tabindex="-1" role="dialog" aria-labelledby="finalizeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="finalizeModalLabel">Finalizar Ticket</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="finalizeForm" action="{{ route('tickets.cliente.finalize', $ticket->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <!-- Relato Final -->
                    <div class="form-group">
                        <label for="descricao_fechamento">Relato final:</label>
                        <textarea id="descricao_fechamento" name="descricao_fechamento" class="form-control" required></textarea>
                    </div>

                    <!-- Aviso de falta de categoria -->
                    <div id="categoriaErrorAlert" class="alert alert-danger d-none" role="alert">
                        Este ticket não possui uma categoria vinculada. Não é possível finalizar.
                    </div>

                    <!-- Campos de Horas e Minutos -->
                    <div id="horasMinutosFields">
                        <div class="form-group" style="visibility: hidden; height: 1px;">
                            <label for="horas">Horas</label>
                            <input type="number" id="horas" name="horas" class="form-control" required disabled>
                        </div>
                        <div class="form-group" style="visibility: hidden; height: 1px;">
                            <label for="minutos">Minutos</label>
                            <input type="number" id="minutos" name="minutos" class="form-control" required disabled>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Finalizar</button>
                </div>
            </form>
        </div>
    </div>
</div>



@endsection

@section('css')
<style>
        .card {
            font-size: 16px;
            margin-bottom: 5px;

        }
        p {
            margin-bottom: 0px;
            padding-bottom: 5px;
        }
</style>
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endsection

@section('js')
<script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Ao abrir o modal
        $('#finalizeModal').on('show.bs.modal', function () {
            const ticketId = {{ $ticket->id }};
            const horasInput = document.getElementById('horas');
            const minutosInput = document.getElementById('minutos');
            const categoriaErrorAlert = document.getElementById('categoriaErrorAlert');
            const horasMinutosFields = document.getElementById('horasMinutosFields');

            // Resetar os campos e mensagens antes de carregar os dados
            horasInput.value = '';
            minutosInput.value = '';
            categoriaErrorAlert.classList.add('d-none');
            horasMinutosFields.classList.remove('d-none');

            // Fazer a requisição AJAX para obter as horas sugeridas
            fetch(`/tickets/cliente/${ticketId}/horas-sugeridas`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erro ao carregar as horas sugeridas.');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        // Caso o ticket não tenha categoria vinculada
                        categoriaErrorAlert.textContent = data.error;
                        categoriaErrorAlert.classList.remove('d-none');
                        horasMinutosFields.classList.add('d-none');
                    } else {
                        // Preencher os campos de horas e minutos com os valores retornados
                        horasInput.value = data.horas;
                        minutosInput.value = data.minutos;

                        // Garantir que os campos sejam editáveis caso o usuário precise ajustar
                        horasInput.removeAttribute('disabled');
                        minutosInput.removeAttribute('disabled');
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar as horas sugeridas:', error);
                    toastr.error('Erro ao carregar as horas sugeridas.', 'Erro');
                });
        });
    });

</script>
<script>
     $(document).ready(function() {
            @if(session('success'))
                toastr.success('{{ session('success') }}', 'Sucesso', {
                    closeButton: true,
                    progressBar: true,
                });
            @endif

            @if(session('error'))
                toastr.error('{{ session('error') }}', 'Erro', {
                    closeButton: true,
                    progressBar: true,
                });
            @endif

            var mensagensContainer = document.getElementById('mensagensContainer');
            if (mensagensContainer) {
                mensagensContainer.scrollTop = mensagensContainer.scrollHeight;
            }
        });

        let attachmentCount = 0;
        function addAttachmentField() {
            if (attachmentCount < 5) {
                attachmentCount++;
                $('#attachmentFields').append(
                    `<input type="file" name="attachments[]" class="form-control-file mt-1" accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx,.txt,.mp4,.kmz,.kml,.zip">`
                );
            } else {
                alert('Máximo de 5 anexos permitidos.');
            }
        }
</script>
@endsection
