<?php

namespace App\Actions\Tickets;

use App\Models\Ticket;
use App\Models\User;

class ClientTicketListing
{
    public function handle(User $user, string $search = '', bool $viewCompanyTickets = false)
    {
        $query = Ticket::with(['user.roles', 'setor']);

        if ($user->empresa_id !== null) {
            $query->where('empresa_id', $user->empresa_id);
            if (! $viewCompanyTickets) {
                $query->where('cliente_id', $user->id);
            }
        } else {
            $query->where('cliente_id', $user->id)->whereNull('empresa_id');
        }

        if (trim($search) !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('id', 'like', '%'.trim($search).'%')
                    ->orWhere('assunto', 'like', '%'.trim($search).'%');
            });
        }

        return $query->orderByDesc('created_at')->paginate(10)->withQueryString();
    }
}
