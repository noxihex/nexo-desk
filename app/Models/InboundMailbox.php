<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InboundMailbox extends Model
{
    protected $fillable = ['address', 'setor_id', 'active'];

    protected $casts = ['active' => 'boolean'];

    public function setor()
    {
        return $this->belongsTo(Setor::class);
    }
}
