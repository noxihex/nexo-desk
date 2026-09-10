<?php

namespace App\Http\Controllers;

use App\Actions\Tickets\AuthorizeClientTicketFlow;
use App\Actions\Tickets\CreateClientTicket;
use App\Actions\Tickets\FinalizeClientTicket;
use App\Actions\Tickets\ReplyToClientTicket;
use App\Support\TicketReturnUrl;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ClienteTicketController extends Controller
{
    public function index()
    {
        app(AuthorizeClientTicketFlow::class)->client();

        return view('tickets.cliente.index');
    }

    public function show(Request $request, $id)
    {
        [, $ticket] = app(AuthorizeClientTicketFlow::class)->ticket((int) $id);

        return view('tickets.cliente.show', [
            'ticketId' => $ticket->id,
            'returnUrl' => TicketReturnUrl::resolve($request, 'tickets.cliente.index'),
        ]);
    }

    public function create(Request $request)
    {
        app(AuthorizeClientTicketFlow::class)->client();

        return view('tickets.cliente.create', [
            'returnUrl' => TicketReturnUrl::resolve($request, 'tickets.cliente.index'),
        ]);
    }

    public function store(Request $request)
    {
        app(CreateClientTicket::class)->handle(
            $request->only(['assunto', 'descricao', 'setor_id']),
            $request->file('anexos', [])
        );

        return redirect()->to(TicketReturnUrl::resolve($request, 'tickets.cliente.index'))
            ->with('success', 'Ticket criado com sucesso!');
    }

    public function storeMessage(Request $request, $id)
    {
        app(ReplyToClientTicket::class)->handle((int) $id, [
            'descricao' => $request->input('descricao'),
        ], $request->file('attachments', []));

        return redirect()->route('tickets.cliente.show', [
            'id' => $id,
            'return_to' => TicketReturnUrl::resolve($request, 'tickets.cliente.index'),
        ])->with('success', 'Mensagem enviada com sucesso!');
    }

    public function finalize(Request $request, $id)
    {
        try {
            app(FinalizeClientTicket::class)->handle((int) $id, $request->only([
                'descricao_fechamento',
            ]));
        } catch (ValidationException $exception) {
            if ($exception->validator->errors()->has('categoria')) {
                return redirect()->route('tickets.cliente.show', [
                    'id' => $id,
                    'return_to' => TicketReturnUrl::resolve($request, 'tickets.cliente.index'),
                ])->with('error', $exception->validator->errors()->first('categoria'));
            }
            throw $exception;
        }

        return redirect()->route('tickets.cliente.show', [
            'id' => $id,
            'return_to' => TicketReturnUrl::resolve($request, 'tickets.cliente.index'),
        ])->with('success', 'Ticket finalizado com sucesso!');
    }

    public function calcularHorasSugeridas($id)
    {
        [, $ticket] = app(FinalizeClientTicket::class)->authorize((int) $id);

        try {
            $minutes = app(FinalizeClientTicket::class)->suggestedMinutes($ticket);

            return response()->json([
                'horas' => intdiv($minutes, 60),
                'minutos' => $minutes % 60,
            ]);
        } catch (ValidationException $exception) {
            return response()->json([
                'error' => $exception->validator->errors()->first('categoria'),
            ], 400);
        }
    }
}
