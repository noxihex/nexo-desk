<?php

namespace App\Http\Resources\Api\V2;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoriaResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id, 'nome' => $this->nome, 'prioridade' => $this->prioridade,
            'slatotal' => $this->slatotal, 'slaupdate' => $this->slaupdate, 'setor_id' => $this->setor_id,
        ];
    }
}
