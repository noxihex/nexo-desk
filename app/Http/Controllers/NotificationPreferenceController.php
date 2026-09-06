<?php

namespace App\Http\Controllers;

use App\Models\NotificationPreference;
use Illuminate\Http\Request;

class NotificationPreferenceController extends Controller
{
    public function update(Request $request)
    {
        $rules = [];
        foreach (NotificationPreference::EVENTS as $event) {
            foreach (NotificationPreference::CHANNELS as $channel) {
                $rules[$event . '_' . $channel] = ['required', 'boolean'];
            }
        }

        $data = $request->validate($rules);
        $request->user()->notificationPreference()->updateOrCreate([], $data);

        return redirect()->route('minhaconta.edit')->with('success', 'Preferências de notificação atualizadas.');
    }
}
