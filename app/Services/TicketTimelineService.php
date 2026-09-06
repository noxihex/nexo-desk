<?php

namespace App\Services;

use App\Models\Categoria;
use App\Models\Empresa;
use App\Models\Grupo;
use App\Models\Mensagem;
use App\Models\Setor;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Models\Audit;

class TicketTimelineService
{
    private const FIELDS = [
        'assunto' => 'Assunto',
        'status' => 'Status',
        'categoria_id' => 'Categoria',
        'setor_id' => 'Setor',
        'grupo_id' => 'Grupo',
        'atribuido_ao_analista_id' => 'Analista',
        'cliente_id' => 'Cliente',
        'empresa_id' => 'Empresa',
    ];

    public function paginate(Ticket $ticket, int $perPage = 50, string $pageName = 'timeline_page'): LengthAwarePaginator
    {
        $audits = Audit::with('user')
            ->where('auditable_type', Ticket::class)
            ->where('auditable_id', $ticket->id)
            ->where('event', 'updated')
            ->get();

        $auditEvents = $audits->map(function (Audit $audit) {
            $changes = [];
            foreach (self::FIELDS as $field => $label) {
                $old = $audit->old_values[$field] ?? null;
                $new = $audit->new_values[$field] ?? null;
                if (!array_key_exists($field, $audit->new_values) || (string) $old === (string) $new) {
                    continue;
                }
                $changes[] = [
                    'field' => $field,
                    'label' => $label,
                    'old' => $this->displayValue($field, $old),
                    'new' => $this->displayValue($field, $new),
                ];
            }
            if (!$changes) {
                return null;
            }
            return [
                'key' => 'audit-' . $audit->id,
                'type' => 'alteracao',
                'created_at' => Carbon::parse($audit->created_at),
                'actor' => optional($audit->user)->name ?: 'Sistema',
                'changes' => $changes,
            ];
        })->filter()->values();

        $messages = $ticket->mensagens()->with(['user', 'attachments', 'mencoes'])
            ->get()->reject(function (Mensagem $message) use ($auditEvents) {
                if ($message->tipo !== Mensagem::TIPO_SISTEMA) {
                    return false;
                }
                $operational = collect(['assumiu', 'transferiu', 'finalizou', 'alterou o status'])
                    ->contains(fn ($term) => stripos($message->descricao, $term) !== false);
                if (!$operational) {
                    return false;
                }
                return $auditEvents->contains(function ($event) use ($message) {
                    return abs($event['created_at']->diffInSeconds($message->created_at, false)) <= 3;
                });
            })->map(function (Mensagem $message) use ($ticket) {
                return [
                    'key' => 'message-' . $message->id,
                    'type' => $message->tipo ?: Mensagem::TIPO_PUBLICA,
                    'created_at' => Carbon::parse($message->created_at),
                    'actor' => optional($message->user)->name ?: 'Usuário removido',
                    'description' => $message->descricao,
                    'attachments' => $message->attachments,
                    'mentions' => $message->mencoes,
                    'ticket_id' => $ticket->id,
                ];
            });

        $creation = collect([[
            'key' => 'ticket-created-' . $ticket->id,
            'type' => 'criacao',
            'created_at' => Carbon::parse($ticket->created_at),
            'actor' => optional($ticket->user)->name ?: 'Usuário removido',
            'description' => 'Ticket criado.',
        ]]);

        $events = $creation->concat($messages)->concat($auditEvents)
            ->sortByDesc(fn ($event) => sprintf('%010d-%s', $event['created_at']->timestamp, $event['key']))
            ->values();
        $page = LengthAwarePaginator::resolveCurrentPage($pageName);

        return new LengthAwarePaginator(
            $events->forPage($page, $perPage)->values(),
            $events->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'pageName' => $pageName, 'query' => request()->query()]
        );
    }

    private function displayValue(string $field, $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }
        $model = null;
        $attribute = 'nome';
        if (in_array($field, ['atribuido_ao_analista_id', 'cliente_id'], true)) {
            $model = User::find($value);
            $attribute = 'name';
        } elseif ($field === 'categoria_id') {
            $model = Categoria::find($value);
        } elseif ($field === 'setor_id') {
            $model = Setor::find($value);
        } elseif ($field === 'grupo_id') {
            $model = Grupo::find($value);
        } elseif ($field === 'empresa_id') {
            $model = Empresa::find($value);
        }

        return $model ? (string) $model->{$attribute} : (substr($field, -3) === '_id' ? '#' . $value : (string) $value);
    }
}
