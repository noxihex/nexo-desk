<?php

namespace App\Actions\Tickets;

use App\Models\Ticket;
use App\Support\AttachmentRules;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Throwable;

class CreateClientTicket
{
    public function handle(array $input, array $files = []): Ticket
    {
        $user = app(AuthorizeClientTicketFlow::class)->client();
        $payload = $input + ['anexos' => $files];
        $data = Validator::make($payload, [
            'assunto' => 'required|string|max:255',
            'descricao' => 'required|string',
            'setor_id' => 'nullable|exists:setores,id',
        ] + AttachmentRules::for('anexos'))->validate();
        unset($data['anexos']);
        $stored = [];

        try {
            return DB::transaction(function () use ($user, $data, $files, &$stored) {
                $ticket = Ticket::create($data + [
                    'user_id' => $user->id,
                    'cliente_id' => $user->id,
                    'empresa_id' => $user->empresa_id,
                    'status' => 'aberto',
                    'grupo_id' => null,
                    'categoria_id' => null,
                    'atribuido_ao_analista_id' => null,
                ]);

                foreach ($files as $file) {
                    if (! $file instanceof UploadedFile || ! $file->isValid()) {
                        continue;
                    }
                    $extension = $file->getClientOriginalExtension();
                    $base = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'anexo';
                    $name = Str::uuid().'-'.$base.($extension ? '.'.$extension : '');
                    $path = $file->storeAs('attachments/tickets', $name, 'public');
                    $stored[] = $path;
                    $ticket->attachments()->create(['file_path' => $path]);
                }

                return $ticket;
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($stored);
            throw $exception;
        }
    }
}
