<?php

namespace Tests\Feature;

use App\Livewire\Modern\Auth\ConfirmPassword;
use App\Livewire\Modern\Auth\ForgotPassword;
use App\Livewire\Modern\Auth\Login;
use App\Livewire\Modern\Auth\ResetPassword;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\PasswordReset as PasswordResetEvent;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Tests\TestCase;

class ModernAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Notification::fake();
    }

    private function user(bool $active = true): User
    {
        return User::factory()->create(['status' => $active, 'password' => Hash::make('password')]);
    }

    public function test_active_pages_render_modern_assets_and_inactive_routes_stay_disabled(): void
    {
        foreach (['/login', '/password/reset', '/password/reset/sample?email=user@example.com'] as $url) {
            $this->get($url)->assertOk()->assertSee('wire:submit="submit"', false)
                ->assertDontSee('adminlte', false)->assertDontSee('bootstrap', false)
                ->assertDontSee('jquery', false)->assertDontSee('modern-sidebar', false);
        }

        $this->get('/password/confirm')->assertRedirect('/login');
        $this->actingAs($this->user())->get('/password/confirm')->assertOk()->assertSee('Confirmar senha');
        foreach (['/register', '/email/verify', '/email/resend'] as $url) {
            $this->get($url)->assertNotFound();
            $this->post($url)->assertNotFound();
        }
        $this->assertFalse(Route::has('register'));
        $this->assertFalse(Route::has('verification.notice'));

        foreach (['auth.register', 'auth.verify'] as $view) {
            $html = view($view)->render();
            $this->assertStringContainsString('disabled', $html);
            $this->assertStringNotContainsString('wire:submit', $html);
            $this->assertStringNotContainsString('adminlte', $html);
        }
    }

    public function test_livewire_login_remembers_user_regenerates_session_and_redirects_to_intended(): void
    {
        $user = $this->user();
        session()->put('url.intended', '/tickets/my');
        $sessionId = session()->getId();

        Livewire::test(Login::class)->set('email', $user->email)->set('password', 'password')
            ->set('remember', true)->call('submit')->assertHasNoErrors()
            ->assertSet('password', '')->assertRedirect('/tickets/my');

        $this->assertAuthenticatedAs($user);
        $this->assertNotSame($sessionId, session()->getId());
        $this->assertNotNull(session('auth.password_confirmed_at'));
        $this->assertTrue(cookie()->hasQueued(Auth::guard()->getRecallerName()));
    }

    public function test_login_accepts_real_livewire_json_requests(): void
    {
        $user = $this->user();
        $html = $this->get('/login')->assertOk()->getContent();
        preg_match('/wire:snapshot="([^"]+)"/', $html, $matches);
        $snapshot = html_entity_decode($matches[1], ENT_QUOTES);

        $response = $this->postJson(app('livewire')->getUpdateUri(), [
            'components' => [[
                'snapshot' => $snapshot,
                'updates' => ['email' => $user->email, 'password' => 'password', 'remember' => true],
                'calls' => [['method' => 'submit', 'params' => []]],
            ]],
        ], ['X-Livewire' => 'true']);

        $response->assertOk()->assertJsonPath('components.0.effects.redirect', '/home');
        $this->assertAuthenticatedAs($user);
        $this->assertSame('', json_decode($response->json('components.0.snapshot'), true)['data']['password']);
    }

    public function test_login_failures_and_inactive_account_messages_match_both_transports(): void
    {
        $user = $this->user(false);
        Livewire::test(Login::class)->set('email', $user->email)->set('password', 'password')
            ->call('submit')->assertHasErrors('email')->assertSee('Sua conta está inativa.');
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors(['email' => 'Sua conta está inativa.']);
        Livewire::test(Login::class)->set('email', 'missing@example.com')->set('password', 'wrong')
            ->call('submit')->assertHasErrors('email')->assertSee(trans('auth.failed'));
        Livewire::test(Login::class)->call('submit')->assertHasErrors(['email', 'password']);
        $this->assertGuest();
    }

    public function test_login_throttle_is_shared_between_livewire_and_post_and_cleared_after_success(): void
    {
        Event::fake([Lockout::class]);
        $user = $this->user();
        for ($i = 0; $i < 3; $i++) {
            $this->post('/login', ['email' => $user->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        }
        for ($i = 0; $i < 2; $i++) {
            Livewire::test(Login::class)->set('email', $user->email)->set('password', 'wrong')
                ->call('submit')->assertHasErrors('email');
        }
        Livewire::test(Login::class)->set('email', $user->email)->set('password', 'password')
            ->call('submit')->assertHasErrors('email');
        $this->assertGuest();
        Event::assertDispatched(Lockout::class);
        $this->postJson('/login', ['email' => $user->email, 'password' => 'password'])->assertStatus(429);
        $this->travel(61)->seconds();
        Livewire::test(Login::class)->set('email', $user->email)->set('password', 'password')
            ->call('submit')->assertHasNoErrors()->assertRedirect('/home');
        Auth::logout();
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/home');
    }

    public function test_authenticated_login_does_not_switch_accounts_and_logout_contract_is_preserved(): void
    {
        $first = $this->user();
        $second = $this->user();
        $component = Livewire::test(Login::class);
        $this->actingAs($first);
        $component->set('email', $second->email)->set('password', 'password')->call('submit')->assertRedirect('/home');
        $this->assertAuthenticatedAs($first);
        $this->get('/login')->assertRedirect('/home');
        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_recovery_checks_configuration_on_each_submission(): void
    {
        config(['auth.password_reset_enabled' => true]);
        $component = Livewire::test(ForgotPassword::class)->set('email', 'user@example.com');
        config(['auth.password_reset_enabled' => false]);
        $component->call('submit')->assertSee('A recuperação automática de senha está temporariamente desativada.')
            ->assertSet('email', 'user@example.com')->assertHasNoErrors();
        Notification::assertNothingSent();
    }

    public function test_recovery_sends_notification_and_shares_broker_throttle_with_post(): void
    {
        config(['auth.password_reset_enabled' => true]);
        $user = $this->user();
        Livewire::test(ForgotPassword::class)->set('email', $user->email)->call('submit')
            ->assertSet('status', trans(Password::RESET_LINK_SENT))->assertHasNoErrors();
        Notification::assertSentTo($user, ResetPasswordNotification::class);
        $this->post('/password/email', ['email' => $user->email])
            ->assertSessionHasErrors(['email' => trans(Password::RESET_THROTTLED)]);
        Livewire::test(ForgotPassword::class)->set('email', 'missing@example.com')->call('submit')->assertHasErrors('email');
        Livewire::test(ForgotPassword::class)->set('email', 'invalid')->call('submit')->assertHasErrors('email');
    }

    public function test_reset_changes_password_consumes_token_logs_in_and_dispatches_event(): void
    {
        Event::fake([PasswordResetEvent::class]);
        $user = $this->user();
        $rememberToken = $user->remember_token;
        $token = Password::broker()->createToken($user);
        Livewire::test(ResetPassword::class, ['token' => $token, 'email' => $user->email])
            ->set('password', 'new-password')->set('password_confirmation', 'new-password')
            ->call('submit')->assertHasNoErrors()->assertSet('password', '')
            ->assertSet('password_confirmation', '')->assertRedirect('/home');
        $this->assertAuthenticatedAs($user);
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
        $this->assertNotSame($rememberToken, $user->fresh()->remember_token);
        $this->assertFalse(Password::broker()->tokenExists($user, $token));
        Event::assertDispatched(PasswordResetEvent::class);
        Auth::logout();
        $this->post('/password/reset', [
            'token' => $token, 'email' => $user->email,
            'password' => 'other-password', 'password_confirmation' => 'other-password',
        ])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_reset_rejects_invalid_expired_tokens_and_password_mismatch(): void
    {
        $user = $this->user();
        $token = Password::broker()->createToken($user);
        $component = Livewire::test(ResetPassword::class, ['token' => $token, 'email' => $user->email]);
        $component->set('password', 'new-password')->set('password_confirmation', 'different')
            ->call('submit')->assertHasErrors('password');
        DB::table('password_resets')->where('email', $user->email)->update(['created_at' => now()->subMinutes(61)]);
        $component->set('password', 'new-password')->set('password_confirmation', 'new-password')
            ->call('submit')->assertHasErrors('email');
        Livewire::test(ResetPassword::class, ['token' => 'invalid', 'email' => $user->email])
            ->set('password', 'new-password')->set('password_confirmation', 'new-password')
            ->call('submit')->assertHasErrors('email');
        $this->assertTrue(Hash::check('password', $user->fresh()->password));
        $this->assertGuest();
    }

    public function test_confirmation_revalidates_authentication_and_updates_timeout_only_on_success(): void
    {
        $user = $this->user();
        $this->actingAs($user);
        session()->put('url.intended', '/minhaconta');
        $component = Livewire::test(ConfirmPassword::class);
        $component->set('password', 'wrong')->call('submit')->assertHasErrors('password');
        $this->assertNull(session('auth.password_confirmed_at'));
        $component->set('password', 'password')->call('submit')->assertHasNoErrors()
            ->assertSet('password', '')->assertRedirect('/minhaconta');
        $this->assertNotNull(session('auth.password_confirmed_at'));
        Auth::logout();
        session()->forget('auth.password_confirmed_at');
        $component->set('password', 'password')->call('submit')->assertRedirect('/login');
        $this->assertNull(session('auth.password_confirmed_at'));
        $this->assertGuest();
    }

    public function test_existing_post_success_responses_are_preserved(): void
    {
        config(['auth.password_reset_enabled' => true]);
        $user = $this->user();
        $this->postJson('/password/email', ['email' => $user->email])->assertOk()->assertJson(['message' => trans(Password::RESET_LINK_SENT)]);
        $token = Password::broker()->createToken($user);
        $this->post('/password/reset', [
            'token' => $token, 'email' => $user->email,
            'password' => 'new-password', 'password_confirmation' => 'new-password',
        ])->assertRedirect('/home')->assertSessionHas('status', trans(Password::PASSWORD_RESET));
        $this->post('/password/confirm', ['password' => 'wrong'])->assertSessionHasErrors('password');
        $this->postJson('/password/confirm', ['password' => 'new-password'])->assertNoContent();
        $this->post('/logout')->assertRedirect('/');
        $this->postJson('/login', ['email' => $user->email, 'password' => 'new-password'])->assertNoContent();
    }
}
