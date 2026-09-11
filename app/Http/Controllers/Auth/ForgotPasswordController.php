<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\SendPasswordResetLink;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $status = app(SendPasswordResetLink::class)->handle($request->only('email'));

        if ($status === null) {
            return back()->withInput($request->only('email'))
                ->with('error', SendPasswordResetLink::DISABLED_MESSAGE);
        }

        if ($status === Password::RESET_LINK_SENT) {
            return $request->wantsJson()
                ? new JsonResponse(['message' => trans($status)])
                : back()->with('status', trans($status));
        }

        if ($request->wantsJson()) {
            throw ValidationException::withMessages(['email' => [trans($status)]]);
        }

        return back()->withInput($request->only('email'))
            ->withErrors(['email' => trans($status)]);
    }
}
