<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\ResetPassword;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;

class ResetPasswordController extends Controller
{
    use ResetsPasswords;

    protected $redirectTo = RouteServiceProvider::HOME;

    public function reset(Request $request)
    {
        $status = app(ResetPassword::class)->handle($request->only('email', 'password', 'password_confirmation', 'token'));

        return $status === \Illuminate\Support\Facades\Password::PASSWORD_RESET
            ? $this->sendResetResponse($request, $status)
            : $this->sendResetFailedResponse($request, $status);
    }
}
