@extends('errors::layout')

@section('title', 'Erro interno do servidor')
@section('code', '500')
@section('message', 'Não foi possível concluir sua solicitação. Tente novamente em alguns instantes.')

@section('action')
    <a href="{{ url()->current() }}" class="error-action">Tentar novamente</a>
@endsection
