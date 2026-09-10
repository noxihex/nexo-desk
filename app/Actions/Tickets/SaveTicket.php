<?php

namespace App\Actions\Tickets;

use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\User;
use App\Rules\CategoriaPertenceAoSetor;
use App\Support\AttachmentRules;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Throwable;

class SaveTicket
{
    public function handle(array $input, array $files = [], ?int $id = null): Ticket
    {
        $actor = app(AuthorizeTicketFlow::class)->staff();
        $existing = $id ? Ticket::findOrFail($id) : null;
        $categoryRules = ['required', 'exists:categorias,id'];
        $historicalPair = $existing
            && (string) ($input['categoria_id'] ?? '') === (string) $existing->categoria_id
            && (string) ($input['setor_id'] ?? '') === (string) $existing->setor_id;

        if (! $historicalPair) {
            $categoryRules[] = new CategoriaPertenceAoSetor($input['setor_id'] ?? null);
        }

        $rules = [
            'assunto' => 'required|string|max:255',
            'descricao' => 'required|string',
            'categoria_id' => $categoryRules,
            'cliente_id' => 'nullable|exists:users,id',
            'empresa_id' => 'nullable|exists:empresas,id',
            'setor_id' => 'nullable|exists:setores,id',
            'atribuido_ao_analista_id' => ['nullable', 'exists:users,id', function ($attribute, $value, $fail) use ($input) {
                if (! $value) {
                    return;
                }
                $valid = User::whereHas('roles', fn ($query) => $query->whereIn('name', ['analista', 'supervisor', 'administrador']))
                    ->where('status', true)
                    ->where('setor_id', $input['setor_id'] ?? null)
                    ->whereKey($value)->exists();
                if (! $valid) {
                    $fail('O analista selecionado deve estar ativo e pertencer ao setor escolhido.');
                }
            }],
        ];
        if ($id) {
            $rules['status'] = 'required|in:aberto,pendente cliente,pendente analista,fechado';
        }
        $payload = $input + ['anexos' => $files];
        $data = Validator::make($payload, $rules + AttachmentRules::for('anexos'))->validate();
        unset($data['anexos']);
        $stored = [];

        try {
            return DB::transaction(function () use ($id, $actor, $data, $files, &$stored) {
                $ticket = $id ? Ticket::lockForUpdate()->findOrFail($id) : new Ticket;
                $ticket->fill($data);
                if (! $ticket->exists) {
                    $ticket->user_id = $actor->id;
                    $ticket->status = 'aberto';
                }
                $ticket->save();

                foreach ($files as $file) {
                    if (! $file instanceof UploadedFile || ! $file->isValid()) {
                        continue;
                    }
                    $extension = $file->getClientOriginalExtension();
                    $base = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'anexo';
                    $name = Str::uuid().'-'.$base.($extension ? '.'.$extension : '');
                    $path = $file->storeAs('anexos', $name, 'public');
                    $stored[] = $path;
                    TicketAttachment::create(['ticket_id' => $ticket->id, 'file_path' => $path]);
                }

                return $ticket;
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($stored);
            throw $exception;
        }
    }
}
