<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Categoria extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'nome',
        'prioridade',
        'slatotal',
        'slaupdate',
        'setor_id', // Permitir atribuição em massa para este campo
    ];

    // Relacionamento com Setor
    public function setor()
    {
        return $this->belongsTo(Setor::class);
    }

    public function setores()
    {
        return $this->belongsToMany(Setor::class, 'categoria_setor')
            ->orderBy('setores.nome');
    }
}
