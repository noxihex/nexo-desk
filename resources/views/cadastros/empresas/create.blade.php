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

                {{-- Nome --}}
                <div class="form-group">
                    <label for="nome"><i class="fas fa-building"></i> Nome Fantasia</label>
                    <input type="text" name="nome" id="nome" class="form-control @error('nome') is-invalid @enderror" value="{{ old('nome') }}" required>
                    @error('nome')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                {{-- Razão Social --}}
<div class="form-group">
    <label for="razao_social"><i class="fas fa-briefcase"></i> Razão Social</label>
    <input type="text" name="razao_social" id="razao_social" class="form-control @error('razao_social') is-invalid @enderror" value="{{ old('razao_social') }}">
    @error('razao_social')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
    @enderror
</div>

                {{-- CNPJ --}}
                <div class="form-group">
                    <label for="cnpj"><i class="fas fa-id-card"></i> CNPJ</label>
                    <input type="text" name="cnpj" id="cnpj" class="form-control @error('cnpj') is-invalid @enderror" value="{{ old('cnpj') }}" required>
                    @error('cnpj')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                {{-- Endereço --}}
                <div class="form-group">
                    <label for="endereco"><i class="fas fa-map-marker-alt"></i> Endereço</label>
                    <input type="text" name="endereco" id="endereco" class="form-control @error('endereco') is-invalid @enderror" value="{{ old('endereco') }}" required>
                    @error('endereco')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                {{-- Bairro --}}
                <div class="form-group">
                    <label for="bairro"><i class="fas fa-map-pin"></i> Bairro</label>
                    <input type="text" name="bairro" id="bairro" class="form-control @error('bairro') is-invalid @enderror" value="{{ old('bairro') }}" required>
                    @error('bairro')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                {{-- Cidade --}}
                <div class="form-group">
                    <label for="cidade"><i class="fas fa-city"></i> Cidade</label>
                    <input type="text" name="cidade" id="cidade" class="form-control @error('cidade') is-invalid @enderror" value="{{ old('cidade') }}" required>
                    @error('cidade')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                {{-- Estado --}}
                <div class="form-group">
                    <label for="estado"><i class="fas fa-flag"></i> Estado</label>
                    <select name="estado" id="estado" class="form-control @error('estado') is-invalid @enderror" required>
                        <option value="">Selecione um estado</option>
                        <option value="AC">Acre</option>
                        <option value="AL">Alagoas</option>
                        <option value="AP">Amapá</option>
                        <option value="AM">Amazonas</option>
                        <option value="BA">Bahia</option>
                        <option value="CE">Ceará</option>
                        <option value="DF">Distrito Federal</option>
                        <option value="ES">Espírito Santo</option>
                        <option value="GO">Goiás</option>
                        <option value="MA">Maranhão</option>
                        <option value="MT">Mato Grosso</option>
                        <option value="MS">Mato Grosso do Sul</option>
                        <option value="MG">Minas Gerais</option>
                        <option value="PA">Pará</option>
                        <option value="PB">Paraíba</option>
                        <option value="PR">Paraná</option>
                        <option value="PE">Pernambuco</option>
                        <option value="PI">Piauí</option>
                        <option value="RJ">Rio de Janeiro</option>
                        <option value="RN">Rio Grande do Norte</option>
                        <option value="RS">Rio Grande do Sul</option>
                        <option value="RO">Rondônia</option>
                        <option value="RR">Roraima</option>
                        <option value="SC">Santa Catarina</option>
                        <option value="SP">São Paulo</option>
                        <option value="SE">Sergipe</option>
                        <option value="TO">Tocantins</option>
                    </select>
                    @error('estado')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                {{-- Horas Contratadas --}}
                <div class="form-group">
                    <label for="horas_contratadas"><i class="fas fa-clock"></i> Horas Contratadas</label>
                    <input type="number" name="horas_contratadas" id="horas_contratadas" class="form-control @error('horas_contratadas') is-invalid @enderror" value="{{ old('horas_contratadas') }}" required>
                    @error('horas_contratadas')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group d-flex justify-content-start">
                    {{-- Botão de Salvar --}}
                    <button type="submit" class="btn btn-success mr-2">
                        <i class="fas fa-save"></i> Criar Empresa
                    </button>

                    {{-- Botão de Voltar --}}
                    <a href="{{ route('empresas.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('css')
    <style>
        .form-group label {
            font-weight: 600;
        }
    </style>
@endsection

@section('js')
    {{-- Incluindo o jQuery --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $(document).ready(function() {

            // Máscara de CNPJ
            $('#cnpj').on('input', function() {
                let cnpj = $(this).val().replace(/\D/g, ''); // Remove qualquer caractere que não seja número
                if (cnpj.length > 14) cnpj = cnpj.slice(0, 14); // Limita o CNPJ a 14 dígitos

                // Formata o CNPJ (##.###.###/####-##)
                if (cnpj.length <= 11) {
                    cnpj = cnpj.replace(/(\d{2})(\d)/, "$1.$2");
                    cnpj = cnpj.replace(/(\d{3})(\d)/, "$1.$2");
                    cnpj = cnpj.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
                } else {
                    cnpj = cnpj.replace(/^(\d{2})(\d)/, "$1.$2");
                    cnpj = cnpj.replace(/^(\d{2})\.(\d{3})(\d)/, "$1.$2.$3");
                    cnpj = cnpj.replace(/\.(\d{3})(\d)/, ".$1/$2");
                    cnpj = cnpj.replace(/(\d{4})(\d)/, "$1-$2");
                }

                $(this).val(cnpj); // Atualiza o campo com o CNPJ formatado
            });

            // Quando o formulário for enviado, remove a formatação do CNPJ
            $('#empresaForm').on('submit', function() {
                const cnpjSemFormatacao = $('#cnpj').val().replace(/\D/g, ''); // Remove todos os caracteres não numéricos
                $('#cnpj').val(cnpjSemFormatacao); // Atualiza o valor do campo antes de enviar o formulário
            });
        });
    </script>
@endsection
