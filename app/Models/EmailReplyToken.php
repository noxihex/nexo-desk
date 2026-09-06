<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailReplyToken extends Model
{
    protected $guarded = [];

    protected $casts = ['expires_at' => 'datetime'];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
}
