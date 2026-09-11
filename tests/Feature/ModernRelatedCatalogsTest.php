<?php

namespace Tests\Feature;

use App\Livewire\Modern\Cadastros\{EmpresaForm, EmpresaIndex, ContatoForm, ContatoIndex, UsuarioForm, UsuarioIndex};
use App\Models\{Empresa, User, Ticket};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Auth, DB, Hash};
use Livewire\Livewire;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ModernRelatedCatalogsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        foreach (['administrador', 'supervisor', 'analista', 'cliente'] as $role) {
            Role::findOrCreate($role, 'web');
        }
    }

    private function user(string $role = 'administrador', array $attributes = []): User
    {
        $user = User::factory()->create(['status' => true] + $attributes);
        $user->assignRole($role);
        return $user;
    }

    private function companyData(string $cnpj = '12345678000190'): array
    {
        return ['nome' => 'Empresa exemplo', 'razao_social' => 'Exemplo Ltda', 'cnpj' => $cnpj,
            'endereco' => 'Rua Um', 'bairro' => 'Centro', 'cidade' => 'São Paulo', 'estado' => 'SP', 'horas_contratadas' => 10];
    }

    public function test_related_pages_render_for_both_management_roles_with_isolated_assets(): void
    {
        $company = Empresa::create($this->companyData());
        $contact = $this->user('cliente', ['empresa_id' => $company->id]);
        $analyst = $this->user('analista');
        foreach (['administrador', 'supervisor'] as $role) {
            $this->actingAs($this->user($role));
            foreach (['empresas' => $company->id, 'clientes' => $contact->id, 'usuarios' => $analyst->id] as $path => $id) {
                foreach ($path === 'clientes' ? ['/create?empresa_id='.$company->id, '/'.$id.'/edit'] : ['', '/create', '/'.$id.'/edit'] as $suffix) {
                    $this->get('/cadastros/'.$path.$suffix)->assertOk()->assertDontSee('Voltar ao sistema')
                        ->assertSee('Empresas')->assertDontSee('adminlte', false)->assertDontSee('jquery', false);
                }
            }
        }
    }

    public function test_company_crud_validation_search_and_pagination(): void
    {
        $this->actingAs($this->user());
        Livewire::test(EmpresaForm::class)->set($this->companyData())->call('save')->assertHasNoErrors()->assertRedirect(route('empresas.index'));
        $company = Empresa::first();
        Livewire::test(EmpresaForm::class)->set($this->companyData())->call('save')->assertHasErrors(['cnpj']);
        Livewire::test(EmpresaForm::class, ['recordId' => $company->id])->set('nome', 'Alterada')->set('horas_contratadas', '1.5')->call('save')->assertHasErrors(['horas_contratadas']);
        Livewire::test(EmpresaForm::class, ['recordId' => $company->id])->set('nome', 'Alterada')->call('save')->assertHasNoErrors();
        for ($i = 0; $i < 10; $i++) {
            Empresa::create(['nome' => 'Z Empresa '.$i] + $this->companyData((string) $i));
        }
        Livewire::test(EmpresaIndex::class)->assertViewHas('records', fn ($records) => $records->total() === 11 && $records->count() === 10)
            ->set('search', 'Alterada')->assertViewHas('records', fn ($records) => $records->total() === 1)
            ->call('confirmDelete', $company->id)->call('delete')->assertSee('Nenhum resultado');
        $this->post(route('empresas.store'), $this->companyData())->assertRedirect(route('empresas.index'));
        $company = Empresa::where('cnpj', '12345678000190')->firstOrFail();
        $this->put(route('empresas.update', $company), ['nome' => 'HTTP'] + $this->companyData())->assertRedirect(route('empresas.index'));
        $this->delete(route('empresas.destroy', $company))->assertRedirect(route('empresas.index'));
        $this->assertDatabaseMissing('empresas', ['id' => $company->id]);
    }

    public function test_company_deletion_preserves_people_and_tickets(): void
    {
        $this->actingAs($this->user('supervisor'));
        $company = Empresa::create($this->companyData());
        $contact = $this->user('cliente', ['empresa_id' => $company->id]);
        $ticket = Ticket::create(['assunto' => 'Preservado', 'descricao' => 'Teste', 'empresa_id' => $company->id, 'user_id' => $contact->id]);
        Livewire::test(EmpresaIndex::class)->call('confirmDelete', $company->id)->call('delete');
        $this->assertNull($contact->fresh()->empresa_id);
        $this->assertNull($ticket->fresh()->empresa_id);
        $this->assertSame($contact->id, $ticket->fresh()->user_id);
    }

    public function test_contact_creation_and_edit_keep_company_and_role_and_preserve_blank_password(): void
    {
        $this->actingAs($this->user('supervisor'));
        $company = Empresa::create($this->companyData());
        Livewire::test(ContatoForm::class, ['empresaId' => $company->id])->set('name', 'Contato novo')->set('email', 'contact@example.invalid')
            ->set('password', 'Password-123')->set('password_confirmation', 'different')->call('save')->assertHasErrors(['password'])
            ->set('password', 'Password-123')->set('password_confirmation', 'Password-123')->set('role', 'administrador')
            ->set('pode_finalizar_tickets_empresa', true)
            ->call('save')->assertHasNoErrors()->assertRedirect(route('empresas.edit', $company));
        $contact = User::where('email', 'contact@example.invalid')->firstOrFail();
        $hash = $contact->password;
        $this->assertTrue($contact->hasRole('cliente'));
        $this->assertSame($company->id, $contact->empresa_id);
        $this->assertTrue($contact->pode_finalizar_tickets_empresa);
        Livewire::test(ContatoForm::class, ['recordId' => $contact->id])->set('name', 'Novo nome')->call('save')->assertHasNoErrors();
        $this->assertSame($hash, $contact->fresh()->password);
        Livewire::test(ContatoIndex::class, ['empresaId' => $company->id])->assertSee('Novo nome');
    }

    public function test_supervisor_can_manage_analyst_but_cannot_elevate_profile(): void
    {
        $this->actingAs($this->user('supervisor'));
        Livewire::test(UsuarioForm::class)->set('name', 'Analista')->set('email', 'analyst@example.invalid')
            ->set('password', 'Password-123')->set('password_confirmation', 'Password-123')->set('role', 'administrador')
            ->call('save')->assertHasErrors(['role'])->set('role', 'analista')
            ->set('password', 'Password-123')->set('password_confirmation', 'Password-123')->set('pode_ver_tickets_outros_setores', true)
            ->call('save')->assertHasNoErrors();
        $user = User::where('email', 'analyst@example.invalid')->firstOrFail();
        $this->assertTrue($user->hasRole('analista'));
        $this->assertTrue((bool) $user->pode_ver_tickets_outros_setores);
        $admin = $this->user();
        Livewire::test(UsuarioIndex::class)->call('confirmStatus', $admin->id)->assertForbidden();
    }

    public function test_status_changes_revoke_sessions_and_remember_token_and_filter_inactive(): void
    {
        $this->actingAs($this->user());
        $company = Empresa::create($this->companyData());
        foreach ([false, true] as $contact) {
            $user = $this->user($contact ? 'cliente' : 'analista', ['remember_token' => 'remember-this', 'empresa_id' => $company->id]);
            DB::table('sessions')->insert(['id' => 'session-'.$user->id, 'user_id' => $user->id, 'payload' => '', 'last_activity' => time()]);
            $component = Livewire::test($contact ? ContatoIndex::class : UsuarioIndex::class, $contact ? ['empresaId' => $company->id] : [])
                ->call('confirmStatus', $user->id)->call('changeStatus')->assertHasNoErrors();
            $this->assertFalse((bool) $user->fresh()->status);
            $this->assertNull($user->fresh()->remember_token);
            $this->assertDatabaseMissing('sessions', ['user_id' => $user->id]);
            if (!$contact) { $component->assertDontSee($user->email)->set('todos', true)->assertSee($user->email); }
            $this->post(route($contact ? 'clientes.deactivate' : 'usuarios.deactivate', $user))->assertRedirect();
            $this->assertTrue((bool) $user->fresh()->status);
        }
    }

    public function test_form_ids_are_locked(): void
    {
        $this->actingAs($this->user());
        $company = Empresa::create($this->companyData());
        $this->expectException(CannotUpdateLockedPropertyException::class);
        Livewire::test(ContatoForm::class, ['empresaId' => $company->id])->set('empresaId', null);
    }

    public function test_session_expiration_is_revalidated(): void
    {
        $this->actingAs($this->user());
        $component = Livewire::test(UsuarioForm::class);
        Auth::logout();
        $this->expectException(\Illuminate\Auth\AuthenticationException::class);
        $component->call('save');
    }

    public function test_inactive_or_demoted_actor_cannot_submit(): void
    {
        $actor = $this->user();
        $this->actingAs($actor);
        $component = Livewire::test(EmpresaForm::class);
        $actor->update(['status' => false]);
        $component->call('save')->assertForbidden();
    }

    public function test_real_json_livewire_submission_creates_user(): void
    {
        $this->actingAs($this->user());
        $html = $this->get(route('usuarios.create'))->assertOk()->getContent();
        preg_match('/wire:snapshot="([^"]+)"/', $html, $matches);
        $this->postJson(app('livewire')->getUpdateUri(), ['components' => [[
            'snapshot' => html_entity_decode($matches[1], ENT_QUOTES),
            'updates' => ['name' => 'Via JSON', 'email' => 'json@example.invalid', 'password' => 'Password-123', 'password_confirmation' => 'Password-123', 'role' => 'analista'],
            'calls' => [['path' => '', 'method' => 'save', 'params' => []]],
        ]]], ['X-Livewire' => 'true'])->assertOk()->assertJsonPath('components.0.effects.redirect', route('usuarios.index'));
        $user = User::where('email', 'json@example.invalid')->firstOrFail();
        $this->assertTrue(Hash::check('Password-123', $user->password));
        $this->assertTrue($user->hasRole('analista'));
    }
    public function test_self_demotion_redirects_out_of_management_without_rendering_forbidden_page(): void
    {
        $actor = $this->user();
        $this->actingAs($actor);
        Livewire::test(UsuarioForm::class, ['recordId' => $actor->id])->set('role', 'analista')
            ->call('save')->assertHasNoErrors()->assertRedirect('/home');
        $this->assertTrue($actor->fresh()->hasRole('analista'));
    }

    public function test_self_deactivation_logs_out_and_protected_user_one_cannot_be_deactivated(): void
    {
        $protected = $this->user('administrador', ['id' => 1]);
        $actor = $this->user();
        $this->actingAs($actor);
        Livewire::test(UsuarioIndex::class)->call('confirmStatus', $protected->id)->call('changeStatus')->assertHasErrors(['status']);
        $this->assertTrue((bool) $protected->fresh()->status);
        Livewire::test(UsuarioIndex::class)->call('confirmStatus', $actor->id)->call('changeStatus')->assertRedirect(route('login'));
        $this->assertGuest();
        $this->assertFalse((bool) $actor->fresh()->status);
        $this->assertNull($actor->fresh()->remember_token);
    }

    public function test_all_related_pages_reject_guests_and_non_management_roles(): void
    {
        foreach (['empresas', 'clientes', 'usuarios'] as $path) {
            $this->get('/cadastros/'.$path)->assertRedirect('/login');
        }
        foreach (['cliente', 'analista'] as $role) {
            $this->actingAs($this->user($role));
            foreach (['empresas', 'clientes', 'usuarios'] as $path) {
                $this->get('/cadastros/'.$path)->assertForbidden();
                $this->post('/cadastros/'.$path, [])->assertForbidden();
            }
        }
    }

    public function test_livewire_rechecks_target_role_and_rejects_cross_company_contact(): void
    {
        $this->actingAs($this->user('supervisor'));
        $analyst = $this->user('analista');
        $form = Livewire::test(UsuarioForm::class, ['recordId' => $analyst->id]);
        $analyst->syncRoles('administrador');
        $form->call('save')->assertForbidden();
        $company = Empresa::create($this->companyData());
        $contact = $this->user('cliente');
        Livewire::test(ContatoIndex::class, ['empresaId' => $company->id])->call('confirmStatus', $contact->id)->assertNotFound();
    }

    public function test_contacts_are_accessible_through_companies_and_company_is_required_on_post(): void
    {
        $this->actingAs($this->user());
        $this->get(route('clientes.index'))->assertRedirect(route('empresas.index'));
        $this->get(route('clientes.create'))->assertRedirect(route('empresas.index'));
        $this->get(route('empresas.index'))->assertDontSee('href="'.route('clientes.index').'"', false);
        $input = ['name' => 'Contato', 'email' => 'required-company@example.invalid', 'password' => 'Password-123', 'password_confirmation' => 'Password-123'];
        foreach ([null, 999999] as $companyId) {
            $this->post(route('clientes.store'), $input + ['empresa_id' => $companyId])->assertSessionHasErrors('empresa_id');
        }
        $this->assertDatabaseMissing('users', ['email' => $input['email']]);
    }

    public function test_livewire_revalidates_company_before_creating_contact(): void
    {
        $this->actingAs($this->user());
        $company = Empresa::create($this->companyData());
        $form = Livewire::test(ContatoForm::class, ['empresaId' => $company->id]);
        $company->delete();
        $form->set('name', 'Contato')->set('email', 'deleted-company@example.invalid')
            ->set('password', 'Password-123')->set('password_confirmation', 'Password-123')
            ->call('save')->assertHasErrors('empresa_id');
        $this->assertDatabaseMissing('users', ['email' => 'deleted-company@example.invalid']);
    }

}
