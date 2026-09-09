<?php

namespace App\Http\Controllers;

use App\Actions\Account\UpdateAccount;
use Illuminate\Http\Request;

class NotificationPreferenceController extends Controller
{
    public function update(Request $request)
    {
        app(UpdateAccount::class)->preferences($request->all());

        return redirect()->route('minhaconta.edit')->with('success', 'Preferências de notificação atualizadas.');
    }
}
