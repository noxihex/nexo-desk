<?php

namespace App\Actions\Tickets;

use App\Models\Mensagem;
use App\Models\Ticket;
use App\Support\ElapsedTime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class FinalizeClientTicket
{
    public function suggestedMinutes(Ticket $ticket): int
    {
        if (! $ticket->categoria) {
            throw ValidationException::withMessages([
                'categoria' => 'O ticket precisa estar vinculado a uma categoria para ser finalizado.',
            ]);
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
        [$user] = $this->authorize($id);
        $data = Validator::make($input, [
            'descricao_fechamento' => 'required|string',
        ])->validate();

        return DB::transaction(function () use ($id, $user, $data) {
            [, $authorizedTicket] = $this->authorize($id);
            $ticket = Ticket::lockForUpdate()->findOrFail($authorizedTicket->id);
            abort_unless($ticket->status !== 'fechado', 422, 'Este ticket já foi finalizado.');
            $minutes = $this->suggestedMinutes($ticket);
            $ticket->fill([
                'status' => 'fechado',
                'horas_gastas' => $minutes,
                'descricao_final' => $data['descricao_fechamento'],
                'finalizado_por_usuario_id' => $ticket->atribuido_ao_analista_id,
                'data_hora_finalizado' => now(),
            ])->save();
            $ticket->mensagens()->create([
                'user_id' => $user->id,
                'descricao' => $user->name.' finalizou o ticket. Relato final: '.$ticket->descricao_final,
                'tipo' => Mensagem::TIPO_SISTEMA,
            ]);

            return $ticket;
        });
    }

    public function authorize(int $id): array
    {
        [$user, $ticket] = app(AuthorizeClientTicketFlow::class)->ticket($id);
        abort_unless(
            $user->pode_finalizar_tickets_empresa
                && $user->empresa_id !== null
                && (int) $ticket->empresa_id === (int) $user->empresa_id,
            403
        );

        return [$user, $ticket];
    }
}
