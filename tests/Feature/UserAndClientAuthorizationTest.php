<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserAndClientAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_registration_is_disabled(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register')->assertNotFound();
    }

    public function test_supervisor_cannot_promote_self_to_administrator(): void
    {
        $supervisor = $this->userWithRole('supervisor');
        Role::findOrCreate('administrador', 'web');

        $this->actingAs($supervisor)
            ->put(route('usuarios.update', $supervisor), [
                'name' => $supervisor->name,
                'email' => $supervisor->email,
                'role' => 'administrador',
            ])
            ->assertForbidden();

        $this->assertTrue($supervisor->fresh()->hasRole('supervisor'));
        $this->assertFalse($supervisor->fresh()->hasRole('administrador'));
    }

    public function test_client_endpoints_reject_team_users(): void
    {
        $supervisor = $this->userWithRole('supervisor');
        $analyst = $this->userWithRole('analista');

        $this->actingAs($supervisor)
            ->get(route('clientes.edit', $analyst))
            ->assertNotFound();

        $this->actingAs($supervisor)
            ->post(route('clientes.deactivate', $analyst))
            ->assertNotFound();
    }

    public function test_team_endpoints_reject_clients(): void
    {
        $administrator = $this->userWithRole('administrador');
        $client = $this->userWithRole('cliente');

        $this->actingAs($administrator)
            ->get(route('usuarios.edit', $client))
            ->assertNotFound();
    }

    public function test_client_without_company_cannot_access_or_change_another_clients_ticket(): void
    {
        $client = $this->userWithRole('cliente', ['empresa_id' => null]);
        $otherClient = $this->userWithRole('cliente', ['empresa_id' => null]);
        $ticket = $this->ticketFor($otherClient);

        $this->actingAs($client)
            ->get(route('tickets.cliente.show', $ticket->id))
            ->assertNotFound();

        $this->actingAs($client)
            ->post(route('tickets.cliente.mensagens.store', $ticket->id), ['descricao' => 'Tentativa indevida'])
            ->assertNotFound();

        $this->actingAs($client)
            ->put(route('tickets.cliente.finalize', $ticket->id), [
                'descricao_fechamento' => 'Tentativa indevida',
                'horas' => 0,
                'minutos' => 1,
            ])
            ->assertNotFound();

        $this->actingAs($client)
            ->get(route('tickets.cliente.calcularHorasSugeridas', $ticket->id))
            ->assertNotFound();

        $this->assertSame('aberto', $ticket->fresh()->status);
        $this->assertDatabaseMissing('mensagens', ['ticket_id' => $ticket->id, 'user_id' => $client->id]);
    }

    private function userWithRole(string $role, array $attributes = []): User
    {
        Role::findOrCreate($role, 'web');
        $user = User::factory()->create(array_merge(['status' => true], $attributes));
        $user->assignRole($role);

        return $user;
    }

    private function ticketFor(User $client): Ticket
    {
        return Ticket::create([
            'assunto' => 'Ticket privado',
            'descricao' => 'Descrição',
            'user_id' => $client->id,
            'cliente_id' => $client->id,
            'empresa_id' => $client->empresa_id,
            'status' => 'aberto',
        ]);
    }
}
