<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Setor;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CategoriaMultiplosSetoresTest extends TestCase
{
    use RefreshDatabase;

    public function test_supervisor_can_create_and_update_a_category_with_multiple_sectors()
    {
        $supervisor = $this->userWithRole('supervisor');
        $setorA = Setor::create(['nome' => 'Atendimento']);
        $setorB = Setor::create(['nome' => 'Financeiro']);
        $setorC = Setor::create(['nome' => 'Infraestrutura']);

        $this->actingAs($supervisor)->post(route('categorias.store'), [
            'nome' => 'Acesso',
            'prioridade' => 'Normal',
            'slatotal' => 120,
            'slaupdate' => 30,
            'setor_ids' => [$setorB->id, $setorA->id],
        ])->assertRedirect(route('categorias.index'));

        $categoria = Categoria::where('nome', 'Acesso')->firstOrFail();
        $this->assertSame(min($setorA->id, $setorB->id), $categoria->setor_id);
        $this->assertEqualsCanonicalizing(
            [$setorA->id, $setorB->id],
            $categoria->setores()->pluck('setores.id')->all()
        );

        $setorLegado = $categoria->setor_id;
        $this->actingAs($supervisor)->put(route('categorias.update', $categoria), [
            'nome' => 'Acesso',
            'prioridade' => 'Alta',
            'slatotal' => 90,
            'slaupdate' => 20,
            'setor_ids' => [$setorC->id, $setorLegado],
        ])->assertRedirect(route('categorias.index'));

        $categoria->refresh();
        $this->assertSame($setorLegado, $categoria->setor_id);
        $this->assertEqualsCanonicalizing(
            [$setorLegado, $setorC->id],
            $categoria->setores()->pluck('setores.id')->all()
        );
    }

    public function test_category_sector_validation_rejects_duplicates_and_unknown_ids()
    {
        $supervisor = $this->userWithRole('supervisor');
        $setor = Setor::create(['nome' => 'Atendimento']);

        $this->actingAs($supervisor)->post(route('categorias.store'), [
            'nome' => 'Duplicada',
            'prioridade' => 'Normal',
            'slatotal' => 120,
            'slaupdate' => 30,
            'setor_ids' => [$setor->id, $setor->id, 999999],
        ])->assertStatus(302)->assertSessionHasErrors('setor_ids.1');

        $this->assertDatabaseMissing('categorias', ['nome' => 'Duplicada']);
    }

    public function test_shared_and_unassigned_categories_are_filtered_by_sector()
    {
        $analista = $this->userWithRole('analista');
        $setorA = Setor::create(['nome' => 'Atendimento']);
        $setorB = Setor::create(['nome' => 'Financeiro']);
        $setorC = Setor::create(['nome' => 'Infraestrutura']);
        $compartilhada = $this->categoria('Compartilhada', $setorA);
        $compartilhada->setores()->sync([$setorA->id, $setorB->id]);
        $exclusiva = $this->categoria('Exclusiva', $setorC);
        $semSetor = $this->categoria('Sem setor');

        foreach ([$setorA, $setorB] as $setor) {
            $response = $this->actingAs($analista)->getJson("/setores/{$setor->id}/categorias")->assertOk();
            $response->assertJsonFragment(['id' => $compartilhada->id, 'nome' => 'Compartilhada']);
            $response->assertJsonMissing(['id' => $exclusiva->id]);
            $response->assertJsonMissing(['id' => $semSetor->id]);
        }

        $this->actingAs($analista)->getJson("/categorias/{$setorA->id}")
            ->assertOk()->assertJsonFragment(['id' => $compartilhada->id]);
    }

    public function test_deleting_a_category_removes_its_sector_associations()
    {
        $setor = Setor::create(['nome' => 'Atendimento']);
        $categoria = $this->categoria('Removível', $setor);

        $categoria->delete();

        $this->assertDatabaseMissing('categoria_setor', [
            'categoria_id' => $categoria->id,
            'setor_id' => $setor->id,
        ]);
    }

    public function test_unchanged_historical_ticket_pair_can_be_edited_but_a_new_invalid_pair_cannot()
    {
        $analista = $this->userWithRole('analista');
        $setorAssociado = Setor::create(['nome' => 'Atendimento']);
        $setorHistorico = Setor::create(['nome' => 'Histórico']);
        $outroSetor = Setor::create(['nome' => 'Outro']);
        $categoria = $this->categoria('Acesso', $setorAssociado);
        $ticket = Ticket::create([
            'assunto' => 'Ticket histórico',
            'descricao' => 'Descrição',
            'categoria_id' => $categoria->id,
            'user_id' => $analista->id,
            'setor_id' => $setorHistorico->id,
            'status' => 'aberto',
        ]);

        $payload = [
            'assunto' => 'Ticket histórico editado',
            'descricao' => $ticket->descricao,
            'categoria_id' => $categoria->id,
            'setor_id' => $setorHistorico->id,
            'status' => $ticket->status,
        ];

        $this->actingAs($analista)->put(route('tickets.update', $ticket), $payload)
            ->assertRedirect();
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'assunto' => 'Ticket histórico editado',
            'setor_id' => $setorHistorico->id,
            'categoria_id' => $categoria->id,
        ]);

        $payload['setor_id'] = $outroSetor->id;
        $this->actingAs($analista)->from(route('tickets.edit', $ticket))
            ->put(route('tickets.update', $ticket), $payload)
            ->assertRedirect(route('tickets.edit', $ticket))
            ->assertSessionHasErrors('categoria_id');

        $this->assertSame($setorHistorico->id, $ticket->fresh()->setor_id);
    }

    public function test_web_transfer_requires_and_applies_a_category_from_the_destination_sector()
    {
        $analista = $this->userWithRole('analista');
        $setorOrigem = Setor::create(['nome' => 'Origem']);
        $setorDestino = Setor::create(['nome' => 'Destino']);
        $categoriaOrigem = $this->categoria('Categoria origem', $setorOrigem);
        $categoriaDestino = $this->categoria('Categoria destino', $setorDestino);
        $ticket = Ticket::create([
            'assunto' => 'Transferência',
            'descricao' => 'Descrição',
            'categoria_id' => $categoriaOrigem->id,
            'user_id' => $analista->id,
            'setor_id' => $setorOrigem->id,
            'status' => 'aberto',
        ]);

        $this->actingAs($analista)->post(route('tickets.transferir', $ticket), [
            'setor' => $setorDestino->id,
        ])->assertStatus(302)->assertSessionHasErrors('categoria');

        $this->actingAs($analista)->post(route('tickets.transferir', $ticket), [
            'setor' => $setorDestino->id,
            'categoria' => $categoriaOrigem->id,
        ])->assertStatus(302)->assertSessionHasErrors('categoria');

        $this->actingAs($analista)->post(route('tickets.transferir', $ticket), [
            'setor' => $setorDestino->id,
            'categoria' => $categoriaDestino->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'setor_id' => $setorDestino->id,
            'categoria_id' => $categoriaDestino->id,
        ]);
    }

    public function test_deleting_the_legacy_sector_promotes_another_association()
    {
        $supervisor = $this->userWithRole('supervisor');
        $setorA = Setor::create(['nome' => 'Primário']);
        $setorB = Setor::create(['nome' => 'Secundário']);
        $categoria = $this->categoria('Compartilhada', $setorA);
        $categoria->setores()->attach($setorB->id);

        $this->actingAs($supervisor)->delete(route('setores.destroy', $setorA))
            ->assertRedirect(route('setores.index'));

        $categoria->refresh();
        $this->assertSame($setorB->id, $categoria->setor_id);
        $this->assertEquals([$setorB->id], $categoria->setores()->pluck('setores.id')->all());
    }

    private function categoria(string $nome, ?Setor $setor = null): Categoria
    {
        $categoria = Categoria::create([
            'nome' => $nome,
            'prioridade' => 'Normal',
            'slatotal' => 120,
            'slaupdate' => 30,
            'setor_id' => optional($setor)->id,
        ]);

        if ($setor) {
            $categoria->setores()->attach($setor->id);
        }

        return $categoria;
    }

    private function userWithRole(string $role): User
    {
        Role::findOrCreate($role, 'web');
        $user = User::factory()->create(['status' => true]);
        $user->assignRole($role);

        return $user;
    }
}
