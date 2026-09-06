<?php

namespace App\Http\Controllers;

use App\Models\InboundMailbox;
use App\Models\Setor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InboundMailboxController extends Controller
{
    public function index()
    {
        return view('administracao.inbound-mailboxes.index', [
            'mailboxes' => InboundMailbox::with('setor')->orderBy('address')->get(),
            'setores' => Setor::orderBy('nome')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateMailbox($request);
        $data['address'] = strtolower($data['address']);
        $data['active'] = $request->boolean('active');
        InboundMailbox::create($data);

        return back()->with('success', 'Caixa de entrada criada.');
    }

    public function update(Request $request, InboundMailbox $mailbox)
    {
        $data = $this->validateMailbox($request, $mailbox);
        $data['address'] = strtolower($data['address']);
        $data['active'] = $request->boolean('active');
        $mailbox->update($data);

        return back()->with('success', 'Caixa de entrada atualizada.');
    }

    public function destroy(InboundMailbox $mailbox)
    {
        $mailbox->delete();

        return back()->with('success', 'Caixa de entrada removida.');
    }

    private function validateMailbox(Request $request, ?InboundMailbox $mailbox = null): array
    {
        return $request->validate([
            'address' => ['required', 'email', 'max:255', Rule::unique('inbound_mailboxes')->ignore(optional($mailbox)->id)],
            'setor_id' => ['required', 'exists:setores,id'],
            'active' => ['nullable', 'boolean'],
        ]);
    }
}
