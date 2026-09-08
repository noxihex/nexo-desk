<?php

namespace App\Actions\Cadastros;

use App\Models\User;

class ManagePeople
{
    public function roles(): array
    {
        app(AuthorizeCatalogs::class)->handle();

        return User::findOrFail(auth()->id())->hasRole('administrador')
            ? ['analista', 'supervisor', 'administrador'] : ['analista'];
    }

    public function authorize(User $user, bool $contact): void
    {
        app(AuthorizeCatalogs::class)->handle();
        abort_unless($user->hasAnyRole($contact ? ['cliente'] : ['analista', 'supervisor', 'administrador']), 404);

        if (! $contact && ! in_array('administrador', $this->roles(), true)) {
            abort_if($user->hasAnyRole(['supervisor', 'administrador']), 403,
                'Apenas administradores podem gerenciar supervisores e administradores.');
        }
    }
}
