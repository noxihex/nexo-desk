<?php

namespace App\Observers;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TicketObserver
{
    public function created(Ticket $ticket)
    {
        $emailsCliente = [];
        $emailsAnalistas = [];
        
        $criadorId = $ticket->user_id;
        $criadoPeloCliente = ($ticket->cliente_id === $criadorId);

        // =========================================================
        // LÓGICA DE DESTINATÁRIOS E TEXTOS
        // =========================================================
        
        $nomeEmpresa = $ticket->empresa ? $ticket->empresa->nome : 'N/A';
        $corpoCliente = "";
        $corpoAnalista = "";

        if (!$criadoPeloCliente) {
            // REGRA 1: Se o Analista abriu -> Envia SÓ para o Cliente (Se estiver ativo)
            if ($ticket->cliente && $ticket->cliente->status && $ticket->cliente->email) {
                $emailsCliente[] = [
                    'email' => $ticket->cliente->email,
                    'nome' => $ticket->cliente->name
                ];
            }
            
            // Texto mais limpo focado no Cliente
            $corpoCliente = "Olá! O ticket de ID #{$ticket->id} \"{$ticket->assunto}\" acabou de ser criado e aguarda interação.";
            
        } else {
            // REGRA 2: Se o Cliente abriu -> Envia para TODOS os Analistas do Setor (Se ativos)
            if ($ticket->setor_id) {
                $analistasSetor = User::whereHas('roles', function ($query) {
                    $query->whereIn('name', ['analista', 'supervisor', 'administrador']);
                })
                ->where('setor_id', $ticket->setor_id)
                ->where('status', true)
                ->whereNotNull('email')
                ->get(['email', 'name']);
                
                foreach ($analistasSetor as $analista) {
                    $emailsAnalistas[] = [
                        'email' => $analista->email,
                        'nome' => $analista->name
                    ];
                }
            }
            
            // Texto detalhado focado no Analista (com nome da empresa)
            $corpoAnalista = "Olá! O ticket de ID #{$ticket->id} \"{$ticket->assunto}\" da empresa \"{$nomeEmpresa}\" acabou de ser criado e aguarda interação.";
        }

        // =========================================================
        // CONSTRUÇÃO DO CONTEÚDO COMUM E DISPARO
        // =========================================================
        
        $titulo = "Novo Ticket #{$ticket->id}";
        $infoExtra = Str::limit(strip_tags($ticket->descricao), 200);

        // Dispara o e-mail para o Cliente
        if (!empty($emailsCliente)) {
            $urlCliente = "https://desk.btx.net.br/tickets/cliente/{$ticket->id}";
            $this->dispararScriptPython($emailsCliente, $titulo, $corpoCliente, $urlCliente, $infoExtra);
        }

        // Dispara o e-mail para os Analistas
        if (!empty($emailsAnalistas)) {
            $urlAnalista = "https://desk.btx.net.br/tickets/{$ticket->id}";
            $this->dispararScriptPython($emailsAnalistas, $titulo, $corpoAnalista, $urlAnalista, $infoExtra);
        }
    }

    private function dispararScriptPython($emails, $titulo, $corpo, $urlBotao, $infoExtra = '')
    {
        // O dispatch garante que o Laravel entregue a tela primeiro e execute o código abaixo depois
        dispatch(function () use ($emails, $titulo, $corpo, $urlBotao, $infoExtra) {
            try {
                $token = env('API_PYTHON_TOKEN', '3be11sXzH0Z9W40nUoFdDyAIw8JPd88T'); 

                Http::withToken($token)
                    ->timeout(5)
                    ->post('http://localhost:5000/send-email', [
                        'emails' => $emails,
                        'titulo_do_email' => $titulo,
                        'corpo_do_email' => $corpo,
                        'titulo_do_botao' => "Acessar ticket",
                        'url_do_botao' => $urlBotao,
                        'informacao_extra' => $infoExtra
                    ]);
            } catch (\Exception $e) {
                Log::error("Erro ao integrar com Python (TicketObserver): " . $e->getMessage());
            }
        })->afterResponse(); // <-- O truque mágico do Laravel 8!
    }
}