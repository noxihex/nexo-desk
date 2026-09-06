<?php

namespace App\Jobs;

use App\Mail\TicketEventMail;
use App\Models\EmailReplyToken;
use App\Models\NotificationDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class SendTicketNotificationMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;
    public $backoff = [60, 300, 900, 3600];
    public $timeout = 30;

    private $deliveryId;
    private $payload;

    public function __construct(int $deliveryId, array $payload)
    {
        $this->deliveryId = $deliveryId;
        $this->payload = $payload;
        $this->onConnection('database')->onQueue('notifications');
    }

    public function handle(): void
    {
        $delivery = NotificationDelivery::with('user')->find($this->deliveryId);
        if (!$delivery || $delivery->status === 'delivered' || !$delivery->user) {
            return;
        }
        if (!$delivery->user->status || !$delivery->user->email) {
            $delivery->update(['status' => 'skipped', 'last_error' => 'Destinatário inativo ou sem e-mail.']);
            return;
        }

        $delivery->increment('attempts');
        $delivery->update(['status' => 'processing']);
        $replyTo = $this->payload['event'] === 'ticket_resolvido'
            ? null
            : $this->createReplyAddress((int) $this->payload['ticket_id']);
        $this->payload['reply_enabled'] = (bool) $replyTo;
        Mail::to($delivery->user->email)->send(new TicketEventMail($this->payload, $replyTo));
        $delivery->update(['status' => 'delivered', 'last_error' => null, 'delivered_at' => now()]);
    }

    public function failed(Throwable $exception): void
    {
        NotificationDelivery::whereKey($this->deliveryId)->update([
            'status' => 'failed',
            'last_error' => Str::limit($exception->getMessage(), 1000),
        ]);
    }

    private function createReplyAddress(int $ticketId): ?string
    {
        $domain = config('ticket_notifications.inbound_domain');
        if (!$domain) {
            return null;
        }

        $token = bin2hex(random_bytes(24));
        EmailReplyToken::create([
            'ticket_id' => $ticketId,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addDays(config('ticket_notifications.reply_token_lifetime_days')),
        ]);

        return config('ticket_notifications.reply_local_part') . "+{$ticketId}.{$token}@{$domain}";
    }
}
