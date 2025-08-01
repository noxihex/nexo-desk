{{-- resources/views/errors/419.blade.php --}}
@extends('errors::minimal')

@section('title', __('Página Expirada'))
@section('code', '419')
@section('message')
    <script>
        // Redirecionar para a página de login após 3 segundos
        setTimeout(function() {
            window.location.href = "{{ route('login') }}";
        }, 3000);
    </script>
    <p>Sessão expirada. Você será redirecionado para a página de login.</p>
@endsection
