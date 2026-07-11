@extends('adminlte::page')

@section('title', config('app.name') . ' - Novo Contrato')

@section('content_header')
    <p style="font-size: 1.2em;">
        Cadastros <i class="fas fa-angle-right" style="font-size: 0.7em;"></i> Contratos <i class="fas fa-angle-right" style="font-size: 0.7em;"></i> Criar
    </p>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Preencha os dados para criar um novo contrato</h3>
        </div>

        <div class="card-body">
            {{-- O ID do formulário é importante para o script --}}
            <form id="form-contrato" action="{{ route('contratos.store') }}" method="POST">
                @csrf
                @include('cadastros.contratos._form')

                <div class="form-group d-flex justify-content-start">
                    <button type="submit" class="btn btn-success mr-2">
                        <i class="fas fa-save"></i> Salvar
                    </button>
                    <a href="{{ route('contratos.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>
            </form>
        </div>
    </div>
@stop

@section('css')
    <style>
        .form-group label { font-weight: 600; }
        .form-group .btn { min-width: 120px; }
    </style>
@endsection

@section('js')
    {{-- Importa a biblioteca jQuery Mask Plugin via CDN --}}

@section('js')
    {{-- Importa a biblioteca jQuery Mask Plugin via CDN --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

    <script>
        $(document).ready(function(){
            // Aplica a máscara de milhar no campo de display
            $('#valor_display').mask('000.000.000.000.000', {reverse: true});

            // Adiciona um listener para o evento de 'input' no campo de display
            $('#valor_display').on('input', function() {
                // Remove os pontos e atualiza o valor do campo oculto 'valor'
                var valorSemFormatacao = $(this).val().replace(/\./g, '');
                $('#valor').val(valorSemFormatacao);
            });
        });
    </script>
@endsection
@endsection
