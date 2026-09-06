<?php

namespace App\Services;

use App\Models\Ticket;
use Carbon\Carbon;

class SlaDeadlineCalculator
{
    public function updateReference(Ticket $ticket): Carbon
    {
        $reference = collect([
            $ticket->created_at,
            $ticket->data_hora_assumido,
            $ticket->data_hora_transferido,
            $ticket->sla_update_reference_at,
        ])->filter()->map(fn ($date) => Carbon::parse($date))->sortByDesc(fn ($date) => $date->timestamp)->first();

        if ($ticket->atribuido_ao_analista_id) {
            $lastPublicReply = $ticket->mensagens()
                ->where('tipo', 'publica')
                ->where('user_id', $ticket->atribuido_ao_analista_id)
                ->where('created_at', '>=', $reference)
                ->latest('created_at')
                ->first();
            if ($lastPublicReply) {
                $reference = Carbon::parse($lastPublicReply->created_at);
            }
        }

        return $reference;
    }

    public function updateIsDue(Ticket $ticket, ?Carbon $now = null): bool
    {
        return $ticket->categoria
            && $this->updateReference($ticket)->diffInMinutes($now ?: now()) >= (int) $ticket->categoria->slaupdate;
    }

    public function totalIsDue(Ticket $ticket, ?Carbon $now = null): bool
    {
        return $ticket->categoria
            && Carbon::parse($ticket->created_at)->diffInMinutes($now ?: now()) >= (int) $ticket->categoria->slatotal;
    }
}
