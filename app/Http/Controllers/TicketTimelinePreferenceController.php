<?php

namespace App\Http\Controllers;

use App\Actions\Tickets\UpdateTimelinePreference;
use Illuminate\Http\Request;

class TicketTimelinePreferenceController extends Controller
{
    public function update(Request $request)
    {
        app(UpdateTimelinePreference::class)->handle($request->input('conversations_only'));

        return redirect()->back();
    }
}
