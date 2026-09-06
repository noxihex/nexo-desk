<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class MessageAttachment extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable;

    protected $fillable = ['mensagem_id', 'file_path', 'disk'];

    public function mensagem()
    {
        return $this->belongsTo(Mensagem::class);
    }
}
