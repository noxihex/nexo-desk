<?php

namespace App\Observers;

use App\Jobs\DispatchTicketNotification;
use App\Jobs\DispatchStaffTicketActivity;
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
        if (!config('ticket_notifications.outbound_enabled')) {
            return;
        }

        $resolved = $ticket->wasChanged('status')
            && $ticket->status === 'fechado'
            && $ticket->getOriginal('status') !== 'fechado';

        if ($resolved) {
            DispatchTicketNotification::dispatch(
                'ticket-resolved:' . $ticket->id,
                'ticket_resolvido',
                $ticket->id,
                $ticket->finalizado_por_usuario_id ?: auth()->id(),
                ['summary' => $ticket->descricao_final]
            )->afterCommit();
        }

        $fields = ['assunto', 'status', 'categoria_id', 'setor_id', 'grupo_id',
            'atribuido_ao_analista_id', 'cliente_id', 'empresa_id'];
        if (collect($fields)->contains(fn ($field) => $ticket->wasChanged($field))) {
            $actorId = auth()->id() ?: auth('sanctum')->id() ?: $ticket->finalizado_por_usuario_id;
            $eventKey = $resolved
                ? 'ticket-resolved:' . $ticket->id
                : 'ticket-updated:' . $ticket->id . ':' . optional($ticket->updated_at)->format('Uu');
            DispatchStaffTicketActivity::dispatch(
                $eventKey,
                $ticket->id,
                $actorId,
                'followers',
                [],
                'Dados operacionais do ticket foram alterados.'
            )->afterCommit();
        }
    }
}
