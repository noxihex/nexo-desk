<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Empresa extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable;

    // Defina os campos que podem ser preenchidos (mass assignable)
    protected $fillable = [
        'nome',
        'cnpj',
        'endereco',
        'bairro',
        'cidade',
        'estado',
        'horas_contratadas',
        'razao_social'
    ];

    /**
     * Uma empresa pode estar associada a vários usuários (One-to-Many)
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

}
