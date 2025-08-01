<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Empresa;
use App\Models\Ticket;
use App\Models\Setor;

class RelatorioController extends Controller
{
    public function index(Request $request)
{
    // Recupera todas as empresas e setores para exibir no filtro
    $empresas = Empresa::all();
    $setores = Setor::all(); // Assumindo que há uma tabela e um modelo chamado Setor

    // Inicializa as variáveis
    $tickets = [];
    $dataLabels = [];
    $ticketsAbertosData = [];
    $ticketsFechadosData = [];

    // Verifica se os filtros foram aplicados
    if ($request->has(['data_inicio', 'data_fim', 'empresa_id'])) {
        // Validação dos filtros
        $request->validate([
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'empresa_id' => 'required|exists:empresas,id',
            'setor_id' => 'nullable|exists:setores,id', // Adiciona validação para setor_id
        ]);

        // Recupera os filtros do request
        $dataInicio = new \DateTime($request->input('data_inicio'));
        $dataFim = new \DateTime($request->input('data_fim'));
        $empresaId = $request->input('empresa_id');
        $setorId = $request->input('setor_id'); // Filtro de setor

        // Define se tickets abertos devem ser ignorados
        $ignoreOpenTickets = $request->input('ignore_open_tickets', '1') == '1';

        // Filtra os tickets conforme os critérios
        $ticketsQuery = Ticket::where('empresa_id', $empresaId)
            ->when($setorId, function ($query, $setorId) {
                $query->where('setor_id', $setorId);
            })
            ->where(function ($query) use ($ignoreOpenTickets, $dataInicio, $dataFim) {
                if ($ignoreOpenTickets) {
                    $query->whereBetween('data_hora_finalizado', [$dataInicio, $dataFim])
                          ->where('status', 'fechado');
                } else {
                    $query->where(function ($q) use ($dataInicio, $dataFim) {
                        $q->whereBetween('created_at', [$dataInicio, $dataFim]);
                    })->orWhere(function ($q) use ($dataInicio, $dataFim) {
                        $q->whereBetween('data_hora_finalizado', [$dataInicio, $dataFim])
                          ->where('status', 'fechado');
                    });
                }
            });

        $tickets = $ticketsQuery->with('categoria')->get();

        // Cria um período entre as datas para as labels e os dados
        $period = new \DatePeriod(
            $dataInicio,
            new \DateInterval('P1D'), // Intervalo de 1 dia
            $dataFim->modify('+1 day')
        );

        foreach ($period as $date) {
            $formattedDate = $date->format('Y-m-d');
            $dataLabels[] = $formattedDate;

            $ticketsAbertosData[] = Ticket::where('empresa_id', $empresaId)
                ->when($setorId, function ($query, $setorId) {
                    $query->where('setor_id', $setorId);
                })
                ->whereDate('created_at', $formattedDate)
                ->count();

            $ticketsFechadosData[] = Ticket::where('empresa_id', $empresaId)
                ->when($setorId, function ($query, $setorId) {
                    $query->where('setor_id', $setorId);
                })
                ->whereDate('data_hora_finalizado', $formattedDate)
                ->where('status', 'fechado')
                ->count();
        }
    }

    return view('relatorios.horas.index', compact(
        'empresas', 'tickets', 'dataLabels', 'ticketsAbertosData', 'ticketsFechadosData', 'setores'
    ));
}



}
