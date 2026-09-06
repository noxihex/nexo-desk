<?php

namespace App\Observers;

use App\Jobs\DispatchTicketNotification;
use App\Models\Ticket;

class TicketObserver
{
    public function created(Ticket $ticket): void
    {
        if (!config('ticket_notifications.outbound_enabled')) {
            return;
        }
        DispatchTicketNotification::dispatch(
            'ticket-created:' . $ticket->id,
            'novo_ticket',
            $ticket->id,
            $ticket->user_id
        )->afterCommit();
    }

    public function updated(Ticket $ticket): void
    {
        if (config('ticket_notifications.outbound_enabled')
            && $ticket->wasChanged('status')
            && $ticket->status === 'fechado'
            && $ticket->getOriginal('status') !== 'fechado') {
            DispatchTicketNotification::dispatch(
                'ticket-resolved:' . $ticket->id,
                'ticket_resolvido',
                $ticket->id,
                $ticket->finalizado_por_usuario_id ?: auth()->id(),
                ['summary' => $ticket->descricao_final]
            )->afterCommit();
        }
    }
}
