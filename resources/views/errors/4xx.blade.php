@extends('errors::layout')

@section('title', 'Não foi possível concluir')
@section('code', (string) $exception->getStatusCode())
@section('message', 'A solicitação não pôde ser atendida. Confira o endereço ou volte para uma página disponível.')

@section('action')
    <a href="{{ auth()->check() ? route('home') : route('login') }}" class="error-action">
        {{ auth()->check() ? 'Voltar à visão geral' : 'Voltar para o login' }}
    </a>
@endsection
