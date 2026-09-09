<?php

namespace App\Actions\Tickets;

use App\Models\Categoria;
use App\Models\Empresa;
use App\Models\Setor;
use App\Models\Ticket;
use App\Models\User;
use App\Support\ElapsedTime;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class TicketListing
{
    public const GENERAL = 'general';
    public const MINE = 'mine';
    public const PENDING = 'pending';

    public function handle(User $user, string $section, array $filters = []): array
    {
        abort_unless(in_array($section, [self::GENERAL, self::MINE, self::PENDING], true), 404);

        $query = Ticket::with(['categoria', 'user.roles', 'cliente', 'empresa', 'setor', 'analista']);

        if ($user->deveRestringirTicketsAoSetor()) {
            $query->where('setor_id', $user->setor_id);
        }

        if ($section === self::MINE) {
            $query->where('atribuido_ao_analista_id', $user->id);
        } elseif ($section === self::PENDING) {
            $query->whereNull('categoria_id')->where('status', '!=', 'fechado');
        } else {
            $this->applyGeneralFilters($query, $filters);
        }

        if ($section !== self::PENDING && ! ($filters['showClosed'] ?? false)) {
            $query->where('status', '!=', 'fechado');
        }

        $sort = $this->normalizeSort($filters['sort'] ?? 'created_at');
        $tickets = $section === self::GENERAL && $sort === 'sla'
            ? $this->paginateBySla($query, $filters)
            : $query->orderBy($sort, 'desc')->paginate(10)->withQueryString();

        return [
            'tickets' => $tickets,
            'setores' => $section === self::GENERAL ? $this->setores($user) : collect(),
            'categorias' => $section === self::GENERAL ? $this->categorias($user) : collect(),
            'empresas' => $section === self::GENERAL ? Empresa::orderBy('nome')->get() : collect(),
        ];
    }

    private function applyGeneralFilters($query, array $filters): void
    {
        $search = trim((string) ($filters['search'] ?? ''));

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('id', 'like', '%'.$search.'%')
                    ->orWhere('assunto', 'like', '%'.$search.'%');
            });
        }

        foreach (['setor_id', 'categoria_id', 'empresa_id'] as $field) {
            if (($filters[$field] ?? '') !== '') {
                $query->where($field, $filters[$field]);
            }
        }
    }

    private function paginateBySla($query, array $filters): LengthAwarePaginator
    {
        $reference = now();
        $tickets = $query->get()->sortByDesc(function (Ticket $ticket) use ($reference) {
            $slaTotal = $ticket->categoria->slatotal ?? 0;
            $createdAt = $ticket->created_at ?: $reference;
            $finishedAt = $ticket->status === 'fechado'
                ? ($ticket->data_hora_finalizado ? Carbon::parse($ticket->data_hora_finalizado) : $createdAt)
                : $reference;
            $elapsed = ElapsedTime::wholeMinutes($finishedAt, $createdAt);

            return $slaTotal > 0 ? ($elapsed / $slaTotal) * 100 : 0;
        });
        $perPage = 10;
        $page = LengthAwarePaginator::resolveCurrentPage();

        return new LengthAwarePaginator(
            $tickets->slice(($page - 1) * $perPage, $perPage)->values(),
            $tickets->count(),
            $perPage,
            $page,
            [
                'path' => route('tickets.index'),
                'query' => array_filter([
                    'search' => $filters['search'] ?? '',
                    'sort' => 'sla',
                    'showClosed' => ($filters['showClosed'] ?? false) ? '1' : null,
                    'setor_id' => $filters['setor_id'] ?? '',
                    'categoria_id' => $filters['categoria_id'] ?? '',
                    'empresa_id' => $filters['empresa_id'] ?? '',
                ], fn ($value) => $value !== '' && $value !== null),
            ]
        );
    }

    private function setores(User $user)
    {
        return Setor::query()
            ->when($user->deveRestringirTicketsAoSetor(), fn ($query) => $query->whereKey($user->setor_id))
            ->orderBy('nome')
            ->get();
    }

    private function categorias(User $user)
    {
        return Categoria::query()
            ->when(
                $user->deveRestringirTicketsAoSetor(),
                fn ($query) => $query->whereHas('setores', fn ($setores) => $setores->whereKey($user->setor_id))
            )
            ->orderBy('nome')
            ->get();
    }

    private function normalizeSort(string $sort): string
    {
        return in_array($sort, ['created_at', 'updated_at', 'sla'], true) ? $sort : 'created_at';
    }
}
