<?php

namespace App\Actions\Tickets;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;

class AuthorizeTicketLists
{
    public function handle(): User
    {
        $user = Auth::guard('web')->id() ? User::find(Auth::guard('web')->id()) : null;

        if (! $user) {
            throw new AuthenticationException;
        }

        abort_unless($user->status && $user->hasAnyRole(['analista', 'supervisor', 'administrador']), 403);

        return $user;
    }
}
