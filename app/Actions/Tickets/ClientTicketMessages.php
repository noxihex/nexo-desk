<?php

namespace App\Actions\Tickets;

use App\Models\Ticket;
use Illuminate\Pagination\LengthAwarePaginator;

class ClientTicketMessages
{
    public function handle(Ticket $ticket, int $limit = 3): LengthAwarePaginator
    {
        $query = $ticket->mensagens()->publicas()->with(['user.roles', 'attachments']);
        $total = (clone $query)->count();
        $messages = $query->orderByDesc('created_at')->orderByDesc('id')->limit($limit)->get();

        return new LengthAwarePaginator(
            $messages,
            $total,
            $limit,
            1,
            ['path' => request()->url(), 'pageName' => 'messages_page']
        );
    }
}
