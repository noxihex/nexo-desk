<?php

namespace Tests\Feature;

use App\Livewire\Modern\ClientTickets\ClientTicketCreate;
use App\Livewire\Modern\ClientTickets\ClientTicketIndex;
use App\Livewire\Modern\ClientTickets\ClientTicketShow;
use App\Models\Empresa;
use App\Models\Categoria;
use App\Models\Mensagem;
use App\Models\Setor;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ModernClientTicketFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        foreach (['cliente', 'analista'] as $role) {
            Role::findOrCreate($role, 'web');
        }
    }

    private function client(Empresa $company, array $attributes = []): User
    {
        $client = User::factory()->create($attributes + [
            'empresa_id' => $company->id,
            'status' => true,
        ]);
        $client->assignRole('cliente');

        return $client;
    }

    private function company(string $name, string $cnpj): Empresa
    {
        return Empresa::create(['nome' => $name, 'cnpj' => $cnpj]);
    }

    private function ticket(User $client, array $attributes = []): Ticket
    {
        return Ticket::create($attributes + [
            'assunto' => 'Falha no portal',
            'descricao' => 'Detalhes da solicitação',
            'user_id' => $client->id,
            'cliente_id' => $client->id,
            'empresa_id' => $client->empresa_id,
            'status' => 'aberto',
        ]);
    }

    public function test_client_entries_use_the_modern_layout_without_legacy_assets(): void
    {
        $company = $this->company('Empresa A', '12345678000190');
        $client = $this->client($company);
        $ticket = $this->ticket($client);
        $this->actingAs($client);

        foreach ([
            route('tickets.cliente.index'),
            route('tickets.cliente.create'),
            route('tickets.cliente.show', $ticket),
        ] as $url) {
            $this->get($url)->assertOk()
                ->assertSee('Tema escuro')
                ->assertDontSee('jquery', false)
                ->assertDontSee('adminlte', false)
                ->assertDontSee('wire:navigate', false);
        }

        $this->get(route('tickets.cliente.create'))
            ->assertSee('Pesquisar setor...')
            ->assertSee('Arraste arquivos ou clique para selecionar');
        $this->get(route('tickets.cliente.show', $ticket))
            ->assertSee('Enviar nova mensagem')
            ->assertDontSee('Horas gastas')
            ->assertDontSee('Finalizar ticket');
    }

    public function test_listing_switches_between_personal_and_company_tickets_without_leaking_companies(): void
    {
        $company = $this->company('Empresa A', '12345678000190');
        $otherCompany = $this->company('Empresa B', '12345678000191');
        $client = $this->client($company);
        $coworker = $this->client($company);
        $external = $this->client($otherCompany);
        $own = $this->ticket($client, ['assunto' => 'Chamado pessoal']);
        $companyTicket = $this->ticket($coworker, ['assunto' => 'Chamado da empresa']);
        $externalTicket = $this->ticket($external, ['assunto' => 'Chamado externo']);
        $this->actingAs($client);

        Livewire::test(ClientTicketIndex::class)
            ->assertSee($own->assunto)
            ->assertDontSee($companyTicket->assunto)
            ->assertDontSee($externalTicket->assunto)
            ->call('toggleCompanyView')
            ->assertSee($own->assunto)
            ->assertSee($companyTicket->assunto)
            ->assertDontSee($externalTicket->assunto)
            ->set('search', 'empresa')
            ->call('applySearch')
            ->assertDontSee($own->assunto)
            ->assertSee($companyTicket->assunto);
    }

    public function test_client_creates_ticket_with_cumulative_multiple_attachments(): void
    {
        Storage::fake('public');
        $company = $this->company('Empresa A', '12345678000190');
        $client = $this->client($company);
        $sector = Setor::create(['nome' => 'Suporte']);
        $this->actingAs($client);

        Livewire::test(ClientTicketCreate::class, ['returnUrl' => route('tickets.cliente.index')])
            ->set('assunto', 'Novo chamado do cliente')
            ->set('descricao', 'Descrição completa do chamado')
            ->set('setor_id', $sector->id)
            ->set('newAnexos', [UploadedFile::fake()->create('evidencia.txt', 5, 'text/plain')])
            ->assertSet('anexos', fn ($files) => count($files) === 1)
            ->set('newAnexos', [UploadedFile::fake()->create('captura.png', 6, 'image/png')])
            ->assertSet('anexos', fn ($files) => count($files) === 2)
            ->call('save')
            ->assertRedirect(route('tickets.cliente.index'));

        $ticket = Ticket::where('assunto', 'Novo chamado do cliente')->firstOrFail();
        $this->assertSame($client->id, $ticket->user_id);
        $this->assertSame($client->id, $ticket->cliente_id);
        $this->assertSame($company->id, $ticket->empresa_id);
        $this->assertSame($sector->id, $ticket->setor_id);
        $this->assertSame('aberto', $ticket->status);
        $this->assertCount(2, $ticket->attachments);
        foreach ($ticket->attachments as $attachment) {
            Storage::disk('public')->assertExists($attachment->file_path);
        }
    }

    public function test_client_history_is_public_newest_first_and_loads_older_messages_by_ten(): void
    {
        $company = $this->company('Empresa A', '12345678000190');
        $client = $this->client($company);
        $ticket = $this->ticket($client);
        $this->actingAs($client);

        foreach (range(1, 5) as $number) {
            $message = $ticket->mensagens()->create([
                'user_id' => $client->id,
                'tipo' => Mensagem::TIPO_PUBLICA,
                'descricao' => 'Mensagem publica '.$number,
            ]);
            $message->forceFill(['created_at' => now()->subMinutes(5 - $number)])->saveQuietly();
        }
        $ticket->mensagens()->create([
            'user_id' => $client->id,
            'tipo' => Mensagem::TIPO_INTERNA,
            'descricao' => 'Nota interna secreta',
        ]);
        $ticket->mensagens()->create([
            'user_id' => $client->id,
            'tipo' => Mensagem::TIPO_SISTEMA,
            'descricao' => 'Evento de sistema secreto',
        ]);

        Livewire::test(ClientTicketShow::class, ['ticketId' => $ticket->id, 'returnUrl' => route('tickets.cliente.index')])
            ->assertSet('messageLimit', 3)
            ->assertSeeInOrder(['Mensagem publica 5', 'Mensagem publica 4', 'Mensagem publica 3'])
            ->assertDontSee('Mensagem publica 2')
            ->assertDontSee('Mensagem publica 1')
            ->assertDontSee('Nota interna secreta')
            ->assertDontSee('Evento de sistema secreto')
            ->assertSee('Exibir mensagens mais antigas')
            ->call('loadOlderMessages')
            ->assertSet('messageLimit', 13)
            ->assertSeeInOrder(['Mensagem publica 5', 'Mensagem publica 4', 'Mensagem publica 3', 'Mensagem publica 2', 'Mensagem publica 1'])
            ->assertDontSee('Exibir mensagens mais antigas');
    }

    public function test_client_reply_supports_multiple_attachments_and_sets_pending_analyst(): void
    {
        Storage::fake('public');
        $company = $this->company('Empresa A', '12345678000190');
        $client = $this->client($company);
        $ticket = $this->ticket($client, ['status' => 'pendente cliente']);
        $this->actingAs($client);

        Livewire::test(ClientTicketShow::class, ['ticketId' => $ticket->id, 'returnUrl' => route('tickets.cliente.index')])
            ->set('message', 'Nova resposta do cliente')
            ->set('newMessageAttachments', [UploadedFile::fake()->create('log.txt', 5, 'text/plain')])
            ->assertSet('messageAttachments', fn ($files) => count($files) === 1)
            ->set('newMessageAttachments', [UploadedFile::fake()->create('imagem.png', 6, 'image/png')])
            ->assertSet('messageAttachments', fn ($files) => count($files) === 2)
            ->call('sendMessage')
            ->assertSee('Mensagem enviada com sucesso!');

        $message = $ticket->mensagens()->where('descricao', 'Nova resposta do cliente')->firstOrFail();
        $this->assertSame(Mensagem::TIPO_PUBLICA, $message->tipo);
        $this->assertSame('pendente analista', $ticket->fresh()->status);
        $this->assertCount(2, $message->attachments);
        foreach ($message->attachments as $attachment) {
            $this->assertSame('public', $attachment->disk);
            Storage::disk('public')->assertExists($attachment->file_path);
        }
    }

    public function test_client_access_is_company_scoped_rechecked_and_ticket_identity_is_locked(): void
    {
        $company = $this->company('Empresa A', '12345678000190');
        $otherCompany = $this->company('Empresa B', '12345678000191');
        $client = $this->client($company);
        $external = $this->client($otherCompany);
        $ownTicket = $this->ticket($client);
        $externalTicket = $this->ticket($external);
        $this->actingAs($client);

        $this->get(route('tickets.cliente.show', $externalTicket))->assertNotFound();

        $component = Livewire::test(ClientTicketShow::class, ['ticketId' => $ownTicket->id, 'returnUrl' => route('tickets.cliente.index')]);
        $client->update(['status' => false]);
        $component->call('loadOlderMessages')->assertForbidden();

        $client->update(['status' => true]);
        $this->expectException(CannotUpdateLockedPropertyException::class);
        Livewire::test(ClientTicketShow::class, ['ticketId' => $ownTicket->id, 'returnUrl' => route('tickets.cliente.index')])
            ->set('ticketId', $externalTicket->id);
    }

    public function test_closed_ticket_hides_spent_time_and_message_composer(): void
    {
        $company = $this->company('Empresa A', '12345678000190');
        $client = $this->client($company);
        $ticket = $this->ticket($client, [
            'status' => 'fechado',
            'horas_gastas' => 75,
            'descricao_final' => 'Atendimento concluído',
        ]);
        $this->actingAs($client);

        Livewire::test(ClientTicketShow::class, ['ticketId' => $ticket->id, 'returnUrl' => route('tickets.cliente.index')])
            ->assertDontSee('Horas gastas')
            ->assertDontSee('1h 15min')
            ->assertSee('Atendimento concluído')
            ->assertDontSee('Enviar nova mensagem');
    }

    public function test_authorized_contact_can_finalize_any_company_ticket_with_automatic_time(): void
    {
        $company = $this->company('Empresa A', '12345678000190');
        $authorized = $this->client($company, ['pode_finalizar_tickets_empresa' => true]);
        $coworker = $this->client($company);
        $analyst = User::factory()->create(['status' => true]);
        $analyst->assignRole('analista');
        $category = Categoria::create([
            'nome' => 'Suporte',
            'prioridade' => 'Normal',
            'slatotal' => 120,
            'slaupdate' => 30,
        ]);
        $ticket = $this->ticket($coworker, [
            'categoria_id' => $category->id,
            'atribuido_ao_analista_id' => $analyst->id,
        ]);
        $ticket->forceFill(['created_at' => now()->subMinutes(10)])->saveQuietly();
        $this->actingAs($authorized);

        Livewire::test(ClientTicketShow::class, ['ticketId' => $ticket->id, 'returnUrl' => route('tickets.cliente.index')])
            ->assertSee('Finalizar ticket')
            ->call('openFinalize')
            ->assertSet('showFinalize', true)
            ->assertSee('calculadas automaticamente')
            ->set('finalDescription', 'Problema resolvido')
            ->call('finalize')
            ->assertHasNoErrors()
            ->assertSee('Ticket finalizado com sucesso!');

        $ticket->refresh();
        $this->assertSame('fechado', $ticket->status);
        $this->assertSame('Problema resolvido', $ticket->descricao_final);
        $this->assertSame($analyst->id, $ticket->finalizado_por_usuario_id);
        $this->assertDatabaseHas('mensagens', [
            'ticket_id' => $ticket->id,
            'user_id' => $authorized->id,
        ]);
        $this->assertGreaterThanOrEqual(10, $ticket->horas_gastas);
        $this->assertLessThanOrEqual(11, $ticket->horas_gastas);
    }

    public function test_contact_without_permission_cannot_finalize_company_ticket(): void
    {
        $company = $this->company('Empresa A', '12345678000190');
        $client = $this->client($company);
        $coworker = $this->client($company);
        $ticket = $this->ticket($coworker);
        $this->actingAs($client);

        Livewire::test(ClientTicketShow::class, ['ticketId' => $ticket->id, 'returnUrl' => route('tickets.cliente.index')])
            ->assertDontSee('Finalizar ticket')
            ->call('openFinalize')
            ->assertForbidden();

        $this->assertSame('aberto', $ticket->fresh()->status);
    }
}
