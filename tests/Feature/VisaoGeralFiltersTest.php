<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class VisaoGeralFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_filter_overview_tickets_by_company(): void
    {
        $administrator = $this->userWithRole('administrador');
        $selectedCompany = $this->createCompany('Empresa selecionada', '11111111000111');
        $otherCompany = $this->createCompany('Outra empresa', '22222222000122');

        $selectedTicket = $this->createTicket($selectedCompany, $administrator, 'Ticket da empresa selecionada');
        $this->createTicket($otherCompany, $administrator, 'Ticket de outra empresa');

        $response = $this->actingAs($administrator)
            ->get(route('home', ['empresa' => $selectedCompany->id]));

        $response->assertOk()
            ->assertViewHas('ticketsAbertos', function ($tickets) use ($selectedTicket) {
                return $tickets->pluck('id')->all() === [$selectedTicket->id];
            })
            ->assertSee('name="empresa"', false)
            ->assertSee('Empresa selecionada');
    }

    public function test_analyst_filter_only_lists_active_staff(): void
    {
        $administrator = $this->userWithRole('administrador');
        $activeAnalyst = $this->userWithRole('analista', ['name' => 'Analista ativo', 'status' => true]);
        $inactiveAnalyst = $this->userWithRole('analista', ['name' => 'Analista inativo', 'status' => false]);

        $response = $this->actingAs($administrator)->get(route('home'));

        $response->assertOk()
            ->assertViewHas('analistas', function ($analistas) use ($activeAnalyst, $inactiveAnalyst) {
                return $analistas->contains($activeAnalyst)
                    && ! $analistas->contains($inactiveAnalyst);
            });
    }

    private function userWithRole(string $role, array $attributes = []): User
    {
        Role::findOrCreate($role, 'web');
        $user = User::factory()->create(array_merge(['status' => true], $attributes));
        $user->assignRole($role);

        return $user;
    }

    private function createCompany(string $name, string $cnpj): Empresa
    {
        return Empresa::create([
            'nome' => $name,
            'cnpj' => $cnpj,
        ]);
    }

    private function createTicket(Empresa $company, User $owner, string $subject): Ticket
    {
        return Ticket::create([
            'assunto' => $subject,
            'descricao' => 'Descrição do ticket',
            'user_id' => $owner->id,
            'empresa_id' => $company->id,
            'status' => 'aberto',
        ]);
    }
}
