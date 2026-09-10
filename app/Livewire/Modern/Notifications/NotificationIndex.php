<?php

namespace App\Livewire\Modern\Notifications;

use App\Actions\Notifications\ManageNotifications;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class NotificationIndex extends Component
{
    use WithPagination;

    #[Url(except: 'all')]
    public string $filter = 'all';

    public function boot(): void
    {
        app(ManageNotifications::class)->user();
    }

    public function updatedFilter(string $value): void
    {
        $this->filter = in_array($value, ['all', 'unread'], true) ? $value : 'all';
        $this->resetPage();
    }

    public function markAsRead(string $id): void
    {
        $manager = app(ManageNotifications::class);
        $manager->markAsRead($manager->user(), $id);
    }

    public function markAllAsRead(): void
    {
        $manager = app(ManageNotifications::class);
        $manager->markAllAsRead($manager->user());
        $this->resetPage();
    }

    public function openNotification(string $id): void
    {
        $manager = app(ManageNotifications::class);
        $user = $manager->user();
        $notification = $manager->markAsRead($user, $id);
        $destination = $manager->destination($user, $notification);

        if ($destination) {
            $this->redirect($destination, navigate: false);
        }
    }

    public function render()
    {
        $user = app(ManageNotifications::class)->user();
        $query = $user->notifications()->latest();

        if ($this->filter === 'unread') {
            $query->whereNull('read_at');
        }

        return view('livewire.modern.notifications.notification-index', [
            'notifications' => $query->paginate(15),
            'unreadCount' => $user->unreadNotifications()->count(),
        ]);
    }
}
