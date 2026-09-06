<?php

namespace App\Console\Commands;

use App\Jobs\DispatchTicketNotification;
use App\Models\SlaAlertOccurrence;
use App\Models\Ticket;
use App\Services\SlaDeadlineCalculator;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;

class CheckTicketSla extends Command
{
    protected $signature = 'tickets:check-sla';
    protected $description = 'Emite alertas idempotentes de SLA para tickets abertos';

    public function handle(SlaDeadlineCalculator $calculator): int
    {
        if (!config('ticket_notifications.sla_enabled') || !config('ticket_notifications.outbound_enabled')) {
            return self::SUCCESS;
        }

        Ticket::with('categoria')
            ->where('status', '!=', 'fechado')
            ->whereNotNull('categoria_id')
            ->orderBy('id')
            ->chunkById(100, function ($tickets) use ($calculator) {
                foreach ($tickets as $ticket) {
                    if ($calculator->updateIsDue($ticket)) {
                        $this->recordAndDispatch($ticket, 'update', $calculator->updateReference($ticket));
                    }
                    if ($calculator->totalIsDue($ticket)) {
                        $this->recordAndDispatch($ticket, 'total', $ticket->created_at);
                    }
                }
            });

        return self::SUCCESS;
    }

    private function recordAndDispatch(Ticket $ticket, string $type, $reference): void
    {
        try {
            $occurrence = SlaAlertOccurrence::create([
                'ticket_id' => $ticket->id,
                'type' => $type,
                'reference_at' => $reference,
            ]);
        } catch (QueryException $exception) {
            if (in_array((string) $exception->getCode(), ['23000', '23505'], true)) {
                return;
            }
            throw $exception;
        }

        DispatchTicketNotification::dispatch(
            "sla:{$type}:{$ticket->id}:" . $occurrence->reference_at->timestamp,
            'sla',
            $ticket->id,
            null,
            ['sla_type' => $type]
        )->afterCommit();
    }
}
