<?php

namespace App\Http\Controllers;

use App\Actions\Tickets\FollowTicket;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketFollowerController extends Controller
{
    public function store(Request $request, Ticket $ticket)
    {
        app(FollowTicket::class)->handle($ticket->id, true);

        return $request->expectsJson()
            ? response()->json(['following' => true])
            : back()->with('success', 'Você está seguindo este ticket.');
    }

    public function destroy(Request $request, Ticket $ticket)
    {
        app(FollowTicket::class)->handle($ticket->id, false);

        return $request->expectsJson()
            ? response()->json(['following' => false])
            : back()->with('success', 'Você deixou de seguir este ticket.');
    }
}
