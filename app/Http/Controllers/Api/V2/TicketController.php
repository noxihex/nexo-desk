<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V2\TicketResource;
use App\Models\Categoria;
use App\Models\Grupo;
use App\Models\Setor;
use App\Models\Ticket;
use App\Models\User;
use App\Rules\CategoriaPertenceAoSetor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TicketController extends Controller
{
    private const RELATIONS = ['categoria', 'cliente', 'empresa', 'grupo', 'setor', 'analista'];

    public function index()
    {
        return TicketResource::collection(Ticket::with(self::RELATIONS)->paginate(100));
    }

    public function search(Request $request)
    {
        $request->validate([
            'assunto' => 'nullable|string|max:255',
            'setor_id' => 'nullable|integer|exists:setores,id',
            'grupo_id' => 'nullable|integer|exists:grupos,id',
            'status' => 'nullable|string|in:aberto,fechado',
        ]);

        $query = Ticket::with(self::RELATIONS);
        $query->when($request->input('assunto'), function ($query, $assunto) {
            $query->where('assunto', 'like', "%{$assunto}%");
        });
        $query->when($request->input('setor_id'), function ($query, $setorId) {
            $query->where('setor_id', $setorId);
        });
        $query->when($request->input('grupo_id'), function ($query, $grupoId) {
            $query->where('grupo_id', $grupoId);
        });
        $query->when($request->input('status'), function ($query, $status) {
            $status === 'aberto'
                ? $query->where('status', '!=', 'fechado')
                : $query->where('status', 'fechado');
        });

        return TicketResource::collection($query->paginate(100)->appends($request->query()));
    }

    public function show($id)
    {
        $ticket = Ticket::with(array_merge(self::RELATIONS, ['mensagens', 'attachments']))->find($id);
        if (!$ticket) {
            return response()->json(['error' => 'Ticket não encontrado.'], 404);
        }

        return new TicketResource($ticket);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'assunto' => 'required|string|max:255',
            'descricao' => 'required|string',
            'categoria_id' => [
                'required',
                'exists:categorias,id',
                new CategoriaPertenceAoSetor($request->input('setor_id')),
            ],
            'cliente_id' => 'required|exists:users,id',
            'empresa_id' => 'nullable|exists:empresas,id',
            'grupo_id' => 'nullable|exists:grupos,id',
            'setor_id' => 'nullable|exists:setores,id',
            'atribuido_ao_analista_id' => 'nullable|exists:users,id',
        ]);
        $data['user_id'] = $request->user()->id;
        $data['status'] = 'aberto';

        $ticket = Ticket::create($data)->load(self::RELATIONS);
        return (new TicketResource($ticket))->additional(['message' => 'Ticket criado com sucesso.'])
            ->response()->setStatusCode(201);
    }

    public function finalizar(Request $request, $id)
    {
        $data = $request->validate([
            'descricao_fechamento' => 'required|string',
            'horas' => 'required|integer|min:0',
            'minutos' => 'required|integer|min:0|max:59',
        ]);
        $ticket = Ticket::findOrFail($id);
        if (!$ticket->categoria) {
            return response()->json(['error' => 'Ticket sem categoria não pode ser finalizado.'], 400);
        }

        $slaUpdate = $ticket->categoria->slaupdate ?? 30;
        $tempoTotal = 0;
        $referencia = $ticket->created_at;
        foreach ($ticket->mensagens()->orderBy('created_at')->get() as $mensagem) {
            $tempoTotal += min($referencia->diffInMinutes($mensagem->created_at), $slaUpdate);
            $referencia = $mensagem->created_at;
        }
        $tempoTotal += min($referencia->diffInMinutes(now()), $slaUpdate);
        $user = $request->user();
        $ticket->update([
            'status' => 'fechado',
            'horas_gastas' => $tempoTotal,
            'descricao_final' => $data['descricao_fechamento'],
            'atribuido_ao_analista_id' => $user->id,
            'finalizado_por_usuario_id' => $user->id,
            'data_hora_finalizado' => now(),
        ]);
        $ticket->mensagens()->create([
            'user_id' => $user->id,
            'descricao' => "{$user->name} finalizou o ticket via API. Relato final: {$data['descricao_fechamento']}",
            'tipo' => 'sistema',
        ]);

        return response()->json(['message' => 'Ticket finalizado com sucesso.']);
    }

    public function addMessage(Request $request, $id)
    {
        $data = $request->validate(['descricao' => 'required|string']);
        $ticket = Ticket::findOrFail($id);
        $mensagem = $ticket->mensagens()->create([
            'user_id' => $request->user()->id,
            'descricao' => $data['descricao'],
        ]);
        return response()->json(['message' => 'Mensagem adicionada com sucesso.', 'data' => $mensagem], 201);
    }

    public function updateStatus(Request $request, $id)
    {
        $data = $request->validate(['status' => 'required|in:aberto,pendente cliente,pendente analista,fechado']);
        $ticket = Ticket::findOrFail($id);
        $ticket->update(['status' => $data['status']]);
        $ticket->mensagens()->create([
            'user_id' => $request->user()->id,
            'descricao' => "{$request->user()->name} alterou o status do ticket para '{$data['status']}'.",
            'tipo' => 'sistema',
        ]);
        return (new TicketResource($ticket->load(self::RELATIONS)))->additional(['message' => 'Status do ticket atualizado com sucesso.']);
    }

    public function assumir(Request $request, $id)
    {
        $data = $request->validate([
            'analista_id' => 'required|exists:users,id',
            'setor_id' => 'required|exists:setores,id',
            'categoria_id' => [
                'required',
                'exists:categorias,id',
                new CategoriaPertenceAoSetor($request->input('setor_id')),
            ],
        ]);
        $ticket = Ticket::findOrFail($id);
        $analista = User::findOrFail($data['analista_id']);
        $ticket->update([
            'setor_id' => $data['setor_id'],
            'categoria_id' => $data['categoria_id'],
            'atribuido_ao_analista_id' => $analista->id,
            'grupo_id' => $analista->grupo_id,
            'assumido_por_usuario_id' => $analista->id,
            'data_hora_assumido' => now(),
            'status' => $ticket->status === 'aberto' ? 'pendente analista' : $ticket->status,
        ]);
        $ticket->mensagens()->create(['user_id' => $analista->id, 'descricao' => "{$analista->name} assumiu o ticket.", 'tipo' => 'sistema']);
        return (new TicketResource($ticket->load(self::RELATIONS)))->additional(['message' => 'Ticket assumido com sucesso!']);
    }

    public function transferir(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);
        $setorDestino = $request->input('setor_id');
        $categoriaAtualCompativel = $ticket->categoria_id && $setorDestino
            && Categoria::whereKey($ticket->categoria_id)
                ->whereHas('setores', fn ($query) => $query->whereKey($setorDestino))
                ->exists();

        $data = $request->validate([
            'setor_id' => 'required|exists:setores,id',
            'grupo_id' => 'required|exists:grupos,id',
            'analista_id' => 'sometimes|nullable|exists:users,id',
            'categoria_id' => [
                Rule::requiredIf(!$categoriaAtualCompativel || $request->exists('categoria_id')),
                'nullable',
                'exists:categorias,id',
                new CategoriaPertenceAoSetor($setorDestino),
            ],
        ]);

        $ticket = DB::transaction(function () use ($request, $data, $id) {
            $ticket = Ticket::lockForUpdate()->findOrFail($id);
            $anterior = [
                'setor' => optional($ticket->setor)->nome ?: '-',
                'grupo' => optional($ticket->grupo)->nome ?: '-',
                'analista' => optional($ticket->analista)->name ?: '-',
            ];
            $ticket->setor_id = $data['setor_id'];
            $ticket->grupo_id = $data['grupo_id'];
            if ($request->exists('categoria_id')) {
                $ticket->categoria_id = $data['categoria_id'];
            }
            if ($request->exists('analista_id')) {
                $ticket->atribuido_ao_analista_id = $data['analista_id'];
            }
            $ticket->transferido_por_usuario_id = $request->user()->id;
            $ticket->data_hora_transferido = now();
            $ticket->save();
            $ticket->load(['setor', 'grupo', 'analista']);

            $alteracoes = [
                "setor de '{$anterior['setor']}' para '" . (optional($ticket->setor)->nome ?: '-') . "'",
                "grupo de '{$anterior['grupo']}' para '" . (optional($ticket->grupo)->nome ?: '-') . "'",
            ];
            if ($request->exists('analista_id')) {
                $alteracoes[] = "analista de '{$anterior['analista']}' para '" . (optional($ticket->analista)->name ?: '-') . "'";
            }
            $ticket->mensagens()->create([
                'user_id' => $request->user()->id,
                'descricao' => $request->user()->name . ' transferiu o ticket: ' . implode('; ', $alteracoes) . '.',
                'tipo' => 'sistema',
            ]);
            return $ticket;
        });

        return (new TicketResource($ticket->load(self::RELATIONS)))->additional(['message' => 'Ticket transferido com sucesso.']);
    }
}
