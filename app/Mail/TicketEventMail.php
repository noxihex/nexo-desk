<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TicketEventMail extends Mailable
{
    use Queueable, SerializesModels;

    public $payload;
    private $replyAddress;

    public function __construct(array $payload, ?string $replyAddress)
    {
        $this->payload = $payload;
        $this->replyAddress = $replyAddress;
    }

    public function build()
    {
        $mail = $this->subject($this->payload['title'])
            ->markdown('mail.ticket-event');

        return $this->replyAddress ? $mail->replyTo($this->replyAddress) : $mail;
    }
}
