<?php

namespace Tests\Feature;

use App\Actions\Overview\ClientOverview as ClientOverviewData;
use App\Livewire\Modern\Overview\ClientOverview;
use App\Models\Empresa;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ModernClientOverviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        foreach (['cliente', 'analista'] as $role) {
            Role::findOrCreate($role, 'web');
        }
    }

    private function client(?Empresa $company = null): User
    {
        $client = User::factory()->create([
            'empresa_id' => $company?->id,
            'status' => true,
        ]);
        $client->assignRole('cliente');

        return $client;
    }

    private function ticket(User $client, array $attributes = []): Ticket
    {
        return Ticket::create($attributes + [
            'assunto' => 'Ticket da visão geral do cliente',
            'descricao' => 'Descrição para teste',
            'user_id' => $client->id,
            'cliente_id' => $client->id,
            'empresa_id' => $client->empresa_id,
            'status' => 'aberto',
        ]);
    }

    public function test_client_home_uses_modern_layout_and_overview_is_first_navigation_link(): void
    {
        $company = Empresa::create(['nome' => 'Empresa cliente', 'cnpj' => '12345678000190']);
        $client = $this->client($company);

        $this->actingAs($client)->get(route('home'))
            ->assertOk()
            ->assertViewIs('home-client')
            ->assertSeeInOrder(['Visão geral', 'Tickets', 'Criar ticket', 'Meus tickets'])
            ->assertSee('Novo ticket')
            ->assertSee('Tema escuro')
            ->assertDontSee('jquery', false)
            ->assertDontSee('adminlte', false)
            ->assertDontSee('wire:navigate', false);

        $this->get(route('tickets.cliente.index'))
            ->assertOk()
            ->assertSeeInOrder(['Visão geral', 'Tickets', 'Criar ticket', 'Meus tickets']);
    }

    public function test_metrics_match_personal_and_company_ticket_scopes(): void
    {
        $company = Empresa::create(['nome' => 'Empresa A', 'cnpj' => '12345678000190']);
        $otherCompany = Empresa::create(['nome' => 'Empresa B', 'cnpj' => '12345678000191']);
        $client = $this->client($company);
        $coworker = $this->client($company);
        $external = $this->client($otherCompany);
        $analyst = User::factory()->create(['status' => true]);
        $analyst->assignRole('analista');

        $mine = $this->ticket($client, ['user_id' => $analyst->id]);
        $awaiting = $this->ticket($coworker, ['status' => 'pendente cliente']);
        $closed = $this->ticket($coworker, ['status' => 'fechado']);
        $outside = $this->ticket($external);
        $this->actingAs($client);

        Livewire::test(ClientOverview::class)
            ->assertViewHas('metrics', fn (array $metrics) => $metrics === [
                'mine' => 1,
                'company' => 2,
                'awaiting' => 1,
                'closed' => 1,
            ])
            ->assertViewHas('ticketIds', function (array $ids) use ($mine, $awaiting, $closed, $outside) {
                return $ids['mine'] === [$mine->id]
                    && collect($ids['company'])->sort()->values()->all() === collect([$mine->id, $awaiting->id])->sort()->values()->all()
                    && $ids['awaiting'] === [$awaiting->id]
                    && $ids['closed'] === [$closed->id]
                    && ! collect($ids)->flatten()->contains($outside->id);
            })
            ->assertSee('Tickets aguardando minha resposta')
            ->assertDontSee(route('tickets.cliente.show', $outside), false);

        $data = app(ClientOverviewData::class)->handle($client);
        $this->assertSame(1, $data['metrics']['mine']);
    }

    public function test_client_without_company_only_receives_personal_metrics(): void
    {
        $client = $this->client();
        $ticket = $this->ticket($client);
        $this->actingAs($client);

        Livewire::test(ClientOverview::class)
            ->assertViewHas('metrics', fn (array $metrics) => $metrics === [
                'mine' => 1,
                'company' => 0,
                'awaiting' => 0,
                'closed' => 0,
            ])
            ->assertViewHas('ticketIds', fn (array $ids) => $ids['mine'] === [$ticket->id])
            ->assertViewHas('companyAvailable', false);
    }

    public function test_component_rechecks_active_client_access(): void
    {
        $client = $this->client();
        $this->actingAs($client);
        $component = Livewire::test(ClientOverview::class);

        $client->update(['status' => false]);
        $component->call('$refresh')->assertForbidden();
    }
}
