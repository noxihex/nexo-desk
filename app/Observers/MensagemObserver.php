<?php

namespace App\Observers;

use App\Models\Mensagem;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MensagemObserver
{
    public function created(Mensagem $mensagem)
    {
        $ticket = $mensagem->ticket;
        if (!$ticket) return;

        $emailsCliente = [];
        $emailsAnalistas = [];
        
        $remetenteId = $mensagem->user_id;
        $enviadoPeloCliente = ($ticket->cliente_id === $remetenteId);

        // =========================================================
        // LÓGICA DE DESTINATÁRIOS
        // =========================================================

        if ($enviadoPeloCliente) {
            // REGRA 1: Se o cliente enviar -> Notifica SOMENTE o analista atribuído (caso tenha um e esteja ativo)
            if ($ticket->analista && $ticket->analista->status && $ticket->analista->email) {
                $emailsAnalistas[] = [
                    'email' => $ticket->analista->email, 
                    'nome' => $ticket->analista->name
                ];
            }
        } else {
            // REGRA 2: Se o analista (ou sistema) enviar -> Notifica SOMENTE o cliente (se estiver ativo)
            if ($ticket->cliente && $ticket->cliente->status && $ticket->cliente->email) {
                $emailsCliente[] = [
                    'email' => $ticket->cliente->email, 
                    'nome' => $ticket->cliente->name
                ];
            }
        }

        // Se ninguém atendeu aos requisitos para receber e-mail, encerra aqui
        if (empty($emailsCliente) && empty($emailsAnalistas)) return;

        // =========================================================
        // CONSTRUÇÃO DO CONTEÚDO DO E-MAIL
        // =========================================================

        $nomeRemetente = $mensagem->user ? $mensagem->user->name : 'Alguém';
        $titulo = "Atualização no Ticket #{$ticket->id}";
        $corpo = "Olá! O ticket de ID #{$ticket->id} \"{$ticket->assunto}\" recebeu uma nova mensagem.";
        
        // Coloca o conteúdo da mensagem (limitado a 200 caracteres e sem formatação) no Box Amarelo
        $infoExtra = Str::limit(strip_tags($mensagem->descricao), 200);

        // =========================================================
        // DISPAROS PARA A API PYTHON
        // =========================================================

        // Dispara o e-mail para o Cliente com a URL específica dele
        if (!empty($emailsCliente)) {
            $urlCliente = "https://desk.btx.net.br/tickets/cliente/{$ticket->id}";
            $this->dispararScriptPython($emailsCliente, $titulo, $corpo, $urlCliente, $infoExtra);
        }

        // Dispara o e-mail para o Analista com a URL administrativa
        if (!empty($emailsAnalistas)) {
            $urlAnalista = "https://desk.btx.net.br/tickets/{$ticket->id}";
            $this->dispararScriptPython($emailsAnalistas, $titulo, $corpo, $urlAnalista, $infoExtra);
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
                Log::error("Erro ao integrar com Python (MensagemObserver): " . $e->getMessage());
            }
        })->afterResponse(); // <-- O truque mágico do Laravel 8!
    }
}