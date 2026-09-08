<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ApiRouteContractTest extends TestCase
{
    public function test_legacy_ticket_routes_remain_registered()
    {
        $this->assertRoutes([
            'GET api/tickets', 'GET api/tickets/search', 'GET api/tickets/{id}',
            'POST api/tickets', 'POST api/tickets/{id}/finalizar',
            'POST api/tickets/{id}/messages', 'PATCH api/tickets/{id}/status',
            'POST api/tickets/{id}/assumir', 'GET api/user',
        ]);
    }

    public function test_all_v2_routes_are_registered()
    {
        $this->assertRoutes([
            'GET api/v2/tickets', 'GET api/v2/tickets/search', 'GET api/v2/tickets/{id}',
            'POST api/v2/tickets', 'POST api/v2/tickets/{id}/finalizar',
            'POST api/v2/tickets/{id}/messages', 'PATCH api/v2/tickets/{id}/status',
            'POST api/v2/tickets/{id}/assumir', 'POST api/v2/tickets/{id}/transferir',
            'GET api/v2/tickets/{id}/timeline',
            'POST api/v2/tickets/{ticket}/followers/me',
            'DELETE api/v2/tickets/{ticket}/followers/me',
            'GET api/v2/tickets/{ticket}/messages/attachments/{attachment}',
            'GET api/v2/usuarios',
            'GET api/v2/empresas', 'GET api/v2/setores', 'GET api/v2/categorias',
            'GET api/v2/grupos', 'GET api/v2/me',
        ]);
    }

    public function test_prazo_endpoint_is_removed()
    {
        $this->patchJson('/api/v2/tickets/1/prazo', ['prazo' => null])->assertNotFound();
    }

    public function test_named_routes_are_unique(): void
    {
        $duplicates = collect(Route::getRoutes())
            ->filter(fn ($route) => $route->getName() !== null)
            ->groupBy(fn ($route) => $route->getName())
            ->filter(fn ($routes) => $routes->count() > 1)
            ->keys()
            ->all();

        $this->assertSame([], $duplicates, 'Nomes de rota duplicados: '.implode(', ', $duplicates));
    }

    public function test_application_route_count_matches_the_upgrade_baseline(): void
    {
        $applicationRoutes = collect(Route::getRoutes())
            ->reject(fn ($route) => str_starts_with($route->uri(), '_ignition/')
                || str_starts_with($route->uri(), 'livewire-')
                || str_starts_with($route->uri(), 'flux/'));

        $this->assertCount(126, $applicationRoutes);
    }

    private function assertRoutes(array $expected): void
    {
        $actual = collect(Route::getRoutes())->flatMap(function ($route) {
            return collect($route->methods())->reject(function ($method) {
                return $method === 'HEAD';
            })->map(function ($method) use ($route) {
                return $method . ' ' . $route->uri();
            });
        });

        foreach ($expected as $route) {
            $this->assertTrue($actual->contains($route), "Rota ausente: {$route}");
        }
    }
}
