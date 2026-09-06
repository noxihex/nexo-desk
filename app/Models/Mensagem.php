<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Mensagem extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable;

    protected $table = 'mensagens'; // Define o nome correto da tabela

    public const TIPO_PUBLICA = 'publica';
    public const TIPO_INTERNA = 'interna';
    public const TIPO_SISTEMA = 'sistema';
    public const TIPOS = [self::TIPO_PUBLICA, self::TIPO_INTERNA, self::TIPO_SISTEMA];

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

    public function mencoes()
    {
        return $this->belongsToMany(User::class, 'mensagem_mencoes')->withTimestamps();
    }

    public function isPublica(): bool
    {
        return ($this->tipo ?: self::TIPO_PUBLICA) === self::TIPO_PUBLICA;
    }

    public function isInterna(): bool
    {
        return $this->tipo === self::TIPO_INTERNA;
    }

    public function scopePublicas($query)
    {
        return $query->where(function ($query) {
            $query->where('tipo', self::TIPO_PUBLICA)->orWhereNull('tipo');
        });
    }

    public function scopeSemInternas($query)
    {
        return $query->where(function ($query) {
            $query->where('tipo', '!=', self::TIPO_INTERNA)->orWhereNull('tipo');
        });
    }
}
