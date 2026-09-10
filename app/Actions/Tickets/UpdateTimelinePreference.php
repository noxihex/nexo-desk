<?php

namespace App\Actions\Tickets;

use Illuminate\Support\Facades\Validator;

class UpdateTimelinePreference
{
    public function handle($value): void
    {
        $user = app(AuthorizeTicketFlow::class)->staff();
        $data = Validator::make(['conversations_only' => $value], [
            'conversations_only' => ['required', 'boolean'],
        ])->validate();
        $user->update(['timeline_conversations_only' => $data['conversations_only']]);
    }
}
