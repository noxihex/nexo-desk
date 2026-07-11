<?php

namespace App\Http\Resources\Api\V2;

use Illuminate\Http\Resources\Json\JsonResource;

class SetorResource extends JsonResource
{
    public function toArray($request)
    {
        return ['id' => $this->id, 'nome' => $this->nome];
    }
}
