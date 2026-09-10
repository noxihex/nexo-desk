<?php

namespace App\Livewire\Modern\ClientTickets;

use App\Actions\Tickets\AuthorizeClientTicketFlow;
use App\Actions\Tickets\ClientTicketMessages;
use App\Actions\Tickets\FinalizeClientTicket;
use App\Actions\Tickets\ReplyToClientTicket;
use App\Support\AttachmentRules;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

class ClientTicketShow extends Component
{
    use WithFileUploads;

    #[Locked]
    public int $ticketId;

    #[Locked]
    public string $returnUrl;

    #[Locked]
    public int $messageLimit = 3;

    public string $message = '';
    public array $messageAttachments = [];
    public array $newMessageAttachments = [];
    public ?string $success = null;
    public bool $showFinalize = false;
    public string $finalDescription = '';

    public function boot(): void
    {
        if (isset($this->ticketId)) {
            app(AuthorizeClientTicketFlow::class)->ticket($this->ticketId);
        } else {
            app(AuthorizeClientTicketFlow::class)->client();
        }
    }

    public function mount(int $ticketId, string $returnUrl): void
    {
        $this->ticketId = $ticketId;
        $this->returnUrl = $returnUrl;
        app(AuthorizeClientTicketFlow::class)->ticket($ticketId);
        $this->success = session('success');
    }

    public function updatedNewMessageAttachments(): void
    {
        $files = collect(array_merge($this->messageAttachments, $this->newMessageAttachments))
            ->unique(fn ($file) => $this->uploadKey($file))
            ->values()
            ->all();
        $validator = Validator::make(['messageAttachments' => $files], AttachmentRules::for('messageAttachments'));

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->addError('messageAttachments', $message);
            }
            $this->newMessageAttachments = $this->messageAttachments;

            return;
        }

        $this->messageAttachments = $files;
        $this->resetValidation(['messageAttachments', 'messageAttachments.*']);
    }

    public function removeMessageAttachment(int $index): void
    {
        if (! array_key_exists($index, $this->messageAttachments)) {
            return;
        }

        $file = $this->messageAttachments[$index];
        $key = $this->uploadKey($file);
        foreach (['messageAttachments', 'newMessageAttachments'] as $property) {
            $this->$property = collect($this->$property)
                ->reject(fn ($attachment) => $this->uploadKey($attachment) === $key)
                ->values()
                ->all();
        }
        if (is_object($file) && method_exists($file, 'delete')) {
            $file->delete();
        }
        $this->resetValidation(['messageAttachments', 'messageAttachments.*']);
    }

    public function sendMessage(): void
    {
        app(ReplyToClientTicket::class)->handle($this->ticketId, [
            'descricao' => trim($this->message) ?: null,
        ], $this->messageAttachments);
        $this->reset('message', 'messageAttachments', 'newMessageAttachments');
        $this->messageLimit = 3;
        $this->success = 'Mensagem enviada com sucesso!';
    }

    public function loadOlderMessages(): void
    {
        app(AuthorizeClientTicketFlow::class)->ticket($this->ticketId);
        $this->messageLimit += 10;
    }

    public function openFinalize(): void
    {
        app(FinalizeClientTicket::class)->authorize($this->ticketId);
        $this->resetValidation();
        $this->showFinalize = true;
    }

    public function finalize(): void
    {
        Validator::make([
            'finalDescription' => trim($this->finalDescription),
        ], [
            'finalDescription' => 'required|string',
        ])->validate();
        app(FinalizeClientTicket::class)->handle($this->ticketId, [
            'descricao_fechamento' => trim($this->finalDescription),
        ]);
        $this->showFinalize = false;
        $this->finalDescription = '';
        $this->success = 'Ticket finalizado com sucesso!';
    }

    public function render()
    {
        [$user, $ticket] = app(AuthorizeClientTicketFlow::class)->ticket($this->ticketId);
        $ticket->load(['attachments', 'categoria', 'user.roles', 'empresa', 'setor']);

        return view('livewire.modern.client-tickets.client-ticket-show', [
            'ticket' => $ticket,
            'messages' => app(ClientTicketMessages::class)->handle($ticket, $this->messageLimit),
            'canFinalize' => $ticket->status !== 'fechado'
                && $user->pode_finalizar_tickets_empresa
                && $user->empresa_id !== null
                && (int) $ticket->empresa_id === (int) $user->empresa_id,
        ]);
    }

    private function uploadKey($file): string
    {
        return is_object($file) && method_exists($file, 'getFilename')
            ? $file->getFilename()
            : md5(serialize($file));
    }
}
