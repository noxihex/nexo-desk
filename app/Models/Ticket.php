<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Ticket extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'assunto',
        'descricao',
        'categoria_id',
        'user_id',
        'cliente_id',
        'empresa_id',
        'status',
        'grupo_id',
        'setor_id',
        'atribuido_ao_analista_id',
        'horas_gastas',
        'origem',
        'prazo',
        'assumido_por_usuario_id',
        'data_hora_assumido',
        'transferido_por_usuario_id',
        'data_hora_transferido',
        'finalizado_por_usuario_id',
        'data_hora_finalizado'
    ];

    protected $casts = [
        'prazo' => 'date:Y-m-d',
    ];

    /**
     * Define os campos a serem ignorados na auditoria.
     *
     * @var array<int, string>
     */
    protected $auditExclude = [
        'horas_gastas',
        'data_hora_assumido',
        'data_hora_transferido',
        'data_hora_finalizado'
    ];

    // Relacionamento com a categoria
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    // Relacionamento com o usuário que criou o ticket
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relacionamento com o cliente (usuário específico com permissão de cliente)
    public function cliente()
    {
        return $this->belongsTo(User::class, 'cliente_id');
    }

    // Relacionamento com a empresa
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    // Relacionamento com o grupo ao qual o ticket está atribuído
    public function grupo()
    {
        return $this->belongsTo(Grupo::class);
    }

    // Relacionamento com o setor ao qual o ticket está atribuído
    public function setor()
    {
        return $this->belongsTo(Setor::class);
    }

    // Relacionamento com o analista ao qual o ticket foi atribuído
    public function analista()
    {
        return $this->belongsTo(User::class, 'atribuido_ao_analista_id');
    }

    public function attachments()
    {
        return $this->hasMany(TicketAttachment::class);
    }

    public function mensagens()
    {
        return $this->hasMany(Mensagem::class);
    }
}
