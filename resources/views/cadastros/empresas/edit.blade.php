@extends('adminlte::page')

@section('title', config('app.name') . ' - Editar Empresa')

@section('content_header')
    <p style="font-size: 1.2em;">
        Cadastros <i class="fas fa-angle-right" style="font-size: 0.7em;"></i> Empresas <i class="fas fa-angle-right" style="font-size: 0.7em;"></i> Editar
    </p>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Editar Empresa: {{ $empresa->nome }}</h3>
        </div>

        <div class="card-body">
            <form action="{{ route('empresas.update', $empresa->id) }}" method="POST" id="empresaForm">
                @csrf
                @method('PUT')

                {{-- Nome Fantasia --}}
                <div class="form-group">
                    <label for="nome"><i class="fas fa-building"></i> Nome Fantasia</label>
                    <input type="text" name="nome" id="nome" class="form-control @error('nome') is-invalid @enderror" value="{{ old('nome', $empresa->nome) }}" required>
                    @error('nome')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                </div>

                {{-- Razão Social --}}
                <div class="form-group">
                    <label for="razao_social"><i class="fas fa-briefcase"></i> Razão Social</label>
                    <input type="text" name="razao_social" id="razao_social" class="form-control @error('razao_social') is-invalid @enderror" value="{{ old('razao_social', $empresa->razao_social) }}" @role('analista') disabled @endrole>
                    @error('razao_social')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                </div>

                {{-- CNPJ --}}
                <div class="form-group">
                    <label for="cnpj"><i class="fas fa-id-card"></i> CNPJ</label>
                    <input type="text" name="cnpj" id="cnpj" class="form-control @error('cnpj') is-invalid @enderror" value="{{ old('cnpj', $empresa->cnpj) }}" required @role('analista') disabled @endrole>
                    @error('cnpj')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                </div>

                {{-- Endereço --}}
                <div class="form-group">
                    <label for="endereco"><i class="fas fa-map-marker-alt"></i> Endereço</label>
                    <input type="text" name="endereco" id="endereco" class="form-control @error('endereco') is-invalid @enderror" value="{{ old('endereco', $empresa->endereco) }}" required>
                    @error('endereco')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                </div>

                {{-- Bairro --}}
                <div class="form-group">
                    <label for="bairro"><i class="fas fa-map-pin"></i> Bairro</label>
                    <input type="text" name="bairro" id="bairro" class="form-control @error('bairro') is-invalid @enderror" value="{{ old('bairro', $empresa->bairro) }}" required>
                    @error('bairro')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                </div>

                {{-- Cidade --}}
                <div class="form-group">
                    <label for="cidade"><i class="fas fa-city"></i> Cidade</label>
                    <input type="text" name="cidade" id="cidade" class="form-control @error('cidade') is-invalid @enderror" value="{{ old('cidade', $empresa->cidade) }}" required @role('analista') disabled @endrole>
                    @error('cidade')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                </div>

                {{-- Estado --}}
                <div class="form-group">
                    <label for="estado"><i class="fas fa-flag"></i> Estado</label>
                    <select name="estado" id="estado" class="form-control @error('estado') is-invalid @enderror" required @role('analista') disabled @endrole>
                        <option value="">Selecione um estado</option>
                        <option value="AC" {{ old('estado', $empresa->estado) == 'AC' ? 'selected' : '' }}>Acre</option>
                        <option value="AL" {{ old('estado', $empresa->estado) == 'AL' ? 'selected' : '' }}>Alagoas</option>
                        <option value="AP" {{ old('estado', $empresa->estado) == 'AP' ? 'selected' : '' }}>Amapá</option>
                        <option value="AM" {{ old('estado', $empresa->estado) == 'AM' ? 'selected' : '' }}>Amazonas</option>
                        <option value="BA" {{ old('estado', $empresa->estado) == 'BA' ? 'selected' : '' }}>Bahia</option>
                        <option value="CE" {{ old('estado', $empresa->estado) == 'CE' ? 'selected' : '' }}>Ceará</option>
                        <option value="DF" {{ old('estado', $empresa->estado) == 'DF' ? 'selected' : '' }}>Distrito Federal</option>
                        <option value="ES" {{ old('estado', $empresa->estado) == 'ES' ? 'selected' : '' }}>Espírito Santo</option>
                        <option value="GO" {{ old('estado', $empresa->estado) == 'GO' ? 'selected' : '' }}>Goiás</option>
                        <option value="MA" {{ old('estado', $empresa->estado) == 'MA' ? 'selected' : '' }}>Maranhão</option>
                        <option value="MT" {{ old('estado', $empresa->estado) == 'MT' ? 'selected' : '' }}>Mato Grosso</option>
                        <option value="MS" {{ old('estado', $empresa->estado) == 'MS' ? 'selected' : '' }}>Mato Grosso do Sul</option>
                        <option value="MG" {{ old('estado', $empresa->estado) == 'MG' ? 'selected' : '' }}>Minas Gerais</option>
                        <option value="PA" {{ old('estado', $empresa->estado) == 'PA' ? 'selected' : '' }}>Pará</option>
                        <option value="PB" {{ old('estado', $empresa->estado) == 'PB' ? 'selected' : '' }}>Paraíba</option>
                        <option value="PR" {{ old('estado', $empresa->estado) == 'PR' ? 'selected' : '' }}>Paraná</option>
                        <option value="PE" {{ old('estado', $empresa->estado) == 'PE' ? 'selected' : '' }}>Pernambuco</option>
                        <option value="PI" {{ old('estado', $empresa->estado) == 'PI' ? 'selected' : '' }}>Piauí</option>
                        <option value="RJ" {{ old('estado', $empresa->estado) == 'RJ' ? 'selected' : '' }}>Rio de Janeiro</option>
                        <option value="RN" {{ old('estado', $empresa->estado) == 'RN' ? 'selected' : '' }}>Rio Grande do Norte</option>
                        <option value="RS" {{ old('estado', $empresa->estado) == 'RS' ? 'selected' : '' }}>Rio Grande do Sul</option>
                        <option value="RO" {{ old('estado', $empresa->estado) == 'RO' ? 'selected' : '' }}>Rondônia</option>
                        <option value="RR" {{ old('estado', $empresa->estado) == 'RR' ? 'selected' : '' }}>Roraima</option>
                        <option value="SC" {{ old('estado', $empresa->estado) == 'SC' ? 'selected' : '' }}>Santa Catarina</option>
                        <option value="SP" {{ old('estado', $empresa->estado) == 'SP' ? 'selected' : '' }}>São Paulo</option>
                        <option value="SE" {{ old('estado', $empresa->estado) == 'SE' ? 'selected' : '' }}>Sergipe</option>
                        <option value="TO" {{ old('estado', $empresa->estado) == 'TO' ? 'selected' : '' }}>Tocantins</option>
                    </select>
                    @error('estado')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                </div>

                {{-- Horas Contratadas --}}
                <div class="form-group">
                    <label for="horas_contratadas"><i class="fas fa-clock"></i> Horas Contratadas</label>
                    <input type="number" name="horas_contratadas" id="horas_contratadas" class="form-control @error('horas_contratadas') is-invalid @enderror" value="{{ old('horas_contratadas', $empresa->horas_contratadas) }}" required @role('analista') disabled @endrole>
                    @error('horas_contratadas')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                </div>

                {{-- NOVO CAMPO DE CONTRATOS --}}
                <div class="form-group">
                    <label for="contratos"><i class="fas fa-file-contract"></i> Contratos Associados</label>
                    <select name="contratos[]" id="contratos" class="form-control select2" multiple="multiple">
                        @foreach($contratos as $contrato)
                            <option value="{{ $contrato->id }}"
                                {{-- Verifica se o contrato já está associado à empresa para pré-selecionar --}}
                                @if(in_array($contrato->id, old('contratos', $empresa->contratos->pluck('id')->toArray())))
                                    selected
                                @endif
                            >
                                {{ $contrato->nome }} - R$ {{ number_format($contrato->valor, 2, ',', '.') }} - {{ $contrato->horas_contratadas }} Horas
                            </option>
                        @endforeach
                    </select>
                    @error('contratos')
                        <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="form-group d-flex justify-content-start">
                    <button type="submit" class="btn btn-success mr-2"><i class="fas fa-save"></i> Atualizar Empresa</button>
                    <a href="{{ route('empresas.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Voltar</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Lista de Clientes Vinculados --}}
    <div class="card mt-4">
        <div class="card-header d-flex flex-column flex-md-row align-items-md-center">
            <h3 class="card-title mb-2 mb-md-0">Contatos da Empresa</h3>
            <a href="{{ route('clientes.create', ['empresa_id' => $empresa->id]) }}" class="btn btn-success ml-md-auto">
                <i class="fas fa-plus-circle"></i> Adicionar Contato
            </a>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="d-none d-md-table-cell">ID</th>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clientes as $cliente)
                        <tr>
                            <td class="d-none d-md-table-cell">{{ $cliente->id }}</td>
                            <td>{{ $cliente->name }}</td>
                            <td>{{ $cliente->email }}</td>
                            <td>
                                <span class="badge badge-{{ $cliente->status ? 'success' : 'danger' }}">
                                    {{ $cliente->status ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>
                            <td class="text-center">
                                @role('supervisor|administrador')
                                    <a href="{{ route('clientes.edit', $cliente->id) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                @endrole
                                <form action="{{ route('clientes.deactivate', $cliente->id) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    @role('supervisor|administrador')
                                        <button type="submit" class="btn btn-sm {{ $cliente->status ? 'btn-danger' : 'btn-success' }}">
                                            <i class="fas {{ $cliente->status ? 'fa-ban' : 'fa-check' }}"></i>
                                            {{ $cliente->status ? 'Desativar' : 'Ativar' }}
                                        </button>
                                    @endrole
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Nenhum contato encontrado para esta empresa.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .form-group label { font-weight: 600; }
        .select2-container .select2-selection--multiple {
            height: auto !important;
            min-height: calc(2.25rem + 2px);
            padding: .375rem .75rem;
            border: 1px solid #ced4da;
        }

        /* --- COLOQUE O NOVO CÓDIGO AQUI --- */
        /* Altera a cor de fundo do item selecionado */
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #d1e7ff; /* Azul claro suave (cor de alerta 'info' do Bootstrap) */
            border-color: #b6d4fe;     /* Borda azul um pouco mais escura */
            color: #0c5460;            /* Cor do texto para bom contraste */
        }
    </style>
@endsection

@push('js')
    <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Toastr para notificações
            @if(session('success'))
                toastr.success('{{ session('success') }}', 'Sucesso', { closeButton: true, progressBar: true });
            @endif
            @if(session('error'))
                toastr.error('{{ session('error') }}', 'Erro', { closeButton: true, progressBar: true });
            @endif

            // Inicializa o Select2
            $('#contratos').select2({
                placeholder: "Selecione um ou mais contratos",
                allowClear: true,
                width: '100%' // <-- Adicione esta linha
            });

            // Máscara de CNPJ
            $('#cnpj').on('input', function() {
                let cnpj = $(this).val().replace(/\D/g, '').slice(0, 14);
                if (cnpj.length > 11) {
                    cnpj = cnpj.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, "$1.$2.$3/$4-$5");
                } else {
                    cnpj = cnpj.replace(/^(\d{3})(\d{3})(\d{3})(\d{2})/, "$1.$2.$3-$4");
                }
                $(this).val(cnpj);
            }).trigger('input'); // Aciona a máscara ao carregar a página

            $('#empresaForm').on('submit', function() {
                // Habilita campos desabilitados para que seus valores sejam enviados
                $('#empresaForm :disabled').prop('disabled', false);
                // Remove a máscara do CNPJ antes de enviar
                const cnpjSemFormatacao = $('#cnpj').val().replace(/\D/g, '');
                $('#cnpj').val(cnpjSemFormatacao);
            });
        });
    </script>
@endpush