@extends('errors::layout')

@section('title', 'Serviço indisponível')
@section('code', '503')
@section('message', 'O sistema está temporariamente indisponível. Aguarde alguns instantes e tente novamente.')

@section('action')
    <a href="{{ url()->current() }}" class="error-action">Tentar novamente</a>
@endsection
