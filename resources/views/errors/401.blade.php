@extends('errors::layout')

@section('title', 'Autenticação necessária')
@section('code', '401')
@section('message', 'Você precisa entrar em sua conta para acessar este conteúdo.')

@section('action')
    <a href="{{ route('login') }}" class="error-action">Ir para o login</a>
@endsection
