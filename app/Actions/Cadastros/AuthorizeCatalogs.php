<?php

namespace App\Actions\Cadastros;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;

class AuthorizeCatalogs
{
    public function handle(): void
    {
        $user = Auth::guard('web')->id() ? User::find(Auth::guard('web')->id()) : null;

        if (! $user) {
            throw new AuthenticationException;
        }

        abort_unless($user->status && $user->hasAnyRole(['supervisor', 'administrador']), 403);
    }
}
