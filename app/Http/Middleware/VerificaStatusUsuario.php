<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerificaStatusUsuario
{
    public function handle($request, Closure $next)
    {
        // Verifica se o usuário está logado e o status está inativo
        if (Auth::check() && Auth::user()->status == 0) {
            Auth::logout(); // Desloga o usuário
            return redirect('/login')->withErrors(['Sua conta foi desativada.']);
        }

        return $next($request);
    }
}
