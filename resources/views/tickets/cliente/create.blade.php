@extends('adminlte::page')

@section('title', config('app.name') . ' - Criar Ticket')

@section('content_header')
<x-page-header title="Criar ticket" :breadcrumbs="['Tickets', 'Criar']" />
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Novo Ticket</h3>
    </div>

    <div class="card-body">
        <form action="{{ route('tickets.cliente.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="return_to" value="{{ $returnUrl }}">

            <div class="form-group">
                <label for="assunto"><i class="fas fa-tag"></i> Assunto</label>
                <input type="text" name="assunto" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="descricao"><i class="fas fa-align-left"></i> Descrição</label>
                <textarea id="descricao" name="descricao" class="form-control" rows="5" required></textarea>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="cliente_id"><i class="fas fa-user"></i> Contato</label>
                    <input type="text" class="form-control" value="{{ auth()->user()->name }}" disabled>
                    <input type="hidden" name="cliente_id" value="{{ auth()->user()->id }}">
                </div>

                <div class="form-group col-md-6">
                    <label for="empresa_id"><i class="fas fa-building"></i> Empresa</label>
                    <input type="text" class="form-control" value="{{ auth()->user()->empresa->nome ?? 'N/A' }}" disabled>
                    <input type="hidden" name="empresa_id" value="{{ auth()->user()->empresa_id }}">
                </div>
            </div>

            <div class="form-group">
                <label for="setor_id"><i class="fas fa-sitemap"></i> Setor</label>
                <select name="setor_id" id="setor_id" class="form-control" required>
                    <option value="">Selecione um setor</option>
                    @foreach($setores as $setor)
                        <option value="{{ $setor->id }}">{{ $setor->nome }}</option>
                    @endforeach
                </select>
            </div>
            
            {{-- NOVO: Campo de Serviço --}}
            <div class="form-group">
                <label for="servico_id"><i class="fas fa-concierge-bell"></i> Serviço:</label>
                <select name="servico_id" id="servico_id" class="form-control">
                    <option value="">Nenhum serviço específico</option>
                    @foreach($servicos as $servico)
                        <option value="{{ $servico->id }}">{{ $servico->nome }}</option>
                    @endforeach
                </select>
            </div>

            {{-- NOVO: Container para o questionário dinâmico --}}
            <div id="questionario-container">
                </div>


            <div class="form-group">
                <label><i class="fas fa-paperclip" aria-hidden="true"></i> Anexos</label>
                <x-attachment-uploader name="anexos[]" />
            </div>
            <x-form-actions :cancel-url="$returnUrl" submit-label="Criar Ticket" />
        </form>
    </div>
</div>

@endsection

@section('js')
<script>
    // NOVO: Lógica para carregar o questionário dinamicamente
    document.addEventListener('DOMContentLoaded', function () {
        const servicoSelect = document.getElementById('servico_id');
        const questionarioContainer = document.getElementById('questionario-container');

        servicoSelect.addEventListener('change', function () {
            const servicoId = this.value;
            // Limpa o container de perguntas anteriores
            questionarioContainer.innerHTML = '';

            if (servicoId) {
                // Faz a chamada AJAX para buscar o questionário
                fetch(`/servicos/${servicoId}/questionario`)
                    .then(response => response.json())
                    .then(perguntas => {
                        if (perguntas && perguntas.length > 0) {
                            
                            // Cria um cabeçalho para a seção
                            const header = document.createElement('h5');
                            header.className = 'mt-3';
                            header.innerText = '';
                            questionarioContainer.appendChild(header);

                            // Itera sobre as perguntas e cria os inputs
                            perguntas.forEach(pergunta => {
                                // Só cria o campo se a pergunta não for vazia
                                if (pergunta.trim() !== '') {
                                    const formGroup = document.createElement('div');
                                    formGroup.className = 'form-group';

                                    const label = document.createElement('label');
                                    label.innerText = pergunta;

                                    const input = document.createElement('input');
                                    input.type = 'text';
                                    input.name = 'questionario_respostas[]';
                                    input.className = 'form-control';
                                    input.required = true; // Torna a resposta obrigatória se a pergunta existe

                                    formGroup.appendChild(label);
                                    formGroup.appendChild(input);
                                    questionarioContainer.appendChild(formGroup);
                                }
                            });
                        }
                    })
                    .catch(error => console.error('Erro ao buscar o questionário:', error));
            }
        });
    });
</script>
@endsection
