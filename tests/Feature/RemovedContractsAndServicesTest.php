<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RemovedContractsAndServicesTest extends TestCase
{
    public function test_contract_and_service_management_routes_are_not_registered(): void
    {
        $this->assertFalse(Route::has('contratos.index'));
        $this->assertFalse(Route::has('contratos.store'));
        $this->assertFalse(Route::has('servicos.create'));
        $this->assertFalse(Route::has('servicos.store'));
        $this->assertFalse(Route::has('servicos.update'));
        $this->assertFalse(Route::has('servicos.destroy'));
        $this->assertFalse(Route::has('servicos.questionario'));
    }

    public function test_company_forms_do_not_offer_contracts_or_services(): void
    {
        $create = file_get_contents(resource_path('views/cadastros/empresas/create.blade.php'));
        $edit = file_get_contents(resource_path('views/cadastros/empresas/edit.blade.php'));

        $this->assertStringNotContainsString('name="contratos[]"', $create);
        $this->assertStringNotContainsString('name="contratos[]"', $edit);
        $this->assertStringNotContainsString("route('servicos.", $edit);
    }

    public function test_client_ticket_form_does_not_offer_services(): void
    {
        $view = file_get_contents(resource_path('views/tickets/cliente/create.blade.php'));

        $this->assertStringNotContainsString('name="servico_id"', $view);
        $this->assertStringNotContainsString('questionario_respostas', $view);
    }
}
