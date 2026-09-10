<?php

namespace App\Actions\Tickets;

use App\Models\Mensagem;
use App\Models\Ticket;
use App\Support\ElapsedTime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class FinalizeTicket
{
    public function suggestedMinutes(Ticket $ticket): int
    {
        if (! $ticket->categoria) {
            throw ValidationException::withMessages(['categoria' => 'O ticket precisa estar vinculado a uma categoria para ser finalizado.']);
        }
        $total = 0;
        $reference = $ticket->created_at;
        foreach ($ticket->mensagens()->semInternas()->orderBy('created_at')->get() as $message) {
            $total += min(ElapsedTime::wholeMinutes($reference, $message->created_at), $ticket->categoria->slaupdate);
            $reference = $message->created_at;
        }

        return $total + min(ElapsedTime::wholeMinutes($reference, now()), $ticket->categoria->slaupdate);
    }

    public function handle(int $id, array $input): Ticket
    {
        $user = app(AuthorizeTicketFlow::class)->staff();
        $ticket = Ticket::findOrFail($id);
        if (! $ticket->categoria) {
            throw ValidationException::withMessages(['categoria' => 'O ticket precisa estar vinculado a uma categoria para ser finalizado.']);
        }
        $data = Validator::make($input, [
            'descricao_fechamento' => 'required|string',
            'horas' => 'required|integer|min:0',
            'minutos' => 'required|integer|min:0|max:59',
        ])->validate();

        return DB::transaction(function () use ($id, $user, $data) {
            $ticket = Ticket::lockForUpdate()->findOrFail($id);
            $ticket->fill([
                'status' => 'fechado',
                'horas_gastas' => ((int) $data['horas'] * 60) + (int) $data['minutos'],
                'descricao_final' => $data['descricao_fechamento'],
                'atribuido_ao_analista_id' => $user->id,
                'finalizado_por_usuario_id' => $user->id,
                'data_hora_finalizado' => now(),
            ])->save();
            Mensagem::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'descricao' => $user->name.' finalizou o ticket. Relato final: '.$ticket->descricao_final,
                'tipo' => Mensagem::TIPO_SISTEMA,
            ]);

            return $ticket;
        });
    }
}
