<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlaAlertOccurrence extends Model
{
    protected $guarded = [];

    protected $casts = ['reference_at' => 'datetime'];
}
