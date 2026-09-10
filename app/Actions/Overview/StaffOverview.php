<?php

namespace App\Actions\Overview;

use App\Models\Empresa;
use App\Models\Setor;
use App\Models\Ticket;
use App\Models\User;
use App\Services\SlaDeadlineCalculator;

class StaffOverview
{
    private const ACTIVE_STATUSES = ['aberto', 'pendente cliente', 'pendente analista'];

    public function handle(User $user, array $filters = []): array
    {
        $filters = $this->normalizeFilters($filters);
        $metrics = $this->metrics($user);

        if (! $user->hasAnyRole(['supervisor', 'administrador'])) {
            return [
                'metrics' => $metrics,
                'ticketsAbertos' => collect(),
                'ticketsPendenteCliente' => collect(),
                'ticketsPendenteAnalista' => collect(),
                'ticketsFechados' => collect(),
                'analistas' => collect(),
                'setores' => collect(),
                'empresas' => collect(),
                'filters' => $filters,
            ];
        }

        $baseQuery = Ticket::with(['empresa', 'categoria', 'analista']);

        if ($user->deveRestringirTicketsAoSetor()) {
            $baseQuery->where('setor_id', $user->setor_id);
        }

        foreach ([
            'atribuido_ao_analista_id' => 'analista',
            'setor_id' => 'setor',
            'empresa_id' => 'empresa',
        ] as $column => $filter) {
            if ($filters[$filter] !== '') {
                $baseQuery->where($column, $filters[$filter]);
            }
        }

        $baseQuery->orderBy('created_at', $filters['order'])->orderBy('id', $filters['order']);

        return [
            'metrics' => $metrics,
            'ticketsAbertos' => (clone $baseQuery)->where('status', 'aberto')->get(),
            'ticketsPendenteCliente' => (clone $baseQuery)->where('status', 'pendente cliente')->get(),
            'ticketsPendenteAnalista' => (clone $baseQuery)->where('status', 'pendente analista')->get(),
            'ticketsFechados' => (clone $baseQuery)->where('status', 'fechado')->limit(100)->get(),
            'analistas' => $this->analysts($user),
            'setores' => $this->sectors($user),
            'empresas' => Empresa::orderBy('nome')->get(),
            'filters' => $filters,
        ];
    }

    public function attentionTicketIds(User $user): array
    {
        $calculator = app(SlaDeadlineCalculator::class);

        return Ticket::with('categoria')
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->where('atribuido_ao_analista_id', $user->id)
            ->whereHas('categoria', fn ($query) => $query->whereNotNull('slaupdate'))
            ->get()
            ->filter(fn (Ticket $ticket) => $calculator->updateIsDue($ticket))
            ->pluck('id')
            ->values()
            ->all();
    }

    private function metrics(User $user): array
    {
        $unassignedIds = $user->setor_id
            ? Ticket::where('setor_id', $user->setor_id)
                ->where('status', '!=', 'fechado')
                ->whereNull('atribuido_ao_analista_id')
                ->orderByDesc('created_at')
                ->pluck('id')
                ->all()
            : [];
        $attentionIds = $this->attentionTicketIds($user);

        return [
            'mine' => Ticket::whereIn('status', self::ACTIVE_STATUSES)
                ->where('atribuido_ao_analista_id', $user->id)
                ->count(),
            'sector' => $user->setor_id
                ? Ticket::whereIn('status', self::ACTIVE_STATUSES)->where('setor_id', $user->setor_id)->count()
                : 0,
            'unassigned' => count($unassignedIds),
            'unassignedIds' => $unassignedIds,
            'attention' => count($attentionIds),
            'attentionIds' => $attentionIds,
        ];
    }

    private function analysts(User $user)
    {
        return User::whereHas('roles', fn ($query) => $query->whereIn('name', ['analista', 'supervisor', 'administrador']))
            ->where('status', true)
            ->when($user->deveRestringirTicketsAoSetor(), fn ($query) => $query->where('setor_id', $user->setor_id))
            ->orderBy('name')
            ->get();
    }

    private function sectors(User $user)
    {
        return Setor::when(
            $user->deveRestringirTicketsAoSetor(),
            fn ($query) => $query->whereKey($user->setor_id)
        )->orderBy('nome')->get();
    }

    private function normalizeFilters(array $filters): array
    {
        $order = (string) ($filters['order'] ?? 'desc');

        return [
            'order' => in_array($order, ['asc', 'desc'], true) ? $order : 'desc',
            'analista' => (string) ($filters['analista'] ?? ''),
            'setor' => (string) ($filters['setor'] ?? ''),
            'empresa' => (string) ($filters['empresa'] ?? ''),
        ];
    }
}
