<?php

namespace App\Actions\Tickets;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;

class AuthorizeClientTicketFlow
{
    public function client(): User
    {
        $id = Auth::guard('web')->id() ?: Auth::id();
        $user = $id ? User::with('roles')->find($id) : null;

        if (! $user) {
            throw new AuthenticationException;
        }

        abort_unless($user->status && $user->hasRole('cliente'), 403);

        return $user;
    }

    public function ticket(int $id): array
    {
        $user = $this->client();
        $query = Ticket::whereKey($id);

        if ($user->empresa_id !== null) {
            $query->where('empresa_id', $user->empresa_id);
        } else {
            $query->where('cliente_id', $user->id)->whereNull('empresa_id');
        }

        return [$user, $query->firstOrFail()];
    }
}
