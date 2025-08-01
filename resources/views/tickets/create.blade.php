@extends('adminlte::page')

@section('title', 'BTXDesk - Criar Ticket')

@section('content_header')
    <p style="font-size: 1.2em;">
        Tickets <i class="fas fa-angle-right" style="font-size: 0.7em;"></i> Criar
    </p>
@endsection


@include('layouts.notificahtml')

@section('content')



<div class="card">
    <div class="card-header">
        <h3 class="card-title">Novo Ticket</h3>
    </div>

    <div class="card-body">
        <form action="{{ route('tickets.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Campo de Assunto -->
            <div class="form-group">
                <label for="assunto"><i class="fas fa-tag"></i> Assunto</label>
                <input type="text" name="assunto" class="form-control" required>
            </div>

            <!-- Campo de Descrição (largura completa) -->
            <div class="form-group">
                <label for="descricao"><i class="fas fa-align-left"></i> Descrição</label>
                <textarea name="descricao" class="form-control" rows="5" required></textarea>
            </div>

            <!-- Campo de Cliente e Empresa (mesma linha) -->
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="cliente_id"><i class="fas fa-user"></i> Contato</label>
                    <select name="cliente_id" id="cliente_id" class="form-control select2" required>
                        <option value="">Selecione um contato</option>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}" data-empresa="{{ $cliente->empresa->id ?? '' }}">
                                {{ $cliente->name }} ({{ $cliente->empresa->nome ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group col-md-6">
                    <label for="empresa_id"><i class="fas fa-building"></i> Empresa</label>
                    <select name="empresa_id" id="empresa_id" class="form-control select2" required>
                        <option value="">Selecione uma empresa</option>
                        @foreach($empresas as $empresa)
                            <option value="{{ $empresa->id }}">{{ $empresa->nome }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

           <!-- Campo de Grupo e Setor (mesma linha) -->
<div class="form-row">
    <div class="form-group col-md-6">
        <label for="grupo_id"><i class="fas fa-users"></i> Grupo</label>
        <select name="grupo_id" class="form-control">
            <option value="">Selecione um grupo</option>
            @foreach($grupos as $grupo)
                <option value="{{ $grupo->id }}" {{ Auth::user()->grupo_id == $grupo->id ? 'selected' : '' }}>
                    {{ $grupo->nome }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="form-group col-md-6">
        <label for="setor_id"><i class="fas fa-sitemap"></i> Setor</label>
        <select name="setor_id" id="setor_id" class="form-control">
            <option value="">Selecione um setor</option>
            @foreach($setores as $setor)
                <option value="{{ $setor->id }}" {{ Auth::user()->setor_id == $setor->id ? 'selected' : '' }}>
                    {{ $setor->nome }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<!-- Campo de Categoria e Atribuído ao Analista (mesma linha, abaixo de Grupo e Setor) -->
<div class="form-row">
    <div class="form-group col-md-6">
        <label for="categoria_id"><i class="fas fa-list"></i> Categoria</label>
        <select name="categoria_id" id="categoria_id" class="form-control" required>
            <option value="">Selecione uma categoria</option>
            <!-- As categorias serão preenchidas dinamicamente -->
        </select>
    </div>
    <div class="form-group col-md-6">
        <label for="atribuido_ao_analista_id"><i class="fas fa-user-tie"></i> Atribuído ao Analista</label>
        <select name="atribuido_ao_analista_id" class="form-control">
            <option value="">Sem analista</option> <!-- Opção para deixar o campo nulo -->
            @foreach($analistas as $analista)
                <option value="{{ $analista->id }}" {{ Auth::id() == $analista->id ? 'selected' : '' }}>
                    {{ $analista->name }}
                </option>
            @endforeach
        </select>
    </div>
</div>


            <!-- Botão para mostrar os campos de anexos -->
            <div class="form-group">
                <button type="button" class="btn btn-info" onclick="mostrarAnexos()">
                    <i class="fas fa-paperclip"></i> Enviar Anexos
                </button>
                <small class="form-text text-muted">Você pode adicionar até 5 anexos, máximo 5MB cada.</small>
            </div>

            <!-- Campos de Anexos (inicialmente ocultos) -->
            <div class="form-group d-none" id="anexosFields">
                <label for="anexos"><i class="fas fa-paperclip"></i> Selecionar Anexos:</label>
                <div class="d-flex">
                    @for ($i = 1; $i <= 5; $i++)
                        <input type="file" name="anexos[]" class="form-control-file mr-2" style="width: 20%;" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx,.txt,.mp4,.kmz,.kml,.zip">
                    @endfor
                </div>
            </div>

            <!-- Botões de Ação -->
            <div class="form-group d-flex justify-content-start mt-3">
                <button type="submit" class="btn btn-success mr-2">
                    <i class="fas fa-save"></i> Criar Ticket
                </button>
                <a href="{{ route('tickets.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>
        </form>
    </div>
</div>

@endsection

@section('js')
@include('layouts.notificajs')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<script>
$(document).ready(function() {
    // Preenchimento dinâmico de categorias ao selecionar um setor
    $('#setor_id').on('change', function() {
        const setorId = $(this).val(); // Obtém o ID do setor selecionado
        const categoriaSelect = $('#categoria_id'); // Campo de categorias

        // Limpa as categorias atuais
        categoriaSelect.empty().append('<option value="">Selecione uma categoria</option>');

        if (setorId) {
            // Faz uma requisição AJAX para buscar categorias relacionadas ao setor
            $.ajax({
                url: `/setores/${setorId}/categorias`, // Certifique-se de que a URL está correta
                type: 'GET',
                success: function(categorias) {
                    // Popula o select de categorias com os dados recebidos
                    $.each(categorias, function(index, categoria) {
                        categoriaSelect.append(`<option value="${categoria.id}">${categoria.nome}</option>`);
                    });
                },
                error: function() {
                    alert('Erro ao carregar as categorias do setor.');
                }
            });
        }
    });

    // Inicializa o evento 'change' manualmente se o setor estiver preenchido
    const setorId = $('#setor_id').val(); // Obtém o valor inicial do setor
    if (setorId) {
        $('#setor_id').trigger('change'); // Dispara o evento 'change' para carregar as categorias
    }
});

</script>
<script>
    $(document).ready(function() {
        // Configura a mensagem de "Nenhum resultado encontrado" para Select2
        $.fn.select2.defaults.set("language", {
            noResults: function() {
                return "Nenhum resultado encontrado";
            }
        });

        // Inicializa o Select2 para os campos Cliente, Empresa e Categoria com placeholders
        $('#cliente_id').select2({
            placeholder: "Selecione um contato",
            allowClear: true,
            width: '100%' // Ajusta a largura para 100%
        });

        $('#empresa_id').select2({
            placeholder: "Selecione uma empresa",
            allowClear: true,
            width: '100%' // Ajusta a largura para 100%
        });

        $('#categoria_id').select2({
            placeholder: "Selecione uma categoria",
            allowClear: true,
            width: '100%' // Ajusta a largura para 100%
        });

        // Preenchimento automático do campo Empresa baseado no Cliente selecionado
        $('#cliente_id').on('change', function() {
            const clienteId = $(this).val(); // Obtém o ID do cliente selecionado
            if (clienteId) {
                fetch(`/clientes/${clienteId}/empresa`) // Faz a requisição para buscar a empresa associada
                    .then(response => response.json())
                    .then(data => {
                        const empresaSelect = $('#empresa_id');
                        if (data.empresa_id) {
                            empresaSelect.val(data.empresa_id).trigger('change'); // Seleciona a empresa associada
                        } else {
                            empresaSelect.val('').trigger('change'); // Limpa o campo se não houver empresa associada
                        }
                    })
                    .catch(error => console.error('Erro ao buscar empresa:', error));
            } else {
                $('#empresa_id').val('').trigger('change'); // Limpa o campo se o cliente não for selecionado
            }
        });
    });

    // Função para exibir ou ocultar os campos de anexos
    function mostrarAnexos() {
        document.getElementById('anexosFields').classList.toggle('d-none');
    }
</script>


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
    /* Customiza o estilo do Select2 para combinar melhor com o layout */
    .select2-container .select2-selection--single {
        height: 38px;
        padding-top: 5px;
        padding-bottom: 5px;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #495057;
        line-height: 28px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }
    .select2-selection__rendered {
        font-size: 0.875rem;
    }
</style>
@endsection


@section('css')
@include('layouts.notificacss')
@endsection
