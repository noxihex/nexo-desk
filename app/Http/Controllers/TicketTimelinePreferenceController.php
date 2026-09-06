<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TicketTimelinePreferenceController extends Controller
{
    public function update(Request $request)
    {
        $data = $request->validate([
            'conversations_only' => ['required', 'boolean'],
        ]);

        $request->user()->update([
            'timeline_conversations_only' => $data['conversations_only'],
        ]);

        return redirect()->back();
    }
}
