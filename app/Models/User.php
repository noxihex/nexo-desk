<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use OwenIt\Auditing\Contracts\Auditable;

class User extends Authenticatable implements Auditable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, \OwenIt\Auditing\Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'grupo_id',
        'setor_id',
        'pode_ver_tickets_outros_setores',
        'empresa_id',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'status' => 'boolean',
        'pode_ver_tickets_outros_setores' => 'boolean',
    ];

    /**
     * O usuário pertence a um grupo.
     */
    public function grupo()
    {
        return $this->belongsTo(Grupo::class);
    }

    /**
     * O usuário pertence a um setor.
     */
    public function setor()
    {
        return $this->belongsTo(Setor::class);
    }

    /**
     * Indica se a visualização de tickets deve ficar limitada ao setor do usuário.
     */
    public function deveRestringirTicketsAoSetor(): bool
    {
        $roles = $this->getRoleNames();

        return $roles->contains('analista')
            && ! $roles->contains(fn ($role) => in_array($role, ['supervisor', 'administrador'], true))
            && ! $this->pode_ver_tickets_outros_setores;
    }

    /**
     * Verifica se o usuário pode visualizar um ticket considerando seu setor.
     */
    public function podeVisualizarTicket(Ticket $ticket): bool
    {
        return ! $this->deveRestringirTicketsAoSetor()
            || $ticket->setor_id === $this->setor_id;
    }

    /**
     * O usuário pertence a uma empresa.
     */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function notificationPreference()
    {
        return $this->hasOne(NotificationPreference::class);
    }

    public function ticketsSeguidos()
    {
        return $this->belongsToMany(Ticket::class, 'ticket_seguidores')->withTimestamps();
    }

    public function isStaff(): bool
    {
        return (bool) $this->status && $this->hasAnyRole(['analista', 'supervisor', 'administrador']);
    }

    public function allowsNotification(string $event, string $channel): bool
    {
        $preference = $this->relationLoaded('notificationPreference')
            ? $this->notificationPreference
            : $this->notificationPreference()->first();

        return $preference ? $preference->allows($event, $channel) : true;
    }

    /**
     * Atributos para serem ignorados pela auditoria.
     *
     * @var array<int, string>
     */
    protected $auditExclude = [
        'password',        // Não auditamos alterações de senha
        'remember_token',  // Ignora o token de sessão
    ];

    /**
     * Campos auditáveis (opcional).
     *
     * Especifique aqui se quiser apenas auditar alguns campos específicos.
     *
     * @var array<int, string>
     */
    protected $auditInclude = [
        'name',
        'email',
        'grupo_id',
        'setor_id',
        'pode_ver_tickets_outros_setores',
        'empresa_id',
        'status',
    ];


}
