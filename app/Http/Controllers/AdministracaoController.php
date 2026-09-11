<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AdministracaoController extends Controller
{
    public function usuariosLogados(): View
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
            ->orderByDesc('sessions.last_activity')
            ->get();

        $users = User::with('roles')->whereIn('id', $sessions->pluck('user_id'))->get()->keyBy('id');

        $sessions->transform(function (object $session) use ($users): object {
            $user = $users->get($session->user_id);
            $session->role = $user ? $user->getRoleNames()->first() : 'Desconhecido'; // Obtém a role do usuário
            $session->last_activity_at = Carbon::createFromTimestamp(
                $session->last_activity,
                config('app.timezone'),
            );

            return $session;
        });

        return view('administracao.usuarioslogados.index', compact('sessions'));
    }

    public function deslogarUsuario(string $sessionId): RedirectResponse
    {
        // Removendo a sessão específica para deslogar o usuário
        DB::table('sessions')->where('id', $sessionId)->delete();

        return redirect()->route('administracao.usuarioslogados')->with('success', 'Usuário deslogado com sucesso!');
    }
}
