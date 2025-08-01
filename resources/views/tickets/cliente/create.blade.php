@extends('adminlte::page')

@section('title', config('app.name') . ' - Criar Ticket')

@section('content_header')
<p style="font-size: 1.2em;">
    Tickets <i class="fas fa-angle-right" style="font-size: 0.7em;"></i> Criar
</p>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Novo Ticket</h3>
    </div>

    <div class="card-body">
        <form action="{{ route('tickets.cliente.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Campo de Assunto -->
            <div class="form-group">
                <label for="assunto"><i class="fas fa-tag"></i> Assunto</label>
                <input type="text" name="assunto" class="form-control" required>
            </div>

            <!-- Campo de Descrição -->
            <div class="form-group">
                <label for="descricao"><i class="fas fa-align-left"></i> Descrição</label>
                <textarea name="descricao" class="form-control" rows="5" required></textarea>
            </div>

            <!-- Campo de Contato e Empresa (não editáveis) -->
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

            <!-- Campo de Setor -->
            <div class="form-group">
                <label for="setor_id"><i class="fas fa-sitemap"></i> Setor</label>
                <select name="setor_id" id="setor_id" class="form-control">
                    <option value="">Selecione um setor</option>
                    @foreach($setores as $setor)
                        <option value="{{ $setor->id }}">{{ $setor->nome }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Botão para anexos -->
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
                <a href="{{ route('tickets.cliente.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>
        </form>
    </div>
</div>

@endsection

@section('js')
<script>
    // Função para exibir ou ocultar os campos de anexos
    function mostrarAnexos() {
        document.getElementById('anexosFields').classList.toggle('d-none');
    }
</script>
@endsection
