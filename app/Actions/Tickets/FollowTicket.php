<?php

namespace App\Actions\Tickets;

use App\Jobs\DispatchStaffTicketActivity;
use App\Models\Mensagem;
use Illuminate\Support\Facades\DB;

class FollowTicket
{
    public function handle(int $id, bool $following): bool
    {
        [$user, $ticket] = app(AuthorizeTicketFlow::class)->ticket($id);

        DB::transaction(function () use ($user, $ticket, $following) {
            $changed = $following
                ? ! empty($ticket->seguidores()->syncWithoutDetaching([$user->id])['attached'])
                : (bool) $ticket->seguidores()->detach($user->id);
            if (! $changed) {
                return;
            }
            $description = $user->name.($following ? ' começou a seguir o ticket.' : ' deixou de seguir o ticket.');
            $message = $ticket->mensagens()->create([
                'user_id' => $user->id,
                'descricao' => $description,
                'tipo' => Mensagem::TIPO_SISTEMA,
            ]);
            DispatchStaffTicketActivity::dispatch(
                'ticket-'.($following ? 'followed' : 'unfollowed').':'.$ticket->id.':'.$user->id.':'.$message->id,
                $ticket->id,
                $user->id,
                'followers',
                [],
                $description
            )->afterCommit();
        });

        return $following;
    }
}
