<?php

namespace App\Support;

use App\Models\Ticket;
use App\Models\User;

class TicketStaffAccess
{
    public static function allows(User $user, Ticket $ticket): bool
    {
        return $user->isStaff() && $user->podeVisualizarTicket($ticket);
    }

    public static function abortUnlessAllowed(User $user, Ticket $ticket): void
    {
        abort_unless(self::allows($user, $ticket), 403, 'Acesso não autorizado.');
    }
}
