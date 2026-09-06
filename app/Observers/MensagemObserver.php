<?php

namespace App\Observers;

use App\Jobs\DispatchTicketNotification;
use App\Jobs\DispatchStaffTicketActivity;
use App\Models\Mensagem;

class MensagemObserver
{
    public function created(Mensagem $mensagem): void
    {
        if (!config('ticket_notifications.outbound_enabled')) {
            return;
        }

        if ($mensagem->tipo !== Mensagem::TIPO_SISTEMA) {
            DispatchStaffTicketActivity::dispatch(
                'message-created:' . $mensagem->id,
                $mensagem->ticket_id,
                $mensagem->user_id,
                'followers',
                [],
                $mensagem->descricao
            )->afterCommit();
        }

        if (!$mensagem->isPublica()) {
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
