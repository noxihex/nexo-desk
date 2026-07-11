@extends('adminlte::page')

@section('title', config('app.name') . ' - Editar Ticket')

@section('content_header')
    <p style="font-size: 1.2em;">
        Tickets <i class="fas fa-angle-right" style="font-size: 0.7em;"></i> Editar
    </p>
@endsection





@section('content')


@role('supervisor|administrador')

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Editar Ticket: #{{ $ticket->id }}</h3>
    </div>

    <div class="card-body">
        <form action="{{ route('tickets.update', $ticket->id) }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="return_to" value="{{ $returnUrl }}">

            <!-- Campo Assunto -->
            <div class="form-group">
                <label for="assunto"><i class="fas fa-tag"></i> Assunto</label>
                <input type="text" name="assunto" class="form-control" value="{{ $ticket->assunto }}" required>
            </div>

            <!-- Campo Descrição -->
            <div class="form-group">
                <label for="descricao"><i class="fas fa-align-left"></i> Descrição</label>
                <textarea name="descricao" class="form-control" rows="5" required>{{ $ticket->descricao }}</textarea>
            </div>

            <!-- Campo Cliente e Empresa (mesma linha) -->
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="cliente_id"><i class="fas fa-user"></i> Contato</label>
                    <select name="cliente_id" id="cliente_id" class="form-control">
                        <option value="">Selecione um contato</option>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}" {{ $ticket->cliente_id == $cliente->id ? 'selected' : '' }}>
                                {{ $cliente->name }} ({{ $cliente->empresa->nome ?? 'Sem Empresa' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group col-md-6">
                    <label for="empresa_id"><i class="fas fa-building"></i> Empresa</label>
                    <select name="empresa_id" id="empresa_id" class="form-control">
                        <option value="">Selecione uma empresa</option>
                        @foreach($empresas as $empresa)
                            <option value="{{ $empresa->id }}" {{ $ticket->empresa_id == $empresa->id ? 'selected' : '' }}>
                                {{ $empresa->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Campo Categoria -->
            <div class="form-row">
                <div class="form-group col-md-12">
                    <label for="categoria_id"><i class="fas fa-list"></i> Categoria</label>
                    <select name="categoria_id" id="categoria_id" class="form-control" required>
                        <option value="">Selecione uma categoria</option>
                        @foreach($categorias as $categoria)
                            <option value="{{ $categoria->id }}" {{ $ticket->categoria_id == $categoria->id ? 'selected' : '' }}>
                                {{ $categoria->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Campo Setor e Atribuído ao Analista -->
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="setor_id"><i class="fas fa-sitemap"></i> Setor</label>
                    <select name="setor_id" id="setor_id" class="form-control">
                        <option value="">Selecione um setor</option>
                        @foreach($setores as $setor)
                            <option value="{{ $setor->id }}" {{ $ticket->setor_id == $setor->id ? 'selected' : '' }}>
                                {{ $setor->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group col-md-6">
                    <label for="atribuido_ao_analista_id"><i class="fas fa-user-tie"></i> Atribuído ao Analista</label>
                    <select name="atribuido_ao_analista_id" id="atribuido_ao_analista_id" class="form-control" disabled>
                        <option value="">Selecione um setor primeiro</option>
                    </select>
                </div>
            </div>

            <!-- Campo Status -->
            <div class="form-group">
                <label for="status"><i class="fas fa-tasks"></i> Status</label>
                <select name="status" class="form-control">
                    <option value="aberto" {{ $ticket->status == 'aberto' ? 'selected' : '' }}>Aberto</option>
                    <option value="pendente cliente" {{ $ticket->status == 'pendente cliente' ? 'selected' : '' }}>Pendente Cliente</option>
                    <option value="pendente analista" {{ $ticket->status == 'pendente analista' ? 'selected' : '' }}>Pendente Analista</option>
                    <option value="fechado" {{ $ticket->status == 'fechado' ? 'selected' : '' }}>Fechado</option>
                </select>
            </div>

            <!-- Botões de Ação -->
            <div class="form-group d-flex justify-content-start">
                <button type="submit" class="btn btn-success mr-2">
                    <i class="fas fa-save"></i> Salvar Alterações
                </button>
                <a href="{{ $returnUrl }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>
        </form>
    </div>
</div>
@endrole
@endsection

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

<script>
    $(document).ready(function() {
        const analistas = @json($analistas->map(fn ($analista) => ['id' => $analista->id, 'name' => $analista->name, 'setor_id' => $analista->setor_id])->values());
        const analistaSelect = $('#atribuido_ao_analista_id');
        const analistaInicial = @json(old('atribuido_ao_analista_id', $ticket->atribuido_ao_analista_id));

        function atualizarAnalistas() {
            const setorId = $('#setor_id').val();
            analistaSelect.empty();

            if (!setorId) {
                analistaSelect.prop('disabled', true)
                    .append('<option value="">Selecione um setor primeiro</option>');
                return;
            }

            analistaSelect.prop('disabled', false)
                .append('<option value="">Sem analista</option>');

            analistas
                .filter(analista => String(analista.setor_id) === String(setorId))
                .forEach(analista => {
                    const selected = String(analista.id) === String(analistaInicial) ? ' selected' : '';
                    analistaSelect.append(`<option value="${analista.id}"${selected}>${analista.name}</option>`);
                });
        }

        // Configura a mensagem de "Nenhum resultado encontrado" para Select2
        $.fn.select2.defaults.set("language", {
            noResults: function() {
                return "Nenhum resultado encontrado";
            }
        });

        // Inicializa os campos Select2
        $('#cliente_id, #empresa_id, #categoria_id').select2({
            placeholder: "Selecione uma opção",
            allowClear: true,
            width: '100%'
        });

        // Preenchimento automático do campo Empresa baseado no Cliente selecionado
        $('#cliente_id').on('change', function() {
            const clienteId = $(this).val();
            if (clienteId) {
                $.ajax({
                    url: `/clientes/${clienteId}/empresa`,
                    method: 'GET',
                    success: function(response) {
                        $('#empresa_id').val(response.empresa_id || '').trigger('change');
                    },
                    error: function() {
                        console.error('Erro ao buscar a empresa do contato.');
                    }
                });
            } else {
                $('#empresa_id').val('').trigger('change');
            }
        });

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

                        // Se o ticket já possui uma categoria selecionada, mantém a seleção
                        const categoriaId = '{{ $ticket->categoria_id ?? '' }}'; // ID da categoria do ticket
                        if (categoriaId) {
                            categoriaSelect.val(categoriaId).trigger('change');
                        }
                    },
                    error: function() {
                        console.error('Erro ao carregar as categorias do setor.');
                    }
                });
            }

            atualizarAnalistas();
        });

        // Inicializa o evento 'change' manualmente se o setor estiver preenchido
        const setorId = $('#setor_id').val(); // Obtém o valor inicial do setor
        if (setorId) {
            $('#setor_id').trigger('change'); // Dispara o evento 'change' para carregar as categorias
        }
    });
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
    /* Ajustes visuais para o Select2 */
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
</style>
@endsection


@section('css')
@endsection
