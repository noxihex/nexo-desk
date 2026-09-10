<?php

namespace App\Actions\Tickets;

use App\Models\Ticket;
use App\Models\User;
use App\Support\TicketStaffAccess;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;

class AuthorizeTicketFlow
{
    public function staff(): User
    {
        $id = Auth::guard('web')->id() ?: Auth::id();
        $user = $id ? User::find($id) : null;

        if (! $user) {
            throw new AuthenticationException;
        }

        abort_unless($user->isStaff(), 403);

        return $user;
    }

    public function ticket(int $id): array
    {
        $user = $this->staff();
        $ticket = Ticket::findOrFail($id);
        TicketStaffAccess::abortUnlessAllowed($user, $ticket);

        return [$user, $ticket];
    }
}
