<?php

namespace Tests\Feature;

use App\Livewire\Modern\Account\Settings;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ModernAccountTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function user(string $role = 'cliente'): User
    {
        Role::findOrCreate($role, 'web');
        $user = User::factory()->create(['status' => true, 'password' => Hash::make('Original-123')]);
        $user->assignRole($role);

        return $user;
    }

    public function test_all_roles_can_render_their_account_with_only_appropriate_navigation(): void
    {
        $this->get(route('minhaconta.edit'))->assertRedirect('/login');
        foreach (['cliente', 'analista', 'supervisor', 'administrador'] as $role) {
            $this->actingAs($this->user($role));
            $response = $this->get(route('minhaconta.edit'))->assertOk()->assertSee('Preferências de notificação')
                ->assertDontSee('adminlte', false)->assertDontSee('jquery', false)->assertDontSee('toastr', false);
            if ($role === 'cliente') {
                $response->assertSeeInOrder(['Visão geral', 'Tickets', 'Criar ticket', 'Meus tickets', 'Minha conta']);
            }
            if (in_array($role, ['cliente', 'analista'])) {
                $response->assertDontSee('href="'.route('usuarios.index').'"', false);
            }
        }
    }

    public function test_profile_validates_unique_email_and_only_changes_authenticated_account(): void
    {
        $user = $this->user();
        $other = $this->user();
        $this->actingAs($user);
        Livewire::test(Settings::class)->set('email', $other->email)->call('saveProfile')->assertHasErrors('email');
        Livewire::test(Settings::class)->set('name', 'Nome atualizado')->call('saveProfile')->assertHasNoErrors()->assertRedirect(route('minhaconta.edit'));
        $this->assertSame('Nome atualizado', $user->fresh()->name);
        $this->assertTrue($user->fresh()->hasRole('cliente'));
        $this->put(route('minhaconta.update'), ['name' => 'Via HTTP', 'email' => $user->email, 'id' => $other->id, 'status' => false, 'role' => 'administrador'])
            ->assertRedirect(route('minhaconta.edit'))->assertSessionHas('success', 'Perfil atualizado com sucesso!');
        $this->assertSame('Via HTTP', $user->fresh()->name);
        $this->assertSame($other->name, $other->fresh()->name);
        $this->assertTrue((bool) $user->fresh()->status);
    }

    public function test_password_checks_current_password_confirmation_and_clears_sensitive_state(): void
    {
        $user = $this->user();
        $this->actingAs($user);
        $component = Livewire::test(Settings::class);
        $component->set('current_password', 'Incorrect')->set('password', 'Changed-123')->set('password_confirmation', 'Changed-123')
            ->call('savePassword')->assertHasErrors('current_password')->assertSet('password', '')->assertSet('current_password', '');
        $component->set('current_password', 'Original-123')->set('password', 'Changed-123')->set('password_confirmation', 'Mismatch')
            ->call('savePassword')->assertHasErrors('password');
        $this->assertTrue(Hash::check('Original-123', $user->fresh()->password));
        $component->set('current_password', 'Original-123')->set('password', 'Changed-123')->set('password_confirmation', 'Changed-123')
            ->call('savePassword')->assertHasNoErrors()->assertSee('Senha alterada com sucesso!')->assertSet('password_confirmation', '');
        $this->assertTrue(Hash::check('Changed-123', $user->fresh()->password));
        $this->assertAuthenticatedAs($user);
        $input = ['current_password' => 'Incorrect', 'password' => 'Another-123', 'password_confirmation' => 'Another-123'];
        $this->put(route('minhaconta.password'), $input)->assertRedirect(route('minhaconta.edit'))->assertSessionHas('error', 'A senha atual está incorreta.');
        $input['current_password'] = 'Changed-123';
        $this->put(route('minhaconta.password'), $input)->assertRedirect(route('minhaconta.edit'))->assertSessionHas('success', 'Senha alterada com sucesso!');
        $this->assertTrue(Hash::check('Another-123', $user->fresh()->password));
    }

    public function test_preferences_default_to_enabled_and_persist_false_values_by_both_transports(): void
    {
        $user = $this->user();
        $this->actingAs($user);
        $component = Livewire::test(Settings::class)->assertSet('preferences.novo_ticket_mail', true);
        $component->set('preferences.novo_ticket_mail', false)->call('savePreferences')->assertHasNoErrors()->assertSee('Preferências de notificação atualizadas.');
        $this->assertFalse($user->fresh()->notificationPreference->novo_ticket_mail);
        $data = $component->get('preferences');
        $data['nova_mensagem_database'] = false;
        $this->put(route('notification-preferences.update'), $data)->assertRedirect(route('minhaconta.edit'));
        Livewire::test(Settings::class)->assertSet('preferences.nova_mensagem_database', false);
        $component->set('preferences.sla_mail', 'invalid')->call('savePreferences')->assertHasErrors('sla_mail');
    }

    public function test_expired_session_cannot_submit(): void
    {
        $this->actingAs($this->user());
        $component = Livewire::test(Settings::class);
        Auth::logout();
        $this->expectException(AuthenticationException::class);
        $component->call('saveProfile');
    }

    public function test_inactive_user_and_switched_account_cannot_submit(): void
    {
        $user = $this->user();
        $this->actingAs($user);
        $component = Livewire::test(Settings::class);
        $user->update(['status' => false]);
        $component->call('savePreferences')->assertForbidden();
        $user->update(['status' => true]);
        $component = Livewire::test(Settings::class);
        $this->actingAs($this->user());
        $component->call('saveProfile')->assertForbidden();
    }

    public function test_account_id_is_locked(): void
    {
        $this->actingAs($this->user());
        $component = Livewire::test(Settings::class);
        $this->expectException(CannotUpdateLockedPropertyException::class);
        $component->set('accountId', 999999);
    }

    public function test_real_json_request_updates_profile(): void
    {
        $user = $this->user();
        $this->actingAs($user);
        $html = $this->get(route('minhaconta.edit'))->assertOk()->getContent();
        preg_match('/wire:snapshot="([^"]+)"/', $html, $matches);
        $this->postJson(app('livewire')->getUpdateUri(), ['components' => [[
            'snapshot' => html_entity_decode($matches[1], ENT_QUOTES),
            'updates' => ['name' => 'Perfil JSON'],
            'calls' => [['method' => 'saveProfile', 'params' => []]],
        ]]], ['X-Livewire' => 'true'])->assertOk()->assertJsonPath('components.0.effects.redirect', route('minhaconta.edit'));
        $this->assertSame('Perfil JSON', $user->fresh()->name);
    }
}
