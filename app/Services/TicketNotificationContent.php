<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Str;

class TicketNotificationContent
{
    public function make(string $event, Ticket $ticket, User $recipient, array $context = []): array
    {
        $subject = "Ticket #{$ticket->id}";
        $body = '';

        if ($event === 'novo_ticket') {
            $subject = "Novo Ticket #{$ticket->id}";
            $body = "O ticket \"{$ticket->assunto}\" foi criado e aguarda atendimento.";
        } elseif ($event === 'nova_mensagem') {
            $subject = "Atualização no Ticket #{$ticket->id}";
            $body = "O ticket \"{$ticket->assunto}\" recebeu uma nova mensagem.";
        } elseif ($event === 'ticket_resolvido') {
            $subject = "Ticket #{$ticket->id} resolvido";
            $body = "O ticket \"{$ticket->assunto}\" foi resolvido.";
        } elseif ($event === 'sla') {
            $type = ($context['sla_type'] ?? 'update') === 'total' ? 'total' : 'de atualização';
            $subject = "Alerta de SLA {$type} — Ticket #{$ticket->id}";
            $body = "O SLA {$type} do ticket \"{$ticket->assunto}\" foi atingido.";
        }

        $summary = Str::limit(strip_tags((string) ($context['summary'] ?? $ticket->descricao)), 200);
        $clientPath = '/tickets/cliente/' . $ticket->id;
        $staffPath = '/tickets/' . $ticket->id;

        return [
            'event' => $event,
            'ticket_id' => $ticket->id,
            'title' => $subject,
            'message' => $body,
            'summary' => $summary,
            'url' => rtrim(config('app.url'), '/') . ($recipient->hasRole('cliente') ? $clientPath : $staffPath),
        ];
    }
}
