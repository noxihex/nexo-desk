@extends('errors::layout')

@section('title', 'Página expirada')
@section('code', '419')
@section('message', 'Sua sessão expirou. Entre novamente para continuar usando o sistema.')

@section('action')
    <a href="{{ route('login') }}" class="error-action">
        Ir para o login
    </a>
@endsection
