<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AdministracaoController extends Controller
{
    public function usuariosLogados()
    {
        // Buscando sessões ativas dos usuários
        $sessions = DB::table('sessions')
            ->join('users', 'sessions.user_id', '=', 'users.id')
            ->select(
                'sessions.id as session_id',
                'users.id as user_id',
                'users.name',
                'sessions.last_activity',
                'sessions.ip_address',
                'sessions.user_agent' // Adiciona o navegador e sistema operacional
            )
            ->whereNotNull('users.id')
            ->get();

        // Adicionando o tipo (Cliente ou Analista) baseado nas permissões (roles)
        $sessions->transform(function ($session) {
            $user = \App\Models\User::find($session->user_id); // Busca o usuário relacionado
            $session->role = $user ? $user->getRoleNames()->first() : 'Desconhecido'; // Obtém a role do usuário
            return $session;
        });

        return view('administracao.usuarioslogados.index', compact('sessions'));
    }

    public function deslogarUsuario($sessionId)
    {
        // Removendo a sessão específica para deslogar o usuário
        DB::table('sessions')->where('id', $sessionId)->delete();

        return redirect()->route('administracao.usuarioslogados')->with('success', 'Usuário deslogado com sucesso!');
    }
}
