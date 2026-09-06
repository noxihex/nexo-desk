<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationDelivery extends Model
{
    protected $guarded = [];

    protected $casts = ['delivered_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
