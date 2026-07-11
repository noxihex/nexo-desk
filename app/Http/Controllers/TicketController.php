<?php

namespace App\Http\Controllers;

use App\Support\AttachmentRules;

use App\Models\Ticket;
use App\Models\Categoria;
use App\Models\Empresa;
use App\Models\User;
use App\Models\Grupo;
use App\Models\Setor;
use App\Models\Mensagem;
use App\Models\Notificacao;
use App\Models\TicketAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;


class TicketController extends Controller
{
    /**
     * Exibe a lista de tickets com ordenação opcional.
     */
    public function index(Request $request)
{
    $user = Auth::user();

    // Define um valor padrão para 'sort' se estiver vazio
    $sort = $request->input('sort') ?: 'created_at'; // Padrão é 'created_at'
    $search = $request->input('search'); // Termo de pesquisa

    // Define o valor de 'showClosed' baseado no parâmetro explícito ou no estado padrão
    $showClosed = $request->has('showClosed') ? $request->input('showClosed') : ($search ? '1' : '0');

    // Obtém os filtros de setor e grupo da requisição
    $setorId = $request->input('setor_id');
    $grupoId = $request->input('grupo_id');

    // Query base com os relacionamentos necessários
    $ticketsQuery = Ticket::with('categoria', 'user', 'cliente', 'empresa', 'grupo', 'setor', 'analista');

    if ($user->hasRole('analista') && !$user->hasRole(['supervisor', 'administrador'])) {
        $ticketsQuery->where('setor_id', $user->setor_id);
    }

    // Lógica de pesquisa (ID ou Assunto)
    if ($search) {
        $ticketsQuery->where(function ($query) use ($search) {
            $query->where('id', 'like', "%$search%")
                  ->orWhere('assunto', 'like', "%$search%");
        });
    }

    // Filtro para tickets fechados (mostrar ou ocultar)
    if ($showClosed === '0') {
        $ticketsQuery->where('status', '!=', 'fechado');
    }

    // Filtro por setor
    if ($setorId) {
        $ticketsQuery->where('setor_id', $setorId);
    }

    // Filtro por grupo
    if ($grupoId) {
        $ticketsQuery->where('grupo_id', $grupoId);
    }

    // Ordenação por SLA
    if ($sort === 'sla') {
        $tickets = $ticketsQuery->get(); // Coleta todos os tickets para ordenação manual

        // Calcula o SLA e ordena manualmente
        $tickets = $tickets->sortByDesc(function ($ticket) {
            $slaTotal = $ticket->categoria->slatotal ?? 0;

            $dataCriacao = $ticket->created_at ? \Carbon\Carbon::parse($ticket->created_at) : now();

            if ($ticket->status === 'fechado') {
                $dataFinalizacao = $ticket->data_hora_finalizado ? \Carbon\Carbon::parse($ticket->data_hora_finalizado) : $dataCriacao;
                $minutosDecorridos = $dataFinalizacao->diffInMinutes($dataCriacao);
            } else {
                $minutosDecorridos = now()->diffInMinutes($dataCriacao);
            }

            return $slaTotal > 0 ? ($minutosDecorridos / $slaTotal) * 100 : 0;
        });

        // Paginação manual
        $perPage = 10;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $tickets = new LengthAwarePaginator(
            $tickets->slice(($currentPage - 1) * $perPage, $perPage)->values(),
            $tickets->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()] // Preserva os parâmetros
        );
    } else {
        // Paginação padrão com ordenação direta pelo banco
        $tickets = $ticketsQuery->orderBy($sort, 'desc')->paginate(10)->withQueryString();
    }

    // Carrega setores e grupos para os filtros
    if ($user->hasRole('analista') && !$user->hasRole(['supervisor', 'administrador'])) {
        $setores = Setor::where('id', $user->setor_id)->get();
    } else {
        $setores = Setor::all();
    }
    $grupos = Grupo::all();

    // Se o usuário for um analista, ele só poderá ver e filtrar seu próprio setor.
    return view('tickets.index', compact('tickets', 'showClosed', 'setores', 'grupos', 'setorId', 'grupoId'));
}










    /**
     * Exibe o formulário de criação de ticket.
     */
    public function create()
    {
        $categorias = Categoria::all();
        $clientes = User::role(['cliente', 'clientedc'])->get();
        $empresas = Empresa::all();
        $grupos = Grupo::all();
        $setores = Setor::all();
        $analistas = User::role(['analista', 'supervisor', 'administrador'])->get();

        return view('tickets.create', compact('categorias', 'clientes', 'empresas', 'grupos', 'setores', 'analistas'));
    }

    /**
     * Armazena um novo ticket no banco de dados.
     */
    public function store(Request $request)
    {
        // Valida os campos do formulário e os arquivos
        $request->validate(array_merge([
            'assunto' => 'required|string|max:255',
            'descricao' => 'required|string',
            'categoria_id' => 'required|exists:categorias,id',
            'cliente_id' => 'nullable|exists:users,id',
            'empresa_id' => 'nullable|exists:empresas,id',
            'grupo_id' => 'nullable|exists:grupos,id',
            'setor_id' => 'nullable|exists:setores,id',
            'atribuido_ao_analista_id' => 'nullable|exists:users,id',
        ], AttachmentRules::for('anexos')));

        // Criação do ticket
        $ticket = Ticket::create([
            'assunto' => $request->assunto,
            'descricao' => $request->descricao,
            'categoria_id' => $request->categoria_id,
            'user_id' => Auth::id(),
            'cliente_id' => $request->cliente_id,
            'empresa_id' => $request->empresa_id,
            'grupo_id' => $request->grupo_id,
            'setor_id' => $request->setor_id,
            'atribuido_ao_analista_id' => $request->atribuido_ao_analista_id ?: null, // Define como null se estiver vazio
            'status' => 'aberto',
        ]);

        Log::info("Ticket criado com ID: {$ticket->id}");

        // Processa cada anexo e salva no banco de dados
        if ($request->hasFile('anexos')) {
            foreach ($request->file('anexos') as $file) {
                if ($file->isValid()) {
                    // Gera um nome único para o arquivo
                    $uniqueName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '-' . uniqid() . '.' . $file->getClientOriginalExtension();

                    // Salva o arquivo em storage/app/public/anexos com o nome único
                    $filePath = $file->storeAs('anexos', $uniqueName, 'public');
                    Log::info("Anexo salvo no caminho: " . $filePath);

                    // Salva o caminho do arquivo e o ID do ticket em ticket_attachments
                    TicketAttachment::create([
                        'ticket_id' => $ticket->id,
                        'file_path' => $filePath,
                    ]);

                    Log::info("Registro de anexo criado no banco de dados para o Ticket ID: {$ticket->id}");
                } else {
                    Log::error("Arquivo inválido: " . $file->getClientOriginalName());
                }
            }
        } else {
            Log::warning("Nenhum anexo encontrado na requisição.");
        }

        return redirect()->route('tickets.index')->with('success', 'Ticket criado com sucesso!');
    }






    /**
     * Exibe um ticket específico.
     */
    public function show(Ticket $ticket)
    {
            $user = Auth::user();
    if ($user->hasRole('analista') && !$user->hasRole(['supervisor', 'administrador'])) {
        if ($ticket->setor_id !== $user->setor_id) {
            // Se o setor do ticket for diferente do setor do analista, nega o acesso.
            abort(403, 'Acesso não autorizado.');
        }
    }
    
        // Recupera os anexos relacionados ao ticket
        $anexos = $ticket->attachments;

        // Obter setores disponíveis
        $setores = Setor::all();

        // Obter grupos disponíveis
        $grupos = Grupo::all();

        // Obter categorias associadas ao setor do ticket (ou uma coleção vazia caso o setor não esteja definido)
        $categoriasAssociadas = $ticket->setor_id
            ? Categoria::where('setor_id', $ticket->setor_id)->get()
            : collect(); // Retorna coleção vazia se não houver setor associado

        // Obter analistas associados ao grupo do ticket (ou todos os analistas se o grupo não estiver definido)
        $analistas = $ticket->setor_id
            ? User::where('setor_id', $ticket->setor_id)->get()
            : User::all(); // Retorna todos os analistas se não houver grupo associado


        $setorSelecionado = $ticket->setor_id;

        // Retorna os dados para a view
        return view('tickets.show', compact('ticket', 'anexos', 'setores', 'grupos', 'categoriasAssociadas', 'analistas', 'setorSelecionado'));
    }




    public function edit(Ticket $ticket)
    {
        $categorias = Categoria::all();
        $clientes = User::role(['cliente', 'clientedc'])->get();
        $empresas = Empresa::all();
        $grupos = Grupo::all();
        $setores = Setor::all();

        // Filtrando usuários com os papéis de analista, supervisor ou administrador
        $analistas = User::role(['analista', 'supervisor', 'administrador'])->get();

        return view('tickets.edit', compact('ticket', 'categorias', 'clientes', 'empresas', 'grupos', 'setores', 'analistas'));
    }


    public function update(Request $request, Ticket $ticket)
    {
        $request->validate([
            'assunto' => 'required|string|max:255',
            'descricao' => 'required|string',
            'categoria_id' => 'required|exists:categorias,id',
            'cliente_id' => 'nullable|exists:users,id',
            'empresa_id' => 'nullable|exists:empresas,id',
            'grupo_id' => 'nullable|exists:grupos,id',
            'setor_id' => 'nullable|exists:setores,id',
            'atribuido_ao_analista_id' => 'nullable|exists:users,id',
            'status' => 'required|in:aberto,pendente cliente,pendente analista,fechado',
        ]);

        $ticket->update([
            'assunto' => $request->assunto,
            'descricao' => $request->descricao,
            'categoria_id' => $request->categoria_id,
            'cliente_id' => $request->cliente_id,
            'empresa_id' => $request->empresa_id,
            'grupo_id' => $request->grupo_id,
            'setor_id' => $request->setor_id,
            'atribuido_ao_analista_id' => $request->atribuido_ao_analista_id,
            'status' => $request->status,
        ]);

        return redirect()->route('tickets.index')->with('success', 'Ticket atualizado com sucesso!');
    }

    /**
     * Exclui um ticket e seus anexos.
     */
    public function destroy(Ticket $ticket)
    {
        // Exclui anexos do storage e do banco de dados
        foreach ($ticket->attachments as $anexo) {
            Storage::delete($anexo->file_path);
            $anexo->delete();
        }

        $ticket->delete();
        return redirect()->route('tickets.index')->with('success', 'Ticket excluído com sucesso!');
    }

    public function myTickets(Request $request)
    {
        $sort = $request->input('sort', 'created_at');
        $showClosed = $request->input('showClosed', '0');
        $userId = Auth::id();

        $ticketsQuery = Ticket::with('categoria', 'user', 'cliente', 'empresa', 'grupo', 'setor', 'analista')
            ->where('atribuido_ao_analista_id', $userId)
            ->orderBy($sort, 'desc');

        if ($showClosed == '0') {
            $ticketsQuery->where('status', '!=', 'fechado');
        }

        $tickets = $ticketsQuery->paginate(10)->withQueryString();
        return view('tickets.my', compact('tickets', 'showClosed', 'sort'));
    }





    public function finalize(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);
        $user = Auth::user(); // Recupera o usuário autenticado

        // Verifica se o ticket possui uma categoria
        if (!$ticket->categoria) {
            return redirect()->route('tickets.show', $ticket->id)
                ->with('error', 'O ticket precisa estar vinculado a uma categoria para ser finalizado.');
        }

        // SLA definido na categoria
        $slaUpdate = $ticket->categoria->slaupdate;

        // Recuperar todas as mensagens do ticket, ordenadas por criação
        $mensagens = $ticket->mensagens()->orderBy('created_at', 'asc')->get();

        // Variável para somar o tempo total congelado
        $tempoTotalMinutos = 0;

        // Data inicial (início da contagem)
        $dataReferencia = $ticket->created_at;

        // Iterar pelas mensagens para calcular o tempo congelado
        foreach ($mensagens as $mensagem) {
            $tempoDecorrido = $dataReferencia->diffInMinutes($mensagem->created_at);
            $tempoCongelado = min($tempoDecorrido, $slaUpdate);
            $tempoTotalMinutos += $tempoCongelado;
            $dataReferencia = $mensagem->created_at;
        }

        $tempoFinal = $dataReferencia->diffInMinutes(now());
        $tempoCongeladoFinal = min($tempoFinal, $slaUpdate);
        $tempoTotalMinutos += $tempoCongeladoFinal;

        // Validar os campos enviados
        $request->validate([
            'descricao_fechamento' => 'required|string',
            'horas' => 'required|integer|min:0',
            'minutos' => 'required|integer|min:0|max:59',
        ]);

        // Atualizar o ticket com os dados de finalização
        $ticket->status = 'fechado';
        $horas = (int) $request->input('horas');
$minutos = (int) $request->input('minutos');
$ticket->horas_gastas = ($horas * 60) + $minutos;
        $ticket->descricao_final = $request->input('descricao_fechamento');
        $ticket->atribuido_ao_analista_id = $user->id;
        $ticket->finalizado_por_usuario_id = $user->id;
        $ticket->data_hora_finalizado = now();
        $ticket->save();

        // Criar mensagem de finalização
        $mensagem = new Mensagem();
        $mensagem->ticket_id = $ticket->id;
        $mensagem->user_id = $user->id;
        $mensagem->descricao = "{$user->name} finalizou o ticket. Relato final: {$ticket->descricao_final}";
        $mensagem->save();

        return redirect()->route('tickets.show', $ticket->id)
            ->with('success', 'Ticket finalizado com sucesso!');
    }

    public function calcularHorasSugeridas($id)
    {
        try {
            $ticket = Ticket::findOrFail($id);

            // Verifica se o ticket possui uma categoria
            if (!$ticket->categoria) {
                return response()->json([
                    'error' => 'O ticket precisa estar vinculado a uma categoria para ser finalizado.'
                ], 400); // Status 400 para indicar erro de validação
            }

            // SLA definido na categoria
            $slaUpdate = $ticket->categoria->slaupdate;

            // Recuperar mensagens do ticket
            $mensagens = $ticket->mensagens()->orderBy('created_at', 'asc')->get();

            // Variável para somar o tempo total congelado
            $tempoTotalMinutos = 0;
            $dataReferencia = $ticket->created_at;

            foreach ($mensagens as $mensagem) {
                $tempoDecorrido = $dataReferencia->diffInMinutes($mensagem->created_at);
                $tempoCongelado = min($tempoDecorrido, $slaUpdate);
                $tempoTotalMinutos += $tempoCongelado;
                $dataReferencia = $mensagem->created_at;
            }

            $tempoFinal = $dataReferencia->diffInMinutes(now());
            $tempoCongeladoFinal = min($tempoFinal, $slaUpdate);
            $tempoTotalMinutos += $tempoCongeladoFinal;

            return response()->json([
                'horas' => intdiv($tempoTotalMinutos, 60),
                'minutos' => $tempoTotalMinutos % 60,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao calcular horas sugeridas.'], 500);
        }
    }





    public function assumirTicket(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);
        $user = Auth::user(); // Recupera o usuário autenticado

        // Validação
        $request->validate([
            'setor' => 'required|exists:setores,id',
            'categoria' => 'required|exists:categorias,id',
        ]);

        // Atualiza setor e categoria do ticket
        $ticket->setor_id = $request->setor;
        $ticket->categoria_id = $request->categoria;

        // Atribui o ticket ao analista atual
        $ticket->atribuido_ao_analista_id = $user->id;

        // Atribui o grupo do usuário ao ticket, se ele tiver um grupo, caso contrário, define como nulo
        $ticket->grupo_id = $user->grupo_id ?? null;

        // Preenche os campos de auditoria para indicar quem assumiu e quando
        $ticket->assumido_por_usuario_id = $user->id;
        $ticket->data_hora_assumido = now(); // Define a data e hora atual

        $ticket->save();

        // Cria uma mensagem de auditoria indicando que o usuário assumiu o ticket
        Mensagem::create([
            'ticket_id' => $ticket->id,
            'descricao' => "{$user->name} assumiu o ticket.", // Mensagem indicando quem assumiu
            'user_id' => $user->id, // Registra o ID do usuário que assumiu
        ]);

        return redirect()->route('tickets.show', $ticket->id)->with('success', 'Ticket assumido com sucesso!');
    }


// Função para transferir o ticket
public function transferirTicket(Request $request, $id)
{
    $ticket = Ticket::findOrFail($id);
    $user = Auth::user(); // Recupera o usuário autenticado (quem está fazendo a transferência)

    // Atualiza o setor, grupo e analista, se fornecidos
    if ($request->setor) {
        $ticket->setor_id = $request->setor;
    }
    if ($request->grupo) {
        $ticket->grupo_id = $request->grupo;
    }
    if ($request->has('analista')) { // Verifica se a chave 'analista' existe na requisição, mesmo que o valor seja null
        $ticket->atribuido_ao_analista_id = $request->analista; // Define como null se nenhum analista for selecionado
    }

    // Preenche os campos de auditoria de transferência
    $ticket->transferido_por_usuario_id = $user->id; // ID do usuário que transferiu o ticket
    $ticket->data_hora_transferido = now(); // Data e hora atuais da transferência

    $ticket->save();

    // Recupera os nomes do usuário que transferiu e do novo analista atribuído
    $novoAnalista = User::find($request->analista); // Busca o novo analista pelo ID

    $grupo = Grupo::find($request->grupo);

    // Cria a mensagem indicando a transferência
    $mensagem = new Mensagem();
    $mensagem->ticket_id = $ticket->id;
    $mensagem->user_id = $user->id; // ID do usuário que fez a transferência
    $mensagem->descricao = "{$user->name} transferiu o ticket para " . ($novoAnalista ? $novoAnalista->name : ($grupo ? $grupo->nome : 'Sem grupo'));
    $mensagem->save();

    return redirect()->route('tickets.show', $ticket->id)->with('success', 'Ticket transferido com sucesso!');
}



public function showWithTransferOptions($id)
{
    $ticket = Ticket::with(['categoria', 'cliente', 'empresa', 'grupo', 'setor', 'analista'])->findOrFail($id);

    // Dados para os dropdowns do modal de transferência
    $setores = Setor::all();  // Carrega todos os setores
    $grupos = Grupo::all();    // Carrega todos os grupos
    $analistas = User::whereHas('roles', function ($query) {
        $query->whereIn('name', ['analista', 'supervisor', 'administrador']);
    })->get(); // Carrega usuários com papéis específicos

    // Retorna os dados como JSON para a requisição AJAX
    return response()->json([
        'setores' => $setores,
        'grupos' => $grupos,
        'analistas' => $analistas,
        'ticket' => $ticket
    ]);
}

public function obterDadosTransferencia($ticketId)
{
    $ticket = Ticket::findOrFail($ticketId);

    // Obter setores disponíveis
    $setores = Setor::all();

    // Obter todos os grupos
    $grupos = Grupo::all();

    // Obter todos os analistas disponíveis no grupo atual do ticket
    $analistas = $ticket->grupo_id
        ? User::where('grupo_id', $ticket->grupo_id)->get()
        : collect();

    // Retornar os dados como JSON
    return response()->json([
        'setores' => $setores,
        'grupos' => $grupos,
        'analistas' => $analistas,
        'ticket' => $ticket, // Incluindo os dados do ticket para preenchimento automático
    ]);
}


public function carregarAssumir($id, Request $request)
{
    $ticket = Ticket::with(['setor', 'categoria'])->findOrFail($id);

    // Obter todos os setores
    $setores = Setor::all();

    // Verificar se o setor foi enviado via query string para atualizar categorias
    $setorSelecionado = $request->input('setor', $ticket->setor_id);

    // Obter categorias associadas ao setor selecionado
    $categoriasAssociadas = $setorSelecionado
        ? Categoria::where('setor_id', $setorSelecionado)->get()
        : collect(); // Retorna coleção vazia se não houver setor selecionado

    // Retornar a view com os dados
    return view('tickets.show', compact('ticket', 'setores', 'categoriasAssociadas', 'setorSelecionado'));
}


public function carregarTransferir($id)
{
    // Busca o ticket pelo ID com os relacionamentos necessários
    $ticket = Ticket::with(['setor', 'grupo', 'analista'])->findOrFail($id);

    // Carregar todos os setores
    $setores = Setor::all();

    // Carregar todos os grupos
    $grupos = Grupo::all();

    // Carregar analistas associados ao grupo atual do ticket ou lista todos
    $analistas = $ticket->setor_id
    ? User::where('setor_id', $ticket->setor_id)->get()
    : collect(); // Retorna uma coleção vazia se o setor não estiver definido

    // Retorna os dados para a view do modal (no caso `tickets.show`)
    return view('tickets.show', compact('ticket', 'setores', 'grupos', 'analistas'));
}

public function carregarCategorias($setor_id)
{
    // Busca categorias associadas ao setor informado
    $categorias = Categoria::where('setor_id', $setor_id)->get();

    // Retorna as categorias como JSON
    return response()->json($categorias);
}

public function pendentes(Request $request)
{
    $user = Auth::user(); // Usuário logado

    // Inicia a query base para tickets pendentes
    $ticketsQuery = Ticket::with(['cliente', 'empresa', 'setor', 'analista'])
        ->whereNull('categoria_id') // Sem categoria atribuída
        ->where('status', '!=', 'fechado'); // Apenas tickets abertos

    // ADIÇÃO: Aplica o filtro de setor para o perfil 'analista'
    if ($user->hasRole('analista') && !$user->hasRole(['supervisor', 'administrador'])) {
        $ticketsQuery->where('setor_id', $user->setor_id);
    }

    // Executa a query final com ordenação e paginação
    $tickets = $ticketsQuery->orderBy('created_at', 'desc')
        ->paginate(10);

    return view('tickets.pendentes', compact('tickets'));
}




}
