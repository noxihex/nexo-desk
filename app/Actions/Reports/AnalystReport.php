<?php

namespace App\Actions\Reports;

use App\Actions\Cadastros\AuthorizeCatalogs;
use App\Models\Ticket;
use App\Models\User;
use App\Support\ElapsedTime;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use DateTimeZone;
use Illuminate\Http\Request;

class AnalystReport
{
    public function handle(Request $request): array
    {
        app(AuthorizeCatalogs::class)->handle();

        $usuarios = User::whereHas('roles', fn ($query) => $query->whereIn('name', ['analista', 'supervisor', 'administrador']))->get();
        $tickets = collect();
        $totalTickets = 0;
        $totalTicketsAbertos = 0;
        $totalTicketsFechados = 0;
        $lineChartData = ['labels' => [], 'created' => [], 'finalized' => [], 'transferred' => [], 'assumed' => []];
        $intervaloHoras = 0;

        if ($request->has(['data_inicio', 'data_fim', 'usuario_id'])) {
            $data = $request->validate([
                'data_inicio' => 'required|date',
                'data_fim' => 'required|date|after_or_equal:data_inicio',
                'usuario_id' => 'required|exists:users,id',
            ]);
            $dataInicio = Carbon::parse($data['data_inicio']);
            $dataFim = Carbon::parse($data['data_fim']);
            $usuarioId = $data['usuario_id'];
            $intervaloHoras = ElapsedTime::wholeHours($dataFim, $dataInicio);
            $interval = new DateInterval($intervaloHoras <= 24 ? 'PT1H' : 'P1D');

            $tickets = Ticket::where('atribuido_ao_analista_id', $usuarioId)
                ->whereBetween('created_at', [$dataInicio, $dataFim])
                ->with(['categoria', 'empresa'])
                ->get();
            $totalTickets = $tickets->count();
            $totalTicketsAbertos = $tickets->whereIn('status', ['aberto', 'pendente analista', 'pendente cliente'])->count();
            $totalTicketsFechados = $tickets->where('status', 'fechado')->count();

            $period = new DatePeriod($dataInicio, $interval, $dataFim->copy()->add($interval));
            $timezone = new DateTimeZone(config('app.timezone'));
            foreach ($period as $dateTime) {
                $dateTime->setTimezone($timezone);
                if ($intervaloHoras > 24) {
                    $dateTime->setTime(0, 0);
                }
                $intervalStart = clone $dateTime;
                $intervalEnd = (clone $intervalStart)->add($interval);
                $lineChartData['labels'][] = $dateTime->format($intervaloHoras <= 24 ? 'Y-m-d H:i' : 'Y-m-d');
                $lineChartData['created'][] = Ticket::where('user_id', $usuarioId)->whereBetween('created_at', [$intervalStart, $intervalEnd])->count();
                $lineChartData['finalized'][] = Ticket::where('finalizado_por_usuario_id', $usuarioId)->whereBetween('data_hora_finalizado', [$intervalStart, $intervalEnd])->count();
                $lineChartData['transferred'][] = Ticket::where('transferido_por_usuario_id', $usuarioId)->whereBetween('data_hora_transferido', [$intervalStart, $intervalEnd])->count();
                $lineChartData['assumed'][] = Ticket::where('assumido_por_usuario_id', $usuarioId)->whereBetween('data_hora_assumido', [$intervalStart, $intervalEnd])->count();
            }
        }

        return compact('usuarios', 'tickets', 'totalTickets', 'totalTicketsAbertos', 'totalTicketsFechados', 'lineChartData', 'intervaloHoras');
    }
}
