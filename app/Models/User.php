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
     * O usuário pertence a uma empresa.
     */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
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
        'empresa_id',
        'status',
    ];


}
