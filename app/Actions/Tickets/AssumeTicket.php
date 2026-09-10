<?php

namespace App\Actions\Tickets;

use App\Models\Mensagem;
use App\Models\Ticket;
use App\Rules\CategoriaPertenceAoSetor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AssumeTicket
{
    public function handle(int $id, array $input): Ticket
    {
        $user = app(AuthorizeTicketFlow::class)->staff();
        Ticket::findOrFail($id);
        $data = Validator::make($input, [
            'setor' => 'required|exists:setores,id',
            'categoria' => ['required', 'exists:categorias,id', new CategoriaPertenceAoSetor($input['setor'] ?? null)],
        ])->validate();

        return DB::transaction(function () use ($id, $user, $data) {
            $ticket = Ticket::lockForUpdate()->findOrFail($id);
            $ticket->fill([
                'setor_id' => $data['setor'],
                'categoria_id' => $data['categoria'],
                'atribuido_ao_analista_id' => $user->id,
                'assumido_por_usuario_id' => $user->id,
                'data_hora_assumido' => now(),
                'status' => $ticket->status === 'aberto' ? 'pendente analista' : $ticket->status,
            ])->save();
            Mensagem::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'descricao' => $user->name.' assumiu o ticket.',
                'tipo' => Mensagem::TIPO_SISTEMA,
            ]);

            return $ticket;
        });
    }
}
