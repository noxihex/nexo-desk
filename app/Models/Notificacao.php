<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notificacao extends Model
{
    use HasFactory;

    // Define o nome da tabela
    protected $table = 'notificacoes';

    protected $fillable = [
        'user_id',
        'titulo',
        'mensagem',
        'lida',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
