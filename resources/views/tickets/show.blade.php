@extends('adminlte::page')

@section('title', config('app.name') . ' - Detalhes do Ticket')

@section('content_header')
<x-page-header title="Detalhes do ticket" :breadcrumbs="['Tickets', 'Detalhes']" />
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
                <p class="mb-1"><strong>Modificado:</strong> {{ $ticket->updated_at ? $ticket->updated_at->format('d/m/Y H:i') : '-' }}</p>
            </div>

            <div class="col-md-4">
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
            <a href="{{ $returnUrl ?? route('tickets.index') }}" class="btn btn-secondary btn-sm">
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
                <a href="{{ route('tickets.edit', ['ticket' => $ticket->id, 'return_to' => $returnUrl]) }}" class="btn btn-warning btn-sm mr-2">
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
        <div class="d-flex justify-content-between align-items-center">
            <h5 style="margin: 0;"><i class="fas fa-stream"></i> Linha do tempo</h5>
            <div class="d-flex align-items-center">
                <form action="{{ route('ticket-timeline-preferences.update') }}" method="POST" class="mr-2">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="conversations_only" value="{{ auth()->user()->timeline_conversations_only ? 0 : 1 }}">
                    <button class="btn btn-sm {{ auth()->user()->timeline_conversations_only ? 'btn-outline-primary' : 'btn-outline-secondary' }}" type="submit">
                        <i class="fas {{ auth()->user()->timeline_conversations_only ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                        {{ auth()->user()->timeline_conversations_only ? 'Exibir toda a atividade' : 'Ocultar sistema e alterações' }}
                    </button>
                </form>
                <form action="{{ route($isFollowing ? 'tickets.followers.destroy' : 'tickets.followers.store', $ticket) }}" method="POST">
                    @csrf
                    @if($isFollowing) @method('DELETE') @endif
                    <button class="btn btn-sm {{ $isFollowing ? 'btn-outline-secondary' : 'btn-outline-primary' }}" type="submit">
                        <i class="fas {{ $isFollowing ? 'fa-bell-slash' : 'fa-bell' }}"></i>
                        {{ $isFollowing ? 'Deixar de seguir' : 'Seguir ticket' }}
                    </button>
                </form>
            </div>
        </div>
        @if($ticket->seguidores->isNotEmpty())
            <div class="mt-2 small text-muted"><strong>Seguidores:</strong> {{ $ticket->seguidores->pluck('name')->join(', ') }}</div>
        @endif
    </div>

    <div class="card-body" id="mensagensContainer" style="max-height: 600px; overflow-y: auto; padding: 10px;">
        <div class="timeline">
            @forelse($timeline as $event)
                @php
                    $styles = [
                        'publica' => ['fa-comment', 'bg-blue', 'Resposta pública'],
                        'interna' => ['fa-lock', 'bg-warning', 'Nota interna'],
                        'sistema' => ['fa-cog', 'bg-gray', 'Evento do sistema'],
                        'alteracao' => ['fa-exchange-alt', 'bg-purple', 'Alteração'],
                        'criacao' => ['fa-plus', 'bg-success', 'Criação'],
                    ];
                    [$icon, $color, $label] = $styles[$event['type']] ?? $styles['sistema'];
                    $isPublicMessage = $event['type'] === 'publica';
                    $authorRole = $event['author_role'] ?? null;
                    if ($isPublicMessage && $authorRole === 'client') {
                        $icon = 'fa-user-circle';
                        $color = 'bg-purple';
                    } elseif ($isPublicMessage && $authorRole === 'staff') {
                        $icon = 'fa-user-circle';
                        $color = 'bg-blue';
                    }
                @endphp
                <div>
                    <i class="fas {{ $icon }} {{ $color }}"></i>
                    <div class="timeline-item {{ $event['type'] === 'interna' ? 'border border-warning' : '' }} {{ $isPublicMessage && $authorRole ? 'timeline-message-' . $authorRole : '' }}">
                        <span class="time"><i class="fas fa-clock"></i> {{ $event['created_at']->format('d/m/Y H:i') }}</span>
                        <h3 class="timeline-header">
                            <strong>{{ $event['actor'] }}</strong>
                            @if($isPublicMessage && !empty($event['author_role_label']))
                                ({{ $event['author_role_label'] }})
                            @endif
                            <span class="badge {{ $event['type'] === 'interna' ? 'badge-warning' : ($authorRole === 'client' ? 'bg-purple text-white' : ($authorRole === 'staff' ? 'badge-primary' : 'badge-light')) }} ml-1">{{ $label }}</span>
                        </h3>
                        <div class="timeline-body">
                            @if($event['type'] === 'alteracao')
                                @foreach($event['changes'] as $change)
                                    <div class="mb-1"><strong>{{ $change['label'] }}:</strong>
                                        <span class="text-muted">{{ $change['old'] }}</span>
                                        <i class="fas fa-long-arrow-alt-right mx-1"></i>
                                        <span>{{ $change['new'] }}</span>
                                    </div>
                                @endforeach
                            @else
                                {!! nl2br(e($event['description'] ?? '')) !!}
                                @if(!empty($event['mentions']) && $event['mentions']->isNotEmpty())
                                    <div class="mt-2 small"><i class="fas fa-at"></i> {{ $event['mentions']->pluck('name')->join(', ') }}</div>
                                @endif
                                @if(!empty($event['attachments']) && $event['attachments']->isNotEmpty())
                                    <hr><strong>Anexos:</strong>
                                    <ul class="mb-0">
                                        @foreach($event['attachments'] as $attachment)
                                            <li>
                                                @if(($attachment->disk ?? 'public') === 'local')
                                                    <a href="{{ route('tickets.internal-attachments.show', [$ticket, $attachment]) }}">{{ basename($attachment->file_path) }}</a>
                                                @else
                                                    <a href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank">{{ basename($attachment->file_path) }}</a>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-muted">Nenhum evento ainda.</p>
            @endforelse
        </div>
    </div>
    @if($timeline->hasPages())
        <div class="card-footer pb-0">{{ $timeline->appends(request()->except('timeline_page'))->links() }}</div>
    @endif

    @if($ticket->status !== 'fechado')
    <div class="card-footer" style="padding: 8px 15px;">
        <form action="{{ route('mensagens.store', $ticket->id) }}" method="POST" enctype="multipart/form-data" id="messageForm">
            @csrf
            <input type="hidden" name="return_to" value="{{ $returnUrl }}">
            <input type="hidden" name="status" id="status" value="">
            <input type="hidden" name="tipo" id="messageType" value="publica">
            <div id="mentionedUsers"></div>

            <div class="form-group" style="margin-bottom: 5px;">
                <label for="descricao" style="margin-bottom: 0.3rem;">Enviar nova mensagem:</label>
                <textarea name="descricao" id="descricao" class="form-control" rows="3" placeholder="Digite sua mensagem aqui..."></textarea>
                <div id="mentionSuggestions" class="list-group position-absolute d-none" style="z-index: 1050; max-height: 200px; overflow-y: auto;"></div>
                <small class="form-text text-muted">Use @ para mencionar analistas em notas internas.</small>
            </div>

            <div class="form-group">
                <x-attachment-uploader name="attachments[]" :max-size-mb="10" collapsible />
            </div>

            <div class="btn-group">
                <button type="button" class="btn btn-success btn-sm" onclick="submitMessageForm(null, 'publica')"><i class="fas fa-paper-plane"></i> Responder publicamente</button>
                <button type="button" class="btn btn-success btn-sm dropdown-toggle dropdown-toggle-split" data-toggle="dropdown"><span class="sr-only">Outras ações</span></button>
                <div class="dropdown-menu">
                    <button type="button" class="dropdown-item" onclick="submitMessageForm('pendente cliente', 'publica')">Responder e alterar para Pendente Cliente</button>
                    <button type="button" class="dropdown-item" onclick="submitMessageForm('pendente analista', 'publica')">Responder e alterar para Pendente Analista</button>
                </div>
            </div>
            <button type="button" class="btn btn-warning btn-sm ml-2" onclick="submitMessageForm(null, 'interna')"><i class="fas fa-lock"></i> Adicionar nota interna</button>
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
                <input type="hidden" name="return_to" value="{{ $returnUrl }}">
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
                <input type="hidden" name="return_to" value="{{ $returnUrl }}">
                <div class="modal-body">
                    <p>Selecione o setor, a categoria e o analista:</p>

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

                    <div class="form-group">
                        <label for="categoria-transfer">Categoria:</label>
                        <select name="categoria" id="categoria-transfer" class="form-control" required disabled>
                            <option value="">Selecione um setor primeiro</option>
                        </select>
                    </div>

                    <!-- Seleção de Analista -->
                    <div class="form-group">
                        <label for="analista-transfer">Analista:</label>
                        <select name="analista" id="analista-transfer" class="form-control" disabled>
                            <option value="">Selecione um setor primeiro</option>
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
                <input type="hidden" name="return_to" value="{{ $returnUrl }}">
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
                    <input type="hidden" name="return_to" value="{{ $returnUrl }}">
                    <button type="submit" class="btn btn-danger">Excluir</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('css')
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

        .timeline-item.timeline-message-staff {
            border-left: 3px solid #007bff;
        }

        .timeline-item.timeline-message-client {
            border-left: 3px solid #6f42c1;
        }
    </style>
@endsection
@section('js')
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
        const mensagensContainer = document.getElementById('mensagensContainer');
        if (mensagensContainer) {
            mensagensContainer.scrollTop = mensagensContainer.scrollHeight;
        }

        const analistasTransferencia = @json($analistas->map(fn ($analista) => ['id' => $analista->id, 'name' => $analista->name, 'setor_id' => $analista->setor_id])->values());
        const analistaTransferSelecionado = @json($ticket->atribuido_ao_analista_id);
        const categoriaTransferSelecionada = @json($ticket->categoria_id);
        const analistaTransfer = $('#analista-transfer');
        const categoriaTransfer = $('#categoria-transfer');

        function atualizarAnalistasTransferencia() {
            const setorId = $('#setor-transfer').val();
            analistaTransfer.empty();

            if (!setorId) {
                analistaTransfer.prop('disabled', true)
                    .append('<option value="">Selecione um setor primeiro</option>');
                return;
            }

            analistaTransfer.prop('disabled', false)
                .append('<option value="">Sem analista</option>');

            analistasTransferencia
                .filter(analista => String(analista.setor_id) === String(setorId))
                .forEach(analista => {
                    const selected = String(analista.id) === String(analistaTransferSelecionado) ? ' selected' : '';
                    analistaTransfer.append(`<option value="${analista.id}"${selected}>${analista.name}</option>`);
                });
        }

        function atualizarCategoriasTransferencia() {
            const setorId = $('#setor-transfer').val();
            categoriaTransfer.empty();

            if (!setorId) {
                categoriaTransfer.prop('disabled', true)
                    .append('<option value="">Selecione um setor primeiro</option>');
                return;
            }

            categoriaTransfer.prop('disabled', true)
                .append('<option value="">Carregando...</option>');

            $.get(`/categorias/${setorId}`, function (categorias) {
                categoriaTransfer.empty()
                    .append('<option value="">Selecione uma categoria</option>');

                categorias.forEach(categoria => {
                    const selected = String(categoria.id) === String(categoriaTransferSelecionada) ? ' selected' : '';
                    categoriaTransfer.append(`<option value="${categoria.id}"${selected}>${categoria.nome}</option>`);
                });
                categoriaTransfer.prop('disabled', false);
            }).fail(function () {
                categoriaTransfer.empty()
                    .append('<option value="">Erro ao carregar categorias</option>')
                    .prop('disabled', true);
            });
        }

        $('#setor-transfer').on('change', function () {
            atualizarAnalistasTransferencia();
            atualizarCategoriasTransferencia();
        });
        atualizarAnalistasTransferencia();
        atualizarCategoriasTransferencia();

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

        const textarea = document.getElementById('descricao');
        const suggestions = document.getElementById('mentionSuggestions');
        const selectedMentions = new Map();
        let mentionTimer = null;

        function renderMentionInputs() {
            const container = document.getElementById('mentionedUsers');
            container.innerHTML = '';
            selectedMentions.forEach(function (name, id) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'mentioned_user_ids[]';
                input.value = id;
                container.appendChild(input);
            });
        }

        if (textarea && suggestions) {
            textarea.addEventListener('input', function () {
                clearTimeout(mentionTimer);
                const beforeCursor = textarea.value.substring(0, textarea.selectionStart);
                const match = beforeCursor.match(/(?:^|\s)@([^@\n]*)$/);
                if (!match) {
                    suggestions.classList.add('d-none');
                    return;
                }
                mentionTimer = setTimeout(function () {
                    fetch(@json(route('tickets.mentionables.index', $ticket)) + '?q=' + encodeURIComponent(match[1].trim()), {
                        headers: { 'Accept': 'application/json' }
                    }).then(response => response.json()).then(function (payload) {
                        suggestions.innerHTML = '';
                        suggestions.style.width = textarea.offsetWidth + 'px';
                        payload.data.forEach(function (user) {
                            const option = document.createElement('button');
                            option.type = 'button';
                            option.className = 'list-group-item list-group-item-action py-2';
                            option.textContent = '@' + user.name;
                            option.addEventListener('click', function () {
                                const cursor = textarea.selectionStart;
                                const start = cursor - match[0].length + (match[0].charAt(0) === ' ' ? 1 : 0);
                                textarea.value = textarea.value.substring(0, start) + '@' + user.name + ' ' + textarea.value.substring(cursor);
                                selectedMentions.set(String(user.id), user.name);
                                renderMentionInputs();
                                suggestions.classList.add('d-none');
                                textarea.focus();
                            });
                            suggestions.appendChild(option);
                        });
                        suggestions.classList.toggle('d-none', payload.data.length === 0);
                    });
                }, 200);
            });
        }
    });

    // Submeter formulário de mensagens com status opcional
    function submitMessageForm(status = null, type = 'publica') {
        if (type === 'publica' && document.querySelectorAll('#mentionedUsers input').length) {
            toastr.warning('As @menções são permitidas somente em notas internas.');
            return;
        }
        document.getElementById('status').value = status;
        document.getElementById('messageType').value = type;
        document.getElementById('messageForm').submit();
    }

</script>
@endsection
