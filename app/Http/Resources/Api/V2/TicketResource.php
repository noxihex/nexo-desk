<?php

namespace App\Http\Resources\Api\V2;

use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'assunto' => $this->assunto,
            'descricao' => $this->descricao,
            'categoria_id' => $this->categoria_id,
            'user_id' => $this->user_id,
            'cliente_id' => $this->cliente_id,
            'empresa_id' => $this->empresa_id,
            'status' => $this->status,
            'grupo_id' => $this->grupo_id,
            'setor_id' => $this->setor_id,
            'atribuido_ao_analista_id' => $this->atribuido_ao_analista_id,
            'horas_gastas' => $this->horas_gastas,
            'descricao_final' => $this->descricao_final,
            'origem' => $this->origem,
            'assumido_por_usuario_id' => $this->assumido_por_usuario_id,
            'data_hora_assumido' => $this->data_hora_assumido,
            'transferido_por_usuario_id' => $this->transferido_por_usuario_id,
            'data_hora_transferido' => $this->data_hora_transferido,
            'finalizado_por_usuario_id' => $this->finalizado_por_usuario_id,
            'data_hora_finalizado' => $this->data_hora_finalizado,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'categoria' => $this->whenLoaded('categoria'),
            'cliente' => $this->whenLoaded('cliente'),
            'empresa' => $this->whenLoaded('empresa'),
            'grupo' => $this->whenLoaded('grupo'),
            'setor' => $this->whenLoaded('setor'),
            'analista' => $this->whenLoaded('analista'),
            'mensagens' => $this->whenLoaded('mensagens'),
            'attachments' => $this->whenLoaded('attachments'),
        ];
    }
}
