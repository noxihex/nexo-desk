<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

        /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Throwable $exception)
    {
        // Verifica se o erro é do tipo 419 (Page Expired)
        if ($exception instanceof HttpException && $exception->getStatusCode() === 419) {
            // Redireciona para a página de login com mensagem de erro
            return redirect()->route('login')->with('error', 'Sua sessão expirou. Por favor, faça login novamente.');
        }

        // Verifica se o erro é do tipo 404 (Not Found)
        if ($exception instanceof HttpException && $exception->getStatusCode() === 404) {
            // Retorna a view personalizada para o erro 404
            return response()->view('errors.404', [], 404);
        }

        // Para outros erros, mantém o comportamento padrão
        return parent::render($request, $exception);
    }

}
