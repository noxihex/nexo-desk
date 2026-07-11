<?php

namespace App\Http\Controllers;

use App\Support\AttachmentRules;
use App\Support\TicketReturnUrl;

use App\Models\Ticket;
use App\Models\Categoria;
use App\Models\Empresa;
use App\Models\User;
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

    // Obtém o filtro de setor da requisição
    $setorId = $request->input('setor_id');

    // Query base com os relacionamentos necessários
    $ticketsQuery = Ticket::with('categoria', 'user', 'cliente', 'empresa', 'setor', 'analista');

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

    // Carrega setores para os filtros
    if ($user->hasRole('analista') && !$user->hasRole(['supervisor', 'administrador'])) {
        $setores = Setor::where('id', $user->setor_id)->get();
    } else {
        $setores = Setor::all();
    }
    // Se o usuário for um analista, ele só poderá ver e filtrar seu próprio setor.
    return view('tickets.index', compact('tickets', 'showClosed', 'setores', 'setorId'));
}










    /**
     * Exibe o formulário de criação de ticket.
     */
    public function create(Request $request)
    {
        $categorias = Categoria::all();
        $clientes = User::role(['cliente', 'clientedc'])->get();
        $empresas = Empresa::all();
        $setores = Setor::all();
        $analistas = $this->analistasAtivos()->get();

        $returnUrl = TicketReturnUrl::resolve($request);

        return view('tickets.create', compact('categorias', 'clientes', 'empresas', 'setores', 'analistas', 'returnUrl'));
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
            'setor_id' => 'nullable|exists:setores,id',
            'atribuido_ao_analista_id' => $this->regrasAnalista('setor_id'),
        ], AttachmentRules::for('anexos')));

        // Criação do ticket
        $ticket = Ticket::create([
            'assunto' => $request->assunto,
            'descricao' => $request->descricao,
            'categoria_id' => $request->categoria_id,
            'user_id' => Auth::id(),
            'cliente_id' => $request->cliente_id,
            'empresa_id' => $request->empresa_id,
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

        return redirect()->to(TicketReturnUrl::resolve($request))->with('success', 'Ticket criado com sucesso!');
    }






    /**
     * Exibe um ticket específico.
     */
    public function show(Request $request, Ticket $ticket)
    {
        $user = Auth::user();

        $returnUrl = TicketReturnUrl::resolve($request);

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

        // Obter categorias associadas ao setor do ticket (ou uma coleção vazia caso o setor não esteja definido)
        $categoriasAssociadas = $ticket->setor_id
            ? Categoria::where('setor_id', $ticket->setor_id)->get()
            : collect(); // Retorna coleção vazia se não houver setor associado

        // A interface de transferência filtra esta lista pelo setor escolhido.
        $analistas = $this->analistasAtivos()->get();


        $setorSelecionado = $ticket->setor_id;

        // Retorna os dados para a view
        return view('tickets.show', compact('ticket', 'anexos', 'setores', 'categoriasAssociadas', 'analistas', 'setorSelecionado', 'returnUrl'));
    }




    public function edit(Request $request, Ticket $ticket)
    {
        $categorias = Categoria::all();
        $clientes = User::role(['cliente', 'clientedc'])->get();
        $empresas = Empresa::all();
        $setores = Setor::all();

        $analistas = $this->analistasAtivos()->get();

        $returnUrl = TicketReturnUrl::resolve($request);

        return view('tickets.edit', compact('ticket', 'categorias', 'clientes', 'empresas', 'setores', 'analistas', 'returnUrl'));
    }


    public function update(Request $request, Ticket $ticket)
    {
        $request->validate([
            'assunto' => 'required|string|max:255',
            'descricao' => 'required|string',
            'categoria_id' => 'required|exists:categorias,id',
            'cliente_id' => 'nullable|exists:users,id',
            'empresa_id' => 'nullable|exists:empresas,id',
            'setor_id' => 'nullable|exists:setores,id',
            'atribuido_ao_analista_id' => $this->regrasAnalista('setor_id'),
            'status' => 'required|in:aberto,pendente cliente,pendente analista,fechado',
        ]);

        $ticket->update([
            'assunto' => $request->assunto,
            'descricao' => $request->descricao,
            'categoria_id' => $request->categoria_id,
            'cliente_id' => $request->cliente_id,
            'empresa_id' => $request->empresa_id,
            'setor_id' => $request->setor_id,
            'atribuido_ao_analista_id' => $request->atribuido_ao_analista_id,
            'status' => $request->status,
        ]);

        return redirect()->to(TicketReturnUrl::resolve($request))->with('success', 'Ticket atualizado com sucesso!');
    }

    /**
     * Exclui um ticket e seus anexos.
     */
    public function destroy(Request $request, Ticket $ticket)
    {
        // Exclui anexos do storage e do banco de dados
        foreach ($ticket->attachments as $anexo) {
            Storage::delete($anexo->file_path);
            $anexo->delete();
        }

        $ticket->delete();
        return redirect()->to(TicketReturnUrl::resolve($request))->with('success', 'Ticket excluído com sucesso!');
    }

    public function myTickets(Request $request)
    {
        $sort = $request->input('sort', 'created_at');
        $showClosed = $request->input('showClosed', '0');
        $userId = Auth::id();

        $ticketsQuery = Ticket::with('categoria', 'user', 'cliente', 'empresa', 'setor', 'analista')
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
            return redirect()->route('tickets.show', ['ticket' => $ticket->id, 'return_to' => TicketReturnUrl::resolve($request)])
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

        return redirect()->route('tickets.show', ['ticket' => $ticket->id, 'return_to' => TicketReturnUrl::resolve($request)])
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

        $returnUrl = TicketReturnUrl::resolve($request);
        $canStillView = !$user->hasRole('analista')
            || $user->hasRole(['supervisor', 'administrador'])
            || $ticket->setor_id === $user->setor_id;

        return $canStillView
            ? redirect()->route('tickets.show', ['ticket' => $ticket->id, 'return_to' => $returnUrl])->with('success', 'Ticket assumido com sucesso!')
            : redirect()->to($returnUrl)->with('success', 'Ticket assumido com sucesso!');
    }


// Função para transferir o ticket
public function transferirTicket(Request $request, $id)
{
    $dados = $request->validate([
        'setor' => 'required|exists:setores,id',
        'analista' => $this->regrasAnalista('setor'),
    ]);

    $ticket = Ticket::findOrFail($id);
    $user = Auth::user(); // Recupera o usuário autenticado (quem está fazendo a transferência)

    // Atualiza o setor e o analista, se fornecidos
    if ($dados['setor']) {
        $ticket->setor_id = $dados['setor'];
    }
    if ($request->has('analista')) { // Verifica se a chave 'analista' existe na requisição, mesmo que o valor seja null
        $ticket->atribuido_ao_analista_id = $dados['analista']; // Define como null se nenhum analista for selecionado
    }

    // Preenche os campos de auditoria de transferência
    $ticket->transferido_por_usuario_id = $user->id; // ID do usuário que transferiu o ticket
    $ticket->data_hora_transferido = now(); // Data e hora atuais da transferência

    $ticket->save();

    // Recupera os nomes do usuário que transferiu e do novo analista atribuído
        $novoAnalista = User::find($dados['analista'] ?? null); // Busca o novo analista pelo ID

    // Cria a mensagem indicando a transferência
    $mensagem = new Mensagem();
    $mensagem->ticket_id = $ticket->id;
    $mensagem->user_id = $user->id; // ID do usuário que fez a transferência
    $destino = $novoAnalista ? $novoAnalista->name : ($ticket->setor ? $ticket->setor->nome : 'sem setor');
    $mensagem->descricao = "{$user->name} transferiu o ticket para {$destino}";
    $mensagem->save();

    $returnUrl = TicketReturnUrl::resolve($request);
    $canStillView = !$user->hasRole('analista')
        || $user->hasRole(['supervisor', 'administrador'])
        || $ticket->setor_id === $user->setor_id;

    return $canStillView
        ? redirect()->route('tickets.show', ['ticket' => $ticket->id, 'return_to' => $returnUrl])->with('success', 'Ticket transferido com sucesso!')
        : redirect()->to($returnUrl)->with('success', 'Ticket transferido com sucesso!');
}



public function showWithTransferOptions($id)
{
    $ticket = Ticket::with(['categoria', 'cliente', 'empresa', 'setor', 'analista'])->findOrFail($id);

    // Dados para os dropdowns do modal de transferência
    $setores = Setor::all();  // Carrega todos os setores
    $analistas = $this->analistasAtivos()->get();

    // Retorna os dados como JSON para a requisição AJAX
    return response()->json([
        'setores' => $setores,
        'analistas' => $analistas,
        'ticket' => $ticket
    ]);
}

public function obterDadosTransferencia($ticketId)
{
    $ticket = Ticket::findOrFail($ticketId);

    // Obter setores disponíveis
    $setores = Setor::all();

    // Obter os analistas disponíveis no setor atual do ticket
    $analistas = $ticket->setor_id
        ? $this->analistasAtivos($ticket->setor_id)->get()
        : collect();

    // Retornar os dados como JSON
    return response()->json([
        'setores' => $setores,
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
    $ticket = Ticket::with(['setor', 'analista'])->findOrFail($id);

    // Carregar todos os setores
    $setores = Setor::all();

    // A interface de transferência filtra os analistas ativos pelo setor escolhido.
    $analistas = $this->analistasAtivos()->get();

    // Retorna os dados para a view do modal (no caso `tickets.show`)
    return view('tickets.show', compact('ticket', 'setores', 'analistas'));
}

public function carregarCategorias($setor_id)
{
    // Busca categorias associadas ao setor informado
    $categorias = Categoria::where('setor_id', $setor_id)->get();

    // Retorna as categorias como JSON
    return response()->json($categorias);
}

    /**
     * Retorna somente integrantes ativos que podem ser atribuídos a tickets.
     */
    private function analistasAtivos(?int $setorId = null)
    {
        return User::role(['analista', 'supervisor', 'administrador'])
            ->where('status', true)
            ->when($setorId, fn ($query) => $query->where('setor_id', $setorId));
    }

    /**
     * Garante que o analista escolhido esteja ativo e pertença ao setor informado.
     */
    private function regrasAnalista(string $campoSetor): array
    {
        return [
            'nullable',
            'exists:users,id',
            function ($attribute, $value, $fail) use ($campoSetor) {
                if (!$value) {
                    return;
                }

                $setorId = request()->input($campoSetor);
                if (!$setorId || !$this->analistasAtivos((int) $setorId)->whereKey($value)->exists()) {
                    $fail('O analista selecionado deve estar ativo e pertencer ao setor escolhido.');
                }
            },
        ];
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
