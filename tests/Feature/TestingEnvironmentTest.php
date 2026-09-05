<?php

namespace Tests\Feature;

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

    public function test_ticket_observer_uses_the_fake_email_service(): void
    {
        $author = User::factory()->create(['status' => true]);
        $client = User::factory()->create(['status' => true, 'email' => 'cliente@example.com']);
        $ticket = Ticket::create([
            'assunto' => 'Aviso de teste',
            'descricao' => 'Descrição',
            'user_id' => $author->id,
            'cliente_id' => $client->id,
            'status' => 'aberto',
        ]);

        // O observer agenda o envio após o término da resposta HTTP.
        $this->get('/')->assertRedirect('/home');

        Http::assertSentCount(1);
        Http::assertSent(function ($request) use ($ticket) {
            return $request->url() === 'http://localhost:5000/send-email'
                && $request['emails'][0]['email'] === 'cliente@example.com'
                && $request['titulo_do_email'] === "Novo Ticket #{$ticket->id}";
        });
    }
}
