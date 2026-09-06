<?php

namespace App\Http\Controllers;

use App\Support\AttachmentRules;
use App\Support\TicketReturnUrl;

use App\Models\Mensagem;
use App\Models\Ticket;
use App\Services\TicketMessageService;
use App\Support\TicketStaffAccess;
use Illuminate\Http\Request;

class MensagemController extends Controller
{
    /**
     * Armazena uma nova mensagem no banco de dados.
     */
    public function store(Request $request, Ticket $ticket, TicketMessageService $messages)
    {
        $request->validate(array_merge([
            'descricao' => 'nullable|string|required_without:attachments',
            'tipo' => 'nullable|in:publica,interna',
            'status' => 'nullable|in:pendente cliente,pendente analista',
            'mentioned_user_ids' => 'nullable|array',
            'mentioned_user_ids.*' => 'integer|distinct|exists:users,id',
        ], AttachmentRules::for('attachments')));
        TicketStaffAccess::abortUnlessAllowed($request->user(), $ticket);
        $type = $request->input('tipo', Mensagem::TIPO_PUBLICA);
        if ($type === Mensagem::TIPO_INTERNA && $request->filled('status')) {
            return back()->withErrors(['status' => 'Notas internas não podem alterar o status.'])->withInput();
        }
        $messages->create($ticket, $request->user(), [
            'descricao' => $request->input('descricao'),
            'tipo' => $type,
            'status' => $request->input('status'),
            'mentioned_user_ids' => $request->input('mentioned_user_ids', []),
        ], $request->file('attachments', []));

        return redirect()->route('tickets.show', [
            'ticket' => $ticket->id,
            'return_to' => TicketReturnUrl::resolve($request),
        ])->with('success', $type === Mensagem::TIPO_INTERNA ? 'Nota interna adicionada com sucesso!' : 'Mensagem enviada com sucesso!');
    }



    /**
     * Exibe mensagens de um ticket específico.
     */
    public function index($ticketId)
    {
        // Verifica se o ticket existe
        $ticket = Ticket::findOrFail($ticketId);

        // Recupera as mensagens do ticket
        $mensagens = Mensagem::where('ticket_id', $ticketId)->with('user')->get();

        return view('mensagens.index', compact('mensagens', 'ticket'));
    }
}
