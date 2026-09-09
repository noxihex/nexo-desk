<?php

namespace App\Actions\Tickets;

use App\Models\Ticket;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DeleteTicket
{
    public function handle(int $id): void
    {
        $user = app(AuthorizeTicketLists::class)->handle();
        abort_unless($user->hasRole('administrador'), 403);

        $paths = DB::transaction(function () use ($id) {
            $ticket = Ticket::with('attachments')->lockForUpdate()->findOrFail($id);
            $paths = $ticket->attachments->pluck('file_path')->filter()->all();
            $ticket->delete();

            return $paths;
        });

        Storage::disk('public')->delete($paths);
    }
}
