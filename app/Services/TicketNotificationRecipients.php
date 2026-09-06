<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Collection;

class TicketNotificationRecipients
{
    public function for(string $event, Ticket $ticket, ?int $actorId, array $context = []): Collection
    {
        $recipients = collect();
        $client = $ticket->cliente;
        $analyst = $ticket->analista;

        if ($event === 'novo_ticket') {
            $recipients = $recipients->when($client, fn ($items) => $items->push($client));
            $recipients = $analyst
                ? $recipients->push($analyst)
                : $recipients->merge($this->teamForSector($ticket->setor_id));
        } elseif ($event === 'nova_mensagem') {
            if ($client && (int) $actorId === (int) $client->id) {
                $recipients = $analyst
                    ? $recipients->push($analyst)
                    : $recipients->merge($this->teamForSector($ticket->setor_id));
            } elseif ($client) {
                $recipients->push($client);
            }
        } elseif ($event === 'ticket_resolvido') {
            $recipients = $recipients->when($client, fn ($items) => $items->push($client));
            $recipients = $recipients->when($analyst, fn ($items) => $items->push($analyst));
        } elseif ($event === 'sla') {
            $recipients = $analyst
                ? $recipients->push($analyst)
                : $recipients->merge($this->teamForSector($ticket->setor_id));

            if (($context['sla_type'] ?? null) === 'total') {
                $escalators = $this->escalatorsForSector($ticket->setor_id);
                if ($escalators->isEmpty()) {
                    $escalators = $this->activeUsersWithRoles(['administrador']);
                }
                $recipients = $recipients->merge($escalators);
            }
        }

        return $recipients
            ->filter(fn ($user) => $user instanceof User && $user->status && (int) $user->id !== (int) $actorId)
            ->unique('id')
            ->values();
    }

    private function teamForSector(?int $sectorId): Collection
    {
        if (!$sectorId) {
            return collect();
        }

        return $this->activeUsersWithRoles(['analista', 'supervisor', 'administrador'])
            ->where('setor_id', $sectorId)
            ->values();
    }

    private function escalatorsForSector(?int $sectorId): Collection
    {
        if (!$sectorId) {
            return collect();
        }

        return $this->activeUsersWithRoles(['supervisor', 'administrador'])
            ->where('setor_id', $sectorId)
            ->values();
    }

    private function activeUsersWithRoles(array $roles): Collection
    {
        return User::where('status', true)
            ->whereHas('roles', fn ($query) => $query->whereIn('name', $roles))
            ->get();
    }
}
