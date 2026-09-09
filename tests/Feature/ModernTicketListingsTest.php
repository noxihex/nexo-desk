<?php

namespace Tests\Feature;

use App\Livewire\Modern\Tickets\TicketIndex;
use App\Models\Categoria;
use App\Models\Empresa;
use App\Models\Setor;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ModernTicketListingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function user(string $role = 'analista', array $attributes = []): User
    {
        Role::findOrCreate($role, 'web');
        $user = User::factory()->create($attributes + ['status' => true]);
        $user->assignRole($role);

        return $user;
    }

    private function ticket(User $creator, array $attributes = []): Ticket
    {
        return Ticket::create($attributes + [
            'assunto' => 'Ticket de teste',
            'descricao' => 'Descrição de teste',
            'user_id' => $creator->id,
            'status' => 'aberto',
        ]);
    }

    public function test_staff_pages_render_modern_navigation_without_legacy_assets(): void
    {
        foreach (['analista', 'supervisor', 'administrador'] as $role) {
            $this->actingAs($this->user($role));

            foreach (['tickets.index', 'tickets.my', 'tickets.pendentes'] as $route) {
                $this->get(route($route))->assertOk()
                    ->assertSee('Geral')
                    ->assertSee('Meus tickets')
                    ->assertSee('Pendentes')
                    ->assertSee('Tema escuro')
                    ->assertDontSee('jquery', false)
                    ->assertDontSee('adminlte', false)
                    ->assertDontSee('wire:navigate', false);
            }
        }
    }

    public function test_general_listing_preserves_search_filters_closed_default_and_sorting(): void
    {
        Carbon::setTestNow('2026-09-09 12:00:00');
        $manager = $this->user('supervisor');
        $sector = Setor::create(['nome' => 'Suporte']);
        $otherSector = Setor::create(['nome' => 'Financeiro']);
        $category = Categoria::create(['nome' => 'Urgente', 'prioridade' => 'Alta', 'slatotal' => 60, 'slaupdate' => 15]);
        $category->setores()->attach($sector);
        $company = Empresa::create(['nome' => 'Empresa A', 'cnpj' => '12345678000190']);

        $open = $this->ticket($manager, ['assunto' => 'Falha urgente', 'setor_id' => $sector->id, 'categoria_id' => $category->id, 'empresa_id' => $company->id]);
        $closed = $this->ticket($manager, ['assunto' => 'Falha encerrada', 'status' => 'fechado', 'setor_id' => $sector->id, 'categoria_id' => $category->id, 'empresa_id' => $company->id]);
        $this->ticket($manager, ['assunto' => 'Outro ticket', 'setor_id' => $otherSector->id]);

        $this->actingAs($manager)
            ->get(route('tickets.index', ['search' => 'Falha']))
            ->assertOk()->assertSee($open->assunto)->assertSee($closed->assunto)->assertSee('09/09/2026 - 12:00')->assertDontSee('Outro ticket');

        $this->get(route('tickets.index', [
            'search' => 'Falha',
            'showClosed' => 0,
            'setor_id' => $sector->id,
            'categoria_id' => $category->id,
            'empresa_id' => $company->id,
            'sort' => 'updated_at',
        ]))->assertOk()->assertSee($open->assunto)->assertDontSee($closed->assunto);

        Livewire::test(TicketIndex::class, ['section' => 'general'])
            ->set('sort', 'not-a-column')->call('applyFilters')->assertSet('sort', 'created_at');
    }

    public function test_analyst_sector_scope_applies_to_general_and_pending_lists(): void
    {
        $ownSector = Setor::create(['nome' => 'Próprio']);
        $otherSector = Setor::create(['nome' => 'Outro']);
        $analyst = $this->user('analista', ['setor_id' => $ownSector->id]);
        $own = $this->ticket($analyst, ['assunto' => 'Pendente próprio', 'setor_id' => $ownSector->id]);
        $other = $this->ticket($analyst, ['assunto' => 'Pendente de outro setor', 'setor_id' => $otherSector->id]);

        $this->actingAs($analyst);
        foreach (['tickets.index', 'tickets.pendentes'] as $route) {
            $this->get(route($route))->assertOk()->assertSee($own->assunto)->assertDontSee($other->assunto);
        }

        $analyst->update(['pode_ver_tickets_outros_setores' => true]);
        $this->get(route('tickets.pendentes'))->assertOk()->assertSee($own->assunto)->assertSee($other->assunto);
    }

    public function test_mine_and_pending_lists_keep_their_legacy_scopes(): void
    {
        $analyst = $this->user();
        $other = $this->user();
        $category = Categoria::create(['nome' => 'Configurado', 'slatotal' => 60, 'slaupdate' => 15]);
        $mine = $this->ticket($analyst, ['assunto' => 'Atribuído a mim', 'atribuido_ao_analista_id' => $analyst->id, 'categoria_id' => $category->id]);
        $closedMine = $this->ticket($analyst, ['assunto' => 'Meu fechado', 'atribuido_ao_analista_id' => $analyst->id, 'categoria_id' => $category->id, 'status' => 'fechado']);
        $notMine = $this->ticket($other, ['assunto' => 'Atribuído a outro', 'atribuido_ao_analista_id' => $other->id]);
        $pending = $this->ticket($analyst, ['assunto' => 'Sem categoria']);
        $closedPending = $this->ticket($analyst, ['assunto' => 'Sem categoria fechado', 'status' => 'fechado']);

        $this->actingAs($analyst)
            ->get(route('tickets.my'))->assertOk()->assertSee($mine->assunto)->assertDontSee($closedMine->assunto)->assertDontSee($notMine->assunto);
        $this->get(route('tickets.my', ['showClosed' => 1]))->assertOk()->assertSee($mine->assunto)->assertSee($closedMine->assunto);
        $this->get(route('tickets.pendentes'))->assertOk()->assertSee($pending->assunto)->assertSee($notMine->assunto)
            ->assertDontSee($mine->assunto)->assertDontSee($closedPending->assunto);
    }

    public function test_livewire_rechecks_session_status_role_and_locks_sensitive_state(): void
    {
        $analyst = $this->user();
        $this->actingAs($analyst);
        $component = Livewire::test(TicketIndex::class, ['section' => 'general']);

        $analyst->update(['status' => false]);
        $component->call('applyFilters')->assertForbidden();

        $analyst->update(['status' => true]);
        $component = Livewire::test(TicketIndex::class, ['section' => 'general']);
        $analyst->syncRoles([]);
        $component->call('applyFilters')->assertForbidden();

        $analyst->assignRole('analista');
        $component = Livewire::test(TicketIndex::class, ['section' => 'general']);
        Auth::logout();
        $this->expectException(AuthenticationException::class);
        $component->call('applyFilters');
    }

    public function test_section_and_delete_target_are_locked(): void
    {
        $this->actingAs($this->user('administrador'));

        $this->expectException(CannotUpdateLockedPropertyException::class);
        Livewire::test(TicketIndex::class, ['section' => 'general'])->set('section', 'pending');
    }

    public function test_confirmed_delete_target_cannot_be_replaced_by_the_client(): void
    {
        $administrator = $this->user('administrador');
        $ticket = $this->ticket($administrator);
        $this->actingAs($administrator);
        $component = Livewire::test(TicketIndex::class, ['section' => 'general'])
            ->call('confirmDelete', $ticket->id);

        $this->expectException(CannotUpdateLockedPropertyException::class);
        $component->set('deleteId', 999999);
    }

    public function test_only_administrator_can_delete_with_http_or_livewire_and_attachments_are_removed(): void
    {
        Storage::fake('public');
        $supervisor = $this->user('supervisor');
        $administrator = $this->user('administrador');
        $ticket = $this->ticket($supervisor, ['assunto' => 'Excluir com anexo']);
        Storage::disk('public')->put('anexos/teste.txt', 'conteúdo');
        TicketAttachment::create(['ticket_id' => $ticket->id, 'file_path' => 'anexos/teste.txt']);

        $this->actingAs($supervisor)
            ->delete(route('tickets.destroy', $ticket))->assertForbidden();
        $this->assertDatabaseHas('tickets', ['id' => $ticket->id]);

        $this->actingAs($administrator);
        Livewire::test(TicketIndex::class, ['section' => 'general'])
            ->call('confirmDelete', $ticket->id)
            ->assertSet('deleteId', $ticket->id)
            ->call('delete')
            ->assertSee('Ticket excluído com sucesso!');

        $this->assertDatabaseMissing('tickets', ['id' => $ticket->id]);
        Storage::disk('public')->assertMissing('anexos/teste.txt');
    }

    public function test_real_livewire_request_updates_general_filters(): void
    {
        $user = $this->user('supervisor');
        $ticket = $this->ticket($user, ['assunto' => 'Localizar pelo envelope']);
        $this->ticket($user, ['assunto' => 'Não localizar']);
        $this->actingAs($user);
        $html = $this->get(route('tickets.index'))->assertOk()->getContent();
        preg_match('/wire:snapshot="([^"]+)"/', $html, $matches);

        $response = $this->postJson(app('livewire')->getUpdateUri(), ['components' => [[
            'snapshot' => html_entity_decode($matches[1], ENT_QUOTES),
            'updates' => ['search' => (string) $ticket->id],
            'calls' => [['method' => 'applyFilters', 'params' => []]],
        ]]], ['X-Livewire' => 'true'])->assertOk()->assertSee($ticket->assunto)->assertDontSee('Não localizar');

        $response->assertSee(rawurlencode(route('tickets.index', ['search' => (string) $ticket->id])), false)
            ->assertDontSee('%2Flivewire-', false);
    }
}
