@extends('adminlte::page')

@section('title', config('app.name') . ' - Criar Categoria')

@section('content_header')
    <p style="font-size: 1.2em;">
        Cadastros <i class="fas fa-angle-right" style="font-size: 0.7em;"></i> Categorias <i class="fas fa-angle-right" style="font-size: 0.7em;"></i> Criar
    </p>
@endsection

@section('content')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Nova Categoria</h3>
        </div>

        <div class="card-body">
            <form action="{{ route('categorias.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="nome"><i class="fas fa-tag"></i> Nome da Categoria</label>
                    <input type="text" name="nome" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="prioridade"><i class="fas fa-exclamation-circle"></i> Prioridade</label>
                    <select name="prioridade" class="form-control">
                        <option value="Normal" selected>Normal</option>
                        <option value="Alta">Alta</option>
                        <option value="Baixa">Baixa</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="slatotal">
                        <i class="fas fa-clock"></i> SLA Total (min)
                        <i class="far fa-question-circle text-muted ml-1 sla-help" tabindex="0" role="button"
                           data-toggle="tooltip" data-placement="right"
                           title="Prazo máximo, em minutos, entre a abertura e a conclusão do ticket. É usado para calcular o percentual de SLA e indicar atrasos."></i>
                    </label>
                    <input type="number" name="slatotal" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="slaupdate">
                        <i class="fas fa-history"></i> SLA Update (min)
                        <i class="far fa-question-circle text-muted ml-1 sla-help" tabindex="0" role="button"
                           data-toggle="tooltip" data-placement="right"
                           title="Intervalo máximo, em minutos, sem uma atualização do analista. Ao atingir esse tempo, o ticket requer atenção. Para sugerir as horas gastas, o sistema soma os intervalos entre a abertura, cada mensagem e a finalização, limitando cada intervalo a este valor."></i>
                    </label>
                    <input type="number" name="slaupdate" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="setor_ids"><i class="fas fa-building"></i> Setores</label>
                    <select name="setor_ids[]" id="setor_ids" class="form-control @error('setor_ids') is-invalid @enderror" multiple>
                        @foreach($setores as $setor)
                            <option value="{{ $setor->id }}" {{ in_array($setor->id, old('setor_ids', [])) ? 'selected' : '' }}>{{ $setor->nome }}</option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">Deixe vazio para manter a categoria indisponível nos tickets.</small>
                    @error('setor_ids')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @error('setor_ids.*')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>

                <div class="form-group d-flex justify-content-start">
                    <button type="submit" class="btn btn-success mr-2">
                        <i class="fas fa-save"></i> Criar Categoria
                    </button>
                    <a href="{{ route('categorias.index') }}" class="btn btn-secondary">
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

        .sla-help {
            cursor: help;
        }
    </style>
@endsection

@section('js')
    <script>
        $(function () {
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
@endsection
