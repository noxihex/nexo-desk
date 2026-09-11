<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\ResetPassword;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class ResetPasswordController extends Controller
{
    public function showResetForm(Request $request)
    {
        return view('auth.passwords.reset', [
            'token' => $request->route('token'),
            'email' => $request->input('email'),
        ]);
    }

    public function reset(Request $request)
    {
        $status = app(ResetPassword::class)->handle($request->only('email', 'password', 'password_confirmation', 'token'));

        if ($status === Password::PASSWORD_RESET) {
            return $request->wantsJson()
                ? new JsonResponse(['message' => trans($status)])
                : redirect(RouteServiceProvider::HOME)->with('status', trans($status));
        }

        if ($request->wantsJson()) {
            throw ValidationException::withMessages(['email' => [trans($status)]]);
        }

        return back()->withInput($request->only('email'))
            ->withErrors(['email' => trans($status)]);
    }
}
