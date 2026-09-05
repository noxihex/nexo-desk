<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Grupo;
use App\Models\Setor;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiV2Test extends TestCase
{
    use RefreshDatabase;

    private function dadosBase(): array
    {
        $user = User::factory()->create(['status' => true]);
        $cliente = User::factory()->create(['status' => true]);
        $setor = Setor::create(['nome' => 'Suporte']);
        $grupo = Grupo::create(['nome' => 'Nível 1']);
        $categoria = Categoria::create([
            'nome' => 'Incidente', 'prioridade' => 'Normal', 'slatotal' => 120,
            'slaupdate' => 30, 'setor_id' => $setor->id,
        ]);
        Sanctum::actingAs($user);

        return compact('user', 'cliente', 'setor', 'grupo', 'categoria');
    }

    private function ticket(array $base, array $attributes = []): Ticket
    {
        return Ticket::create(array_merge([
            'assunto' => 'Ticket de teste',
            'descricao' => 'Descrição',
            'categoria_id' => $base['categoria']->id,
            'user_id' => $base['user']->id,
            'cliente_id' => $base['cliente']->id,
            'setor_id' => $base['setor']->id,
            'grupo_id' => $base['grupo']->id,
            'status' => 'aberto',
            'origem' => 'Integração legada',
        ], $attributes));
    }

    public function test_v2_requires_sanctum_authentication()
    {
        $this->getJson('/api/v2/tickets')->assertUnauthorized();
        $this->getJson('/api/v2/me')->assertUnauthorized();
    }

    public function test_legacy_and_v2_paginate_one_hundred_tickets_and_preserve_origem()
    {
        $base = $this->dadosBase();
        for ($i = 0; $i < 101; $i++) {
            $this->ticket($base, ['assunto' => "Ticket {$i}"]);
        }

        $this->getJson('/api/tickets')
            ->assertOk()->assertJsonPath('per_page', 100)
            ->assertJsonCount(100, 'data')
            ->assertJsonPath('data.0.origem', 'Integração legada');
        $this->getJson('/api/v2/tickets')
            ->assertOk()->assertJsonPath('meta.per_page', 100)
            ->assertJsonCount(100, 'data');
        $this->getJson('/api/tickets?page=2')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/v2/tickets?page=2')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/v2/tickets/search?status=aberto')
            ->assertOk()->assertJsonPath('meta.per_page', 100);
    }

    public function test_prazo_can_be_created_changed_removed_and_rejects_datetime()
    {
        $base = $this->dadosBase();
        $response = $this->postJson('/api/v2/tickets', [
            'assunto' => 'Com prazo', 'descricao' => 'Descrição',
            'categoria_id' => $base['categoria']->id, 'cliente_id' => $base['cliente']->id,
            'prazo' => '2026-07-31',
        ])->assertCreated()->assertJsonPath('data.prazo', '2026-07-31');
        $id = $response->json('data.id');

        $this->patchJson("/api/v2/tickets/{$id}/prazo", ['prazo' => '2026-08-15'])
            ->assertOk()->assertJsonPath('data.prazo', '2026-08-15');
        $this->patchJson("/api/v2/tickets/{$id}/prazo", ['prazo' => null])
            ->assertOk()->assertJsonPath('data.prazo', null);
        $this->patchJson("/api/v2/tickets/{$id}/prazo", ['prazo' => '2026-08-15 12:00:00'])
            ->assertStatus(422)->assertJsonValidationErrors('prazo');
        $this->patchJson("/api/v2/tickets/{$id}/prazo", ['prazo' => '15/08/2026'])
            ->assertStatus(422)->assertJsonValidationErrors('prazo');
    }

    public function test_transfer_updates_audit_history_and_supports_omitted_or_null_analyst()
    {
        $base = $this->dadosBase();
        $analista = User::factory()->create(['status' => true]);
        $novoSetor = Setor::create(['nome' => 'Infraestrutura']);
        $novoGrupo = Grupo::create(['nome' => 'Nível 2']);
        $ticket = $this->ticket($base, ['atribuido_ao_analista_id' => $analista->id]);

        $this->postJson("/api/v2/tickets/{$ticket->id}/transferir", [
            'setor_id' => $novoSetor->id, 'grupo_id' => $novoGrupo->id,
        ])->assertOk()->assertJsonPath('data.atribuido_ao_analista_id', $analista->id);
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id, 'transferido_por_usuario_id' => $base['user']->id,
            'atribuido_ao_analista_id' => $analista->id,
        ]);
        $this->assertDatabaseHas('mensagens', ['ticket_id' => $ticket->id, 'user_id' => $base['user']->id]);

        $this->postJson("/api/v2/tickets/{$ticket->id}/transferir", [
            'setor_id' => $novoSetor->id, 'grupo_id' => $novoGrupo->id, 'analista_id' => null,
        ])->assertOk()->assertJsonPath('data.atribuido_ao_analista_id', null);
    }

    public function test_transfer_requires_sector_and_group_and_rejects_invalid_ids()
    {
        $base = $this->dadosBase();
        $ticket = $this->ticket($base);

        $this->postJson("/api/v2/tickets/{$ticket->id}/transferir", [])
            ->assertStatus(422)->assertJsonValidationErrors(['setor_id', 'grupo_id']);
        $this->postJson("/api/v2/tickets/{$ticket->id}/transferir", [
            'setor_id' => 999999, 'grupo_id' => 999999, 'analista_id' => 999999,
        ])->assertStatus(422)->assertJsonValidationErrors(['setor_id', 'grupo_id', 'analista_id']);
    }

    public function test_me_and_users_do_not_expose_sensitive_fields()
    {
        $base = $this->dadosBase();
        $me = $this->getJson('/api/v2/me')->assertOk()->json('data');
        $users = $this->getJson('/api/v2/usuarios')->assertOk()
            ->assertJsonPath('meta.per_page', 100)->json('data');
        $this->assertNotEmpty($users);
        foreach (array_merge([$me], $users) as $user) {
            $this->assertIsArray($user);
            foreach (['password', 'remember_token', 'access_token', 'tokens'] as $field) {
                $this->assertArrayNotHasKey($field, $user);
            }
        }
        $this->getJson('/api/v2/categorias')->assertOk()
            ->assertJsonPath('meta.per_page', 100)
            ->assertJsonPath('data.0.setor_id', $base['setor']->id);
        $this->getJson('/api/v2/grupos')->assertOk()->assertJsonPath('meta.per_page', 100);
    }

    public function test_v2_replicates_message_status_assume_finalize_and_detail_operations()
    {
        $base = $this->dadosBase();
        $analista = User::factory()->create(['status' => true, 'grupo_id' => $base['grupo']->id]);
        $ticket = $this->ticket($base);

        $this->getJson("/api/v2/tickets/{$ticket->id}")->assertOk()->assertJsonPath('data.id', $ticket->id);
        $this->postJson("/api/v2/tickets/{$ticket->id}/messages", ['descricao' => 'Acompanhamento'])
            ->assertCreated()->assertJsonPath('data.descricao', 'Acompanhamento');
        $this->patchJson("/api/v2/tickets/{$ticket->id}/status", ['status' => 'pendente cliente'])
            ->assertOk()->assertJsonPath('data.status', 'pendente cliente');
        $this->postJson("/api/v2/tickets/{$ticket->id}/assumir", [
            'analista_id' => $analista->id,
            'setor_id' => $base['setor']->id,
            'categoria_id' => $base['categoria']->id,
        ])->assertOk()->assertJsonPath('data.atribuido_ao_analista_id', $analista->id);
        $this->postJson("/api/v2/tickets/{$ticket->id}/finalizar", [
            'descricao_fechamento' => 'Resolvido', 'horas' => 0, 'minutos' => 10,
        ])->assertOk();
        $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'status' => 'fechado']);
    }
}
