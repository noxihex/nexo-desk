@extends('adminlte::page')

@section('title', config('app.name') . ' - Criar Empresa')

@section('content_header')
    <p style="font-size: 1.2em;">
        Cadastros <i class="fas fa-angle-right" style="font-size: 0.7em;"></i> Empresas <i class="fas fa-angle-right" style="font-size: 0.7em;"></i> Criar
    </p>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Preencha os dados para criar uma nova empresa</h3>
        </div>

        <div class="card-body">
            <form action="{{ route('empresas.store') }}" method="POST" id="empresaForm">
                @csrf

                {{-- Nome Fantasia --}}
                <div class="form-group">
                    <label for="nome"><i class="fas fa-building"></i> Nome Fantasia</label>
                    <input type="text" name="nome" id="nome" class="form-control @error('nome') is-invalid @enderror" value="{{ old('nome') }}" required>
                    @error('nome')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                </div>

                {{-- Razão Social --}}
                <div class="form-group">
                    <label for="razao_social"><i class="fas fa-briefcase"></i> Razão Social</label>
                    <input type="text" name="razao_social" id="razao_social" class="form-control @error('razao_social') is-invalid @enderror" value="{{ old('razao_social') }}">
                    @error('razao_social')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                </div>

                {{-- CNPJ --}}
                <div class="form-group">
                    <label for="cnpj"><i class="fas fa-id-card"></i> CNPJ</label>
                    <input type="text" name="cnpj" id="cnpj" class="form-control @error('cnpj') is-invalid @enderror" value="{{ old('cnpj') }}" required>
                    @error('cnpj')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                </div>

                {{-- Endereço --}}
                <div class="form-group">
                    <label for="endereco"><i class="fas fa-map-marker-alt"></i> Endereço</label>
                    <input type="text" name="endereco" id="endereco" class="form-control @error('endereco') is-invalid @enderror" value="{{ old('endereco') }}" required>
                    @error('endereco')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                </div>

                {{-- Bairro --}}
                <div class="form-group">
                    <label for="bairro"><i class="fas fa-map-pin"></i> Bairro</label>
                    <input type="text" name="bairro" id="bairro" class="form-control @error('bairro') is-invalid @enderror" value="{{ old('bairro') }}" required>
                    @error('bairro')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                </div>

                {{-- Cidade --}}
                <div class="form-group">
                    <label for="cidade"><i class="fas fa-city"></i> Cidade</label>
                    <input type="text" name="cidade" id="cidade" class="form-control @error('cidade') is-invalid @enderror" value="{{ old('cidade') }}" required>
                    @error('cidade')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                </div>

                {{-- Estado --}}
                <div class="form-group">
                    <label for="estado"><i class="fas fa-flag"></i> Estado</label>
                    <select name="estado" id="estado" class="form-control @error('estado') is-invalid @enderror" required>
                        <option value="">Selecione um estado</option>
                        <option value="AC" {{ old('estado') == 'AC' ? 'selected' : '' }}>Acre</option>
                        <option value="AL" {{ old('estado') == 'AL' ? 'selected' : '' }}>Alagoas</option>
                        <option value="AP" {{ old('estado') == 'AP' ? 'selected' : '' }}>Amapá</option>
                        <option value="AM" {{ old('estado') == 'AM' ? 'selected' : '' }}>Amazonas</option>
                        <option value="BA" {{ old('estado') == 'BA' ? 'selected' : '' }}>Bahia</option>
                        <option value="CE" {{ old('estado') == 'CE' ? 'selected' : '' }}>Ceará</option>
                        <option value="DF" {{ old('estado') == 'DF' ? 'selected' : '' }}>Distrito Federal</option>
                        <option value="ES" {{ old('estado') == 'ES' ? 'selected' : '' }}>Espírito Santo</option>
                        <option value="GO" {{ old('estado') == 'GO' ? 'selected' : '' }}>Goiás</option>
                        <option value="MA" {{ old('estado') == 'MA' ? 'selected' : '' }}>Maranhão</option>
                        <option value="MT" {{ old('estado') == 'MT' ? 'selected' : '' }}>Mato Grosso</option>
                        <option value="MS" {{ old('estado') == 'MS' ? 'selected' : '' }}>Mato Grosso do Sul</option>
                        <option value="MG" {{ old('estado') == 'MG' ? 'selected' : '' }}>Minas Gerais</option>
                        <option value="PA" {{ old('estado') == 'PA' ? 'selected' : '' }}>Pará</option>
                        <option value="PB" {{ old('estado') == 'PB' ? 'selected' : '' }}>Paraíba</option>
                        <option value="PR" {{ old('estado') == 'PR' ? 'selected' : '' }}>Paraná</option>
                        <option value="PE" {{ old('estado') == 'PE' ? 'selected' : '' }}>Pernambuco</option>
                        <option value="PI" {{ old('estado') == 'PI' ? 'selected' : '' }}>Piauí</option>
                        <option value="RJ" {{ old('estado') == 'RJ' ? 'selected' : '' }}>Rio de Janeiro</option>
                        <option value="RN" {{ old('estado') == 'RN' ? 'selected' : '' }}>Rio Grande do Norte</option>
                        <option value="RS" {{ old('estado') == 'RS' ? 'selected' : '' }}>Rio Grande do Sul</option>
                        <option value="RO" {{ old('estado') == 'RO' ? 'selected' : '' }}>Rondônia</option>
                        <option value="RR" {{ old('estado') == 'RR' ? 'selected' : '' }}>Roraima</option>
                        <option value="SC" {{ old('estado') == 'SC' ? 'selected' : '' }}>Santa Catarina</option>
                        <option value="SP" {{ old('estado') == 'SP' ? 'selected' : '' }}>São Paulo</option>
                        <option value="SE" {{ old('estado') == 'SE' ? 'selected' : '' }}>Sergipe</option>
                        <option value="TO" {{ old('estado') == 'TO' ? 'selected' : '' }}>Tocantins</option>
                    </select>
                    @error('estado')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                </div>

                {{-- Horas Contratadas --}}
                <div class="form-group">
                    <label for="horas_contratadas"><i class="fas fa-clock"></i> Horas Contratadas</label>
                    <input type="number" name="horas_contratadas" id="horas_contratadas" class="form-control @error('horas_contratadas') is-invalid @enderror" value="{{ old('horas_contratadas') }}" required>
                    @error('horas_contratadas')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                </div>

                <div class="form-group d-flex justify-content-start">
                    <button type="submit" class="btn btn-success mr-2"><i class="fas fa-save"></i> Criar Empresa</button>
                    <a href="{{ route('empresas.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Voltar</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('css')
    <style>
        .form-group label { font-weight: 600; }
    </style>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            // Máscara de CNPJ
            $('#cnpj').on('input', function() {
                let cnpj = $(this).val().replace(/\D/g, '').slice(0, 14);
                if (cnpj.length > 11) {
                    cnpj = cnpj.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, "$1.$2.$3/$4-$5");
                } else {
                    cnpj = cnpj.replace(/^(\d{3})(\d{3})(\d{3})(\d{2})/, "$1.$2.$3-$4");
                }
                $(this).val(cnpj);
            });

            $('#empresaForm').on('submit', function() {
                const cnpjSemFormatacao = $('#cnpj').val().replace(/\D/g, '');
                $('#cnpj').val(cnpjSemFormatacao);
            });
        });
    </script>
@endpush
