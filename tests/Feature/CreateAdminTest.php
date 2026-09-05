<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_active_admin_with_hashed_password_and_access(): void
    {
        $this->runCommand();

        $user = User::where('email', 'admin@example.com')->firstOrFail();
        $this->assertTrue($user->status);
        $this->assertTrue($user->hasRole('administrador'));
        $this->assertTrue($user->hasPermissionTo('acesso admin'));
        $this->assertTrue(Hash::check('A-long-password-123', $user->password));
        $this->assertSame(1, User::count());
    }

    public function test_existing_inactive_admin_blocks_creation_and_keeps_password(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create(['status' => false]);
        $user->assignRole('administrador');
        $password = $user->password;

        $this->artisan('app:create-admin')->assertExitCode(1);

        $this->assertSame(1, User::count());
        $this->assertSame($password, $user->fresh()->password);
    }

    public function test_rejects_invalid_credentials_without_creating_users(): void
    {
        $this->runCommand('invalid-email', 'short', 'different', 1);
        $this->assertSame(0, User::count());
    }

    public function test_does_not_promote_an_existing_email(): void
    {
        $user = User::factory()->create(['email' => 'admin@example.com']);
        $this->runCommand('admin@example.com', 'A-long-password-123', 'A-long-password-123', 1);
        $this->assertSame(1, User::count());
        $this->assertFalse($user->fresh()->hasRole('administrador'));
    }

    public function test_rejects_non_interactive_execution(): void
    {
        $this->artisan('app:create-admin', ['--no-interaction' => true])->assertExitCode(1);
        $this->assertSame(0, User::count());
    }

    private function runCommand($email = 'admin@example.com', $password = 'A-long-password-123', $confirmation = 'A-long-password-123', $exitCode = 0): void
    {
        $this->artisan('app:create-admin')
            ->expectsQuestion('Nome', 'Administrador')
            ->expectsQuestion('E-mail', $email)
            ->expectsQuestion('Senha (mínimo de 12 caracteres)', $password)
            ->expectsQuestion('Confirme a senha', $confirmation)
            ->assertExitCode($exitCode);
    }
}
