<?php

namespace App\Livewire\Modern\Notifications;

use App\Actions\Notifications\ManageNotifications;
use Livewire\Component;

class NotificationCenter extends Component
{
    public int $unreadCount = 0;

    public function boot(): void
    {
        app(ManageNotifications::class)->user();
    }

    public function mount(): void
    {
        $this->unreadCount = app(ManageNotifications::class)->user()->unreadNotifications()->count();
    }

    public function refreshNotifications(): void
    {
        $count = app(ManageNotifications::class)->user()->unreadNotifications()->count();

        if ($count > $this->unreadCount) {
            $this->dispatch('notification-received', count: $count - $this->unreadCount);
        }

        $this->unreadCount = $count;
    }

    public function markAllAsRead(): void
    {
        $manager = app(ManageNotifications::class);
        $manager->markAllAsRead($manager->user());
        $this->unreadCount = 0;
    }

    public function openNotification(string $id): void
    {
        $manager = app(ManageNotifications::class);
        $user = $manager->user();
        $notification = $manager->markAsRead($user, $id);
        $this->unreadCount = $user->unreadNotifications()->count();
        $destination = $manager->destination($user, $notification);

        if ($destination) {
            $this->redirect($destination, navigate: false);
        }
    }

    public function render()
    {
        $user = app(ManageNotifications::class)->user();

        return view('livewire.modern.notifications.notification-center', [
            'notifications' => $user->notifications()->latest()->limit(20)->get(),
        ]);
    }
}
