<?php

namespace Tests\Feature;

use App\Livewire\Modern\Overview\StaffOverview;
use App\Models\Categoria;
use App\Models\Empresa;
use App\Models\Setor;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ModernStaffOverviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        foreach (['cliente', 'analista', 'supervisor', 'administrador'] as $role) {
            Role::findOrCreate($role, 'web');
        }
    }

    private function user(string $role, array $attributes = []): User
    {
        $user = User::factory()->create($attributes + ['status' => true]);
        $user->assignRole($role);

        return $user;
    }

    private function ticket(User $creator, array $attributes = []): Ticket
    {
        return Ticket::create($attributes + [
            'assunto' => 'Ticket da visão geral',
            'descricao' => 'Descrição para teste',
            'user_id' => $creator->id,
            'status' => 'aberto',
        ]);
    }

    public function test_staff_and_client_home_use_their_role_specific_modern_views(): void
    {
        $staff = $this->user('administrador');

        $this->actingAs($staff)->get(route('home'))
            ->assertOk()
            ->assertViewIs('home-staff')
            ->assertSeeInOrder(['Visão geral', 'Tickets'])
            ->assertDontSee('>Atendimento<', false)
            ->assertSee('Tema escuro')
            ->assertDontSee('jquery', false)
            ->assertDontSee('adminlte', false)
            ->assertDontSee('wire:navigate', false);

        $company = Empresa::create(['nome' => 'Empresa cliente', 'cnpj' => '12345678000190']);
        $client = $this->user('cliente', ['empresa_id' => $company->id]);

        $this->actingAs($client)->get(route('home'))
            ->assertOk()
            ->assertViewIs('home-client')
            ->assertSee('Meus tickets abertos')
            ->assertDontSee('adminlte', false);
    }

    public function test_home_rejects_users_without_a_supported_role_and_has_no_legacy_fallback(): void
    {
        $user = User::factory()->create(['status' => true]);

        $this->actingAs($user)->get(route('home'))->assertForbidden();

        $this->assertFileDoesNotExist(app_path('Http/Controllers/HomeController.php'));
        $this->assertFileDoesNotExist(resource_path('views/home.blade.php'));
    }

    public function test_staff_overview_link_is_the_first_navigation_option_across_modern_pages(): void
    {
        $staff = $this->user('administrador');
        $this->actingAs($staff);

        foreach (['tickets.index', 'empresas.index', 'relatorios.horas'] as $route) {
            $this->get(route($route))
                ->assertOk()
                ->assertSeeInOrder(['Visão geral', 'Tickets']);
        }

        $this->get(route('minhaconta.edit'))
            ->assertOk()
            ->assertSeeInOrder(['Visão geral', 'Minha conta']);
    }

    public function test_metrics_preserve_staff_counts_and_attention_ticket_ids(): void
    {
        $sector = Setor::create(['nome' => 'Suporte']);
        $category = Categoria::create([
            'nome' => 'Incidente',
            'slatotal' => 120,
            'slaupdate' => 1,
            'setor_id' => $sector->id,
        ]);
        $analyst = $this->user('analista', ['setor_id' => $sector->id]);
        $otherAnalyst = $this->user('analista', ['setor_id' => $sector->id]);
        $attention = $this->ticket($analyst, [
            'assunto' => 'Ticket vencido',
            'setor_id' => $sector->id,
            'categoria_id' => $category->id,
            'atribuido_ao_analista_id' => $analyst->id,
        ]);
        $attention->forceFill(['created_at' => now()->subMinutes(5)])->saveQuietly();
        $this->ticket($analyst, [
            'assunto' => 'Ticket sem responsável',
            'setor_id' => $sector->id,
            'atribuido_ao_analista_id' => null,
        ]);
        $this->ticket($analyst, [
            'assunto' => 'Ticket de outro responsável',
            'setor_id' => $sector->id,
            'atribuido_ao_analista_id' => $otherAnalyst->id,
        ]);
        $this->actingAs($analyst);

        Livewire::test(StaffOverview::class)
            ->assertViewHas('metrics', function (array $metrics) use ($attention) {
                return $metrics['mine'] === 1
                    && $metrics['sector'] === 3
                    && $metrics['unassigned'] === 1
                    && $metrics['attention'] === 1
                    && $metrics['attentionIds'] === [$attention->id];
            })
            ->assertSee('Meus tickets abertos')
            ->assertSee('Tickets não assumidos em meu setor')
            ->assertDontSee('Tickets por status');

        $this->get(route('tickets.atencao'))
            ->assertOk()
            ->assertExactJson([$attention->id]);
    }

    public function test_manager_board_filters_orders_and_preserves_return_to_home(): void
    {
        $sector = Setor::create(['nome' => 'Suporte']);
        $companyA = Empresa::create(['nome' => 'Empresa A', 'cnpj' => '12345678000190']);
        $companyB = Empresa::create(['nome' => 'Empresa B', 'cnpj' => '12345678000191']);
        $administrator = $this->user('administrador', ['setor_id' => $sector->id]);
        $analyst = $this->user('analista', ['setor_id' => $sector->id]);
        $older = $this->ticket($administrator, [
            'assunto' => 'Ticket antigo selecionado',
            'empresa_id' => $companyA->id,
            'setor_id' => $sector->id,
            'atribuido_ao_analista_id' => $analyst->id,
            'created_at' => now()->subDay(),
        ]);
        $newer = $this->ticket($administrator, [
            'assunto' => 'Ticket novo selecionado',
            'empresa_id' => $companyA->id,
            'setor_id' => $sector->id,
            'atribuido_ao_analista_id' => $analyst->id,
        ]);
        $this->ticket($administrator, [
            'assunto' => 'Ticket de outra empresa',
            'empresa_id' => $companyB->id,
            'setor_id' => $sector->id,
        ]);
        $this->actingAs($administrator);

        Livewire::test(StaffOverview::class)
            ->set('empresa', $companyA->id)
            ->set('analista', $analyst->id)
            ->set('order', 'asc')
            ->assertSeeInOrder([$older->assunto, $newer->assunto])
            ->assertDontSee('Ticket de outra empresa')
            ->assertSee('Pesquisar analista...')
            ->assertSee('Pesquisar setor...')
            ->assertSee('Pesquisar empresa...')
            ->assertDontSee('Aplicar filtros')
            ->assertSee('return_to=', false);
    }

    public function test_supervisor_sees_board_with_only_order_control(): void
    {
        $supervisor = $this->user('supervisor');
        $this->actingAs($supervisor);

        Livewire::test(StaffOverview::class)
            ->assertSee('Tickets por status')
            ->assertSee('Ordenar por')
            ->assertDontSee('A coluna de fechados exibe no máximo os 100 tickets mais recentes do filtro.')
            ->assertDontSee('Pesquisar analista...')
            ->assertDontSee('Pesquisar empresa...');
    }

    public function test_component_rechecks_active_staff_access(): void
    {
        $analyst = $this->user('analista');
        $this->actingAs($analyst);
        $component = Livewire::test(StaffOverview::class);

        $analyst->update(['status' => false]);
        $component->call('clearFilters')->assertForbidden();
    }
}
