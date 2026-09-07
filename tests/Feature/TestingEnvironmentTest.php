<?php

namespace Tests\Feature;

use App\Models\Mensagem;
use App\Models\Setor;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TestingEnvironmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_is_isolated_from_application_connection_settings(): void
    {
        $this->assertTrue(app()->environment('testing'));
        $this->assertSame('mysql', config('database.default'));
        $this->assertSame('nexodesk_testing', app('db')->connection()->getDatabaseName());
        $this->assertNull(config('database.connections.mysql.url'));
    }

    public function test_storage_roots_remain_explicit_and_separated(): void
    {
        $this->assertSame(storage_path('app'), config('filesystems.disks.local.root'));
        $this->assertSame(storage_path('app/public'), config('filesystems.disks.public.root'));
        $this->assertSame(storage_path('app/private/backups'), config('filesystems.disks.backups.root'));
    }

    public function test_sanctum_preserves_authentication_defaults_and_uses_current_middleware(): void
    {
        $this->assertSame(['web'], config('sanctum.guard'));
        $this->assertNull(config('sanctum.expiration'));
        $this->assertSame('', config('sanctum.token_prefix'));
        $this->assertSame(\Laravel\Sanctum\Http\Middleware\AuthenticateSession::class, config('sanctum.middleware.authenticate_session'));
        $this->assertSame(\Illuminate\Cookie\Middleware\EncryptCookies::class, config('sanctum.middleware.encrypt_cookies'));
        $this->assertSame(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class, config('sanctum.middleware.validate_csrf_token'));
    }

    public function test_tickets_and_messages_are_saved_without_email_requests(): void
    {
        $sector = Setor::create(['nome' => 'Suporte']);
        $author = User::factory()->create(['status' => true, 'setor_id' => $sector->id]);
        \Spatie\Permission\Models\Role::findOrCreate('analista', 'web');
        $author->assignRole('analista');
        $client = User::factory()->create(['status' => true, 'email' => 'cliente@example.com']);
        foreach ([$author, $client] as $creator) {
            $ticket = Ticket::create([
                'assunto' => 'Aviso de teste',
                'descricao' => 'Descrição',
                'user_id' => $creator->id,
                'setor_id' => $sector->id,
                'atribuido_ao_analista_id' => $author->id,
                'cliente_id' => $client->id,
                'status' => 'aberto',
            ]);

            $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'status' => 'aberto']);

            foreach ([$author, $client] as $sender) {
                $message = Mensagem::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $sender->id,
                    'descricao' => 'Resposta sem e-mail',
                ]);
                $this->assertDatabaseHas('mensagens', [
                    'id' => $message->id,
                    'ticket_id' => $ticket->id,
                    'user_id' => $sender->id,
                    'descricao' => 'Resposta sem e-mail',
                ]);
            }
        }

        // Executa também os callbacks registrados para depois da resposta HTTP.
        $this->get('/')->assertRedirect('/home');

        Http::assertNothingSent();
    }
}
