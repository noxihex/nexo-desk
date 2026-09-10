<?php

namespace App\Http\Controllers;

use App\Actions\Tickets\AssumeTicket;
use App\Actions\Tickets\AuthorizeTicketFlow;
use App\Actions\Tickets\AuthorizeTicketLists;
use App\Actions\Tickets\DeleteTicket;
use App\Actions\Tickets\FinalizeTicket;
use App\Actions\Tickets\SaveTicket;
use App\Actions\Tickets\TransferTicket;
use App\Models\Categoria;
use App\Models\Setor;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\User;
use App\Support\TicketReturnUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        app(AuthorizeTicketLists::class)->handle();

        return view('tickets.index');
    }

    public function create(Request $request)
    {
        app(AuthorizeTicketFlow::class)->staff();
        $returnUrl = TicketReturnUrl::resolve($request);

        return view('tickets.create', compact('returnUrl'));
    }

    public function store(Request $request)
    {
        app(SaveTicket::class)->handle($request->all(), $request->file('anexos', []));

        return redirect()->to(TicketReturnUrl::resolve($request))->with('success', 'Ticket criado com sucesso!');
    }

    public function show(Request $request, Ticket $ticket)
    {
        app(AuthorizeTicketFlow::class)->ticket($ticket->id);
        $returnUrl = TicketReturnUrl::resolve($request);

        return view('tickets.show', compact('ticket', 'returnUrl'));
    }

    public function edit(Request $request, Ticket $ticket)
    {
        app(AuthorizeTicketFlow::class)->staff();
        $returnUrl = TicketReturnUrl::resolve($request);

        return view('tickets.edit', compact('ticket', 'returnUrl'));
    }

    public function update(Request $request, Ticket $ticket)
    {
        app(SaveTicket::class)->handle($request->all(), [], $ticket->id);

        return redirect()->to(TicketReturnUrl::resolve($request))->with('success', 'Ticket atualizado com sucesso!');
    }

    public function destroy(Request $request, Ticket $ticket)
    {
        app(DeleteTicket::class)->handle($ticket->id);

        return redirect()->to(TicketReturnUrl::resolve($request))->with('success', 'Ticket excluído com sucesso!');
    }

    public function myTickets(Request $request)
    {
        app(AuthorizeTicketLists::class)->handle();

        return view('tickets.my');
    }

    public function pendentes(Request $request)
    {
        app(AuthorizeTicketLists::class)->handle();

        return view('tickets.pendentes');
    }

    public function finalize(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);
        if (! $ticket->categoria) {
            return redirect()->route('tickets.show', ['ticket' => $ticket->id, 'return_to' => TicketReturnUrl::resolve($request)])
                ->with('error', 'O ticket precisa estar vinculado a uma categoria para ser finalizado.');
        }
        app(FinalizeTicket::class)->handle((int) $id, $request->all());

        return redirect()->route('tickets.show', ['ticket' => $id, 'return_to' => TicketReturnUrl::resolve($request)])
            ->with('success', 'Ticket finalizado com sucesso!');
    }

    public function calcularHorasSugeridas($id)
    {
        try {
            [, $ticket] = app(AuthorizeTicketFlow::class)->ticket((int) $id);
            $minutes = app(FinalizeTicket::class)->suggestedMinutes($ticket);

            return response()->json(['horas' => intdiv($minutes, 60), 'minutos' => $minutes % 60]);
        } catch (\Illuminate\Validation\ValidationException $exception) {
            return response()->json(['error' => $exception->validator->errors()->first()], 400);
        } catch (\Throwable $exception) {
            return response()->json(['error' => 'Erro ao calcular horas sugeridas.'], 500);
        }
    }

    public function assumirTicket(Request $request, Ticket $ticket)
    {
        $ticket = app(AssumeTicket::class)->handle($ticket->id, $request->all());

        return $this->operationRedirect($request, $ticket, 'Ticket assumido com sucesso!');
    }

    public function transferirTicket(Request $request, Ticket $ticket)
    {
        $ticket = app(TransferTicket::class)->handle($ticket->id, $request->all());

        return $this->operationRedirect($request, $ticket, 'Ticket transferido com sucesso!');
    }

    public function carregarCategorias($setor_id)
    {
        app(AuthorizeTicketFlow::class)->staff();

        return response()->json(Categoria::whereHas('setores', fn ($query) => $query->whereKey($setor_id))
            ->orderBy('nome')->get());
    }

    public function carregarAssumir(Ticket $ticket, Request $request)
    {
        return $this->show($request, $ticket);
    }

    public function carregarTransferir(Ticket $ticket, Request $request)
    {
        return $this->show($request, $ticket);
    }

    public function showWithTransferOptions($id)
    {
        [, $ticket] = app(AuthorizeTicketFlow::class)->ticket((int) $id);

        return response()->json([
            'setores' => Setor::orderBy('nome')->get(),
            'analistas' => $this->analistasAtivos()->get(),
            'ticket' => $ticket,
        ]);
    }

    public function obterDadosTransferencia($ticketId)
    {
        [, $ticket] = app(AuthorizeTicketFlow::class)->ticket((int) $ticketId);

        return response()->json([
            'setores' => Setor::orderBy('nome')->get(),
            'analistas' => $ticket->setor_id ? $this->analistasAtivos($ticket->setor_id)->get() : collect(),
            'ticket' => $ticket,
        ]);
    }

    public function downloadAttachment(Ticket $ticket, TicketAttachment $attachment)
    {
        app(AuthorizeTicketFlow::class)->ticket($ticket->id);
        abort_unless((int) $attachment->ticket_id === (int) $ticket->id, 404);
        abort_unless(Storage::disk('public')->exists($attachment->file_path), 404);

        return Storage::disk('public')->download($attachment->file_path, basename($attachment->file_path));
    }

    private function analistasAtivos(?int $setorId = null)
    {
        return User::whereHas('roles', fn ($query) => $query->whereIn('name', ['analista', 'supervisor', 'administrador']))
            ->where('status', true)
            ->when($setorId, fn ($query) => $query->where('setor_id', $setorId));
    }

    private function operationRedirect(Request $request, Ticket $ticket, string $message)
    {
        $user = app(AuthorizeTicketFlow::class)->staff();
        $returnUrl = TicketReturnUrl::resolve($request);

        return $user->podeVisualizarTicket($ticket)
            ? redirect()->route('tickets.show', ['ticket' => $ticket->id, 'return_to' => $returnUrl])->with('success', $message)
            : redirect()->to($returnUrl)->with('success', $message);
    }
}
