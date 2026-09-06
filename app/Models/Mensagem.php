<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Mensagem extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable;

    protected $table = 'mensagens'; // Define o nome correto da tabela

    protected $fillable = ['descricao', 'tipo', 'origem', 'user_id', 'ticket_id'];

    // Relacionamento com o usuário
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relacionamento com o ticket
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    // Relacionamento com os anexos da mensagem
    public function attachments()
    {
        return $this->hasMany(MessageAttachment::class);
    }

    public function isPublica(): bool
    {
        return ($this->tipo ?: 'publica') === 'publica';
    }
}
