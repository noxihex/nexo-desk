<?php

namespace App\Http\Controllers;

use App\Support\AttachmentRules;
use App\Support\TicketReturnUrl;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Ticket;
use App\Models\Categoria;
use App\Models\Empresa;
use App\Models\Servico;
use App\Models\User;
use App\Models\Grupo;
use App\Models\Setor;
use App\Models\Mensagem;
use App\Models\Notificacao;
use App\Models\TicketAttachment;
use App\Models\MessageAttachment;




class ClienteTicketController extends Controller
{
    /**
     * Exibe a lista de tickets do cliente e/ou da empresa.
     */
    public function index(Request $request)
    {
        $user = auth()->user(); // Usuário autenticado
        $empresaId = $user->empresa_id; // Empresa vinculada ao usuário
        $userId = $user->id; // ID do usuário autenticado
        $search = $request->input('search');

        // Define se a visualização será dos tickets da empresa ou apenas dos tickets do cliente
        $viewCompanyTickets = filter_var($request->get('viewCompanyTickets', false), FILTER_VALIDATE_BOOLEAN);

        $query = Ticket::query();

        if ($empresaId) {
            // Todo ticket exibido ao cliente precisa pertencer à empresa dele.
            $query->where('empresa_id', $empresaId);

            if (!$viewCompanyTickets) {
                // Na visão pessoal, limita também ao cliente autenticado.
                $query->where('cliente_id', $userId);
            }
        } else {
            // Usuários sem empresa só podem acessar os próprios tickets sem empresa.
            $query->where('cliente_id', $userId)
                  ->whereNull('empresa_id');
        }

        // Pesquisa por ID ou assunto dentro do escopo de tickets já autorizado.
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('assunto', 'like', "%{$search}%");
            });
        }

        // Ordena do mais recente para o mais antigo
        $query->orderBy('created_at', 'desc');

        // Paginação de tickets
        $tickets = $query->paginate(10)->withQueryString();

        // Retorna a view com os dados de tickets e a flag de visualização
        return view('tickets.cliente.index', compact('tickets', 'viewCompanyTickets'));
    }



    public function show(Request $request, $id)
{
    // Obtém o usuário autenticado
    $user = auth()->user();

    // Busca o ticket pelo ID, garantindo que seja do mesmo usuário ou empresa
    $ticket = Ticket::with(['mensagens.user', 'mensagens.attachments', 'attachments', 'categoria', 'user', 'empresa'])
        ->where(function ($query) use ($user) {
            $query->where('user_id', $user->id) // Tickets criados pelo usuário
                  ->orWhere('empresa_id', $user->empresa_id); // Ou da mesma empresa
        })
        ->findOrFail($id); // Lança 404 se não encontrar o ticket

    // Retorna a view com o ticket
    $returnUrl = TicketReturnUrl::resolve($request, 'tickets.cliente.index');

    return view('tickets.cliente.show', compact('ticket', 'returnUrl'));
}



public function storeMessage(Request $request, $id)
{
    $request->validate(array_merge([
        'descricao' => 'required|string',
    ], AttachmentRules::for('attachments')));

    // Busca o ticket e verifica permissões
    $ticket = Ticket::findOrFail($id);
    $user = auth()->user();

    if ($ticket->cliente_id !== $user->id && $ticket->empresa_id !== $user->empresa_id) {
        abort(403, 'Você não tem permissão para interagir com este ticket.');
    }

    // Cria a mensagem vinculada ao ticket
    $mensagem = Mensagem::create([
        'user_id' => $user->id,
        'ticket_id' => $id,
        'descricao' => $request->descricao,
    ]);

    // Processa os anexos enviados, se houver
    if ($request->hasFile('attachments')) {
        foreach ($request->file('attachments') as $file) {
            if ($file->isValid()) {
                // Gera um nome único para o arquivo
                $uniqueName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '-' . uniqid() . '.' . $file->getClientOriginalExtension();

                // Salva o arquivo na pasta especificada com o nome único
                $path = $file->storeAs('attachments/messages', $uniqueName, 'public');

                // Salva o anexo usando o modelo `MessageAttachment`
                MessageAttachment::create([
                    'mensagem_id' => $mensagem->id,
                    'file_path' => $path,
                ]);

                Log::info("Anexo salvo no caminho: " . $path);
            } else {
                Log::error("Arquivo inválido: " . $file->getClientOriginalName());
            }
        }
    }

    // Atualiza o status do ticket para "pendente analista"
    $ticket->status = 'pendente analista';
    $ticket->save();

    // Redireciona de volta para a página do ticket com mensagem de sucesso
    return redirect()
        ->route('tickets.cliente.show', [
            'id' => $ticket->id,
            'return_to' => TicketReturnUrl::resolve($request, 'tickets.cliente.index'),
        ])
        ->with('success', 'Mensagem enviada com sucesso!');
}


public function create(Request $request)
{
    $user = auth()->user();
    $empresa = $user->empresa;
    
    // Busca os setores e os serviços da empresa do usuário
    $setores = Setor::all();
    $servicos = $empresa ? $empresa->servicos()->get() : collect();

    // Retorna a view com os dados necessários
    $returnUrl = TicketReturnUrl::resolve($request, 'tickets.cliente.index');

    return view('tickets.cliente.create', compact('setores', 'servicos', 'returnUrl'));
}

public function store(Request $request)
{
    // Valida os campos do formulário, incluindo os novos
    $request->validate(array_merge([
        'assunto' => 'required|string|max:255',
        'descricao' => 'required|string',
        'setor_id' => 'nullable|exists:setores,id', // Setor agora pode ser nulo
        'servico_id' => 'nullable|exists:servicos,id',
        'questionario_respostas' => 'nullable|array',
    ], AttachmentRules::for('anexos')));

    // Prepara a descrição do ticket
    $descricaoOriginal = $request->input('descricao');
    $prependText = '';

    // Se um serviço foi selecionado, busca os dados e monta o texto
    if ($request->filled('servico_id')) {
        $servico = Servico::find($request->input('servico_id'));
        
        if ($servico) {
            // Monta o texto do questionário
            $respostas = $request->input('questionario_respostas', []);
            $perguntas = $servico->questionario ?? [];
            
            $questionarioText = '';
            foreach ($perguntas as $index => $pergunta) {
                if (!empty($pergunta) && isset($respostas[$index]) && !empty($respostas[$index])) {
                    $questionarioText .= "-> {$pergunta}\n";
                    $questionarioText .= "R: {$respostas[$index]}\n\n";
                }
            }
            
            if (!empty($questionarioText)) {
                 $prependText .= "--- QUESTIONÁRIO DO SERVIÇO: {$servico->nome} ---\n";
                 $prependText .= $questionarioText;
            }

            // Monta o texto das informações do serviço
            $informacoes = $servico->informacoes ?? [];
            $informacoesText = '';
            foreach ($informacoes as $info) {
                if (!empty($info['campo']) && !empty($info['valor'])) {
                    $informacoesText .= "- {$info['campo']}: {$info['valor']}\n";
                }
            }
            
            if (!empty($informacoesText)) {
                $prependText .= "--- INFORMAÇÕES DO SERVIÇO ---\n";
                $prependText .= $informacoesText . "\n";
            }
        }
    }
    
    if(!empty($prependText)){
        $prependText .= "--------------------------------------------------\n\n";
    }

    $descricaoFinal = $prependText . $descricaoOriginal;
    
    // Obtém o usuário autenticado
    $user = auth()->user();

    // Cria o ticket com a descrição modificada
    $ticket = Ticket::create([
        'assunto' => $request->assunto,
        'descricao' => $descricaoFinal, // Usa a descrição final
        'setor_id' => $request->setor_id,
        'user_id' => $user->id,
        'cliente_id' => $user->id,
        'empresa_id' => $user->empresa_id,
        'status' => 'aberto',
        'grupo_id' => null,
        'categoria_id' => null,
        'atribuido_ao_analista_id' => null,
    ]);

    // Processa anexos, se houver
    if ($request->hasFile('anexos')) {
        foreach ($request->file('anexos') as $file) {
            if ($file->isValid()) {
                $uniqueName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs('attachments/tickets', $uniqueName, 'public');

                TicketAttachment::create([
                    'ticket_id' => $ticket->id,
                    'file_path' => $filePath,
                ]);
            }
        }
    }


        // Criação de notificação
        $this->criarNotificacao($ticket);

    // Redireciona para a página de listagem de tickets com uma mensagem de sucesso
    return redirect()->to(TicketReturnUrl::resolve($request, 'tickets.cliente.index'))->with('success', 'Ticket criado com sucesso!');
}


private function criarNotificacao(Ticket $ticket)
{
    // Obtém todos os analistas que pertencem ao mesmo setor do ticket
    $analistas = User::whereHas('roles', function ($query) {
        $query->whereIn('name', ['analista', 'supervisor', 'administrador']);
    })->where('setor_id', $ticket->setor_id)->get();

    // Cria a notificação para cada analista do setor
    foreach ($analistas as $analista) {
        \App\Models\Notificacao::create([
            'user_id' => $analista->id,
            'titulo' => "Novo ticket criado: #{$ticket->id}",
            'mensagem' => "Um novo ticket foi criado por {$ticket->user->name} no seu setor {$ticket->setor->nome}.",
            'lida' => false, // Indica que a notificação ainda não foi lida
        ]);
    }
}


public function finalize(Request $request, $id)
{
    $ticket = Ticket::findOrFail($id);
    $user = Auth::user(); // Recupera o usuário autenticado

    // Verifica se o ticket possui uma categoria
    if (!$ticket->categoria) {
        return redirect()->route('tickets.cliente.show', [
            'id' => $ticket->id,
            'return_to' => TicketReturnUrl::resolve($request, 'tickets.cliente.index'),
        ])
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
    $ticket->horas_gastas = $tempoTotalMinutos; // Salvar o tempo total em minutos
    $ticket->descricao_final = $request->input('descricao_fechamento');
    $ticket->finalizado_por_usuario_id = $user->id;
    $ticket->data_hora_finalizado = now();
    $ticket->save();

    // Criar mensagem de finalização
    $mensagem = new Mensagem();
    $mensagem->ticket_id = $ticket->id;
    $mensagem->user_id = $user->id;
    $mensagem->descricao = "{$user->name} finalizou o ticket. Relato final: {$ticket->descricao_final}";
    $mensagem->save();

    return redirect()->route('tickets.cliente.show', [
        'id' => $ticket->id,
        'return_to' => TicketReturnUrl::resolve($request, 'tickets.cliente.index'),
    ])
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

public function getQuestionario(Servico $servico)
{
    // Garante que a resposta seja sempre um array, mesmo se o campo for nulo no BD
    return response()->json($servico->questionario ?? []);
}

}
