<?php

namespace Tests\Feature;

use App\Jobs\DispatchStaffTicketActivity;
use App\Jobs\SendTicketNotificationMail;
use App\Models\Categoria;
use App\Models\Mensagem;
use App\Models\Setor;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketTimelineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TicketInternalCollaborationTest extends TestCase
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
        $client = User::factory()->create(['status' => true]);
        $client->assignRole('cliente');
        $analyst = User::factory()->create(['status' => true, 'setor_id' => $sector->id]);
        $analyst->assignRole('analista');
        $mentioned = User::factory()->create(['status' => true, 'setor_id' => $sector->id]);
        $mentioned->assignRole('analista');
        $ticket = Ticket::create([
            'assunto' => 'Falha', 'descricao' => 'Descrição', 'categoria_id' => $category->id,
            'setor_id' => $sector->id, 'user_id' => $client->id, 'cliente_id' => $client->id,
            'atribuido_ao_analista_id' => $analyst->id, 'status' => 'aberto',
        ]);

        return compact('sector', 'category', 'client', 'analyst', 'mentioned', 'ticket');
    }

    public function test_internal_note_keeps_status_private_and_stores_attachment_on_private_disk(): void
    {
        Queue::fake();
        Storage::fake('local');
        $context = $this->context();

        $this->actingAs($context['analyst'])->post("/tickets/{$context['ticket']->id}/mensagens", [
            'descricao' => '@' . $context['mentioned']->name . ' verifique isto',
            'tipo' => 'interna',
            'mentioned_user_ids' => [$context['mentioned']->id],
            'attachments' => [UploadedFile::fake()->create('evidencia.txt', 10, 'text/plain')],
        ])->assertRedirect();

        $message = Mensagem::where('ticket_id', $context['ticket']->id)->where('tipo', 'interna')->firstOrFail();
        $this->assertSame('aberto', $context['ticket']->fresh()->status);
        $this->assertDatabaseHas('mensagem_mencoes', ['mensagem_id' => $message->id, 'user_id' => $context['mentioned']->id]);
        $this->assertSame('local', $message->attachments()->first()->disk);
        Storage::disk('local')->assertExists($message->attachments()->first()->file_path);
        $this->assertDatabaseMissing('ticket_seguidores', ['ticket_id' => $context['ticket']->id, 'user_id' => $context['mentioned']->id]);
        Queue::assertPushed(DispatchStaffTicketActivity::class, 1);
    }

    public function test_client_never_sees_internal_or_system_messages(): void
    {
        $context = $this->context();
        foreach (['publica' => 'Resposta visível', 'interna' => 'Segredo interno', 'sistema' => 'Transferência interna'] as $type => $text) {
            $context['ticket']->mensagens()->create(['user_id' => $context['analyst']->id, 'descricao' => $text, 'tipo' => $type]);
        }

        $this->actingAs($context['client'])->get("/tickets/cliente/{$context['ticket']->id}")
            ->assertOk()->assertSee('Resposta visível')->assertDontSee('Segredo interno')->assertDontSee('Transferência interna');
    }

    public function test_following_is_self_service_idempotent_and_does_not_grant_access(): void
    {
        Queue::fake();
        $context = $this->context();
        $url = "/tickets/{$context['ticket']->id}/seguir";

        $this->actingAs($context['analyst'])->post($url)->assertRedirect();
        $this->actingAs($context['analyst'])->post($url)->assertRedirect();
        $this->assertDatabaseCount('ticket_seguidores', 1);
        $this->delete($url)->assertRedirect();
        $this->assertDatabaseCount('ticket_seguidores', 0);

        $otherSector = Setor::create(['nome' => 'Financeiro']);
        $outsider = User::factory()->create(['status' => true, 'setor_id' => $otherSector->id]);
        $outsider->assignRole('analista');
        $this->actingAs($outsider)->post($url)->assertForbidden();
        $this->assertDatabaseMissing('ticket_seguidores', ['user_id' => $outsider->id]);
    }

    public function test_v2_defaults_to_public_and_restricts_internal_contracts_to_staff(): void
    {
        Queue::fake();
        $context = $this->context();
        Sanctum::actingAs($context['analyst']);

        $this->postJson("/api/v2/tickets/{$context['ticket']->id}/messages", ['descricao' => 'Compatível'])
            ->assertCreated()->assertJsonPath('data.tipo', 'publica');
        $this->postJson("/api/v2/tickets/{$context['ticket']->id}/messages", [
            'descricao' => 'Nota', 'tipo' => 'interna',
            'mentioned_user_ids' => [$context['mentioned']->id],
        ])->assertCreated()->assertJsonPath('data.tipo', 'interna');
        $this->getJson("/api/v2/tickets/{$context['ticket']->id}/timeline")->assertOk();

        Sanctum::actingAs($context['client']);
        $this->postJson("/api/v2/tickets/{$context['ticket']->id}/messages", ['descricao' => 'Inválida', 'tipo' => 'interna'])
            ->assertForbidden();
        $this->getJson("/api/v2/tickets/{$context['ticket']->id}")
            ->assertOk()->assertJsonMissing(['descricao' => 'Nota']);
        $this->getJson("/api/v2/tickets/{$context['ticket']->id}/timeline")->assertForbidden();
    }

    public function test_followers_receive_one_internal_notification_without_email(): void
    {
        Queue::fake();
        $context = $this->context();
        $context['ticket']->seguidores()->attach($context['analyst']->id);
        config(['ticket_notifications.outbound_enabled' => true]);

        $job = new DispatchStaffTicketActivity(
            'activity:test:' . $context['ticket']->id,
            $context['ticket']->id,
            $context['client']->id,
            'followers',
            [],
            'Nova atividade'
        );
        $job->handle();
        $job->handle();

        $this->assertDatabaseCount('notifications', 1);
        $this->assertDatabaseCount('notification_deliveries', 1);
        Queue::assertNotPushed(SendTicketNotificationMail::class);
    }

    public function test_timeline_paginates_and_formats_operational_changes(): void
    {
        config(['audit.console' => true]);
        Ticket::observe(new \OwenIt\Auditing\AuditableObserver());
        $context = $this->context();
        $this->actingAs($context['analyst']);
        $context['ticket']->update(['status' => 'pendente cliente']);
        $this->assertDatabaseHas('audits', [
            'auditable_type' => Ticket::class,
            'auditable_id' => $context['ticket']->id,
            'event' => 'updated',
        ]);
        $timeline = app(TicketTimelineService::class)->paginate($context['ticket']->fresh()->load('user'));

        $this->assertSame(50, $timeline->perPage());
        $change = collect($timeline->items())->firstWhere('type', 'alteracao');
        $this->assertNotNull($change);
        $this->assertSame('Status', collect($change['changes'])->firstWhere('field', 'status')['label']);
    }
}
