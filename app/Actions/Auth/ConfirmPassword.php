<?php

namespace App\Actions\Auth;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ConfirmPassword
{
    public function handle(array $input): void
    {
        if (! Auth::guard('web')->check()) {
            throw new AuthenticationException;
        }

        Validator::make($input, ['password' => 'required|current_password:web'])->validate();
        session()->put('auth.password_confirmed_at', time());
    }
}
