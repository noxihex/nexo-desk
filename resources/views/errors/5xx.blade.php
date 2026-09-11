@extends('errors::layout')

@section('title', 'Erro no servidor')
@section('code', (string) $exception->getStatusCode())
@section('message', 'O sistema encontrou um problema temporário ao processar sua solicitação.')

@section('action')
    <a href="{{ url()->current() }}" class="error-action">Tentar novamente</a>
@endsection
