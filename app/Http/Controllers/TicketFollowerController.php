<?php

namespace App\Http\Controllers;

use App\Jobs\DispatchStaffTicketActivity;
use App\Models\Mensagem;
use App\Models\Ticket;
use App\Support\TicketStaffAccess;
use Illuminate\Http\Request;

class TicketFollowerController extends Controller
{
    public function store(Request $request, Ticket $ticket)
    {
        TicketStaffAccess::abortUnlessAllowed($request->user(), $ticket);
        $changes = $ticket->seguidores()->syncWithoutDetaching([$request->user()->id]);
        if (!empty($changes['attached'])) {
            $message = $ticket->mensagens()->create([
                'user_id' => $request->user()->id,
                'descricao' => $request->user()->name . ' começou a seguir o ticket.',
                'tipo' => Mensagem::TIPO_SISTEMA,
            ]);
            DispatchStaffTicketActivity::dispatch(
                'ticket-followed:' . $ticket->id . ':' . $request->user()->id . ':' . $message->id,
                $ticket->id,
                $request->user()->id,
                'followers',
                [],
                $message->descricao
            )->afterCommit();
        }

        return $request->expectsJson()
            ? response()->json(['following' => true])
            : back()->with('success', 'Você está seguindo este ticket.');
    }

    public function destroy(Request $request, Ticket $ticket)
    {
        TicketStaffAccess::abortUnlessAllowed($request->user(), $ticket);
        if ($ticket->seguidores()->detach($request->user()->id)) {
            $message = $ticket->mensagens()->create([
                'user_id' => $request->user()->id,
                'descricao' => $request->user()->name . ' deixou de seguir o ticket.',
                'tipo' => Mensagem::TIPO_SISTEMA,
            ]);
            DispatchStaffTicketActivity::dispatch(
                'ticket-unfollowed:' . $ticket->id . ':' . $request->user()->id . ':' . $message->id,
                $ticket->id,
                $request->user()->id,
                'followers',
                [],
                $message->descricao
            )->afterCommit();
        }

        return $request->expectsJson()
            ? response()->json(['following' => false])
            : back()->with('success', 'Você deixou de seguir este ticket.');
    }
}
