<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Support\ElapsedTime;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RelatorioAnalistaController extends Controller
{
    public function index(Request $request)
    {
        // Recupera os usuários com permissão de 'analista', 'supervisor' ou 'administrador'
        $usuarios = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['analista', 'supervisor', 'administrador']);
        })->get();

        // Inicializa variáveis para os tickets e os dados dos gráficos
        $tickets = collect();
        $totalTickets = 0;  // Nova variável para contagem total de tickets
        $totalTicketsAbertos = 0;
        $totalTicketsFechados = 0;
        $lineChartData = [
            'labels' => [],
            'created' => [],
            'finalized' => [],
            'transferred' => [],
            'assumed' => []
        ];

        // Inicializa o $intervaloHoras com um valor padrão
        $intervaloHoras = 0;

        // Verifica se os filtros foram aplicados
        if ($request->has(['data_inicio', 'data_fim', 'usuario_id'])) {
            // Validação dos filtros
            $request->validate([
                'data_inicio' => 'required|date',
                'data_fim' => 'required|date|after_or_equal:data_inicio',
                'usuario_id' => 'required|exists:users,id',
            ]);

            // Converte as datas para instâncias de Carbon
            $dataInicio = Carbon::parse($request->input('data_inicio'));
            $dataFim = Carbon::parse($request->input('data_fim'));
            $usuarioId = $request->input('usuario_id');

            // Calcula o intervalo em horas
            $intervaloHoras = ElapsedTime::wholeHours($dataFim, $dataInicio);

            // Define o intervalo de agrupamento (hora ou dia)
            if ($intervaloHoras <= 24) {
                // Agrupamento por hora
                $interval = new \DateInterval('PT1H'); // Intervalo de 1 hora
                $format = 'Y-m-d H:i';
            } else {
                // Agrupamento por dia
                $interval = new \DateInterval('P1D'); // Intervalo de 1 dia
                $format = 'Y-m-d';
            }

            // Consulta os tickets do analista selecionado no período definido
            $tickets = Ticket::where('atribuido_ao_analista_id', $usuarioId)
                ->whereBetween('created_at', [$dataInicio, $dataFim])
                ->with(['categoria', 'empresa'])
                ->get();

            // Contagem total de tickets
            $totalTickets = $tickets->count();

            // Contagem de tickets abertos e fechados
            $totalTicketsAbertos = $tickets->whereIn('status', ['aberto', 'pendente analista', 'pendente cliente'])->count();
            $totalTicketsFechados = $tickets->where('status', 'fechado')->count();

            // Criação do período para o gráfico de linhas
            $period = new \DatePeriod(
                $dataInicio,
                $interval,
                $dataFim->copy()->add($interval)
            );

            $timezone = new \DateTimeZone(config('app.timezone'));

            foreach ($period as $dateTime) {
                // Ajusta o timezone
                $dateTime->setTimezone($timezone);

                if ($intervaloHoras <= 24) {
                    // Para períodos menores que 24 horas (agrupamento por hora)
                    $formattedLabel = $dateTime->format('Y-m-d H:i'); // Formata como "Y-m-d H:i" para horas
                    $intervalStart = clone $dateTime;
                    $intervalEnd = (clone $intervalStart)->add($interval);
                } else {
                    // Para períodos maiores que 24 horas (agrupamento por dia)
                    $dateTime->setTime(0, 0, 0); // Ajusta para o início do dia
                    $formattedLabel = $dateTime->format('Y-m-d'); // Formata como "Y-m-d" para dias
                    $intervalStart = clone $dateTime;
                    $intervalEnd = (clone $intervalStart)->add($interval);
                }

                // Adiciona o rótulo formatado
                $lineChartData['labels'][] = $formattedLabel;

                // Contabiliza os dados dentro do intervalo
                $lineChartData['created'][] = Ticket::where('user_id', $usuarioId)
                    ->whereBetween('created_at', [$intervalStart, $intervalEnd])
                    ->count();

                $lineChartData['finalized'][] = Ticket::where('finalizado_por_usuario_id', $usuarioId)
                    ->whereBetween('data_hora_finalizado', [$intervalStart, $intervalEnd])
                    ->count();

                $lineChartData['transferred'][] = Ticket::where('transferido_por_usuario_id', $usuarioId)
                    ->whereBetween('data_hora_transferido', [$intervalStart, $intervalEnd])
                    ->count();

                $lineChartData['assumed'][] = Ticket::where('assumido_por_usuario_id', $usuarioId)
                    ->whereBetween('data_hora_assumido', [$intervalStart, $intervalEnd])
                    ->count();
            }

        }

        // Retorna a view com os dados de usuários, tickets e dados dos gráficos
        return view('relatorios.analista.index', compact(
            'usuarios', 'tickets', 'totalTickets', 'totalTicketsAbertos', 'totalTicketsFechados', 'lineChartData', 'intervaloHoras'
        ));
    }
}
