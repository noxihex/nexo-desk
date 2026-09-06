<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationPreference extends Model
{
    public const EVENTS = ['novo_ticket', 'nova_mensagem', 'sla', 'ticket_resolvido'];
    public const CHANNELS = ['database', 'mail'];

    protected $guarded = [];

    protected $casts = [
        'novo_ticket_database' => 'boolean',
        'novo_ticket_mail' => 'boolean',
        'nova_mensagem_database' => 'boolean',
        'nova_mensagem_mail' => 'boolean',
        'sla_database' => 'boolean',
        'sla_mail' => 'boolean',
        'ticket_resolvido_database' => 'boolean',
        'ticket_resolvido_mail' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function allows(string $event, string $channel): bool
    {
        return (bool) $this->getAttribute($event . '_' . $channel);
    }
}
