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
            
            <div class="form-group">
                <label><i class="fas fa-paperclip" aria-hidden="true"></i> Anexos</label>
                <x-attachment-uploader name="anexos[]" />
            </div>
            <x-form-actions :cancel-url="$returnUrl" submit-label="Criar Ticket" />
        </form>
    </div>
</div>

@endsection
