<?php

namespace App\Http\Resources\Api\V2;

use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'descricao' => $this->descricao,
            'tipo' => $this->tipo ?: \App\Models\Mensagem::TIPO_PUBLICA,
            'origem' => $this->origem,
            'user_id' => $this->user_id,
            'ticket_id' => $this->ticket_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'attachments' => $this->whenLoaded('attachments', function () {
                return $this->attachments->map(function ($attachment) {
                    $private = ($attachment->disk ?? 'public') === 'local';
                    return [
                        'id' => $attachment->id,
                        'name' => basename($attachment->file_path),
                        'private' => $private,
                        'url' => $private
                            ? url('/api/v2/tickets/' . $this->ticket_id . '/messages/attachments/' . $attachment->id)
                            : asset('storage/' . $attachment->file_path),
                    ];
                });
            }),
            'mencoes' => $this->whenLoaded('mencoes', function () {
                return $this->mencoes->map(fn ($user) => ['id' => $user->id, 'name' => $user->name]);
            }),
        ];
    }
}
