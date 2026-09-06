<?php

namespace App\Http\Controllers;

use App\Models\EmailReplyToken;
use App\Models\InboundEmail;
use App\Models\InboundMailbox;
use App\Models\MessageAttachment;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\User;
use App\Support\AttachmentRules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Throwable;

class MailgunInboundController extends Controller
{
    public function __invoke(Request $request)
    {
        if (!config('ticket_notifications.inbound_enabled')) {
            return response()->json(['message' => 'Entrada por e-mail desativada.'], 406);
        }

        if (!$this->validSignature($request)) {
            return response()->json(['message' => 'Assinatura inválida.'], 406);
        }

        $tokenHash = hash('sha256', (string) $request->input('token'));
        $existing = InboundEmail::where('webhook_token_hash', $tokenHash)->first();
        if ($existing && $existing->status !== 'failed') {
            return response()->json(['message' => 'Webhook já recebido.'], $existing->status === 'rejected' ? 406 : 200);
        }

        $sender = $this->normalizeAddress((string) $request->input('sender'));
        $recipient = $this->normalizeAddress((string) $request->input('recipient'));
        $subject = Str::limit(trim(strip_tags((string) $request->input('subject'))), 255, '');
        $messageId = $this->messageId($request);

        if (!$existing && $messageId) {
            $duplicate = InboundEmail::where('provider', 'mailgun')->where('provider_message_id', $messageId)->first();
            if ($duplicate) {
                return response()->json(['message' => 'Mensagem já recebida.']);
            }
        }

        $inbound = $existing ?: InboundEmail::create([
            'provider' => 'mailgun',
            'provider_message_id' => $messageId,
            'webhook_token_hash' => $tokenHash,
            'sender' => $sender,
            'recipient' => $recipient,
            'subject' => $subject ?: null,
        ]);

        $user = User::whereRaw('LOWER(email) = ?', [$sender])->where('status', true)->first();
        if (!$user || !$user->hasRole('cliente') && !$user->hasAnyRole(['analista', 'supervisor', 'administrador'])) {
            return $this->reject($inbound, 'Remetente desconhecido ou inativo.');
        }

        $body = trim(strip_tags((string) ($request->input('stripped-text') ?: $request->input('body-plain'))));
        if ($body === '') {
            return $this->reject($inbound, 'Mensagem sem conteúdo textual.');
        }
        if (Str::length($body) > 100000) {
            return $this->reject($inbound, 'Mensagem excede o tamanho permitido.');
        }

        if ((int) $request->input('attachment-count', 0) > AttachmentRules::MAX_FILES) {
            return $this->reject($inbound, 'Anexos fora dos limites permitidos.');
        }

        $attachments = $this->attachments($request);
        $validator = Validator::make(['attachments' => $attachments], AttachmentRules::for('attachments'));
        if ($validator->fails()) {
            return $this->reject($inbound, 'Anexos fora dos limites permitidos.');
        }

        $storedPaths = [];
        try {
            $reply = $this->parseReplyRecipient($recipient);
            if ($reply) {
                $ticket = $this->authorizedReplyTicket($reply['ticket_id'], $reply['token'], $user);
                if (!$ticket) {
                    return $this->reject($inbound, 'Resposta não autorizada, expirada ou para ticket fechado.');
                }

                DB::transaction(function () use ($inbound, $ticket, $user, $body, $attachments, &$storedPaths) {
                    $message = $ticket->mensagens()->create([
                        'user_id' => $user->id,
                        'descricao' => $body,
                        'tipo' => 'publica',
                        'origem' => 'email',
                    ]);
                    foreach ($attachments as $attachment) {
                        $path = $attachment->storeAs('attachments/messages', Str::uuid() . '.' . $attachment->extension(), 'public');
                        $storedPaths[] = $path;
                        MessageAttachment::create(['mensagem_id' => $message->id, 'file_path' => $path]);
                    }
                    $inbound->update(['status' => 'processed', 'ticket_id' => $ticket->id, 'mensagem_id' => $message->id, 'failure_reason' => null]);
                });
            } else {
                if (!$user->hasRole('cliente')) {
                    return $this->reject($inbound, 'Apenas clientes podem criar tickets por e-mail.');
                }
                $mailbox = InboundMailbox::whereRaw('LOWER(address) = ?', [$recipient])->where('active', true)->first();
                if (!$mailbox) {
                    return $this->reject($inbound, 'Caixa de entrada inexistente ou desativada.');
                }

                DB::transaction(function () use ($inbound, $mailbox, $user, $subject, $body, $attachments, &$storedPaths) {
                    $ticket = Ticket::create([
                        'assunto' => $subject ?: 'Ticket recebido por e-mail',
                        'descricao' => $body,
                        'user_id' => $user->id,
                        'cliente_id' => $user->id,
                        'empresa_id' => $user->empresa_id,
                        'setor_id' => $mailbox->setor_id,
                        'categoria_id' => null,
                        'grupo_id' => null,
                        'atribuido_ao_analista_id' => null,
                        'status' => 'aberto',
                        'origem' => 'email',
                    ]);
                    foreach ($attachments as $attachment) {
                        $path = $attachment->storeAs('attachments/tickets', Str::uuid() . '.' . $attachment->extension(), 'public');
                        $storedPaths[] = $path;
                        TicketAttachment::create(['ticket_id' => $ticket->id, 'file_path' => $path]);
                    }
                    $inbound->update(['status' => 'processed', 'ticket_id' => $ticket->id, 'failure_reason' => null]);
                });
            }
        } catch (Throwable $exception) {
            foreach ($storedPaths as $path) {
                Storage::disk('public')->delete($path);
            }
            $inbound->update(['status' => 'failed', 'failure_reason' => Str::limit($exception->getMessage(), 255)]);
            report($exception);

            return response()->json(['message' => 'Falha transitória ao processar e-mail.'], 500);
        }

        return response()->json(['message' => 'E-mail processado.']);
    }

    private function validSignature(Request $request): bool
    {
        $key = (string) config('ticket_notifications.mailgun_signing_key');
        $timestamp = (int) $request->input('timestamp');
        $token = (string) $request->input('token');
        $signature = (string) $request->input('signature');
        if ($key === '' || $timestamp <= 0 || $token === '' || $signature === '') {
            return false;
        }
        if (abs(time() - $timestamp) > config('ticket_notifications.mailgun_timestamp_tolerance')) {
            return false;
        }

        return hash_equals(hash_hmac('sha256', $timestamp . $token, $key), $signature);
    }

    private function normalizeAddress(string $address): string
    {
        return Str::lower(trim($address, " \t\n\r\0\x0B<>"));
    }

    private function messageId(Request $request): ?string
    {
        $headers = json_decode((string) $request->input('message-headers'), true);
        foreach (is_array($headers) ? $headers : [] as $header) {
            if (is_array($header) && Str::lower((string) ($header[0] ?? '')) === 'message-id') {
                return Str::limit(trim((string) ($header[1] ?? ''), '<>'), 191, '');
            }
        }

        return null;
    }

    private function attachments(Request $request): array
    {
        $attachments = [];
        $count = min((int) $request->input('attachment-count', 0), AttachmentRules::MAX_FILES + 1);
        for ($index = 1; $index <= $count; $index++) {
            if ($request->hasFile('attachment-' . $index)) {
                $attachments[] = $request->file('attachment-' . $index);
            }
        }

        return $attachments;
    }

    private function parseReplyRecipient(string $recipient): ?array
    {
        $local = preg_quote(config('ticket_notifications.reply_local_part'), '/');
        $domain = preg_quote((string) config('ticket_notifications.inbound_domain'), '/');
        if (!$domain || !preg_match("/^{$local}\\+(\\d+)\\.([A-Za-z0-9]+)@{$domain}$/i", $recipient, $matches)) {
            return null;
        }

        return ['ticket_id' => (int) $matches[1], 'token' => $matches[2]];
    }

    private function authorizedReplyTicket(int $ticketId, string $token, User $user): ?Ticket
    {
        $validToken = EmailReplyToken::where('ticket_id', $ticketId)
            ->where('token_hash', hash('sha256', $token))
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->exists();
        if (!$validToken) {
            return null;
        }

        $ticket = Ticket::find($ticketId);
        if (!$ticket || $ticket->status === 'fechado') {
            return null;
        }
        $authorized = (int) $ticket->cliente_id === (int) $user->id
            || (int) $ticket->atribuido_ao_analista_id === (int) $user->id;

        return $authorized ? $ticket : null;
    }

    private function reject(InboundEmail $inbound, string $reason)
    {
        $inbound->update(['status' => 'rejected', 'failure_reason' => $reason]);

        return response()->json(['message' => $reason], 406);
    }
}
