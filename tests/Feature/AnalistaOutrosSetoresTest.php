<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Setor;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AnalistaOutrosSetoresTest extends TestCase
{
    use RefreshDatabase;

    public function test_supervisor_can_enable_other_sectors_when_creating_an_analyst(): void
    {
        $supervisor = $this->userWithRole('supervisor');
        Role::findOrCreate('analista', 'web');
        $setor = Setor::create(['nome' => 'Suporte']);

        $this->actingAs($supervisor)
            ->post(route('usuarios.store'), [
                'name' => 'Analista com acesso',
                'email' => 'analista-acesso@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
                'setor_id' => $setor->id,
                'role' => 'analista',
                'pode_ver_tickets_outros_setores' => '1',
            ])
            ->assertRedirect(route('usuarios.index'));

        $analista = User::where('email', 'analista-acesso@example.com')->firstOrFail();

        $this->assertTrue($analista->pode_ver_tickets_outros_setores);
        $this->assertTrue($analista->hasRole('analista'));
    }

    public function test_option_is_cleared_when_user_is_no_longer_an_analyst(): void
    {
        $administrator = $this->userWithRole('administrador');
        $analista = $this->userWithRole('analista', [
            'pode_ver_tickets_outros_setores' => true,
        ]);
        Role::findOrCreate('supervisor', 'web');

        $this->actingAs($administrator)
            ->put(route('usuarios.update', $analista), [
                'name' => $analista->name,
                'email' => $analista->email,
                'role' => 'supervisor',
                'pode_ver_tickets_outros_setores' => '1',
            ])
            ->assertRedirect(route('usuarios.index'));

        $this->assertFalse($analista->fresh()->pode_ver_tickets_outros_setores);
    }

    public function test_analyst_without_option_remains_restricted_to_own_sector(): void
    {
        [$analista, $ticketMesmoSetor, $ticketOutroSetor] = $this->ticketScenario(false);

        $response = $this->actingAs($analista)->get(route('tickets.index'));

        $response->assertOk();
        $this->assertEqualsCanonicalizing(
            [$ticketMesmoSetor->id],
            $response->viewData('tickets')->pluck('id')->all()
        );

        $this->actingAs($analista)
            ->get(route('tickets.show', $ticketOutroSetor))
            ->assertForbidden();
    }

    public function test_analyst_with_option_can_view_tickets_from_every_sector(): void
    {
        [$analista, $ticketMesmoSetor, $ticketOutroSetor] = $this->ticketScenario(true);

        $response = $this->actingAs($analista)->get(route('tickets.index'));

        $response->assertOk();
        $this->assertEqualsCanonicalizing(
            [$ticketMesmoSetor->id, $ticketOutroSetor->id],
            $response->viewData('tickets')->pluck('id')->all()
        );

        $this->actingAs($analista)
            ->get(route('tickets.show', $ticketOutroSetor))
            ->assertOk();
    }

    private function ticketScenario(bool $podeVerOutrosSetores): array
    {
        Role::findOrCreate('supervisor', 'web');
        Role::findOrCreate('administrador', 'web');
        $setorDoAnalista = Setor::create(['nome' => 'Setor do analista']);
        $outroSetor = Setor::create(['nome' => 'Outro setor']);
        $analista = $this->userWithRole('analista', [
            'setor_id' => $setorDoAnalista->id,
            'pode_ver_tickets_outros_setores' => $podeVerOutrosSetores,
        ]);
        $categoria = Categoria::create([
            'nome' => 'Categoria de teste',
            'slatotal' => 60,
            'slaupdate' => 30,
            'setor_id' => $setorDoAnalista->id,
        ]);
        $categoria->setores()->attach([$setorDoAnalista->id, $outroSetor->id]);

        $ticketMesmoSetor = $this->ticket($analista, $categoria, $setorDoAnalista);
        $ticketOutroSetor = $this->ticket($analista, $categoria, $outroSetor);

        return [$analista, $ticketMesmoSetor, $ticketOutroSetor];
    }

    private function ticket(User $autor, Categoria $categoria, Setor $setor): Ticket
    {
        return Ticket::create([
            'assunto' => 'Ticket do ' . $setor->nome,
            'descricao' => 'Descrição do ticket',
            'categoria_id' => $categoria->id,
            'user_id' => $autor->id,
            'setor_id' => $setor->id,
            'status' => 'aberto',
        ]);
    }

    private function userWithRole(string $role, array $attributes = []): User
    {
        Role::findOrCreate($role, 'web');
        $user = User::factory()->create(array_merge(['status' => true], $attributes));
        $user->assignRole($role);

        return $user;
    }
}
