<?php

namespace App\Actions\Tickets;

use App\Models\Mensagem;
use App\Services\TicketMessageService;
use App\Support\AttachmentRules;
use Illuminate\Support\Facades\Validator;

class ReplyToClientTicket
{
    public function handle(int $id, array $input, array $files = []): Mensagem
    {
        [$user, $ticket] = app(AuthorizeClientTicketFlow::class)->ticket($id);
        $payload = $input + ['attachments' => $files];
        $data = Validator::make($payload, [
            'descricao' => 'nullable|string|required_without:attachments',
        ] + AttachmentRules::for('attachments'))->validate();

        return app(TicketMessageService::class)->create($ticket, $user, [
            'descricao' => $data['descricao'] ?? null,
            'tipo' => Mensagem::TIPO_PUBLICA,
            'status' => 'pendente analista',
        ], $files);
    }
}
