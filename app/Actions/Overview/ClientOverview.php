<?php

namespace App\Actions\Overview;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class ClientOverview
{
    private const ACTIVE_STATUSES = ['aberto', 'pendente analista', 'pendente cliente'];

    public function handle(User $user): array
    {
        $personal = $this->personalTickets($user);
        $company = $this->companyTickets($user);

        return [
            'metrics' => [
                'mine' => (clone $personal)->whereIn('status', self::ACTIVE_STATUSES)->count(),
                'company' => (clone $company)->whereIn('status', self::ACTIVE_STATUSES)->count(),
                'awaiting' => (clone $company)->where('status', 'pendente cliente')->count(),
                'closed' => (clone $company)->where('status', 'fechado')->count(),
            ],
            'ticketIds' => [
                'mine' => $this->ids((clone $personal)->whereIn('status', self::ACTIVE_STATUSES)),
                'company' => $this->ids((clone $company)->whereIn('status', self::ACTIVE_STATUSES)),
                'awaiting' => $this->ids((clone $company)->where('status', 'pendente cliente')),
                'closed' => $this->ids((clone $company)->where('status', 'fechado')),
            ],
            'companyAvailable' => $user->empresa_id !== null,
        ];
    }

    private function personalTickets(User $user): Builder
    {
        return Ticket::query()
            ->where('cliente_id', $user->id)
            ->when(
                $user->empresa_id !== null,
                fn (Builder $query) => $query->where('empresa_id', $user->empresa_id),
                fn (Builder $query) => $query->whereNull('empresa_id')
            );
    }

    private function companyTickets(User $user): Builder
    {
        return Ticket::query()
            ->when(
                $user->empresa_id !== null,
                fn (Builder $query) => $query->where('empresa_id', $user->empresa_id),
                fn (Builder $query) => $query->whereRaw('1 = 0')
            );
    }

    private function ids(Builder $query): array
    {
        return $query->orderByDesc('created_at')->orderByDesc('id')->pluck('id')->all();
    }
}
