<?php

namespace App\Jobs;

use App\Models\NotificationDelivery;
use App\Models\Ticket;
use App\Models\User;
use App\Support\TicketStaffAccess;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DispatchStaffTicketActivity implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    private $eventKey;
    private $ticketId;
    private $actorId;
    private $recipientMode;
    private $recipientIds;
    private $summary;

    public function __construct(
        string $eventKey,
        int $ticketId,
        ?int $actorId,
        string $recipientMode = 'followers',
        array $recipientIds = [],
        ?string $summary = null
    ) {
        $this->eventKey = $eventKey;
        $this->ticketId = $ticketId;
        $this->actorId = $actorId;
        $this->recipientMode = $recipientMode;
        $this->recipientIds = array_values(array_unique(array_map('intval', $recipientIds)));
        $this->summary = $summary;
        $this->onConnection('database')->onQueue('notifications');
        $this->afterCommit();
    }

    public function handle(): void
    {
        if (!config('ticket_notifications.outbound_enabled')) {
            return;
        }

        $ticket = Ticket::with('seguidores.roles')->find($this->ticketId);
        if (!$ticket) {
            return;
        }

        $followers = $ticket->seguidores->pluck('id')->map(fn ($id) => (int) $id);
        $users = $this->recipientMode === 'mentions'
            ? User::with('roles')->whereIn('id', $this->recipientIds)->get()
                ->reject(fn (User $user) => $followers->contains((int) $user->id))
            : $ticket->seguidores;

        $users->filter(function (User $user) use ($ticket) {
            return (int) $user->id !== (int) $this->actorId
                && TicketStaffAccess::allows($user, $ticket);
        })->unique('id')->each(function (User $user) use ($ticket) {
            DB::transaction(function () use ($user, $ticket) {
                $delivery = NotificationDelivery::firstOrCreate([
                    'event_key' => $this->eventKey,
                    'user_id' => $user->id,
                    'channel' => 'database',
                ]);
                if (!$delivery->wasRecentlyCreated) {
                    return;
                }

                DatabaseNotification::create([
                    'id' => (string) Str::uuid(),
                    'type' => 'ticket.atividade',
                    'notifiable_type' => User::class,
                    'notifiable_id' => $user->id,
                    'data' => [
                        'event' => 'atividade_ticket',
                        'ticket_id' => $ticket->id,
                        'title' => "Atualização no Ticket #{$ticket->id}",
                        'message' => "O ticket \"{$ticket->assunto}\" recebeu uma atualização.",
                        'summary' => Str::limit(strip_tags((string) $this->summary), 200),
                        'url' => rtrim(config('app.url'), '/') . '/tickets/' . $ticket->id,
                    ],
                ]);
                $delivery->update(['status' => 'delivered', 'attempts' => 1, 'delivered_at' => now()]);
            });
        });
    }
}
