<?php

namespace App\Http\Resources\Api\V2;

use Illuminate\Http\Resources\Json\JsonResource;

class EmpresaResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id, 'nome' => $this->nome, 'razao_social' => $this->razao_social,
            'cnpj' => $this->cnpj, 'endereco' => $this->endereco, 'bairro' => $this->bairro,
            'cidade' => $this->cidade, 'estado' => $this->estado,
            'horas_contratadas' => $this->horas_contratadas,
        ];
    }
}
