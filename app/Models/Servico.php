<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// 1. Importe a interface de auditoria
use OwenIt\Auditing\Contracts\Auditable;

// 2. Faça a classe implementar a interface
class Servico extends Model implements Auditable
{
    // 3. Inclua o trait de auditoria
    use HasFactory, \OwenIt\Auditing\Auditable;

    /**
     * Os atributos que podem ser atribuídos em massa.
     *
     * @var array
     */
    protected $fillable = [
        'empresa_id',
        'nome',
        'informacoes',
        'questionario',
    ];

    /**
     * A conversão de tipos de atributos.
     *
     * @var array
     */
    protected $casts = [
        'informacoes' => 'array',
        'questionario' => 'array',
    ];

    /**
     * Define o relacionamento: um Serviço pertence a uma Empresa.
     */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}