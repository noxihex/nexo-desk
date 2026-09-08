<?php

namespace App\Actions\Auth;

use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class SendPasswordResetLink
{
    public const DISABLED_MESSAGE = 'A recuperação automática de senha está temporariamente desativada. Entre em contato com o administrador.';

    public function handle(array $input): ?string
    {
        if (! config('auth.password_reset_enabled')) {
            return null;
        }

        $data = Validator::make($input, ['email' => 'required|email'])->validate();

        return Password::broker()->sendResetLink($data);
    }
}
