<?php

namespace Tests\Feature;

use App\Livewire\Modern\Notifications\NotificationCenter;
use App\Livewire\Modern\Notifications\NotificationIndex;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ModernNotificationCenterTest extends TestCase
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

    private function user(string $role = 'cliente'): User
    {
        $user = User::factory()->create(['status' => true]);
        $user->assignRole($role);

        return $user;
    }

    private function notification(User $user, array $data = [], bool $read = false): DatabaseNotification
    {
        return DatabaseNotification::create([
            'id' => (string) Str::uuid(),
            'type' => 'ticket.nova_mensagem',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => $data + [
                'event' => 'nova_mensagem',
                'ticket_id' => 123,
                'title' => 'Nova resposta no ticket #123',
                'message' => 'Uma nova mensagem foi adicionada.',
                'summary' => 'Resumo da conversa',
                'url' => 'https://example.invalid/nao-confiar',
            ],
            'read_at' => $read ? now() : null,
        ]);
    }

    public function test_notifications_page_uses_modern_layout_for_staff_and_client(): void
    {
        foreach (['cliente', 'analista'] as $role) {
            $user = $this->user($role);
            $this->notification($user);

            $response = $this->actingAs($user)->get(route('notifications.index'))
                ->assertOk()
                ->assertViewIs('notifications.index')
                ->assertSee('Notificações')
                ->assertSee('Somente não lidas')
                ->assertSee('Nova resposta no ticket #123')
                ->assertSee('Tema escuro')
                ->assertDontSee('jquery', false)
                ->assertDontSee('adminlte', false);

            if ($role === 'analista') {
                $response->assertDontSee('Novo ticket');
            }
        }
    }

    public function test_existing_json_contract_is_preserved_and_limited_to_latest_twenty(): void
    {
        $user = $this->user();

        foreach (range(1, 21) as $number) {
            $this->notification($user, ['title' => "Notificação {$number}"]);
        }

        $response = $this->actingAs($user)->getJson(route('notifications.index'));

        $response->assertOk()
            ->assertJsonPath('unread_count', 21)
            ->assertJsonCount(20, 'notifications')
            ->assertJsonStructure(['notifications' => [['id', 'title', 'message', 'url', 'read_at', 'created_at']]]);
    }

    public function test_quick_center_marks_notifications_and_uses_role_safe_ticket_routes(): void
    {
        $client = $this->user();
        $own = $this->notification($client);
        $other = $this->notification($this->user());
        $this->actingAs($client);

        Livewire::test(NotificationCenter::class)
            ->assertSet('unreadCount', 1)
            ->assertSee('Ver todas as notificações')
            ->call('openNotification', $own->id)
            ->assertRedirect(route('tickets.cliente.show', ['id' => 123]));

        $this->assertNotNull($own->fresh()->read_at);

        Livewire::test(NotificationCenter::class)
            ->call('openNotification', $other->id)
            ->assertNotFound();

        $analyst = $this->user('analista');
        $staffNotification = $this->notification($analyst, ['ticket_id' => 321]);
        $this->actingAs($analyst);

        Livewire::test(NotificationCenter::class)
            ->call('openNotification', $staffNotification->id)
            ->assertRedirect(route('tickets.show', ['ticket' => 321]));
    }

    public function test_full_center_filters_paginates_and_marks_all_as_read(): void
    {
        $user = $this->user();

        foreach (range(1, 16) as $number) {
            $this->notification($user, ['title' => "Aviso {$number}"], $number === 1);
        }

        $this->actingAs($user);

        Livewire::test(NotificationIndex::class)
            ->assertViewHas('notifications', fn ($notifications) => $notifications->total() === 16 && $notifications->perPage() === 15)
            ->assertViewHas('unreadCount', 15)
            ->set('filter', 'unread')
            ->assertSet('filter', 'unread')
            ->assertViewHas('notifications', fn ($notifications) => $notifications->total() === 15)
            ->call('markAllAsRead')
            ->assertViewHas('unreadCount', 0);

        $this->assertSame(0, $user->fresh()->unreadNotifications()->count());
    }

    public function test_livewire_requests_revalidate_account_status(): void
    {
        $user = $this->user();
        $this->actingAs($user);
        $component = Livewire::test(NotificationCenter::class);

        $user->update(['status' => false]);

        $component->call('refreshNotifications')->assertForbidden();
    }
}
