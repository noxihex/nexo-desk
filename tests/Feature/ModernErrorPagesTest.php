<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ModernErrorPagesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_not_found_page_uses_the_modern_public_layout(): void
    {
        $this->get('/pagina-que-nao-existe')
            ->assertNotFound()
            ->assertSee('Página não encontrada')
            ->assertSee('Voltar para o login')
            ->assertSee('favicon.ico', false)
            ->assertDontSee('adminlte', false)
            ->assertDontSee('errors::minimal', false);
    }

    public function test_expired_page_keeps_its_status_and_does_not_force_a_redirect(): void
    {
        Route::get('/pagina-expirada-de-teste', fn () => abort(419));

        $this->get('/pagina-expirada-de-teste')
            ->assertStatus(419)
            ->assertSee('Página expirada')
            ->assertSee('Ir para o login')
            ->assertDontSee('setTimeout', false)
            ->assertDontSee('adminlte', false);
    }

    public function test_common_http_errors_have_specific_modern_pages(): void
    {
        $pages = [
            401 => 'Autenticação necessária',
            403 => 'Acesso não autorizado',
            429 => 'Muitas tentativas',
            500 => 'Erro interno do servidor',
            503 => 'Serviço indisponível',
        ];

        foreach ($pages as $status => $title) {
            $path = "/erro-{$status}-de-teste";
            Route::get($path, fn () => abort($status));

            $this->get($path)
                ->assertStatus($status)
                ->assertSee($title)
                ->assertSee((string) $status)
                ->assertDontSee('adminlte', false);
        }
    }

    public function test_unmapped_client_and_server_errors_use_modern_fallbacks(): void
    {
        Route::get('/erro-418-de-teste', fn () => abort(418));
        Route::get('/erro-502-de-teste', fn () => abort(502));

        $this->get('/erro-418-de-teste')
            ->assertStatus(418)
            ->assertSee('Não foi possível concluir')
            ->assertSee('418');

        $this->get('/erro-502-de-teste')
            ->assertStatus(502)
            ->assertSee('Erro no servidor')
            ->assertSee('502');
    }

    public function test_json_errors_keep_the_framework_content_negotiation(): void
    {
        $this->getJson('/pagina-json-que-nao-existe')
            ->assertNotFound()
            ->assertHeader('content-type', 'application/json')
            ->assertJsonStructure(['message']);
    }
}
