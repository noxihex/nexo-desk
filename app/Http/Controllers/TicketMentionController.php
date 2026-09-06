<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Support\TicketStaffAccess;
use Illuminate\Http\Request;

class TicketMentionController extends Controller
{
    public function index(Request $request, Ticket $ticket)
    {
        TicketStaffAccess::abortUnlessAllowed($request->user(), $ticket);
        $term = trim((string) $request->query('q'));
        $users = User::with('roles')->where('status', true)
            ->whereHas('roles', fn ($query) => $query->whereIn('name', ['analista', 'supervisor', 'administrador']))
            ->when($term, fn ($query) => $query->where('name', 'like', '%' . $term . '%'))
            ->orderBy('name')->limit(30)->get()
            ->filter(fn (User $user) => TicketStaffAccess::allows($user, $ticket))
            ->map(fn (User $user) => ['id' => $user->id, 'name' => $user->name])->values();

        return response()->json(['data' => $users]);
    }
}
