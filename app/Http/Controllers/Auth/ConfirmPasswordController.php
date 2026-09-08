<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\ConfirmPassword;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\ConfirmsPasswords;
use Illuminate\Http\Request;

class ConfirmPasswordController extends Controller
{
    use ConfirmsPasswords;

    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function confirm(Request $request)
    {
        app(ConfirmPassword::class)->handle($request->only('password'));

        return $request->wantsJson()
            ? new \Illuminate\Http\JsonResponse([], 204)
            : redirect()->intended($this->redirectPath());
    }
}
