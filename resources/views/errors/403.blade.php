@extends('errors::layout')

@section('title', 'Acesso não autorizado')
@section('code', '403')
@section('message', 'Sua conta não possui permissão para acessar esta página.')

@section('action')
    @if(auth()->check() && (auth()->user()->isStaff() || auth()->user()->hasRole('cliente')))
        <a href="{{ route('home') }}" class="error-action">Voltar à visão geral</a>
    @elseif(auth()->check())
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="error-action">Encerrar sessão</button>
        </form>
    @else
        <a href="{{ route('login') }}" class="error-action">Ir para o login</a>
    @endif
@endsection
