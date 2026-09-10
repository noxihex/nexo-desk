<?php

namespace App\Actions\Tickets;

use App\Models\Mensagem;
use App\Models\Ticket;
use App\Models\User;
use App\Rules\CategoriaPertenceAoSetor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TransferTicket
{
    public function handle(int $id, array $input): Ticket
    {
        $user = app(AuthorizeTicketFlow::class)->staff();
        Ticket::findOrFail($id);
        $data = Validator::make($input, [
            'setor' => 'required|exists:setores,id',
            'categoria' => ['required', 'exists:categorias,id', new CategoriaPertenceAoSetor($input['setor'] ?? null)],
            'analista' => ['nullable', 'exists:users,id', function ($attribute, $value, $fail) use ($input) {
                if ($value && ! User::whereHas('roles', fn ($query) => $query->whereIn('name', ['analista', 'supervisor', 'administrador']))->where('status', true)
                    ->where('setor_id', $input['setor'] ?? null)->whereKey($value)->exists()) {
                    $fail('O analista selecionado deve estar ativo e pertencer ao setor escolhido.');
                }
            }],
        ])->validate();

        return DB::transaction(function () use ($id, $user, $data) {
            $ticket = Ticket::with('setor')->lockForUpdate()->findOrFail($id);
            $ticket->fill([
                'setor_id' => $data['setor'],
                'categoria_id' => $data['categoria'],
                'atribuido_ao_analista_id' => $data['analista'] ?? null,
                'transferido_por_usuario_id' => $user->id,
                'data_hora_transferido' => now(),
            ])->save();
            $analyst = ! empty($data['analista']) ? User::find($data['analista']) : null;
            $ticket->load('setor');
            Mensagem::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'descricao' => $user->name.' transferiu o ticket para '.($analyst?->name ?? $ticket->setor?->nome ?? 'sem setor'),
                'tipo' => Mensagem::TIPO_SISTEMA,
            ]);

            return $ticket;
        });
    }
}
