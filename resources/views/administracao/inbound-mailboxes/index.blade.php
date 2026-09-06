@extends('adminlte::page')

@section('title', config('app.name') . ' - Caixas de e-mail')

@section('content_header')
    <h1>Caixas de entrada de e-mail</h1>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('inbound-mailboxes.store') }}" class="row align-items-end">
                @csrf
                <div class="form-group col-md-5">
                    <label for="address">Endereço</label>
                    <input id="address" type="email" name="address" value="{{ old('address') }}" class="form-control" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="setor_id">Setor padrão</label>
                    <select id="setor_id" name="setor_id" class="form-control" required>
                        @foreach($setores as $setor)<option value="{{ $setor->id }}">{{ $setor->nome }}</option>@endforeach
                    </select>
                </div>
                <div class="form-group col-md-1">
                    <input type="hidden" name="active" value="0">
                    <label><input type="checkbox" name="active" value="1" checked> Ativa</label>
                </div>
                <div class="form-group col-md-2"><button class="btn btn-primary btn-block">Adicionar</button></div>
            </form>
        </div>
    </div>

    @foreach($mailboxes as $mailbox)
        <div class="card"><div class="card-body">
            <form method="POST" action="{{ route('inbound-mailboxes.update', $mailbox) }}" class="row align-items-end">
                @csrf @method('PUT')
                <div class="form-group col-md-5"><label>Endereço</label><input type="email" name="address" value="{{ $mailbox->address }}" class="form-control" required></div>
                <div class="form-group col-md-4"><label>Setor padrão</label><select name="setor_id" class="form-control" required>@foreach($setores as $setor)<option value="{{ $setor->id }}" {{ $mailbox->setor_id === $setor->id ? 'selected' : '' }}>{{ $setor->nome }}</option>@endforeach</select></div>
                <div class="form-group col-md-1"><input type="hidden" name="active" value="0"><label><input type="checkbox" name="active" value="1" {{ $mailbox->active ? 'checked' : '' }}> Ativa</label></div>
                <div class="form-group col-md-2"><button class="btn btn-success btn-block">Salvar</button></div>
            </form>
            <form method="POST" action="{{ route('inbound-mailboxes.destroy', $mailbox) }}" onsubmit="return confirm('Remover esta caixa?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Remover</button></form>
        </div></div>
    @endforeach
@endsection
