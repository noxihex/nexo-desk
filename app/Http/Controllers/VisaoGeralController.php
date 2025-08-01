<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Grupo;
use App\Models\Setor;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class VisaoGeralController extends Controller
{
    /**
     * Exibe a página de visão geral dos tickets.
     */
    public function index(Request $request)
    {
        // Captura os parâmetros de ordenação e filtros
        $order = $request->input('order', 'desc');
        $analista = $request->input('analista');
        $grupo = $request->input('grupo');
        $setor = $request->input('setor');

        // Consulta base para tickets
        $query = Ticket::query();

        // Aplica filtro por analista atribuído, se selecionado
        if ($analista) {
            $query->where('atribuido_ao_analista_id', $analista);
        }

        // Aplica filtro por grupo se selecionado
        if ($grupo) {
            $query->where('grupo_id', $grupo);
        }

        // Aplica filtro por setor se selecionado
        if ($setor) {
            $query->where('setor_id', $setor);
        }

        // Aplica ordenação
        $query->orderBy('created_at', $order);

        // Filtra tickets por status
        $ticketsAbertos = (clone $query)->where('status', 'aberto')->get();
        $ticketsPendenteCliente = (clone $query)->where('status', 'pendente cliente')->get();
        $ticketsPendenteAnalista = (clone $query)->where('status', 'pendente analista')->get();
        $ticketsFechados = (clone $query)->where('status', 'fechado')->take(100)->get();

        // Carrega listas de analistas (com papéis específicos), grupos e setores para os filtros
        $analistas = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['analista', 'supervisor', 'administrador']);
        })->get();

        $grupos = Grupo::all();
        $setores = Setor::all();

        return view('home', compact(
            'ticketsAbertos', 'ticketsPendenteCliente', 'ticketsPendenteAnalista', 'ticketsFechados',
            'analistas', 'grupos', 'setores'
        ));
    }

    public function contarMeusTicketsAbertos()
    {
        return Ticket::whereIn('status', ['aberto', 'pendente analista', 'pendente cliente'])
            ->where('atribuido_ao_analista_id', Auth::id())
            ->count();
    }

    public function contarTicketsAbertosGrupo()
    {
        return Ticket::whereIn('status', ['aberto', 'pendente analista', 'pendente cliente'])
            ->where('grupo_id', Auth::user()->grupo_id)
            ->count();
    }

    public function contarTicketsAbertosGrupoSemAnalista()
    {
        return Ticket::where('status', 'aberto')
            ->where('grupo_id', Auth::user()->grupo_id)
            ->whereNull('atribuido_ao_analista_id')
            ->count();
    }

    // Nova função para obter IDs dos Tickets Abertos em Meu Grupo sem Analista
    public function obterTicketsSemAnalista()
    {
        $user = Auth::user();

        // 1. Garante que o usuário está logado e realmente pertence a um grupo.
        if (!$user || !$user->grupo_id) {
            return [];
        }

        // 2. Busca os tickets usando a nova lógica.
        return Ticket::where('grupo_id', $user->grupo_id)
            ->where('status', '!=', 'fechado') // <- MUDANÇA PRINCIPAL AQUI
            ->whereNull('atribuido_ao_analista_id')
            ->pluck('id')
            ->toArray();
    }



    public function obterTicketsAtencao()
{
    $tickets = Ticket::whereIn('status', ['aberto', 'pendente cliente', 'pendente analista']) // Inclui todos os status relevantes
        ->where('atribuido_ao_analista_id', Auth::id()) // Apenas tickets atribuídos ao analista atual
        ->whereHas('categoria', function ($query) {
            $query->whereNotNull('slaupdate'); // Apenas categorias com slaupdate definido
        })
        ->get();

    $ticketsComAlerta = [];

    foreach ($tickets as $ticket) {
        // Determina a data inicial de referência (transferência, assunção ou criação)
        $dataReferencia = $ticket->data_hora_transferido
            ?? $ticket->data_hora_assumido
            ?? $ticket->created_at;

        // Obtém a última mensagem enviada pelo analista atribuído
        $ultimaMensagem = $ticket->mensagens()
            ->where('user_id', $ticket->atribuido_ao_analista_id) // Apenas mensagens do analista atribuído
            ->latest()
            ->first();

        $dataUltimaInteracao = $ultimaMensagem
            ? $ultimaMensagem->created_at
            : $dataReferencia;

        // Calcula o tempo decorrido desde a última interação
        $tempoDecorrido = Carbon::now()->diffInMinutes($dataUltimaInteracao);

        // Verifica se o tempo decorrido excede o SLA definido
        if ($tempoDecorrido >= $ticket->categoria->slaupdate) {
            $ticketsComAlerta[] = $ticket->id; // Adiciona o ticket ao alerta
        }
    }

    return $ticketsComAlerta; // Retorna apenas os tickets com alerta
}

public function contarMeusTicketsAbertosCliente()
{
    return Ticket::whereIn('status', ['aberto', 'pendente analista', 'pendente cliente'])
        ->where('user_id', Auth::id())
        ->count();
}

public function obterMeusTicketsAbertosCliente()
{
    return Ticket::whereIn('status', ['aberto', 'pendente analista', 'pendente cliente'])
        ->where('user_id', Auth::id())
        ->pluck('id')
        ->toArray();
}


public function contarTicketsAbertosMinhaEmpresa()
{
    return Ticket::whereIn('status', ['aberto', 'pendente analista', 'pendente cliente'])
        ->whereHas('empresa', function ($query) {
            $query->where('id', Auth::user()->empresa_id);
        })
        ->count();
}

public function obterTicketsAbertosMinhaEmpresa()
{
    return Ticket::whereIn('status', ['aberto', 'pendente analista', 'pendente cliente'])
        ->whereHas('empresa', function ($query) {
            $query->where('id', Auth::user()->empresa_id);
        })
        ->pluck('id')
        ->toArray();
}


public function contarTicketsPendenteCliente()
{
    return Ticket::where('status', 'pendente cliente')
        ->whereHas('empresa', function ($query) {
            $query->where('id', Auth::user()->empresa_id);
        })
        ->count();
}

public function obterTicketsPendenteCliente()
{
    return Ticket::where('status', 'pendente cliente')
        ->whereHas('empresa', function ($query) {
            $query->where('id', Auth::user()->empresa_id);
        })
        ->pluck('id')
        ->toArray();
}


public function contarTicketsFechadosMinhaEmpresa()
{
    return Ticket::where('status', 'fechado')
        ->whereHas('empresa', function ($query) {
            $query->where('id', Auth::user()->empresa_id);
        })
        ->count();
}

public function obterTicketsFechadosMinhaEmpresa()
{
    return Ticket::where('status', 'fechado')
        ->whereHas('empresa', function ($query) {
            $query->where('id', Auth::user()->empresa_id);
        })
        ->pluck('id')
        ->toArray();
}





}
