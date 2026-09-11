<?php

namespace App\Http\Controllers;

use App\Actions\Overview\StaffOverview;
use App\Actions\Tickets\AuthorizeTicketLists;
use Illuminate\Support\Facades\Auth;

class VisaoGeralController extends Controller
{
    /**
     * Exibe a página de visão geral dos tickets.
     */
    public function index()
    {
        $user = Auth::user()->loadMissing('roles');

        if ($user->isStaff()) {
            return view('home-staff');
        }

        if ($user->hasRole('cliente')) {
            return view('home-client');
        }

        abort(403);
    }

    public function obterTicketsAtencao()
    {
        $user = app(AuthorizeTicketLists::class)->handle();

        return app(StaffOverview::class)->attentionTicketIds($user);
    }
}
