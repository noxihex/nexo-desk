<?php

namespace App\Http\Resources\Api\V2;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'status' => $this->status,
            'empresa_id' => $this->empresa_id,
            'setor_id' => $this->setor_id,
            'grupo_id' => $this->grupo_id,
            'roles' => $this->whenLoaded('roles', function () {
                return $this->roles->pluck('name')->values();
            }),
        ];
    }
}
