<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\SendPasswordResetLink;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;

    public function sendResetLinkEmail(Request $request)
    {
        $status = app(SendPasswordResetLink::class)->handle($request->only('email'));

        if ($status === null) {
            return back()->withInput($request->only('email'))
                ->with('error', SendPasswordResetLink::DISABLED_MESSAGE);
        }

        return $status === Password::RESET_LINK_SENT
            ? $this->sendResetLinkResponse($request, $status)
            : $this->sendResetLinkFailedResponse($request, $status);
    }
}
