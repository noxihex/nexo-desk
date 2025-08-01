<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Notificacao;

class NotificacaoController extends Controller
{
    // Retorna as notificações do usuário autenticado
    public function index()
    {
        try {
            // Obtém todas as notificações do usuário autenticado
            $notificacoes = Notificacao::where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->limit(100)
                ->get();

            return response()->json($notificacoes); // Garante retorno em JSON
        } catch (\Exception $e) {
            Log::error('Erro ao carregar notificações: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao carregar notificações.'], 500); // Retorna erro como JSON
        }
    }

    // Marca uma ou todas as notificações como lidas
    public function marcarComoLida($id)
    {
        try {
            if ($id === 'all') {
                // Marca todas as notificações do usuário autenticado como lidas
                Notificacao::where('user_id', Auth::id())->update(['lida' => true]);

                return response()->json(['success' => true, 'message' => 'Todas as notificações foram marcadas como lidas.']);
            }

            // Marca uma notificação específica como lida
            $notificacao = Notificacao::where('id', $id)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            $notificacao->lida = true;
            $notificacao->save();

            return response()->json(['success' => true, 'message' => "Notificação #{$id} marcada como lida."]);
        } catch (\Exception $e) {
            Log::error('Erro ao marcar notificação como lida: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao marcar notificações como lidas.'], 500); // Retorna erro como JSON
        }
    }
}
