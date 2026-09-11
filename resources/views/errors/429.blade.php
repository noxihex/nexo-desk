@extends('errors::layout')

@section('title', 'Muitas tentativas')
@section('code', '429')
@section('message', 'Você realizou muitas solicitações em pouco tempo. Aguarde um momento e tente novamente.')

@section('action')
    <a href="{{ auth()->check() ? route('home') : route('login') }}" class="error-action">
        {{ auth()->check() ? 'Voltar à visão geral' : 'Voltar para o login' }}
    </a>
@endsection
