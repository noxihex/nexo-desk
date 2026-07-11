<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails {
        sendResetLinkEmail as protected sendResetLinkEmailUsingTrait;
    }

    /**
     * Impede temporariamente o envio automático, sem remover o fluxo de
     * recuperação. Para reativá-lo, basta definir PASSWORD_RESET_ENABLED=true.
     */
    public function sendResetLinkEmail(Request $request)
    {
        if (! config('auth.password_reset_enabled')) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', 'A recuperação automática de senha está temporariamente desativada. Entre em contato com o administrador.');
        }
        return $this->sendResetLinkEmailUsingTrait($request);
    }
}
