<?php

namespace App\Http\Resources\Api\V2;

use Illuminate\Http\Resources\Json\JsonResource;

class MeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'status' => $this->status,
            'roles' => $this->roles->pluck('name')->values(),
            'permissions' => $this->getAllPermissions()->pluck('name')->values(),
            'empresa' => $this->empresa ? ['id' => $this->empresa->id, 'nome' => $this->empresa->nome] : null,
            'setor' => $this->setor ? ['id' => $this->setor->id, 'nome' => $this->setor->nome] : null,
            'grupo' => $this->grupo ? ['id' => $this->grupo->id, 'nome' => $this->grupo->nome] : null,
        ];
    }
}
