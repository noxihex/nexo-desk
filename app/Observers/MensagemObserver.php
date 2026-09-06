<?php

namespace App\Observers;

use App\Jobs\DispatchTicketNotification;
use App\Models\Mensagem;

class MensagemObserver
{
    public function created(Mensagem $mensagem): void
    {
        if (!config('ticket_notifications.outbound_enabled') || !$mensagem->isPublica()) {
            return;
        }

        DispatchTicketNotification::dispatch(
            'message-created:' . $mensagem->id,
            'nova_mensagem',
            $mensagem->ticket_id,
            $mensagem->user_id,
            ['summary' => $mensagem->descricao]
        )->afterCommit();
    }
}
