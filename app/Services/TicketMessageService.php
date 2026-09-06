<?php

namespace App\Services;

use App\Jobs\DispatchStaffTicketActivity;
use App\Models\Mensagem;
use App\Models\Ticket;
use App\Models\User;
use App\Support\TicketStaffAccess;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class TicketMessageService
{
    public function create(Ticket $ticket, User $actor, array $data, array $files = []): Mensagem
    {
        $type = $data['tipo'] ?? Mensagem::TIPO_PUBLICA;
        $mentionIds = array_values(array_unique(array_map('intval', $data['mentioned_user_ids'] ?? [])));

        if ($type === Mensagem::TIPO_INTERNA) {
            TicketStaffAccess::abortUnlessAllowed($actor, $ticket);
        }
        if ($type !== Mensagem::TIPO_INTERNA && $mentionIds) {
            throw ValidationException::withMessages([
                'mentioned_user_ids' => 'Menções são permitidas somente em notas internas.',
            ]);
        }

        $mentioned = $this->validateMentionedUsers($ticket, $mentionIds)
            ->filter(function (User $user) use ($data) {
                return stripos((string) ($data['descricao'] ?? ''), '@' . $user->name) !== false;
            })->values();
        $stored = [];

        try {
            $message = DB::transaction(function () use ($ticket, $actor, $data, $files, $type, $mentioned, &$stored) {
                $message = $ticket->mensagens()->create([
                    'user_id' => $actor->id,
                    'descricao' => $data['descricao'] ?: 'Anexo enviado.',
                    'tipo' => $type,
                    'origem' => $data['origem'] ?? 'web',
                ]);

                $disk = $type === Mensagem::TIPO_INTERNA ? 'local' : 'public';
                $directory = $type === Mensagem::TIPO_INTERNA ? 'private/attachments/messages' : 'attachments/messages';
                foreach ($files as $file) {
                    if (!$file instanceof UploadedFile || !$file->isValid()) {
                        continue;
                    }
                    $extension = $file->getClientOriginalExtension();
                    $baseName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'anexo';
                    $name = Str::uuid() . '-' . $baseName . ($extension ? '.' . $extension : '');
                    $path = $file->storeAs($directory, $name, $disk);
                    $stored[] = [$disk, $path];
                    $message->attachments()->create(['file_path' => $path, 'disk' => $disk]);
                }

                if ($mentioned->isNotEmpty()) {
                    $message->mencoes()->sync($mentioned->pluck('id'));
                }

                if ($type === Mensagem::TIPO_PUBLICA && !empty($data['status'])) {
                    $ticket->status = $data['status'];
                }
                $ticket->updated_at = now();
                $ticket->save();

                return $message;
            });
        } catch (Throwable $exception) {
            foreach ($stored as [$disk, $path]) {
                Storage::disk($disk)->delete($path);
            }
            throw $exception;
        }

        if ($mentioned->isNotEmpty()) {
            DispatchStaffTicketActivity::dispatch(
                'message-mention:' . $message->id,
                $ticket->id,
                $actor->id,
                'mentions',
                $mentioned->pluck('id')->all(),
                $message->descricao
            )->afterCommit();
        }

        return $message->load(['attachments', 'mencoes']);
    }

    private function validateMentionedUsers(Ticket $ticket, array $ids)
    {
        if (!$ids) {
            return collect();
        }

        $users = User::with('roles')->whereIn('id', $ids)->where('status', true)->get()
            ->filter(fn (User $user) => TicketStaffAccess::allows($user, $ticket))->values();

        if ($users->count() !== count($ids)) {
            throw ValidationException::withMessages([
                'mentioned_user_ids' => 'Um ou mais analistas mencionados não podem visualizar este ticket.',
            ]);
        }

        return $users;
    }
}
