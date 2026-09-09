<?php

namespace App\Actions\Reports;

use App\Actions\Cadastros\AuthorizeCatalogs;
use App\Models\Empresa;
use App\Models\Setor;
use App\Models\Ticket;
use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Http\Request;

class CompanyReport
{
    public function handle(Request $request): array
    {
        app(AuthorizeCatalogs::class)->handle();

        $empresas = Empresa::all();
        $setores = Setor::all();
        $tickets = collect();
        $dataLabels = [];
        $ticketsAbertosData = [];
        $ticketsFechadosData = [];

        if ($request->has(['data_inicio', 'data_fim', 'empresa_id'])) {
            $data = $request->validate([
                'data_inicio' => 'required|date',
                'data_fim' => 'required|date|after_or_equal:data_inicio',
                'empresa_id' => 'required|exists:empresas,id',
                'setor_id' => 'nullable|exists:setores,id',
            ]);
            $dataInicio = new DateTime($data['data_inicio']);
            $dataFim = new DateTime($data['data_fim']);
            $empresaId = $data['empresa_id'];
            $setorId = $data['setor_id'] ?? null;
            $ignoreOpenTickets = $request->input('ignore_open_tickets', '1') === '1';

            $tickets = Ticket::where('empresa_id', $empresaId)
                ->when($setorId, fn ($query) => $query->where('setor_id', $setorId))
                ->where(function ($query) use ($ignoreOpenTickets, $dataInicio, $dataFim) {
                    if ($ignoreOpenTickets) {
                        $query->whereBetween('data_hora_finalizado', [$dataInicio, $dataFim])
                            ->where('status', 'fechado');
                    } else {
                        $query->whereBetween('created_at', [$dataInicio, $dataFim])
                            ->orWhere(function ($query) use ($dataInicio, $dataFim) {
                                $query->whereBetween('data_hora_finalizado', [$dataInicio, $dataFim])
                                    ->where('status', 'fechado');
                            });
                    }
                })
                ->with('categoria')
                ->get();

            $period = new DatePeriod($dataInicio, new DateInterval('P1D'), (clone $dataFim)->modify('+1 day'));
            foreach ($period as $date) {
                $formattedDate = $date->format('Y-m-d');
                $dataLabels[] = $formattedDate;
                $ticketsAbertosData[] = Ticket::where('empresa_id', $empresaId)
                    ->when($setorId, fn ($query) => $query->where('setor_id', $setorId))
                    ->whereDate('created_at', $formattedDate)
                    ->count();
                $ticketsFechadosData[] = Ticket::where('empresa_id', $empresaId)
                    ->when($setorId, fn ($query) => $query->where('setor_id', $setorId))
                    ->whereDate('data_hora_finalizado', $formattedDate)
                    ->where('status', 'fechado')
                    ->count();
            }
        }

        return compact('empresas', 'tickets', 'dataLabels', 'ticketsAbertosData', 'ticketsFechadosData', 'setores');
    }
}
