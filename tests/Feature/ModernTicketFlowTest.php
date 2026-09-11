<?php

namespace Tests\Feature;

use App\Livewire\Modern\Tickets\TicketForm;
use App\Livewire\Modern\Tickets\TicketShow;
use App\Models\Categoria;
use App\Models\Empresa;
use App\Models\Mensagem;
use App\Models\Setor;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketTimelineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ModernTicketFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        foreach (['cliente', 'analista', 'supervisor', 'administrador'] as $role) {
            Role::findOrCreate($role, 'web');
        }
    }

    private function user(string $role, array $attributes = []): User
    {
        $user = User::factory()->create($attributes + ['status' => true]);
        $user->assignRole($role);

        return $user;
    }

    private function context(string $role = 'supervisor'): array
    {
        $sector = Setor::create(['nome' => 'Suporte']);
        $category = Categoria::create([
            'nome' => 'Incidente', 'prioridade' => 'Normal', 'slatotal' => 120,
            'slaupdate' => 30, 'setor_id' => $sector->id,
        ]);
        $category->setores()->attach($sector);
        $company = Empresa::create(['nome' => 'Empresa A', 'cnpj' => '12345678000190']);
        $client = $this->user('cliente', ['empresa_id' => $company->id]);
        $actor = $this->user($role, ['setor_id' => $sector->id]);
        $mentioned = $this->user('analista', ['setor_id' => $sector->id]);
        $ticket = Ticket::create([
            'assunto' => 'Falha de acesso', 'descricao' => 'Detalhes da falha',
            'categoria_id' => $category->id, 'setor_id' => $sector->id,
            'empresa_id' => $company->id, 'cliente_id' => $client->id,
            'user_id' => $actor->id, 'status' => 'aberto',
        ]);

        return compact('sector', 'category', 'company', 'client', 'actor', 'mentioned', 'ticket');
    }

    public function test_create_edit_and_show_are_modern_entries_without_legacy_assets(): void
    {
        $context = $this->context();
        $this->actingAs($context['actor']);

        foreach ([route('tickets.create'), route('tickets.edit', $context['ticket']), route('tickets.show', $context['ticket'])] as $url) {
            $this->get($url)->assertOk()
                ->assertSee('Tema escuro')
                ->assertDontSee('jquery', false)
                ->assertDontSee('adminlte', false)
                ->assertDontSee('wire:navigate', false);
        }

        foreach ([route('tickets.create'), route('tickets.edit', $context['ticket'])] as $url) {
            $this->get($url)
                ->assertSee('role="combobox"', false)
                ->assertSee('Pesquisar contato...')
                ->assertSee('Pesquisar empresa...')
                ->assertSee('Pesquisar setor...')
                ->assertSee('Pesquisar categoria...')
                ->assertSee('Pesquisar analista responsável...');
        }

        $this->get(route('tickets.show', $context['ticket']))
            ->assertSee('Responder publicamente')
            ->assertSee('Adicionar nota interna')
            ->assertSee('Use @ para mencionar integrantes da equipe')
            ->assertSee($context['actor']->name.' (Equipe)')
            ->assertDontSee('Horas gastas');
    }

    public function test_ticket_details_identify_a_client_creator(): void
    {
        $context = $this->context();
        $context['ticket']->update(['user_id' => $context['client']->id]);
        $this->actingAs($context['actor']);

        $this->get(route('tickets.show', $context['ticket']))
            ->assertSee($context['client']->name.' (Cliente)');
    }

    public function test_livewire_creates_ticket_with_attachment_and_contact_company(): void
    {
        Storage::fake('public');
        $context = $this->context();
        $this->actingAs($context['actor']);

        Livewire::test(TicketForm::class, ['recordId' => null, 'returnUrl' => route('tickets.index')])
            ->set('assunto', 'Novo chamado')
            ->set('descricao', 'Descrição completa')
            ->set('cliente_id', $context['client']->id)
            ->assertSet('empresa_id', $context['company']->id)
            ->set('setor_id', $context['sector']->id)
            ->set('categoria_id', $context['category']->id)
            ->set('atribuido_ao_analista_id', $context['actor']->id)
            ->set('newAnexos', [UploadedFile::fake()->create('evidencia.txt', 5, 'text/plain')])
            ->assertSet('anexos', fn ($files) => count($files) === 1)
            ->set('newAnexos', [UploadedFile::fake()->create('captura.png', 6, 'image/png')])
            ->assertSet('anexos', fn ($files) => count($files) === 2)
            ->call('removeAnexo', 0)
            ->assertSet('anexos', fn ($files) => count($files) === 1)
            ->set('newAnexos', [UploadedFile::fake()->create('diagnostico.pdf', 7, 'application/pdf')])
            ->assertSet('anexos', fn ($files) => count($files) === 2)
            ->call('save')
            ->assertRedirect(route('tickets.index'));

        $ticket = Ticket::where('assunto', 'Novo chamado')->firstOrFail();
        $this->assertSame($context['actor']->id, $ticket->user_id);
        $this->assertSame('aberto', $ticket->status);
        $this->assertCount(2, $ticket->attachments);
        foreach ($ticket->attachments as $attachment) {
            Storage::disk('public')->assertExists($attachment->file_path);
        }
    }

    public function test_edit_uses_shared_action_and_preserves_historical_category_pair(): void
    {
        $context = $this->context();
        $context['category']->setores()->detach($context['sector']);
        $this->actingAs($context['actor']);

        Livewire::test(TicketForm::class, ['recordId' => $context['ticket']->id, 'returnUrl' => route('tickets.index')])
            ->set('assunto', 'Falha atualizada')
            ->call('save')
            ->assertRedirect(route('tickets.index'));

        $this->assertSame('Falha atualizada', $context['ticket']->fresh()->assunto);
    }

    public function test_details_support_public_reply_internal_note_mentions_and_private_attachment(): void
    {
        Queue::fake();
        Storage::fake('local');
        $context = $this->context();
        $this->actingAs($context['actor']);

        Livewire::test(TicketShow::class, ['ticketId' => $context['ticket']->id, 'returnUrl' => route('tickets.index')])
            ->set('message', 'Resposta ao cliente')
            ->call('sendPublicReply', 'pendente cliente')
            ->assertSee('Mensagem enviada com sucesso!')
            ->set('message', 'Verifique @')
            ->assertSet('mentioning', true)
            ->call('addMention', $context['mentioned']->id)
            ->assertSet('message', 'Verifique @'.$context['mentioned']->name.' ')
            ->set('newMessageAttachments', [UploadedFile::fake()->create('log.txt', 5, 'text/plain')])
            ->assertSet('messageAttachments', fn ($files) => count($files) === 1)
            ->set('newMessageAttachments', [UploadedFile::fake()->create('diagnostico.txt', 6, 'text/plain')])
            ->assertSet('messageAttachments', fn ($files) => count($files) === 2)
            ->call('removeMessageAttachment', 0)
            ->assertSet('messageAttachments', fn ($files) => count($files) === 1)
            ->call('sendInternalNote');

        $this->assertSame('pendente cliente', $context['ticket']->fresh()->status);
        $note = Mensagem::where('ticket_id', $context['ticket']->id)->where('tipo', 'interna')->firstOrFail();
        $this->assertDatabaseHas('mensagem_mencoes', ['mensagem_id' => $note->id, 'user_id' => $context['mentioned']->id]);
        $this->assertSame('local', $note->attachments()->firstOrFail()->disk);
        $this->assertCount(1, $note->attachments);
        $this->assertStringEndsWith('diagnostico.txt', $note->attachments->first()->file_path);
        Storage::disk('local')->assertExists($note->attachments()->first()->file_path);
    }

    public function test_history_starts_with_three_newest_messages_and_loads_older_messages(): void
    {
        $context = $this->context();
        $this->actingAs($context['actor']);

        foreach (range(1, 5) as $number) {
            $message = Mensagem::create([
                'ticket_id' => $context['ticket']->id,
                'user_id' => $context['actor']->id,
                'tipo' => Mensagem::TIPO_PUBLICA,
                'descricao' => 'Mensagem historica '.$number,
            ]);
            $message->forceFill(['created_at' => now()->subMinutes(5 - $number)])->saveQuietly();
        }

        $latest = app(TicketTimelineService::class)
            ->paginate($context['ticket'], 3, 'timeline_test_page', false, true);
        $this->assertSame(
            ['Mensagem historica 5', 'Mensagem historica 4', 'Mensagem historica 3'],
            collect($latest->items())->pluck('description')->all()
        );

        Livewire::test(TicketShow::class, ['ticketId' => $context['ticket']->id, 'returnUrl' => route('tickets.index')])
            ->set('conversationsOnly', true)
            ->assertSet('timelineLimit', 3)
            ->assertSeeInOrder(['Mensagem historica 5', 'Mensagem historica 4', 'Mensagem historica 3'])
            ->assertDontSee('Mensagem historica 2')
            ->assertDontSee('Mensagem historica 1')
            ->assertSee('Exibir mensagens mais antigas')
            ->call('loadOlderMessages')
            ->assertSet('timelineLimit', 13)
            ->assertSeeInOrder(['Mensagem historica 5', 'Mensagem historica 4', 'Mensagem historica 3', 'Mensagem historica 2', 'Mensagem historica 1'])
            ->assertDontSee('Exibir mensagens mais antigas');
    }

    public function test_details_support_follow_assume_transfer_and_finalize_actions(): void
    {
        Queue::fake();
        $context = $this->context();
        $destination = Setor::create(['nome' => 'Infraestrutura']);
        $destinationCategory = Categoria::create(['nome' => 'Rede', 'slatotal' => 60, 'slaupdate' => 15, 'setor_id' => $destination->id]);
        $destinationCategory->setores()->attach($destination);
        $destinationAnalyst = $this->user('analista', ['setor_id' => $destination->id]);
        $this->actingAs($context['actor']);

        $component = Livewire::test(TicketShow::class, ['ticketId' => $context['ticket']->id, 'returnUrl' => route('tickets.index')])
            ->call('toggleFollowing')
            ->assertSee('Você está seguindo este ticket.')
            ->call('openAssume')
            ->call('assume');

        $this->assertSame($context['actor']->id, $context['ticket']->fresh()->atribuido_ao_analista_id);

        $component->call('openTransfer')
            ->set('transferSector', $destination->id)
            ->set('transferCategory', $destinationCategory->id)
            ->set('transferAnalyst', $destinationAnalyst->id)
            ->call('transfer');

        $this->assertSame($destinationAnalyst->id, $context['ticket']->fresh()->atribuido_ao_analista_id);
        $component->call('openFinalize')
            ->set('finalDescription', 'Atendimento concluído')
            ->set('finalHours', 1)
            ->set('finalMinutes', 15)
            ->call('finalize');

        $ticket = $context['ticket']->fresh();
        $this->assertSame('fechado', $ticket->status);
        $this->assertSame(75, $ticket->horas_gastas);
        $this->assertSame('Atendimento concluído', $ticket->descricao_final);
        $component->assertSee('Tempo registrado: 1h 15min')
            ->assertDontSee('Horas gastas');
    }

    public function test_component_rechecks_access_and_locks_ticket_identity(): void
    {
        $context = $this->context();
        $this->actingAs($context['actor']);
        $component = Livewire::test(TicketShow::class, ['ticketId' => $context['ticket']->id, 'returnUrl' => route('tickets.index')]);
        $context['actor']->update(['status' => false]);
        $component->call('toggleFollowing')->assertForbidden();

        $context['actor']->update(['status' => true]);
        $this->expectException(CannotUpdateLockedPropertyException::class);
        Livewire::test(TicketShow::class, ['ticketId' => $context['ticket']->id, 'returnUrl' => route('tickets.index')])
            ->set('ticketId', 999999);
    }

    public function test_ticket_attachment_download_checks_ticket_relationship_and_access(): void
    {
        Storage::fake('public');
        $context = $this->context();
        Storage::disk('public')->put('anexos/manual.txt', 'conteúdo');
        $attachment = $context['ticket']->attachments()->create(['file_path' => 'anexos/manual.txt']);

        $this->actingAs($context['actor'])
            ->get(route('tickets.downloadAttachment', [$context['ticket'], $attachment]))
            ->assertOk()
            ->assertDownload('manual.txt');

        $other = Ticket::create(['assunto' => 'Outro', 'descricao' => 'Outro', 'user_id' => $context['actor']->id, 'status' => 'aberto', 'setor_id' => $context['sector']->id]);
        $this->get(route('tickets.downloadAttachment', [$other, $attachment]))->assertNotFound();
    }
}
