<?php

namespace App\Jobs;

use App\Jobs\SendTicketNotificationMail;
use App\Models\NotificationDelivery;
use App\Models\Ticket;
use App\Services\TicketNotificationContent;
use App\Services\TicketNotificationRecipients;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DispatchTicketNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    private $eventKey;
    private $event;
    private $ticketId;
    private $actorId;
    private $context;

    public function __construct(string $eventKey, string $event, int $ticketId, ?int $actorId = null, array $context = [])
    {
        $this->eventKey = $eventKey;
        $this->event = $event;
        $this->ticketId = $ticketId;
        $this->actorId = $actorId;
        $this->context = $context;
        $this->onConnection('database')->onQueue('notifications');
        $this->afterCommit();
    }

    public function handle(TicketNotificationRecipients $recipients, TicketNotificationContent $content): void
    {
        if (!config('ticket_notifications.outbound_enabled')) {
            return;
        }

        $ticket = Ticket::with(['cliente.notificationPreference', 'analista.notificationPreference', 'setor'])->find($this->ticketId);
        if (!$ticket) {
            return;
        }

        foreach ($recipients->for($this->event, $ticket, $this->actorId, $this->context) as $recipient) {
            $payload = $content->make($this->event, $ticket, $recipient, $this->context);

            foreach (['database', 'mail'] as $channel) {
                if (!$recipient->allowsNotification($this->event, $channel)) {
                    continue;
                }
                if ($channel === 'mail' && !$recipient->email) {
                    continue;
                }

                if ($channel === 'database') {
                    DB::transaction(function () use ($recipient, $payload, $channel) {
                        $delivery = NotificationDelivery::firstOrCreate([
                            'event_key' => $this->eventKey,
                            'user_id' => $recipient->id,
                            'channel' => $channel,
                        ]);
                        if (!$delivery->wasRecentlyCreated) {
                            return;
                        }
                        DatabaseNotification::create([
                            'id' => (string) Str::uuid(),
                            'type' => 'ticket.' . $payload['event'],
                            'notifiable_type' => get_class($recipient),
                            'notifiable_id' => $recipient->id,
                            'data' => $payload,
                        ]);
                        $delivery->update(['status' => 'delivered', 'attempts' => 1, 'delivered_at' => now()]);
                    });
                } else {
                    DB::transaction(function () use ($recipient, $payload, $channel) {
                        $delivery = NotificationDelivery::firstOrCreate([
                            'event_key' => $this->eventKey,
                            'user_id' => $recipient->id,
                            'channel' => $channel,
                        ]);
                        if (!$delivery->wasRecentlyCreated) {
                            return;
                        }
                        SendTicketNotificationMail::dispatch($delivery->id, $payload);
                    });
                }
            }
        }
    }
}
