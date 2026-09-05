<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Bootstrap\LoadConfiguration;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        if ($app->configurationIsCached()) {
            throw new \RuntimeException('Execute php artisan config:clear antes dos testes.');
        }

        // Nunca reutiliza o banco ou a URL de conexão do .env da aplicação.
        $app->afterBootstrapping(LoadConfiguration::class, function ($app) {
            $app['config']->set('database.default', 'mysql');
            $app['config']->set('database.connections.mysql', array_merge(
                $app['config']->get('database.connections.mysql'),
                [
                    'url' => null,
                    'host' => env('TEST_DB_HOST', '127.0.0.1'),
                    'port' => env('TEST_DB_PORT', '3306'),
                    'database' => 'nexodesk_testing',
                    'username' => env('TEST_DB_USERNAME', 'root'),
                    'password' => env('TEST_DB_PASSWORD', ''),
                    'unix_socket' => '',
                ]
            ));
        });

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
