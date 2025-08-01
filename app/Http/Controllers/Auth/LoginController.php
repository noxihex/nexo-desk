<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | Este controlador lida com a autenticação de usuários para o aplicativo e
    | redirecionando-os para sua tela inicial. O controlador usa um trait
    | para fornecer convenientemente sua funcionalidade ao seu aplicativo.
    |
    */

    use AuthenticatesUsers;

    /**
     * Onde redirecionar os usuários após o login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Cria uma nova instância do controlador.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Sobrescreve as credenciais para adicionar a verificação de status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        // Adiciona 'status' ao array de credenciais
        return array_merge($request->only($this->username(), 'password'), ['status' => 1]);
    }

    /**
     * Sobrescreve a resposta de login falho para lidar com usuários inativos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        // Tenta encontrar o usuário pelo email (ou nome de usuário)
        $user = \App\Models\User::where($this->username(), $request->{$this->username()})->first();

        // Se o usuário existir e estiver inativo, retorna uma mensagem personalizada
        if ($user && $user->status == 0) {
            throw ValidationException::withMessages([
                $this->username() => ['Sua conta está inativa.'],
            ]);
        }

        // Caso contrário, retorna a mensagem de erro padrão
        throw ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
        ]);
    }
}
