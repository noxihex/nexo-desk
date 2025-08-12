@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="form-group">
    <label for="nome"><i class="fas fa-file-signature"></i> Nome do Contrato</label>
    <input type="text" name="nome" id="nome" class="form-control @error('nome') is-invalid @enderror" value="{{ old('nome', $contrato->nome ?? '') }}" placeholder="Digite o nome do contrato" required>
    @error('nome')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
    @enderror
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="valor_display"><i class="fas fa-dollar-sign"></i> Valor (R$)</label>
            {{-- Campo visível para o usuário com a máscara --}}
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text">R$</span>
                </div>
                <input type="text" id="valor_display" class="form-control @error('valor') is-invalid @enderror" value="{{ old('valor', isset($contrato) ? number_format($contrato->valor, 0, '', '.') : '') }}" placeholder="0" required>
                <div class="input-group-append">
                    <span class="input-group-text">,00</span>
                </div>
            </div>

            {{-- Campo oculto que será enviado para o backend --}}
            <input type="hidden" name="valor" id="valor" value="{{ old('valor', $contrato->valor ?? '') }}">

            @error('valor')
                <div class="invalid-feedback d-block"><strong>{{ $message }}</strong></div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="horas_contratadas"><i class="far fa-clock"></i> Horas Contratadas</label>
            <input type="number" name="horas_contratadas" id="horas_contratadas" class="form-control @error('horas_contratadas') is-invalid @enderror" min="0" value="{{ old('horas_contratadas', $contrato->horas_contratadas ?? '') }}" placeholder="Ex: 10" required>
            @error('horas_contratadas')
                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>
    </div>
</div>

<div class="form-group">
    <label for="descricao"><i class="fas fa-align-left"></i> Descrição</label>
    <textarea name="descricao" id="descricao" class="form-control @error('descricao') is-invalid @enderror" rows="4" placeholder="Digite uma descrição para o contrato (opcional)">{{ old('descricao', $contrato->descricao ?? '') }}</textarea>
    @error('descricao')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
    @enderror
</div>