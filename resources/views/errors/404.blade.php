@extends('errors::minimal')

@section('title', __('Página Não Encontrada'))
@section('code', '404')
@section('message')
    <div style="text-align: center; margin-top: 50px;">
        <p>Página não encontrada</p>

        <button id="btn-home" onclick="location.href='{{ route('home') }}'"
            style="
                display: inline-block;
                padding: 8px 8px;
                font-size: 12px;
                font-weight: bold;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                text-align: center;
            ">
            <i class="fas fa-home" style="margin-right: 5px;"></i> Voltar para a Página Inicial
        </button>
    </div>
@endsection
