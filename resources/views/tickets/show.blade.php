@extends('adminlte::page')

@section('title', config('app.name') . ' - Detalhes do Ticket')

@section('content_header')
<p style="font-size: 1.2em;">
    Tickets <i class="fas fa-angle-right" style="font-size: 0.7em;"></i> Detalhes
</p>
@endsection

@include('layouts.notificahtml')


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

<div class="card" style="margin-bottom: 10px;">
    <div class="card-header" style="padding: 8px 15px;">
        <h3 class="card-title" style="margin: 0; font-size: 1.1em;">Detalhes do Ticket <strong>#{{ $ticket->id }}</strong></h3>
    </div>

    <div class="card-body" style="padding: 10px;">
        <div class="row">
            <div class="col-md-4">
                <p class="mb-1"><strong>ID:</strong> {{ $ticket->id }}</p>
                <p class="mb-1"><strong>Assunto:</strong> {{ $ticket->assunto }}</p>
                <p class="mb-1"><strong>Categoria:</strong> {{ $ticket->categoria->nome ?? 'N/A' }}</p>

                @php
                    $horas = intdiv($ticket->horas_gastas, 60);
                    $minutos = $ticket->horas_gastas % 60;
                @endphp

                <p class="mb-1"><strong>Horas Gastas:</strong>
                    @if($ticket->horas_gastas > 0)
                        {{ $horas > 0 ? $horas . ' horas' : '' }}{{ $horas > 0 && $minutos > 0 ? ' e ' : '' }}{{ $minutos > 0 ? $minutos . ' minutos' : '' }}
                    @endif
                </p>
                <p class="mb-1"><strong>Prazo:</strong> {{ $ticket->prazo ? $ticket->prazo->format('d/m/Y') : '-' }}</p>
            </div>

            <div class="col-md-4">
                <p>
                    <strong>Criado por:</strong>
                    {{ $ticket->user->name }}
                    ({{ $ticket->user->hasRole(['supervisor', 'analista', 'administrador']) ? 'Analista' : 'Cliente' }})
                </p>
                <p class="mb-1"><strong>Contato:</strong> {{ $ticket->cliente ? $ticket->cliente->name : 'N/A' }}</p>
                <p class="mb-1"><strong>Empresa:</strong> {{ $ticket->empresa ? $ticket->empresa->nome : 'N/A' }}</p>
                <p class="mb-1"><strong>Data de Criação:</strong> {{ $ticket->created_at->format('d/m/Y H:i') }}</p>
            </div>

            <div class="col-md-4">
                <p class="mb-1"><strong>Grupo:</strong> {{ $ticket->grupo ? $ticket->grupo->nome : 'N/A' }}</p>
                <p class="mb-1"><strong>Setor:</strong> {{ $ticket->setor ? $ticket->setor->nome : 'N/A' }}</p>
                <p class="mb-1"><strong>Atribuído ao Analista:</strong> {{ $ticket->analista ? $ticket->analista->name : 'N/A' }}</p>
                <p class="mb-1"><strong>Status:</strong>
                    <span class="badge
                        @if($ticket->status == 'aberto') badge-success
                        @elseif($ticket->status == 'pendente cliente') badge-primary
                        @elseif($ticket->status == 'pendente analista') badge-warning
                        @elseif($ticket->status == 'fechado') badge-secondary
                        @endif">
                        {{ ucfirst($ticket->status) }}
                    </span>
                </p>
            </div>
        </div>

        <div class="mt-2">
            <p><strong>Descrição:</strong></p>
<div style="white-space: pre-line;">{{ $ticket->descricao }}</div>
        </div>

        @if($ticket->status === 'fechado' && !empty($ticket->descricao_final))
            <div class="mt-2">
                <p><strong>Relato final:</strong> {{ $ticket->descricao_final }}</p>
            </div>
        @endif

        @if($ticket->attachments->isNotEmpty())
            <div class="mt-2">
                <h5><i class="fas fa-paperclip"></i> Anexos:</h5>
                <ul style="padding-left: 15px;">
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

    <div class="card-footer d-flex justify-content-between" style="padding: 8px 15px;">
        <div class="col-md-6">
            <a href="{{ route('tickets.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>

        <div class="col-md-6 text-right" >
            @if($ticket->status !== 'fechado')
                <!-- Botão para Assumir Ticket -->
                @if(!$ticket->atribuido_ao_analista_id || $ticket->atribuido_ao_analista_id !== auth()->id())
                    <button class="btn btn-success btn-sm mr-2" data-toggle="modal" data-target="#assumeModal">
                        <i class="fas fa-user-check"></i> Assumir
                    </button>
                @endif

                <!-- Botão para Finalizar Ticket -->
                <button class="btn btn-primary btn-sm mr-2" data-toggle="modal" data-target="#finalizeModal">
                    <i class="fas fa-check"></i> Finalizar
                </button>

                <!-- Botão para Transferir Ticket -->
                <button
                    class="btn btn-purple btn-sm mr-2"
                    data-toggle="modal"
                    data-target="#transferModal"
                    data-ticket-id="{{ $ticket->id }}">
                    <i class="fas fa-exchange-alt"></i> Transferir
                </button>
            @endif

            @role('supervisor|administrador')
                <!-- Botão para Editar Ticket -->
                <a href="{{ route('tickets.edit', $ticket->id) }}" class="btn btn-warning btn-sm mr-2">
                    <i class="fas fa-edit"></i> Editar
                </a>
                @endrole
                @role('administrador')
                <!-- Botão para Excluir Ticket -->
                <button class="btn btn-danger btn-sm mr-2" data-toggle="modal" data-target="#deleteModal">
                    <i class="fas fa-trash"></i> Excluir
                </button>
            @endrole
        </div>
    </div>
</div>



<div class="card mt-3">
    <div class="card-header" style="padding: 8px 15px;">
        <h5 style="margin: 0;"><i class="fas fa-comments"></i> Mensagens</h5>
    </div>

    <div class="card-body" id="mensagensContainer" style="max-height: 400px; overflow-y: auto; padding: 10px;">
        <div class="timeline">
            @if($ticket->mensagens->isNotEmpty())
                @foreach($ticket->mensagens as $mensagem)
                    @php
                        $userType = $mensagem->user->hasRole(['administrador', 'analista', 'supervisor']) ? 'Analista' : 'Cliente';
                        $iconColor = $userType === 'Analista' ? 'bg-blue' : 'bg-purple';
                        $badgeColor = $userType === 'Analista' ? 'bg-primary' : 'bg-purple';
                    @endphp

                    <div class="time-label">
                        <span class="{{ $badgeColor }}" style="padding: 2px 8px; font-size: 0.85em;">
                            {{ $mensagem->created_at->format('d/m/Y H:i') }}
                        </span>
                    </div>
                    <div>
                        <i class="fas fa-user {{ $iconColor }}" style="margin-right: 5px;"></i>
                        <div class="timeline-item" style="padding: 8px; margin-bottom: 0;">
                            <span class="time"><i class="fas fa-clock"></i> {{ $mensagem->created_at->diffForHumans() }}</span>
                            <h3 class="timeline-header" style="margin-bottom: 5px;">
                                <strong>{{ $mensagem->user->name }} ({{ $userType }})</strong>
                            </h3>
                            <div class="timeline-body" style="font-size: 0.9em;">
                                {!! nl2br(e($mensagem->descricao)) !!}

                                @if($mensagem->attachments->isNotEmpty())
                                    <hr>
                                    <p><strong>Anexos:</strong></p>
                                    <ul style="padding-left: 15px;">
                                        @foreach($mensagem->attachments as $attachment)
                                            <li><a href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank">{{ basename($attachment->file_path) }}</a></li>
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
    <div class="card-footer" style="padding: 8px 15px;">
        <form action="{{ route('mensagens.store', $ticket->id) }}" method="POST" enctype="multipart/form-data" id="messageForm">
            @csrf
            <input type="hidden" name="status" id="status" value=""> <!-- Campo oculto para definir o status -->

            <div class="form-group" style="margin-bottom: 5px;">
                <label for="descricao" style="margin-bottom: 0.3rem;">Enviar nova mensagem:</label>
                <textarea name="descricao" id="descricao" class="form-control" rows="3" placeholder="Digite sua mensagem aqui..." required></textarea>
            </div>

            <div class="form-group">
                <button type="button" class="btn btn-info btn-sm" onclick="addAttachmentField()">
                    <i class="fas fa-paperclip"></i> Inserir Anexo
                </button>
                <small class="form-text text-muted">Você pode adicionar até 5 anexos, máximo 5MB cada. Formatos aceitos: jpg, jpeg, png, pdf, doc, docx, xls, xlsx, txt, kmz, kml, zip e mp4</small>
                <div id="attachmentFields" style="margin-top: 10px;"></div>
            </div>

            <!-- Botões de Envio -->
            <button type="button" class="btn btn-success btn-sm" onclick="submitMessageForm()"><i class="fas fa-paper-plane"></i> Enviar Mensagem</button>
            <button type="button" class="btn btn-primary btn-sm" onclick="submitMessageForm('pendente cliente')"><i class="fas fa-paper-plane"></i> Enviar e alterar para Pendente Cliente</button>
            <button type="button" class="btn btn-warning btn-sm" onclick="submitMessageForm('pendente analista')"><i class="fas fa-paper-plane"></i> Enviar e alterar para Pendente Analista</button>
        </form>
    </div>
</div>
@endif


<div class="modal fade" id="assumeModal" tabindex="-1" role="dialog" aria-labelledby="assumeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assumeModalLabel">Assumir Ticket</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <!-- Formulário de Assumir Ticket -->
            <form action="{{ route('tickets.assumir', $ticket->id) }}" method="POST">
                @csrf <!-- Token de segurança obrigatório para métodos POST -->
                <div class="modal-body">
                    <p>Deseja assumir o Ticket <strong>#{{ $ticket->id }} - {{ $ticket->assunto }}</strong>?</p>

                    <!-- Seleção de Setor -->
                    <div class="form-group">
                        <label for="setor">Setor:</label>
                        <select name="setor" id="setor" class="form-control" required>
                            <option value="" disabled {{ is_null($ticket->setor_id) ? 'selected' : '' }}>Selecione um setor</option>
                            @foreach ($setores as $setor)
                                <option value="{{ $setor->id }}" {{ $ticket->setor_id == $setor->id ? 'selected' : '' }}>
                                    {{ $setor->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Seleção de Categoria -->
                    <div class="form-group">
                        <label for="categoria">Categoria:</label>
                        <select name="categoria" id="categoria" class="form-control" required>
                            <option value="" disabled {{ is_null($ticket->categoria_id) ? 'selected' : '' }}>Selecione uma categoria</option>
                            @foreach ($categoriasAssociadas as $categoria)
                                <option value="{{ $categoria->id }}" {{ $ticket->categoria_id == $categoria->id ? 'selected' : '' }}>
                                    {{ $categoria->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Assumir Ticket</button>
                </div>
            </form>
        </div>
    </div>
</div>










<div class="modal fade" id="transferModal" tabindex="-1" role="dialog" aria-labelledby="transferModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="transferModalLabel">Transferir Ticket</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('tickets.transferir', $ticket->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Selecione o setor, grupo e analista:</p>

                    <!-- Seleção de Setor -->
                    <div class="form-group">
                        <label for="setor-transfer">Setor:</label>
                        <select name="setor" id="setor-transfer" class="form-control" required>
                            <option value="" disabled selected>Selecione um setor</option>
                            @foreach ($setores as $setor)
                                <option value="{{ $setor->id }}" {{ $ticket->setor_id == $setor->id ? 'selected' : '' }}>
                                    {{ $setor->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Seleção de Grupo -->
                    <div class="form-group">
                        <label for="grupo-transfer">Grupo:</label>
                        <select name="grupo" id="grupo-transfer" class="form-control" required>
                            <option value="" disabled selected>Selecione um grupo</option>
                            @foreach ($grupos as $grupo)
                                <option value="{{ $grupo->id }}" {{ $ticket->grupo_id == $grupo->id ? 'selected' : '' }}>
                                    {{ $grupo->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Seleção de Analista -->
                    <div class="form-group">
                        <label for="analista-transfer">Analista:</label>
                        <select name="analista" id="analista-transfer" class="form-control">
                            <option value="" {{ is_null($ticket->atribuido_ao_analista_id) ? 'selected' : '' }}>Sem analista</option>
                            @foreach ($analistas as $analista)
                                <option value="{{ $analista->id }}" {{ $ticket->atribuido_ao_analista_id == $analista->id ? 'selected' : '' }}>
                                    {{ $analista->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Transferir Ticket</button>
                </div>
            </form>
        </div>
    </div>
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
            <form action="{{ route('tickets.finalize', $ticket->id) }}" method="POST">
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
                        Este ticket não possui uma categoria vinculada. Não é possível finalizá-lo.
                    </div>

                    <!-- Campos de Horas e Minutos -->
                    <div id="horasMinutosFields">
                        <div class="form-group">
                            <label for="horas">Horas</label>
                            <input type="number" id="horas" name="horas" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="minutos">Minutos</label>
                            <input type="number" id="minutos" name="minutos" class="form-control" required>
                        </div>
                        <p class="text-muted">O valor de horas foi calculado com base nas interações do ticket e sua categoria.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" id="finalizarButton" class="btn btn-primary">Finalizar</button>
                </div>
            </form>
        </div>
    </div>
</div>




<!-- Modal para Confirmar Exclusão -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmar Exclusão</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Tem certeza de que deseja excluir o Ticket #{{ $ticket->id }}?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <form action="{{ route('tickets.destroy', $ticket->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Excluir</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('css')
@include('layouts.notificacss')
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
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

        .card-body p, .card-body h3, .card-footer .btn, .timeline-item h3, .timeline-body {
            margin-bottom: 0.25rem;
        }

        .btn-purple {
            background-color: #6f42c1;
            color: white;
        }
        .btn-purple:hover {
            background-color: #5a33a0;
            color: white;
        }
    </style>
@endsection
@section('js')
@include('layouts.notificajs')
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
    (function ($) {
        'use strict';

        // Ativador do modal
        $(document).on('click', '[data-toggle="modal"]', function (e) {
            e.preventDefault();
            const target = $(this).attr('data-target');
            $(target).modal('show');
        });

        // Fechamento do modal
        $(document).on('click', '[data-dismiss="modal"]', function (e) {
            e.preventDefault();
            $(this).closest('.modal').modal('hide');
        });

        // Funções principais do modal
        $.fn.modal = function (action) {
            return this.each(function () {
                const $modal = $(this);
                if (action === 'show') {
                    $modal.addClass('show').css({ display: 'block' });
                    $('<div class="modal-backdrop fade show"></div>').appendTo('body');

                    // Aciona o evento personalizado de abertura
                    $modal.trigger('modal.show');
                } else if (action === 'hide') {
                    $modal.removeClass('show').css({ display: 'none' });
                    $('.modal-backdrop').remove();
                }
            });
        };

        // Evento personalizado para abrir o modal e preencher dados
        $('#finalizeModal').on('modal.show', function () {
            const ticketId = {{ $ticket->id }}; // ID do ticket atual

            // Fazer requisição AJAX para calcular as horas sugeridas
            $.ajax({
                url: `/tickets/${ticketId}/horas-sugeridas`, // Endpoint para buscar horas sugeridas
                method: 'GET',
                success: function (response) {
                    // Caso o ticket tenha categoria e as horas sejam retornadas
                    if (response.horas !== undefined && response.minutos !== undefined) {
                        // Preenche os campos de horas e minutos
                        $('#horas').val(response.horas);
                        $('#minutos').val(response.minutos);

                        // Oculta a mensagem de erro
                        $('#categoriaErrorAlert').addClass('d-none');

                        // Habilita o botão de finalização
                        $('#finalizarButton').prop('disabled', false);
                    } else if (response.error) {
                        // Exibe mensagem de erro se não houver categoria
                        $('#categoriaErrorAlert').removeClass('d-none');

                        // Limpa os campos de horas e minutos
                        $('#horas').val('');
                        $('#minutos').val('');

                        // Desabilita o botão de finalização
                        $('#finalizarButton').prop('disabled', true);
                    }
                },
                error: function (xhr) {
                    // Em caso de erro na requisição AJAX
                    console.error('Erro ao buscar horas sugeridas:', xhr);

                    // Exibe mensagem de erro e desabilita o botão
                    $('#categoriaErrorAlert').removeClass('d-none');
                    $('#finalizarButton').prop('disabled', true);
                }
            });
        });
    })(jQuery);
</script>




<script>
    $(document).ready(function () {
        $('#setor').on('change', function () {
            const setorId = $(this).val();

            // Limpa as categorias atuais
            $('#categoria').html('<option value="" disabled selected>Carregando...</option>');

            // Faz a requisição AJAX para carregar as categorias
            $.get(`/categorias/${setorId}`, function (categorias) {
                let options = '<option value="" disabled selected>Selecione uma categoria</option>';

                categorias.forEach(categoria => {
                    options += `<option value="${categoria.id}">${categoria.nome}</option>`;
                });

                // Atualiza o select de categorias
                $('#categoria').html(options);
            });
        });
    });
</script>

<script>
    $(document).ready(function () {
        // Exibir mensagens de sucesso ou erro usando Toastr
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

        // Rolar automaticamente as mensagens até o final
        const mensagensContainer = document.getElementById('mensagensContainer');
        if (mensagensContainer) {
            mensagensContainer.scrollTop = mensagensContainer.scrollHeight;
        }
    });

    // Submeter formulário de mensagens com status opcional
    function submitMessageForm(status = null) {
        document.getElementById('status').value = status;
        document.getElementById('messageForm').submit();
    }

    // Função para adicionar campos de anexos no formulário
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
