<?php

namespace Tests\Feature;

use App\Jobs\DispatchTicketNotification;
use App\Jobs\SendTicketNotificationMail;
use App\Models\Categoria;
use App\Models\EmailReplyToken;
use App\Models\InboundMailbox;
use App\Models\Mensagem;
use App\Models\NotificationPreference;
use App\Models\NotificationDelivery;
use App\Models\Setor;
use App\Models\SlaAlertOccurrence;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketNotificationContent;
use App\Services\TicketNotificationRecipients;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TicketNotificationsAndEmailTest extends TestCase
{
    use RefreshDatabase;

    private function context(): array
    {
        foreach (['cliente', 'analista', 'supervisor', 'administrador'] as $role) {
            Role::findOrCreate($role, 'web');
        }
        $sector = Setor::create(['nome' => 'Suporte']);
        $category = Categoria::create([
            'nome' => 'Incidente', 'prioridade' => 'Normal', 'slatotal' => 120,
            'slaupdate' => 30, 'setor_id' => $sector->id,
        ]);
        $category->setores()->attach($sector);
        $client = User::factory()->create(['status' => true, 'email' => 'cliente@example.com']);
        $client->assignRole('cliente');
        $analyst = User::factory()->create(['status' => true, 'email' => 'analista@example.com', 'setor_id' => $sector->id]);
        $analyst->assignRole('analista');

        return compact('sector', 'category', 'client', 'analyst');
    }

    private function ticket(array $context, array $overrides = []): Ticket
    {
        return Ticket::create(array_merge([
            'assunto' => 'Falha de acesso',
            'descricao' => 'Descrição do problema',
            'categoria_id' => $context['category']->id,
            'setor_id' => $context['sector']->id,
            'user_id' => $context['client']->id,
            'cliente_id' => $context['client']->id,
            'atribuido_ao_analista_id' => $context['analyst']->id,
            'status' => 'aberto',
        ], $overrides));
    }

    public function test_dispatch_is_idempotent_and_respects_channel_preferences(): void
    {
        Queue::fake();
        config(['ticket_notifications.outbound_enabled' => true]);
        $context = $this->context();
        $ticket = $this->ticket($context);
        NotificationPreference::create([
            'user_id' => $context['analyst']->id,
            'novo_ticket_database' => true,
            'novo_ticket_mail' => false,
        ]);

        $job = new DispatchTicketNotification('test:new:' . $ticket->id, 'novo_ticket', $ticket->id, $context['client']->id);
        $job->handle(app(TicketNotificationRecipients::class), app(TicketNotificationContent::class));
        $job->handle(app(TicketNotificationRecipients::class), app(TicketNotificationContent::class));

        $this->assertDatabaseCount('notifications', 1);
        $this->assertDatabaseCount('notification_deliveries', 1);
        Queue::assertNotPushed(SendTicketNotificationMail::class);
    }

    public function test_system_messages_do_not_dispatch_message_notifications(): void
    {
        Queue::fake();
        config(['ticket_notifications.outbound_enabled' => true]);
        $context = $this->context();
        $ticket = $this->ticket($context);
        Queue::assertPushed(DispatchTicketNotification::class, 1);

        Mensagem::create(['ticket_id' => $ticket->id, 'user_id' => $context['analyst']->id, 'descricao' => 'Transferido', 'tipo' => 'sistema']);
        Queue::assertPushed(DispatchTicketNotification::class, 1);

        Mensagem::create(['ticket_id' => $ticket->id, 'user_id' => $context['analyst']->id, 'descricao' => 'Resposta pública']);
        Queue::assertPushed(DispatchTicketNotification::class, 2);
    }

    public function test_resolution_transition_dispatches_once(): void
    {
        Queue::fake();
        config(['ticket_notifications.outbound_enabled' => true]);
        $context = $this->context();
        $ticket = $this->ticket($context);

        $ticket->update(['status' => 'fechado', 'finalizado_por_usuario_id' => $context['analyst']->id]);
        $ticket->update(['descricao_final' => 'Resolvido']);
        Mensagem::create(['ticket_id' => $ticket->id, 'user_id' => $context['analyst']->id, 'descricao' => 'Finalizado', 'tipo' => 'sistema']);

        Queue::assertPushed(DispatchTicketNotification::class, 2);
    }

    public function test_mail_job_records_delivery_and_creates_hashed_reply_token(): void
    {
        Mail::fake();
        config(['ticket_notifications.inbound_domain' => 'inbound.example.com']);
        $context = $this->context();
        $ticket = $this->ticket($context);
        $delivery = NotificationDelivery::create([
            'event_key' => 'mail-test',
            'user_id' => $context['analyst']->id,
            'channel' => 'mail',
        ]);
        $payload = app(TicketNotificationContent::class)->make('novo_ticket', $ticket, $context['analyst']);

        (new SendTicketNotificationMail($delivery->id, $payload))->handle();

        Mail::assertSent(\App\Mail\TicketEventMail::class, 1);
        $this->assertDatabaseHas('notification_deliveries', ['id' => $delivery->id, 'status' => 'delivered', 'attempts' => 1]);
        $this->assertDatabaseCount('email_reply_tokens', 1);
        $this->assertSame(64, strlen(EmailReplyToken::first()->token_hash));
    }

    public function test_sla_alerts_are_emitted_once_per_reference(): void
    {
        Queue::fake();
        config(['ticket_notifications.sla_enabled' => true, 'ticket_notifications.outbound_enabled' => true]);
        $context = $this->context();
        $ticket = $this->ticket($context);
        Ticket::whereKey($ticket->id)->update(['created_at' => now()->subMinutes(180)]);

        $this->artisan('tickets:check-sla')->assertExitCode(0);
        $this->artisan('tickets:check-sla')->assertExitCode(0);

        $this->assertSame(2, SlaAlertOccurrence::where('ticket_id', $ticket->id)->count());
    }

    public function test_user_can_only_update_own_preferences_and_notifications(): void
    {
        $context = $this->context();
        $other = User::factory()->create(['status' => true]);
        $payload = [];
        foreach (NotificationPreference::EVENTS as $event) {
            foreach (NotificationPreference::CHANNELS as $channel) {
                $payload[$event . '_' . $channel] = false;
            }
        }

        $this->actingAs($context['client'])->put('/minhaconta/notificacoes', $payload)->assertRedirect();
        $this->assertDatabaseHas('notification_preferences', ['user_id' => $context['client']->id, 'sla_mail' => false]);
        $this->assertDatabaseMissing('notification_preferences', ['user_id' => $other->id]);
        $this->actingAs($other)->post('/notificacoes/nao-existe/lida')->assertNotFound();
    }

    public function test_only_administrators_manage_inbound_mailboxes(): void
    {
        $context = $this->context();
        $this->actingAs($context['client'])->get('/administracao/caixas-email')->assertForbidden();

        $admin = User::factory()->create(['status' => true]);
        $admin->assignRole('administrador');
        $this->actingAs($admin)->post('/administracao/caixas-email', [
            'address' => 'Financeiro@Inbound.Example.com',
            'setor_id' => $context['sector']->id,
            'active' => true,
        ])->assertRedirect();

        $this->assertDatabaseHas('inbound_mailboxes', ['address' => 'financeiro@inbound.example.com', 'active' => true]);
    }

    public function test_valid_mailgun_message_creates_ticket_and_is_idempotent(): void
    {
        Queue::fake();
        $context = $this->context();
        InboundMailbox::create(['address' => 'suporte@inbound.example.com', 'setor_id' => $context['sector']->id, 'active' => true]);
        $payload = $this->signedPayload([
            'sender' => $context['client']->email,
            'from' => 'Pessoa Falsa <outra@example.com>',
            'recipient' => 'suporte@inbound.example.com',
            'subject' => 'Ajuda',
            'stripped-text' => 'Não consigo acessar.',
            'message-headers' => json_encode([['Message-Id', '<mail-1@example.com>']]),
        ]);

        $this->postJson('/api/mailgun/inbound', $payload)->assertOk();
        $this->postJson('/api/mailgun/inbound', $payload)->assertOk();

        $this->assertDatabaseCount('tickets', 1);
        $this->assertDatabaseHas('tickets', [
            'assunto' => 'Ajuda', 'origem' => 'email', 'cliente_id' => $context['client']->id,
            'setor_id' => $context['sector']->id, 'categoria_id' => null,
        ]);
    }

    public function test_mailgun_reply_requires_current_participant_and_open_ticket(): void
    {
        Queue::fake();
        $context = $this->context();
        $ticket = $this->ticket($context);
        $token = 'validreplytoken123';
        EmailReplyToken::create(['ticket_id' => $ticket->id, 'token_hash' => hash('sha256', $token), 'expires_at' => now()->addDay()]);
        $payload = $this->signedPayload([
            'sender' => $context['client']->email,
            'recipient' => "reply+{$ticket->id}.{$token}@inbound.example.com",
            'subject' => 'Re: ticket',
            'stripped-text' => 'Resposta do cliente',
            'message-headers' => json_encode([['Message-Id', '<reply-1@example.com>']]),
        ]);

        $this->postJson('/api/mailgun/inbound', $payload)->assertOk();
        $this->assertDatabaseHas('mensagens', ['ticket_id' => $ticket->id, 'descricao' => 'Resposta do cliente', 'origem' => 'email', 'tipo' => 'publica']);

        $ticket->update(['status' => 'fechado']);
        $payload = $this->signedPayload(array_merge($payload, [
            'token' => 'second-webhook-token',
            'message-headers' => json_encode([['Message-Id', '<reply-2@example.com>']]),
        ]));
        $this->postJson('/api/mailgun/inbound', $payload)->assertStatus(406);
    }

    public function test_mailgun_rejects_invalid_signature_and_unknown_sender(): void
    {
        $this->context();
        $this->postJson('/api/mailgun/inbound', ['timestamp' => time(), 'token' => 'x', 'signature' => 'invalid'])->assertStatus(406);

        $payload = $this->signedPayload([
            'sender' => 'desconhecido@example.com', 'recipient' => 'suporte@inbound.example.com',
            'subject' => 'Spam', 'body-plain' => 'Mensagem',
        ]);
        $this->postJson('/api/mailgun/inbound', $payload)->assertStatus(406);
        $this->assertDatabaseHas('inbound_emails', ['status' => 'rejected', 'failure_reason' => 'Remetente desconhecido ou inativo.']);
    }

    private function signedPayload(array $payload): array
    {
        config([
            'ticket_notifications.inbound_enabled' => true,
            'ticket_notifications.inbound_domain' => 'inbound.example.com',
            'ticket_notifications.mailgun_signing_key' => 'test-signing-key',
        ]);
        $timestamp = time();
        $token = $payload['token'] ?? 'webhook-token-' . uniqid();
        $payload['timestamp'] = $timestamp;
        $payload['token'] = $token;
        $payload['signature'] = hash_hmac('sha256', $timestamp . $token, 'test-signing-key');

        return $payload;
    }
}
