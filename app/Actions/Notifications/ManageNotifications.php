<?php

namespace App\Actions\Notifications;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Notifications\DatabaseNotification;

class ManageNotifications
{
    public function user(): User
    {
        $id = auth()->id();
        $user = $id ? User::with('roles')->find($id) : null;

        if (! $user) {
            throw new AuthenticationException;
        }

        abort_unless($user->status, 403);

        return $user;
    }

    public function notification(User $user, string $id): DatabaseNotification
    {
        $notification = $user->notifications()->whereKey($id)->first();

        abort_unless($notification, 404);

        return $notification;
    }

    public function markAsRead(User $user, string $id): DatabaseNotification
    {
        $notification = $this->notification($user, $id);
        $notification->markAsRead();

        return $notification;
    }

    public function markAllAsRead(User $user): void
    {
        $user->unreadNotifications()->update(['read_at' => now()]);
    }

    public function destination(User $user, DatabaseNotification $notification): ?string
    {
        $ticketId = filter_var($notification->data['ticket_id'] ?? null, FILTER_VALIDATE_INT);

        if (! $ticketId || $ticketId < 1) {
            return null;
        }

        if ($user->hasRole('cliente')) {
            return route('tickets.cliente.show', ['id' => $ticketId]);
        }

        if ($user->hasAnyRole(['analista', 'supervisor', 'administrador'])) {
            return route('tickets.show', ['ticket' => $ticketId]);
        }

        return null;
    }
}
