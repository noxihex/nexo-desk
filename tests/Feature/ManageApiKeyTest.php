<?php

namespace Tests\Feature;

use App\Console\Commands\ManageApiKey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\TestCase;

class ManageApiKeyTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_an_api_key_for_an_active_user(): void
    {
        $user = User::factory()->create([
            'email' => 'integracao@example.com',
            'status' => true,
        ]);

        $this->artisan('app:api-key', [
            'action' => 'create',
            'identifier' => $user->email,
            '--name' => 'integracao',
        ])->assertSuccessful();

        $token = PersonalAccessToken::query()->firstOrFail();

        $this->assertSame('integracao', $token->name);
        $this->assertSame($user->id, $token->tokenable_id);
        $this->assertSame(User::class, $token->tokenable_type);
        $this->assertSame(['*'], $token->abilities);
        $this->assertSame(64, strlen($token->token));
    }

    public function test_authenticates_a_token_issued_with_the_legacy_format(): void
    {
        $user = User::factory()->create(['status' => true]);
        $plainTextToken = 'legacy-plain-text-token';
        $tokenId = DB::table('personal_access_tokens')->insertGetId([
            'tokenable_type' => User::class,
            'tokenable_id' => $user->id,
            'name' => 'legacy',
            'token' => hash('sha256', $plainTextToken),
            'abilities' => json_encode(['*']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withToken($tokenId.'|'.$plainTextToken)
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('id', $user->id);
    }

    public function test_interactive_menu_creates_an_api_key(): void
    {
        $user = User::factory()->create([
            'name' => 'Integração',
            'email' => 'integracao@example.com',
            'status' => true,
        ]);

        $this->artisan('app:api-key')
            ->expectsQuestion('O que deseja fazer?', 'Criar uma chave')
            ->expectsQuestion('Selecione o usuário:', 'Integração <integracao@example.com>')
            ->expectsQuestion('Nome da chave', 'ERP')
            ->expectsConfirmation('Criar a chave "ERP" para integracao@example.com?', 'yes')
            ->assertSuccessful();

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'ERP',
        ]);
    }

    public function test_does_not_create_an_api_key_for_an_inactive_user(): void
    {
        $user = User::factory()->create(['status' => false]);

        $this->artisan('app:api-key', [
            'action' => 'criar',
            'identifier' => $user->email,
            '--name' => 'integracao',
        ])->assertExitCode(1);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_revokes_a_key_by_id(): void
    {
        $user = User::factory()->create(['status' => true]);
        $token = $user->createToken('integracao')->accessToken;

        $this->artisan('app:api-key', [
            'action' => 'revoke',
            'identifier' => (string) $token->getKey(),
            '--force' => true,
        ])->assertSuccessful();

        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $token->getKey()]);
    }

    public function test_interactive_menu_revokes_a_selected_key(): void
    {
        $user = User::factory()->create(['status' => true]);
        $token = $user->createToken('integracao')->accessToken;

        $this->artisan('app:api-key')
            ->expectsQuestion('O que deseja fazer?', 'Revogar uma chave')
            ->expectsQuestion('Selecione a chave para revogar:', $this->tokenLabel($token, $user))
            ->expectsConfirmation("Revogar a chave #{$token->getKey()} (integracao)?", 'yes')
            ->assertSuccessful();

        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $token->getKey()]);
    }

    public function test_revokes_all_keys_for_a_user(): void
    {
        $user = User::factory()->create(['status' => true]);
        $user->createToken('integracao-1');
        $user->createToken('integracao-2');

        $this->artisan('app:api-key', [
            'action' => 'revogar',
            'identifier' => $user->email,
            '--all' => true,
            '--force' => true,
        ])->assertSuccessful();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_real_console_choice_creates_an_api_key(): void
    {
        $user = User::factory()->create([
            'name' => 'Integração',
            'email' => 'integracao@example.com',
            'status' => true,
        ]);

        $command = new ManageApiKey();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);
        $tester->setInputs(['1', '1', 'ERP', 'yes']);

        $this->assertSame(0, $tester->execute([]));
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'ERP',
        ]);
        $this->assertStringContainsString('Chave de API criada com sucesso.', $tester->getDisplay());
    }

    public function test_real_console_choice_revokes_a_selected_key(): void
    {
        $user = User::factory()->create(['status' => true]);
        $token = $user->createToken('integracao')->accessToken;
        $command = new ManageApiKey();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);
        $tester->setInputs(['2', '1', 'yes']);

        $this->assertSame(0, $tester->execute([]));
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $token->getKey()]);
        $this->assertStringContainsString("Chave #{$token->getKey()} revogada com sucesso.", $tester->getDisplay());
    }

    public function test_real_console_choice_revokes_all_keys_from_a_user(): void
    {
        $user = User::factory()->create(['status' => true]);
        $user->createToken('integracao-1');
        $user->createToken('integracao-2');
        $command = new ManageApiKey();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);
        $tester->setInputs(['3', '1', 'yes']);

        $this->assertSame(0, $tester->execute([]));
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->assertStringContainsString('2 chave(s) de API revogada(s) com sucesso.', $tester->getDisplay());
    }

    private function tokenLabel(PersonalAccessToken $token, User $user): string
    {
        $createdAt = $token->created_at ? $token->created_at->format('d/m/Y H:i') : '-';
        $lastUsedAt = $token->last_used_at ? $token->last_used_at->format('d/m/Y H:i') : 'nunca';

        return "#{$token->getKey()} | {$user->name} <{$user->email}> | {$token->name} | criada {$createdAt} | último uso {$lastUsedAt}";
    }
}
