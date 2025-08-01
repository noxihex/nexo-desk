<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Grupo extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable;

    protected $fillable = ['nome'];

    /**
     * Um grupo pode ter vários usuários.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
