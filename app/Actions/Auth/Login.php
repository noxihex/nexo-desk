<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class Login
{
    private const MAX_ATTEMPTS = 5;

    private const DECAY_SECONDS = 60;

    public function handle(Request $request): void
    {
        $request->validate(['email' => 'required|string', 'password' => 'required|string']);

        if ($this->limiter()->tooManyAttempts($this->throttleKey($request), self::MAX_ATTEMPTS)) {
            event(new Lockout($request));

            $seconds = $this->limiter()->availableIn($this->throttleKey($request));

            throw ValidationException::withMessages([
                'email' => [trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ])],
            ])->status(Response::HTTP_TOO_MANY_REQUESTS);
        }

        if (! Auth::guard()->attempt(array_merge($request->only('email', 'password'), ['status' => 1]), $request->boolean('remember'))) {
            $this->limiter()->hit($this->throttleKey($request), self::DECAY_SECONDS);
            $user = User::where('email', $request->input('email'))->first();

            throw ValidationException::withMessages([
                'email' => $user && ! $user->status ? 'Sua conta está inativa.' : trans('auth.failed'),
            ]);
        }

        session()->put('auth.password_confirmed_at', time());
        session()->regenerate();
        $this->limiter()->clear($this->throttleKey($request));
    }

    private function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower((string) $request->input('email')).'|'.$request->ip());
    }

    private function limiter(): RateLimiter
    {
        return app(RateLimiter::class);
    }
}
