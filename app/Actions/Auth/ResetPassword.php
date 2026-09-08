<?php

namespace App\Actions\Auth;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class ResetPassword
{
    public function handle(array $input): string
    {
        $data = Validator::make($input, [
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ])->validate();
        $data['password_confirmation'] = $input['password_confirmation'] ?? null;

        return Password::broker()->reset($data, function ($user, $password) {
            $user->password = Hash::make($password);
            $user->setRememberToken(Str::random(60));
            $user->save();
            event(new PasswordReset($user));
            Auth::guard()->login($user);
        });
    }
}
