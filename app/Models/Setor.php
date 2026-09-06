<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Setor extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable;

    // Definindo explicitamente o nome da tabela correta
    protected $table = 'setores';

    // Permite o preenchimento das colunas específicas
    protected $fillable = ['nome'];

    /**
     * Um setor pode ter muitos usuários.
     * Definindo a relação de "Um para Muitos" (One-to-Many) entre Setor e User.
     */
    public function users()
    {
        return $this->hasMany(User::class); // Um Setor pode estar relacionado a vários usuários
    }

    public function categorias()
    {
        return $this->belongsToMany(Categoria::class, 'categoria_setor');
    }
}
