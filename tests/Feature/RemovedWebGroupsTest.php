<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RemovedWebGroupsTest extends TestCase
{
    public function test_group_management_is_not_exposed_on_the_web(): void
    {
        $routes = collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($route) => in_array('web', $route->middleware(), true))
            ->map(fn ($route) => $route->uri())
            ->values();

        $this->assertFalse($routes->contains(fn ($uri) => str_starts_with($uri, 'cadastros/grupos')));
        $this->assertStringNotContainsString("'text' => 'Grupos'", file_get_contents(config_path('adminlte.php')));
    }

    public function test_web_forms_and_views_do_not_expose_group_fields(): void
    {
        $views = [
            'cadastros/usuarios/create.blade.php',
            'cadastros/usuarios/edit.blade.php',
            'cadastros/usuarios/index.blade.php',
            'tickets/create.blade.php',
            'tickets/edit.blade.php',
            'tickets/index.blade.php',
            'tickets/show.blade.php',
            'home.blade.php',
        ];

        foreach ($views as $view) {
            $contents = file_get_contents(resource_path("views/{$view}"));
            $this->assertStringNotContainsString('grupo_id', $contents, $view);
            $this->assertDoesNotMatchRegularExpression('/(?:Grupo|grupo|grupos)\s*:/', $contents, $view);
        }
    }

    public function test_web_updates_preserve_historical_group_values_and_sector_drives_the_queue(): void
    {
        $users = file_get_contents(app_path('Http/Controllers/UserController.php'));
        $tickets = file_get_contents(app_path('Http/Controllers/TicketController.php'));
        $dashboard = file_get_contents(app_path('Http/Controllers/VisaoGeralController.php'));

        $this->assertStringNotContainsString("'grupo_id' => \$request->grupo_id", $users);
        $this->assertStringNotContainsString("'grupo_id' => \$request->grupo_id", $tickets);
        $this->assertStringNotContainsString("\$ticket->grupo_id = \$user->grupo_id", $tickets);
        $this->assertStringContainsString("Ticket::where('setor_id', \$user->setor_id)", $dashboard);
        $this->assertStringContainsString("->whereNull('atribuido_ao_analista_id')", $dashboard);
    }

    public function test_v2_group_contract_remains_available(): void
    {
        $apiRoutes = file_get_contents(base_path('routes/api.php'));
        $apiTickets = file_get_contents(app_path('Http/Controllers/Api/V2/TicketController.php'));

        $this->assertStringContainsString("Route::get('/grupos'", $apiRoutes);
        $this->assertStringContainsString("'grupo_id' => 'nullable|exists:grupos,id'", $apiTickets);
        $this->assertStringContainsString("'grupo_id' => 'required|exists:grupos,id'", $apiTickets);
    }
}
