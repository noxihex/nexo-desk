<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Rules\CategoriaPertenceAoSetor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Ticket;
use App\Models\User;
use App\Support\ElapsedTime;

class TicketApiController extends Controller
{
    // Lista todos os tickets com paginação
    public function index()
    {
        return response()->json(
            Ticket::with(['categoria', 'cliente', 'empresa'])->paginate(100)
        );
    }

    // Mostra os detalhes de um ticket específico
    public function show($id)
    {
        $ticket = Ticket::with([
            'mensagens' => fn ($query) => $query->semInternas(),
            'attachments',
        ])->find($id);

        if (!$ticket) {
            return response()->json(['error' => 'Ticket não encontrado.'], 404);
        }

        return response()->json($ticket);
    }

    // Cria um novo ticket
    public function store(Request $request)
    {
        $request->validate([
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

        $ticket = Ticket::create([
            'assunto' => $request->assunto,
            'descricao' => $request->descricao,
            'categoria_id' => $request->categoria_id,
            'cliente_id' => $request->cliente_id,
            'empresa_id' => $request->empresa_id,
            'grupo_id' => $request->grupo_id,
            'setor_id' => $request->setor_id,
            'atribuido_ao_analista_id' => $request->atribuido_ao_analista_id,
            'user_id' => Auth::id(),
            'status' => 'aberto',
        ]);

        return response()->json([
            'message' => 'Ticket criado com sucesso.',
            'ticket' => $ticket
        ], 201);
    }

    // Finaliza um ticket existente
    public function finalizar(Request $request, $id)
    {
$user = Auth::user();

if (!$user) {
    return response()->json(['error' => 'Usuário não autenticado.'], 401);
}

$ticket = Ticket::findOrFail($id);

        if (!$ticket->categoria) {
            return response()->json(['error' => 'Ticket sem categoria não pode ser finalizado.'], 400);
        }

        $slaUpdate = $ticket->categoria->slaupdate ?? 30;

        $mensagens = $ticket->mensagens()->semInternas()->orderBy('created_at', 'asc')->get();
        $tempoTotalMinutos = 0;
        $dataReferencia = $ticket->created_at;

        foreach ($mensagens as $mensagem) {
            $tempoDecorrido = ElapsedTime::wholeMinutes($dataReferencia, $mensagem->created_at);
            $tempoTotalMinutos += min($tempoDecorrido, $slaUpdate);
            $dataReferencia = $mensagem->created_at;
        }

        $tempoFinal = ElapsedTime::wholeMinutes($dataReferencia, now());
        $tempoTotalMinutos += min($tempoFinal, $slaUpdate);

        $request->validate([
            'descricao_fechamento' => 'required|string',
            'horas' => 'required|integer|min:0',
            'minutos' => 'required|integer|min:0|max:59',
        ]);

        $ticket->update([
            'status' => 'fechado',
            'horas_gastas' => $tempoTotalMinutos,
            'descricao_final' => $request->descricao_fechamento,
            'atribuido_ao_analista_id' => $user->id,
            'finalizado_por_usuario_id' => $user->id,
            'data_hora_finalizado' => now(),
        ]);

        $ticket->mensagens()->create([
            'user_id' => $user->id,
            'descricao' => "{$user->name} finalizou o ticket via API. Relato final: {$request->descricao_fechamento}",
            'tipo' => 'sistema',
        ]);

        return response()->json(['message' => 'Ticket finalizado com sucesso.']);
    }

     public function addMessage(Request $request, $id)
    {
        // Valida se o campo 'descricao' foi enviado e não está vazio
        $request->validate([
            'descricao' => 'required|string',
        ]);

        // Busca o ticket pelo ID ou falha caso não encontre
        $ticket = Ticket::findOrFail($id);

        // Recupera o usuário autenticado via API
        $user = Auth::user();

        // Verifica se o usuário está autenticado
        if (!$user) {
            return response()->json(['error' => 'Usuário não autenticado.'], 401);
        }

        // Cria a nova mensagem associada ao ticket e ao usuário
        $mensagem = $ticket->mensagens()->create([
            'user_id' => $user->id,
            'descricao' => $request->descricao,
        ]);

        // Retorna uma resposta de sucesso com a mensagem criada
        return response()->json([
            'message' => 'Mensagem adicionada com sucesso.',
            'data' => $mensagem
        ], 201); // HTTP 201 Created
    }

        public function updateStatus(Request $request, $id)
    {
        // Valida se o campo 'status' foi enviado e se é um dos valores permitidos
        // A lista de status permitidos foi baseada na função update do TicketController
        $request->validate([
            'status' => 'required|in:aberto,pendente cliente,pendente analista,fechado',
        ]);

        // Busca o ticket pelo ID ou falha caso não encontre
        $ticket = Ticket::findOrFail($id);

        // Recupera o usuário autenticado via API
        $user = Auth::user();

        // Verifica se o usuário está autenticado
        if (!$user) {
            return response()->json(['error' => 'Usuário não autenticado.'], 401);
        }

        // Atualiza o status do ticket
        $ticket->status = $request->status;
        $ticket->save();

        // Cria uma mensagem para registrar a alteração de status no histórico do ticket
        $ticket->mensagens()->create([
            'user_id' => $user->id,
            'descricao' => "{$user->name} alterou o status do ticket para '{$request->status}'.",
            'tipo' => 'sistema',
        ]);

        // Retorna uma resposta de sucesso com o ticket atualizado
        return response()->json([
            'message' => 'Status do ticket atualizado com sucesso.',
            'ticket' => $ticket
        ]);
    }

    public function search(Request $request)
    {
        // Valida os parâmetros de entrada (todos são opcionais)
        $request->validate([
            'assunto' => 'nullable|string|max:255',
            'setor_id' => 'nullable|integer|exists:setores,id',
            'grupo_id' => 'nullable|integer|exists:grupos,id',
            'status' => 'nullable|string|in:aberto,fechado',
        ]);

        // Inicia a query base com os relacionamentos
        $query = Ticket::query()->with(['categoria', 'cliente', 'empresa']);

        // Aplica os filtros dinamicamente se eles existirem na requisição
        $query->when($request->input('assunto'), function ($q, $assunto) {
            return $q->where('assunto', 'like', "%{$assunto}%");
        });

        $query->when($request->input('setor_id'), function ($q, $setor_id) {
            return $q->where('setor_id', $setor_id);
        });

        $query->when($request->input('grupo_id'), function ($q, $grupo_id) {
            return $q->where('grupo_id', $grupo_id);
        });

        $query->when($request->input('status'), function ($q, $status) {
            if ($status === 'aberto') {
                return $q->where('status', '!=', 'fechado');
            }
            if ($status === 'fechado') {
                return $q->where('status', 'fechado');
            }
        });

        // Executa a query com paginação e mantém os parâmetros de filtro nos links da paginação
        $tickets = $query->paginate(100)->appends($request->query()); // <-- CORREÇÃO APLICADA AQUI

        return response()->json($tickets);
    }


    public function assumirTicket(Request $request, $id)
    {
        // Validação dos dados enviados pela API
        $request->validate([
            'analista_id'  => 'required|exists:users,id',
            'setor_id'     => 'required|exists:setores,id',
            'categoria_id' => [
                'required',
                'exists:categorias,id',
                new CategoriaPertenceAoSetor($request->input('setor_id')),
            ],
        ]);

        // Busca o ticket pelo ID
        $ticket = Ticket::findOrFail($id);

        // Busca o analista que foi enviado na API
        $analista = User::findOrFail($request->analista_id);

        // Atualiza setor e categoria do ticket
        $ticket->setor_id = $request->setor_id;
        $ticket->categoria_id = $request->categoria_id;

        // Atribui o ticket ao analista enviado
        $ticket->atribuido_ao_analista_id = $analista->id;

        // Atribui o grupo do usuário ao ticket, se ele tiver um grupo
        $ticket->grupo_id = $analista->grupo_id ?? null;

        // Preenche os campos de auditoria para indicar quem assumiu e quando
        $ticket->assumido_por_usuario_id = $analista->id;
        $ticket->data_hora_assumido = now();

        if ($ticket->status === 'aberto') {
            $ticket->status = 'pendente analista';
        }

        $ticket->save();

        // Cria uma mensagem de auditoria no histórico do ticket
        // Usando o relacionamento igual aos métodos anteriores
        $ticket->mensagens()->create([
            'user_id'   => $analista->id,
            'descricao' => "{$analista->name} assumiu o ticket.",
            'tipo' => 'sistema',
        ]);

        return response()->json([
            'message' => 'Ticket assumido com sucesso!',
            'ticket'  => $ticket
        ], 200);
    }

}
