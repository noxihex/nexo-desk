<?php

namespace Tests\Feature;

use App\Livewire\Modern\Administration\AuditIndex;
use App\Models\Backup;
use App\Models\InboundMailbox;
use App\Models\Setor;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ModernAdministrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $permission = Permission::findOrCreate('acesso admin', 'web');

        foreach (['cliente', 'analista', 'supervisor', 'administrador'] as $name) {
            $role = Role::findOrCreate($name, 'web');

            if ($name === 'administrador') {
                $role->givePermissionTo($permission);
            }
        }
    }

    private function user(string $role): User
    {
        $user = User::factory()->create(['status' => true]);
        $user->assignRole($role);

        return $user;
    }

    public function test_all_administration_pages_use_modern_layout_and_navigation(): void
    {
        $admin = $this->user('administrador');
        $sector = Setor::create(['nome' => 'Suporte']);
        InboundMailbox::create(['address' => 'suporte@example.com', 'setor_id' => $sector->id, 'active' => true]);
        Backup::create(['data_hora' => now(), 'local_arquivo' => 'backup.sql', 'status' => 'sucesso']);
        DB::table('sessions')->insert([
            'id' => 'session-modern-admin',
            'user_id' => $admin->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Navegador de teste',
            'payload' => 'payload',
            'last_activity' => now()->timestamp,
        ]);
        DB::table('audits')->insert([
            'user_type' => User::class,
            'user_id' => $admin->id,
            'event' => 'updated',
            'auditable_type' => User::class,
            'auditable_id' => $admin->id,
            'old_values' => json_encode(['name' => 'Antes']),
            'new_values' => json_encode(['name' => 'Depois']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $pages = [
            route('administracao.usuarioslogados') => ['Usuários logados', 'Navegador de teste'],
            route('inbound-mailboxes.index') => ['Caixas de e-mail', 'suporte@example.com'],
            route('auditoria.index') => ['Auditoria', 'Valores anteriores'],
            route('backup.index') => ['Backups', 'backup.sql'],
        ];

        foreach ($pages as $url => [$heading, $content]) {
            $this->actingAs($admin)->get($url)
                ->assertOk()
                ->assertSee($heading)
                ->assertSee($content)
                ->assertSeeInOrder(['Administração', 'Usuários logados', 'Caixas de e-mail', 'Auditoria', 'Backups'])
                ->assertDontSee('Novo ticket')
                ->assertDontSee('jquery', false)
                ->assertDontSee('adminlte', false);
        }
    }

    public function test_logged_users_page_displays_last_activity_in_application_timezone(): void
    {
        $admin = $this->user('administrador');
        $user = $this->user('cliente');
        DB::table('sessions')->insert([
            'id' => 'session-with-known-activity',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Navegador de teste',
            'payload' => 'payload',
            'last_activity' => 1789134000,
        ]);

        $this->actingAs($admin)
            ->get(route('administracao.usuarioslogados'))
            ->assertSee('11/09/2026 10:40:00');
    }

    public function test_audit_page_filters_records_by_user_action_object_and_date_range(): void
    {
        $admin = $this->user('administrador');
        $admin->update(['name' => 'Administradora da auditoria']);
        $auditor = User::factory()->create(['name' => 'Responsavel da auditoria', 'status' => true]);

        DB::table('audits')->insert([
            [
                'user_type' => User::class,
                'user_id' => $auditor->id,
                'event' => 'updated',
                'auditable_type' => Ticket::class,
                'auditable_id' => 1234,
                'old_values' => json_encode(['status' => 'Alteração selecionada']),
                'new_values' => json_encode(['status' => 'Concluído']),
                'created_at' => '2026-09-01 12:00:00',
                'updated_at' => '2026-09-01 12:00:00',
            ],
            [
                'user_type' => User::class,
                'user_id' => $admin->id,
                'event' => 'created',
                'auditable_type' => User::class,
                'auditable_id' => $admin->id,
                'old_values' => json_encode(['name' => 'Alteração fora dos filtros']),
                'new_values' => json_encode(['name' => 'Outro registro']),
                'created_at' => '2026-09-03 12:00:00',
                'updated_at' => '2026-09-03 12:00:00',
            ],
        ]);

        $this->actingAs($admin);

        Livewire::test(AuditIndex::class)
            ->set('userId', (string) $auditor->id)
            ->set('event', 'updated')
            ->set('auditableType', Ticket::class)
            ->set('auditableId', '1234')
            ->set('from', '2026-09-01')
            ->set('to', '2026-09-02')
            ->call('applyFilters')
            ->assertHasNoErrors()
            ->assertSee('Todas as ações')
            ->assertSee('Todos os objetos')
            ->assertSee('Administradora da auditoria (Equipe)', false)
            ->assertSee('Responsavel da auditoria (Cliente)', false)
            ->assertSee('Alteração selecionada')
            ->assertDontSee('Alteração fora dos filtros');
    }

    public function test_audit_page_pagination_updates_with_livewire(): void
    {
        $admin = $this->user('administrador');
        $audits = collect(range(1, 11))->map(fn (int $id) => [
            'user_type' => User::class,
            'user_id' => $admin->id,
            'event' => 'updated',
            'auditable_type' => Ticket::class,
            'auditable_id' => $id,
            'old_values' => json_encode(['status' => 'Anterior']),
            'new_values' => json_encode(['status' => 'Atualizado']),
            'created_at' => sprintf('2026-09-01 12:%02d:00', 60 - $id),
            'updated_at' => sprintf('2026-09-01 12:%02d:00', 60 - $id),
        ])->all();
        DB::table('audits')->insert($audits);

        $this->actingAs($admin);

        Livewire::test(AuditIndex::class)
            ->set('event', 'updated')
            ->call('applyFilters')
            ->assertViewHas('audits', fn ($audits) => $audits->currentPage() === 1)
            ->assertSee('wire:click="gotoPage(2, \'page\')"', false)
            ->call('gotoPage', 2)
            ->assertViewHas('audits', fn ($audits) => $audits->currentPage() === 2)
            ->assertSee('Ticket #11');
    }

    public function test_administration_pages_reject_guests_and_non_administrators(): void
    {
        $urls = [
            route('administracao.usuarioslogados'),
            route('inbound-mailboxes.index'),
            route('auditoria.index'),
            route('backup.index'),
        ];

        foreach ($urls as $url) {
            $this->get($url)->assertRedirect('/login');
        }

        foreach (['cliente', 'analista', 'supervisor'] as $role) {
            $this->actingAs($this->user($role));

            foreach ($urls as $url) {
                $this->get($url)->assertForbidden();
            }
        }
    }

    public function test_administration_navigation_is_available_in_every_staff_layout(): void
    {
        $admin = $this->user('administrador');
        $this->actingAs($admin);

        foreach ([
            route('home'),
            route('tickets.index'),
            route('empresas.index'),
            route('relatorios.horas'),
            route('minhaconta.edit'),
        ] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSeeInOrder(['Administração', 'Usuários logados', 'Caixas de e-mail', 'Auditoria', 'Backups']);
        }
    }

    public function test_shared_navigation_preserves_the_staff_permission_matrix(): void
    {
        $analystResponse = $this->actingAs($this->user('analista'))->get(route('home'))->assertOk();
        $analystResponse->assertSee('Tickets')->assertDontSee('Cadastros')->assertDontSee('Relatórios')->assertDontSee('Administração');

        $supervisorResponse = $this->actingAs($this->user('supervisor'))->get(route('home'))->assertOk();
        $supervisorResponse->assertSee('Tickets')->assertSee('Cadastros')->assertSee('Relatórios')->assertDontSee('Administração');

        $administratorResponse = $this->actingAs($this->user('administrador'))->get(route('home'))->assertOk();
        $administratorResponse->assertSee('Tickets')->assertSee('Cadastros')->assertSee('Relatórios')->assertSee('Administração');

        foreach (['staff', 'tickets', 'cadastros', 'reports'] as $layout) {
            $this->assertStringContainsString(
                '<x-modern.staff.navigation',
                File::get(resource_path("views/components/modern/{$layout}/layout.blade.php")),
            );
        }

        $this->assertStringContainsString('<x-modern.client.navigation', File::get(resource_path('views/components/modern/client-tickets/layout.blade.php')));
    }

    public function test_administrator_can_end_a_specific_session(): void
    {
        $admin = $this->user('administrador');
        $target = $this->user('cliente');
        DB::table('sessions')->insert([
            'id' => 'session-to-end',
            'user_id' => $target->id,
            'ip_address' => null,
            'user_agent' => null,
            'payload' => 'payload',
            'last_activity' => now()->timestamp,
        ]);

        $this->actingAs($admin)
            ->delete(route('administracao.usuarioslogados.deslogar', 'session-to-end'))
            ->assertRedirect(route('administracao.usuarioslogados'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('sessions', ['id' => 'session-to-end']);
    }

    public function test_mailbox_http_crud_remains_available_from_modern_page(): void
    {
        $admin = $this->user('administrador');
        $firstSector = Setor::create(['nome' => 'Suporte']);
        $secondSector = Setor::create(['nome' => 'Financeiro']);
        $this->actingAs($admin);

        $this->post(route('inbound-mailboxes.store'), [
            'address' => 'Suporte@Example.com',
            'setor_id' => $firstSector->id,
            'active' => true,
        ])->assertRedirect();

        $mailbox = InboundMailbox::firstOrFail();
        $this->put(route('inbound-mailboxes.update', $mailbox), [
            'address' => 'financeiro@example.com',
            'setor_id' => $secondSector->id,
            'active' => false,
        ])->assertRedirect();

        $this->assertDatabaseHas('inbound_mailboxes', [
            'id' => $mailbox->id,
            'address' => 'financeiro@example.com',
            'setor_id' => $secondSector->id,
            'active' => false,
        ]);

        $this->delete(route('inbound-mailboxes.destroy', $mailbox))->assertRedirect();
        $this->assertDatabaseMissing('inbound_mailboxes', ['id' => $mailbox->id]);
    }
}
