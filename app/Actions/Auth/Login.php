<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class Login
{
    use ThrottlesLogins;

    public function username(): string
    {
        return 'email';
    }

    public function handle(Request $request): void
    {
        $request->validate(['email' => 'required|string', 'password' => 'required|string']);

        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            $this->sendLockoutResponse($request);
        }

        if (! Auth::guard()->attempt(array_merge($request->only('email', 'password'), ['status' => 1]), $request->boolean('remember'))) {
            $this->incrementLoginAttempts($request);
            $user = User::where('email', $request->input('email'))->first();

            throw ValidationException::withMessages([
                'email' => $user && ! $user->status ? 'Sua conta está inativa.' : trans('auth.failed'),
            ]);
        }

        session()->put('auth.password_confirmed_at', time());
        session()->regenerate();
        $this->clearLoginAttempts($request);
    }
}
