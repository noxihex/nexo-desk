<?php

namespace App\Http\Controllers;

use App\Models\Mensagem;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MensagemController extends Controller
{
    /**
     * Armazena uma nova mensagem no banco de dados.
     */
    public function store(Request $request, $ticketId)
    {
        $request->validate([
            'descricao' => 'required|string',
            'attachments.*' => 'file|max:5120', // Valida até 5MB
        ]);

        // Cria a mensagem
        $mensagem = Mensagem::create([
            'user_id' => auth()->id(),
            'ticket_id' => $ticketId,
            'descricao' => $request->input('descricao'),
        ]);

        // Processa os anexos enviados
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                if ($file->isValid()) {
                    // Gera um nome único para o arquivo
                    $uniqueName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '-' . uniqid() . '.' . $file->getClientOriginalExtension();

                    // Salva o arquivo na pasta especificada com o nome único
                    $path = $file->storeAs('attachments/messages', $uniqueName, 'public');

                    // Associa o arquivo ao registro da mensagem
                    $mensagem->attachments()->create(['file_path' => $path]);

                    Log::info("Anexo salvo no caminho: " . $path);
                } else {
                    Log::error("Arquivo inválido: " . $file->getClientOriginalName());
                }
            }
        }

        // Recupera o ticket
        $ticket = Ticket::findOrFail($ticketId);

        // Atualiza o status do ticket apenas se o campo 'status' estiver presente e não vazio
        if ($request->filled('status')) {
            $ticket->status = $request->input('status');
        }

        $ticket->touch(); // Atualiza o campo updated_at para a data e hora atuais
        $ticket->save(); // Salva o ticket com as alterações, se houver

        return redirect()->route('tickets.show', $ticketId)->with('success', 'Mensagem enviada com sucesso!');
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
