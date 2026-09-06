<?php

namespace App\Http\Controllers;

use App\Models\MessageAttachment;
use App\Models\Mensagem;
use App\Models\Ticket;
use App\Support\TicketStaffAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InternalMessageAttachmentController extends Controller
{
    public function show(Request $request, Ticket $ticket, MessageAttachment $attachment)
    {
        TicketStaffAccess::abortUnlessAllowed($request->user(), $ticket);
        $attachment->load('mensagem');
        abort_unless($attachment->mensagem
            && (int) $attachment->mensagem->ticket_id === (int) $ticket->id
            && $attachment->mensagem->tipo === Mensagem::TIPO_INTERNA
            && $attachment->disk === 'local', 404);
        abort_unless(Storage::disk('local')->exists($attachment->file_path), 404);

        return Storage::disk('local')->download($attachment->file_path, basename($attachment->file_path));
    }
}
