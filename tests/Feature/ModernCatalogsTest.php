<?php

namespace Tests\Feature;

use App\Livewire\Modern\Cadastros\CategoriaForm;
use App\Livewire\Modern\Cadastros\CategoriaIndex;
use App\Livewire\Modern\Cadastros\SetorForm;
use App\Livewire\Modern\Cadastros\SetorIndex;
use App\Models\Categoria;
use App\Models\InboundMailbox;
use App\Models\Setor;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ModernCatalogsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function user(string $role = 'supervisor', bool $active = true): User
    {
        Role::findOrCreate($role, 'web');
        $user = User::factory()->create(['status' => $active]);
        $user->assignRole($role);

        return $user;
    }

    private function category(string $name = 'Acesso', array $sectors = []): Categoria
    {
        $category = Categoria::create([
            'nome' => $name, 'prioridade' => 'Normal', 'slatotal' => 120,
            'slaupdate' => 30, 'setor_id' => $sectors[0] ?? null,
        ]);
        $category->setores()->sync($sectors);

        return $category;
    }

    public function test_six_pages_render_with_modern_navigation_for_both_management_roles(): void
    {
        $sector = Setor::create(['nome' => 'Atendimento']);
        $category = $this->category('Acesso', [$sector->id]);
        foreach (['supervisor', 'administrador'] as $role) {
            $this->actingAs($this->user($role));
            foreach ([
                '/cadastros/categorias', '/cadastros/categorias/create', '/cadastros/categorias/'.$category->id.'/edit',
                '/cadastros/setores', '/cadastros/setores/create', '/cadastros/setores/'.$sector->id.'/edit',
            ] as $url) {
                $this->get($url)->assertOk()->assertSee('Voltar ao sistema')->assertSee('aria-current="page"', false)
                    ->assertDontSee('adminlte', false)->assertDontSee('jquery', false)->assertDontSee('bootstrap', false);
            }
            $this->get('/cadastros/categorias')->assertSee('Excluir Acesso');
            $this->get('/cadastros/setores')->assertSee('Excluir Atendimento');
        }
    }

    public function test_http_routes_reject_guests_other_roles_and_inactive_users(): void
    {
        $this->get('/cadastros/categorias')->assertRedirect('/login');
        $sector = Setor::create(['nome' => 'Protegido']);
        $category = $this->category('Protegida');
        foreach (['analista', 'cliente'] as $role) {
            $this->actingAs($this->user($role));
            foreach (['categorias', 'setores'] as $catalog) {
                $id = $catalog === 'categorias' ? $category->id : $sector->id;
                $this->get('/cadastros/'.$catalog)->assertForbidden();
                $this->get('/cadastros/'.$catalog.'/create')->assertForbidden();
                $this->get('/cadastros/'.$catalog.'/'.$id.'/edit')->assertForbidden();
                $this->post('/cadastros/'.$catalog, ['nome' => 'Indevido'])->assertForbidden();
                $this->put('/cadastros/'.$catalog.'/'.$id, ['nome' => 'Indevido'])->assertForbidden();
                $this->delete('/cadastros/'.$catalog.'/'.$id)->assertForbidden();
            }
        }
        $this->actingAs($this->user('supervisor', false))->get('/cadastros/setores')->assertRedirect('/login');
        $this->assertDatabaseHas('setores', ['id' => $sector->id]);
        $this->assertDatabaseHas('categorias', ['id' => $category->id]);
    }

    public function test_livewire_checks_status_roles_and_session_again_on_each_request(): void
    {
        $user = $this->user();
        $this->actingAs($user);
        $component = Livewire::test(SetorForm::class)->set('nome', 'Não salvar');
        $user->update(['status' => false]);
        $component->call('save')->assertForbidden();
        $this->assertDatabaseMissing('setores', ['nome' => 'Não salvar']);

        $user->update(['status' => true]);
        $component = Livewire::test(CategoriaIndex::class);
        $user->syncRoles([]);
        $component->set('search', 'teste')->assertForbidden();
        $user->assignRole('supervisor');
        $component = Livewire::test(SetorIndex::class);
        Auth::logout();
        $this->expectException(\Illuminate\Auth\AuthenticationException::class);
        $component->call('$refresh');
    }

    public function test_create_and_update_categories_keep_all_sector_and_legacy_rules(): void
    {
        $this->actingAs($this->user());
        $a = Setor::create(['nome' => 'A']);
        $b = Setor::create(['nome' => 'B']);
        Livewire::test(CategoriaForm::class)->set('nome', 'Compartilhada')->set('prioridade', 'Alta')
            ->set('slatotal', 120)->set('slaupdate', 30)->set('setor_ids', [$b->id, $a->id])
            ->call('save')->assertHasNoErrors()->assertRedirect(route('categorias.index'));
        $category = Categoria::where('nome', 'Compartilhada')->firstOrFail();
        $this->assertSame($a->id, $category->setor_id);
        $this->assertCount(2, $category->setores);
        $category->update(['setor_id' => $b->id]);
        Livewire::test(CategoriaForm::class, ['recordId' => $category->id])->set('nome', 'Editada')
            ->call('save')->assertHasNoErrors();
        $this->assertSame($b->id, $category->fresh()->setor_id);
        Livewire::test(CategoriaForm::class, ['recordId' => $category->id])->set('setor_ids', [])
            ->call('save')->assertHasNoErrors();
        $this->assertNull($category->fresh()->setor_id);
        $this->assertCount(0, $category->fresh()->setores);
        $this->assertDatabaseHas('categorias', ['id' => $category->id, 'prioridade' => 'Alta', 'slatotal' => 120, 'slaupdate' => 30]);
    }

    public function test_validation_rejects_duplicate_category_invalid_slas_priority_and_sector_ids(): void
    {
        $this->actingAs($this->user());
        $this->category('Duplicada');
        $sector = Setor::create(['nome' => 'A']);
        Livewire::test(CategoriaForm::class)->set('nome', 'Duplicada')->set('prioridade', 'Urgente')
            ->set('slatotal', 0)->set('slaupdate', 1.5)->set('setor_ids', [$sector->id, $sector->id, 999999])
            ->call('save')->assertHasErrors(['nome', 'prioridade', 'slatotal', 'slaupdate', 'setor_ids.1', 'setor_ids.2']);
        Livewire::test(SetorForm::class)->call('save')->assertHasErrors('nome');
        Livewire::test(SetorForm::class)->set('nome', str_repeat('a', 256))->call('save')->assertHasErrors('nome');
        $this->assertSame(1, Categoria::count());
    }

    public function test_sector_names_can_repeat_and_http_and_livewire_updates_are_compatible(): void
    {
        $this->actingAs($this->user());
        $this->post(route('setores.store'), ['nome' => 'Atendimento'])->assertRedirect(route('setores.index'));
        Livewire::test(SetorForm::class)->set('nome', 'Atendimento')->call('save')->assertHasNoErrors();
        $this->assertSame(2, Setor::where('nome', 'Atendimento')->count());
        $sector = Setor::first();
        Livewire::test(SetorForm::class, ['recordId' => $sector->id])->set('nome', 'Editado')->call('save')->assertHasNoErrors();
        $this->patch(route('setores.update', $sector), ['nome' => 'Atualizado'])->assertRedirect(route('setores.index'));
        $this->assertSame('Atualizado', $sector->fresh()->nome);
    }

    public function test_category_search_pagination_and_delete_last_page_keep_search(): void
    {
        $this->actingAs($this->user());
        for ($i = 1; $i <= 11; $i++) {
            $this->category(sprintf('Categoria %02d', $i));
        }
        $other = $this->category('Outro registro');
        $last = Categoria::where('nome', 'Categoria 11')->first();
        Livewire::withQueryParams(['search' => 'Categoria', 'page' => 2])->test(CategoriaIndex::class)
            ->assertSee('Categoria 11')->assertDontSee('Categoria 01')->assertDontSee($other->nome)
            ->call('confirmDelete', $last->id)->assertSet('showDelete', true)->call('delete')
            ->assertSet('showDelete', false)->assertSet('search', 'Categoria')->assertSet('paginators.page', 1)
            ->assertSee('Categoria 01')->assertDontSee('Categoria 11');
        Livewire::test(CategoriaIndex::class)->call('setPage', 2)->set('search', 'Inexistente')
            ->assertSet('paginators.page', 1)->assertSee('Nenhum resultado');
    }

    public function test_mutation_replaces_the_previous_success_message(): void
    {
        $this->actingAs($this->user());
        session()->flash('success', 'Mensagem anterior');
        $sector = Setor::create(['nome' => 'Temporário']);
        Livewire::test(SetorIndex::class)->assertSee('Mensagem anterior')
            ->call('confirmDelete', $sector->id)->call('delete')
            ->assertSee('Setor excluído com sucesso!')->assertDontSee('Mensagem anterior');
    }

    public function test_sector_search_is_unpaginated_and_empty_states_render(): void
    {
        $this->actingAs($this->user());
        Livewire::test(SetorIndex::class)->assertSee('Nenhum setor cadastrado.');
        Livewire::test(CategoriaIndex::class)->assertSee('Nenhuma categoria cadastrada.');
        for ($i = 1; $i <= 12; $i++) {
            Setor::create(['nome' => sprintf('Setor %02d', $i)]);
        }
        Livewire::test(SetorIndex::class)->assertSee('Setor 01')->assertSee('Setor 12')
            ->set('search', '12')->assertSee('Setor 12')->assertDontSee('Setor 01');
    }

    public function test_sector_deletion_preserves_users_tickets_and_promotes_category_sector(): void
    {
        $user = $this->user();
        $this->actingAs($user);
        $a = Setor::create(['nome' => 'A']);
        $b = Setor::create(['nome' => 'B']);
        $category = $this->category('Compartilhada', [$a->id, $b->id]);
        $user->update(['setor_id' => $a->id]);
        $ticket = Ticket::create(['assunto' => 'Preservar', 'descricao' => 'Teste', 'user_id' => $user->id, 'categoria_id' => $category->id, 'setor_id' => $a->id]);
        Livewire::test(SetorIndex::class)->call('confirmDelete', $a->id)->call('delete')->assertHasNoErrors();
        $this->assertNull($user->fresh()->setor_id);
        $this->assertNull($ticket->fresh()->setor_id);
        $this->assertSame($b->id, $category->fresh()->setor_id);
        $this->assertSame([$b->id], $category->fresh()->setores->modelKeys());
        Livewire::test(CategoriaIndex::class)->call('confirmDelete', $category->id)->call('delete')->assertHasNoErrors();
        $this->assertNull($ticket->fresh()->categoria_id);
        $this->assertDatabaseMissing('categoria_setor', ['categoria_id' => $category->id]);
    }

    public function test_mailbox_blocks_sector_deletion_in_both_transports_without_partial_changes(): void
    {
        $this->actingAs($this->user());
        $a = Setor::create(['nome' => 'A']);
        $b = Setor::create(['nome' => 'B']);
        $category = $this->category('Compartilhada', [$a->id, $b->id]);
        InboundMailbox::create(['address' => 'qa@example.invalid', 'setor_id' => $a->id, 'active' => false]);
        Livewire::test(SetorIndex::class)->call('confirmDelete', $a->id)->call('delete')
            ->assertHasErrors('delete')->assertSet('showDelete', true)->assertSee('caixa de e-mail');
        $this->from(route('setores.index'))->delete(route('setores.destroy', $a))
            ->assertRedirect(route('setores.index'))->assertSessionHasErrors('delete');
        $this->assertSame($a->id, $category->fresh()->setor_id);
        $this->assertCount(2, $category->fresh()->setores);
        $this->assertDatabaseHas('setores', ['id' => $a->id]);
    }

    public function test_record_ids_cannot_be_changed_by_the_client(): void
    {
        $this->actingAs($this->user());
        $a = Setor::create(['nome' => 'A']);
        $b = Setor::create(['nome' => 'B']);
        $this->expectException(CannotUpdateLockedPropertyException::class);
        Livewire::test(SetorForm::class, ['recordId' => $a->id])->set('recordId', $b->id);
    }

    public function test_deleted_record_is_not_recreated_by_stale_form(): void
    {
        $this->actingAs($this->user());
        $sector = Setor::create(['nome' => 'Removido']);
        $form = Livewire::test(SetorForm::class, ['recordId' => $sector->id]);
        $sector->delete();
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        try {
            $form->set('nome', 'Não recriar')->call('save');
        } finally {
            $this->assertDatabaseMissing('setores', ['nome' => 'Não recriar']);
        }
    }

    public function test_delete_selection_is_locked_and_requires_confirmation(): void
    {
        $this->actingAs($this->user());
        $category = $this->category();
        Livewire::test(CategoriaIndex::class)->call('delete')->assertNotFound();
        $this->expectException(CannotUpdateLockedPropertyException::class);
        Livewire::test(CategoriaIndex::class)->set('deleteId', $category->id);
    }

    public function test_shared_actions_keep_model_auditing(): void
    {
        config(['audit.console' => true]);
        $user = $this->user();
        $this->actingAs($user);
        Livewire::test(SetorForm::class)->set('nome', 'Auditado')->call('save')->assertHasNoErrors();
        $sector = Setor::where('nome', 'Auditado')->firstOrFail();
        $this->assertDatabaseHas('audits', [
            'auditable_type' => Setor::class, 'auditable_id' => $sector->id, 'event' => 'created',
        ]);
        $this->delete(route('setores.destroy', $sector))->assertRedirect(route('setores.index'));
        $this->assertDatabaseHas('audits', [
            'auditable_type' => Setor::class, 'auditable_id' => $sector->id, 'event' => 'deleted',
        ]);
    }

    public function test_real_json_request_rechecks_status_before_persisting(): void
    {
        $user = $this->user();
        $this->actingAs($user);
        $html = $this->get(route('setores.create'))->assertOk()->getContent();
        preg_match('/wire:snapshot="([^"]+)"/', $html, $matches);
        $user->update(['status' => false]);
        $this->postJson(app('livewire')->getUpdateUri(), ['components' => [[
            'snapshot' => html_entity_decode($matches[1], ENT_QUOTES),
            'updates' => ['nome' => 'Não autorizado'],
            'calls' => [['method' => 'save', 'params' => []]],
        ]]], ['X-Livewire' => 'true'])->assertForbidden();
        $this->assertDatabaseMissing('setores', ['nome' => 'Não autorizado']);
    }

    public function test_real_livewire_json_request_saves_category_with_sector_selection(): void
    {
        $this->actingAs($this->user());
        $sector = Setor::create(['nome' => 'Atendimento']);
        $html = $this->get(route('categorias.create'))->assertOk()->getContent();
        preg_match('/wire:snapshot="([^"]+)"/', $html, $matches);
        $this->postJson(app('livewire')->getUpdateUri(), ['components' => [[
            'snapshot' => html_entity_decode($matches[1], ENT_QUOTES),
            'updates' => ['nome' => 'Via JSON', 'prioridade' => 'Baixa', 'slatotal' => 60, 'slaupdate' => 15, 'setor_ids' => [$sector->id]],
            'calls' => [['method' => 'save', 'params' => []]],
        ]]], ['X-Livewire' => 'true'])->assertOk()->assertJsonPath('components.0.effects.redirect', route('categorias.index'));
        $this->assertDatabaseHas('categorias', ['nome' => 'Via JSON', 'setor_id' => $sector->id]);
    }
}
