<?php

namespace Tests\Feature;

use App\Livewire\Modern\Reports\Analyst;
use App\Livewire\Modern\Reports\Company;
use App\Models\Categoria;
use App\Models\Empresa;
use App\Models\Setor;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ModernReportsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function user(string $role = 'supervisor'): User
    {
        Role::findOrCreate($role, 'web');
        $user = User::factory()->create(['status' => true]);
        $user->assignRole($role);
        return $user;
    }

    private function company(): Empresa
    {
        return Empresa::create(['nome' => 'Empresa do relatório', 'cnpj' => '12345678000190', 'endereco' => 'Rua A', 'bairro' => 'Centro', 'cidade' => 'São Paulo', 'estado' => 'SP', 'horas_contratadas' => 40]);
    }

    private function ticket(array $attributes): Ticket
    {
        $ticket = new Ticket;
        $ticket->forceFill($attributes + ['assunto' => 'Ticket do relatório', 'descricao' => 'Teste', 'status' => 'aberto', 'created_at' => '2026-09-01 09:00:00'])->save();
        return $ticket;
    }

    public function test_initial_pages_render_without_legacy_assets_for_managers(): void
    {
        foreach (['supervisor', 'administrador'] as $role) {
            $this->actingAs($this->user($role));
            foreach (['horas', 'analista'] as $report) {
                $this->get(route('relatorios.'.$report))->assertOk()->assertSee('Selecione os filtros para começar')
                    ->assertSee('Por empresa')->assertSee('Por analista')->assertDontSee('chart.js', false)
                    ->assertDontSee('jquery', false)->assertDontSee('adminlte', false);
            }
        }
    }

    public function test_navigation_keeps_tickets_catalogs_and_reports_in_the_same_order(): void
    {
        $this->actingAs($this->user());

        foreach (['tickets.index', 'empresas.index', 'relatorios.horas'] as $route) {
            $html = $this->get(route($route))->assertOk()->getContent();

            $this->assertLessThan(strpos($html, 'Cadastros</p>'), strpos($html, 'Tickets</p>'));
            $this->assertLessThan(strpos($html, 'Relatórios</p>'), strpos($html, 'Cadastros</p>'));
        }
    }

    public function test_http_and_livewire_restrict_access_and_recheck_inactive_users(): void
    {
        $this->get(route('relatorios.horas'))->assertRedirect('/login');
        foreach (['cliente', 'analista'] as $role) {
            $this->actingAs($this->user($role));
            $this->get(route('relatorios.horas'))->assertForbidden();
            $this->get(route('relatorios.analista'))->assertForbidden();
        }
        $user = $this->user();
        $this->actingAs($user);
        $component = Livewire::test(Company::class);
        $user->update(['status' => false]);
        $component->call('applyFilters')->assertForbidden();
    }

    public function test_company_report_preserves_closed_filter_sector_hours_and_daily_series(): void
    {
        $user = $this->user();
        $this->actingAs($user);
        $company = $this->company();
        $sector = Setor::create(['nome' => 'Suporte']);
        $this->ticket(['user_id' => $user->id, 'empresa_id' => $company->id, 'setor_id' => $sector->id, 'status' => 'fechado', 'data_hora_finalizado' => '2026-09-01 11:00:00', 'horas_gastas' => 90]);
        $this->ticket(['user_id' => $user->id, 'empresa_id' => $company->id, 'setor_id' => $sector->id, 'horas_gastas' => 30, 'assunto' => 'Aberto no período']);
        $this->ticket(['user_id' => $user->id, 'empresa_id' => $company->id, 'status' => 'fechado', 'data_hora_finalizado' => '2026-09-01 11:00:00', 'horas_gastas' => 999]);
        $component = Livewire::test(Company::class)->set('data_inicio', '2026-09-01T00:00')->set('data_fim', '2026-09-01T23:59')
            ->set('empresa_id', (string) $company->id)->set('setor_id', (string) $sector->id)->call('applyFilters')->assertHasNoErrors()
            ->assertViewHas('tickets', fn ($tickets) => $tickets->count() === 1)->assertSee('1h 30m')
            ->assertViewHas('ticketsAbertosData', fn ($values) => $values[0] === 2)
            ->assertViewHas('ticketsFechadosData', fn ($values) => $values[0] === 1);
        $component->set('ignore_open_tickets', false)->call('applyFilters')->assertViewHas('tickets', fn ($tickets) => $tickets->count() === 2)->assertSee('2h 0m');
        $this->get(route('relatorios.horas', ['data_inicio' => '2026-09-01T00:00', 'data_fim' => '2026-09-01T23:59', 'empresa_id' => $company->id, 'setor_id' => $sector->id, 'ignore_open_tickets' => 0]))
            ->assertOk()->assertSee('Aberto no período')->assertSee('2h 0m');
    }

    public function test_analyst_report_counts_assignments_activities_and_positive_sla(): void
    {
        $manager = $this->user();
        $analyst = $this->user('analista');
        $this->actingAs($manager);
        $category = Categoria::create(['nome' => 'Suporte', 'prioridade' => 'Normal', 'slatotal' => 60, 'slaupdate' => 15]);
        $this->ticket(['user_id' => $analyst->id, 'atribuido_ao_analista_id' => $analyst->id, 'categoria_id' => $category->id,
            'status' => 'fechado', 'data_hora_finalizado' => '2026-09-01 10:30:00', 'finalizado_por_usuario_id' => $analyst->id,
            'transferido_por_usuario_id' => $analyst->id, 'data_hora_transferido' => '2026-09-01 09:15:00',
            'assumido_por_usuario_id' => $analyst->id, 'data_hora_assumido' => '2026-09-01 09:20:00']);
        $this->ticket(['user_id' => $manager->id, 'atribuido_ao_analista_id' => $analyst->id, 'status' => 'pendente cliente']);
        Livewire::test(Analyst::class)->set('usuario_id', (string) $analyst->id)->set('data_inicio', '2026-09-01T08:00')->set('data_fim', '2026-09-01T12:00')
            ->call('applyFilters')->assertHasNoErrors()->assertViewHas('totalTickets', 2)->assertViewHas('totalTicketsAbertos', 1)
            ->assertViewHas('totalTicketsFechados', 1)->assertSee('150%')->assertDontSee('-150%')
            ->assertViewHas('lineChartData', fn ($data) => max($data['created']) === 1 && max($data['assumed']) === 1 && max($data['transferred']) === 1 && max($data['finalized']) === 1);
        $this->get(route('relatorios.analista', ['usuario_id' => $analyst->id, 'data_inicio' => '2026-09-01T08:00', 'data_fim' => '2026-09-03T12:00']))
            ->assertOk()->assertSee('150%');
    }

    public function test_filters_validate_dates_references_and_empty_results(): void
    {
        $this->actingAs($this->user());
        $company = $this->company();
        $component = Livewire::test(Company::class)->set('empresa_id', '999999')->set('data_inicio', '2026-09-02T00:00')->set('data_fim', '2026-09-01T00:00');
        $component->call('applyFilters')->assertHasErrors(['empresa_id', 'data_fim']);
        $component->set('empresa_id', (string) $company->id)->set('data_fim', '2026-09-03T00:00')->call('applyFilters')->assertHasNoErrors()->assertSee('Nenhum ticket encontrado');
        $component->set('setor_id', '999999')->call('applyFilters')->assertHasErrors('setor_id');
        Livewire::test(Analyst::class)->set('usuario_id', '999999')->call('applyFilters')->assertHasErrors('usuario_id');
    }

    public function test_expired_session_cannot_generate_report(): void
    {
        $this->actingAs($this->user());
        $component = Livewire::test(Analyst::class);
        Auth::logout();
        $this->expectException(AuthenticationException::class);
        $component->call('applyFilters');
    }

    public function test_applied_filters_cannot_be_changed_directly(): void
    {
        $this->actingAs($this->user());
        $component = Livewire::test(Company::class);
        $this->expectException(CannotUpdateLockedPropertyException::class);
        $component->set('applied.empresa_id', 999999);
    }

    public function test_real_livewire_json_request_generates_company_report(): void
    {
        $user = $this->user();
        $this->actingAs($user);
        $company = $this->company();
        $html = $this->get(route('relatorios.horas'))->assertOk()->getContent();
        preg_match('/wire:snapshot="([^"]+)"/', $html, $matches);
        $this->postJson(app('livewire')->getUpdateUri(), ['components' => [[
            'snapshot' => html_entity_decode($matches[1], ENT_QUOTES),
            'updates' => ['empresa_id' => (string) $company->id, 'data_inicio' => '2026-09-01T00:00', 'data_fim' => '2026-09-02T00:00'],
            'calls' => [['method' => 'applyFilters', 'params' => []]],
        ]]], ['X-Livewire' => 'true'])->assertOk()->assertSee('Nenhum ticket encontrado');
    }
}
