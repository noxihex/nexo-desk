<?php

namespace App\Livewire\Modern\Tickets;

use App\Actions\Tickets\AssumeTicket;
use App\Actions\Tickets\AuthorizeTicketFlow;
use App\Actions\Tickets\DeleteTicket;
use App\Actions\Tickets\FinalizeTicket;
use App\Actions\Tickets\FollowTicket;
use App\Actions\Tickets\TransferTicket;
use App\Actions\Tickets\UpdateTimelinePreference;
use App\Models\Categoria;
use App\Models\Mensagem;
use App\Models\Setor;
use App\Models\Ticket;
use App\Models\User;
use App\Rules\CategoriaPertenceAoSetor;
use App\Services\TicketMessageService;
use App\Services\TicketTimelineService;
use App\Support\AttachmentRules;
use App\Support\TicketStaffAccess;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class TicketShow extends Component
{
    use WithFileUploads, WithPagination;

    #[Locked]
    public int $ticketId = 0;

    #[Locked]
    public string $returnUrl;

    public ?string $success = null;
    public string $message = '';
    public string $messageType = Mensagem::TIPO_PUBLICA;
    public string $messageStatus = '';
    public array $messageAttachments = [];
    public array $newMessageAttachments = [];
    public string $mentionSearch = '';
    public bool $mentioning = false;

    #[Locked]
    public array $mentionedUserIds = [];

    public bool $conversationsOnly = false;

    #[Locked]
    public int $timelineLimit = 3;
    public bool $showAssume = false;
    public bool $showTransfer = false;
    public bool $showFinalize = false;
    public bool $showDelete = false;
    public $assumeSector = null;
    public $assumeCategory = null;
    public $transferSector = null;
    public $transferCategory = null;
    public $transferAnalyst = null;
    public string $finalDescription = '';
    public $finalHours = 0;
    public $finalMinutes = 0;

    public function boot(): void
    {
        if ($this->ticketId) {
            $this->authorizedTicket();
        } else {
            app(AuthorizeTicketFlow::class)->staff();
        }
    }

    public function mount(int $ticketId, string $returnUrl): void
    {
        $this->ticketId = $ticketId;
        $this->returnUrl = $returnUrl;
        [$user] = app(AuthorizeTicketFlow::class)->ticket($ticketId);
        $this->conversationsOnly = (bool) $user->timeline_conversations_only;
        $this->success = session('success');
    }

    public function updatedConversationsOnly(bool $value): void
    {
        app(UpdateTimelinePreference::class)->handle($value);
        $this->timelineLimit = 3;
        $this->resetPage('timeline_page');
    }

    public function loadOlderMessages(): void
    {
        $this->authorizedTicket();
        $this->timelineLimit += 10;
    }

    public function updatedMessage(string $value): void
    {
        if (preg_match('/@([\pL\pN ._-]*)$/u', $value, $matches)) {
            $this->mentioning = true;
            $this->mentionSearch = trim($matches[1]);
        } else {
            $this->mentioning = false;
            $this->mentionSearch = '';
        }
    }

    public function sendPublicReply(?string $status = null): void
    {
        if ($this->mentionedUserIds) {
            $this->addError('message', 'As @menções são permitidas somente em notas internas.');

            return;
        }

        $this->messageType = Mensagem::TIPO_PUBLICA;
        $this->messageStatus = $status ?? '';
        $this->sendMessage();
    }

    public function sendInternalNote(): void
    {
        $this->messageType = Mensagem::TIPO_INTERNA;
        $this->messageStatus = '';
        $this->sendMessage();
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

    public function addMention(int $id): void
    {
        $ticket = $this->authorizedTicket();
        $user = User::with('roles')->where('status', true)->findOrFail($id);
        abort_unless(TicketStaffAccess::allows($user, $ticket), 404);
        if (! in_array($id, $this->mentionedUserIds, true)) {
            $this->mentionedUserIds[] = $id;
            $mention = '@'.$user->name;
            $this->message = preg_match('/@[\pL\pN ._-]*$/u', $this->message)
                ? preg_replace('/@[\pL\pN ._-]*$/u', $mention.' ', $this->message)
                : rtrim($this->message).' '.$mention.' ';
        }
        $this->mentionSearch = '';
        $this->mentioning = false;
    }

    public function removeMention(int $id): void
    {
        $this->mentionedUserIds = array_values(array_filter($this->mentionedUserIds, fn ($item) => (int) $item !== $id));
    }

    public function sendMessage(): void
    {
        $ticket = $this->authorizedTicket();
        $payload = [
            'message' => trim($this->message) ?: null,
            'messageType' => $this->messageType,
            'messageStatus' => $this->messageStatus ?: null,
            'mentionedUserIds' => $this->mentionedUserIds,
            'messageAttachments' => $this->messageAttachments,
        ];
        Validator::make($payload, [
            'message' => 'nullable|string|required_without:messageAttachments',
            'messageType' => 'required|in:publica,interna',
            'messageStatus' => 'nullable|in:pendente cliente,pendente analista',
            'mentionedUserIds' => 'nullable|array',
            'mentionedUserIds.*' => 'integer|distinct|exists:users,id',
        ] + AttachmentRules::for('messageAttachments'))->validate();
        if ($this->messageType === Mensagem::TIPO_INTERNA && $this->messageStatus) {
            $this->addError('messageStatus', 'Notas internas não podem alterar o status.');
            return;
        }
        app(TicketMessageService::class)->create($ticket, app(AuthorizeTicketFlow::class)->staff(), [
            'descricao' => $payload['message'],
            'tipo' => $payload['messageType'],
            'status' => $payload['messageStatus'],
            'mentioned_user_ids' => $payload['mentionedUserIds'],
        ], $this->messageAttachments);
        $this->reset('message', 'messageStatus', 'messageAttachments', 'newMessageAttachments', 'mentionSearch', 'mentionedUserIds', 'mentioning');
        $this->messageType = Mensagem::TIPO_PUBLICA;
        $this->success = 'Mensagem enviada com sucesso!';
        $this->timelineLimit = 3;
    }

    public function toggleFollowing(): void
    {
        [$user, $ticket] = app(AuthorizeTicketFlow::class)->ticket($this->ticketId);
        $following = ! $ticket->seguidores()->whereKey($user->id)->exists();
        app(FollowTicket::class)->handle($ticket->id, $following);
        $this->success = $following ? 'Você está seguindo este ticket.' : 'Você deixou de seguir este ticket.';
    }

    public function openAssume(): void
    {
        $ticket = $this->authorizedTicket();
        $this->assumeSector = $ticket->setor_id;
        $this->assumeCategory = $ticket->categoria_id;
        $this->showAssume = true;
        $this->resetValidation();
    }

    public function updatedAssumeSector(): void
    {
        $this->assumeCategory = null;
    }

    public function assume(): void
    {
        $this->authorizedTicket();
        Validator::make([
            'assumeSector' => $this->assumeSector,
            'assumeCategory' => $this->assumeCategory,
        ], [
            'assumeSector' => 'required|exists:setores,id',
            'assumeCategory' => ['required', 'exists:categorias,id', new CategoriaPertenceAoSetor($this->assumeSector)],
        ])->validate();
        app(AssumeTicket::class)->handle($this->ticketId, ['setor' => $this->assumeSector, 'categoria' => $this->assumeCategory]);
        $this->showAssume = false;
        $this->success = 'Ticket assumido com sucesso!';
    }

    public function openTransfer(): void
    {
        $ticket = $this->authorizedTicket();
        $this->transferSector = $ticket->setor_id;
        $this->transferCategory = $ticket->categoria_id;
        $this->transferAnalyst = $ticket->atribuido_ao_analista_id;
        $this->showTransfer = true;
        $this->resetValidation();
    }

    public function updatedTransferSector(): void
    {
        $this->transferCategory = null;
        $this->transferAnalyst = null;
    }

    public function transfer(): void
    {
        $this->authorizedTicket();
        Validator::make([
            'transferSector' => $this->transferSector,
            'transferCategory' => $this->transferCategory,
            'transferAnalyst' => $this->transferAnalyst,
        ], [
            'transferSector' => 'required|exists:setores,id',
            'transferCategory' => ['required', 'exists:categorias,id', new CategoriaPertenceAoSetor($this->transferSector)],
            'transferAnalyst' => ['nullable', 'exists:users,id', function ($attribute, $value, $fail) {
                if ($value && ! User::whereHas('roles', fn ($query) => $query->whereIn('name', ['analista', 'supervisor', 'administrador']))->where('status', true)
                    ->where('setor_id', $this->transferSector)->whereKey($value)->exists()) {
                    $fail('O analista selecionado deve estar ativo e pertencer ao setor escolhido.');
                }
            }],
        ])->validate();
        app(TransferTicket::class)->handle($this->ticketId, [
            'setor' => $this->transferSector,
            'categoria' => $this->transferCategory,
            'analista' => $this->transferAnalyst ?: null,
        ]);
        $this->showTransfer = false;
        if (! app(AuthorizeTicketFlow::class)->staff()->podeVisualizarTicket(Ticket::findOrFail($this->ticketId))) {
            session()->flash('success', 'Ticket transferido com sucesso!');
            $this->redirect($this->returnUrl, navigate: false);
            return;
        }
        $this->success = 'Ticket transferido com sucesso!';
    }

    public function openFinalize(): void
    {
        $ticket = $this->authorizedTicket();
        $minutes = app(FinalizeTicket::class)->suggestedMinutes($ticket);
        $this->finalHours = intdiv($minutes, 60);
        $this->finalMinutes = $minutes % 60;
        $this->showFinalize = true;
        $this->resetValidation();
    }

    public function finalize(): void
    {
        $this->authorizedTicket();
        Validator::make([
            'finalDescription' => trim($this->finalDescription),
            'finalHours' => $this->finalHours,
            'finalMinutes' => $this->finalMinutes,
        ], [
            'finalDescription' => 'required|string',
            'finalHours' => 'required|integer|min:0',
            'finalMinutes' => 'required|integer|min:0|max:59',
        ])->validate();
        app(FinalizeTicket::class)->handle($this->ticketId, [
            'descricao_fechamento' => trim($this->finalDescription),
            'horas' => $this->finalHours,
            'minutos' => $this->finalMinutes,
        ]);
        $this->showFinalize = false;
        $this->success = 'Ticket finalizado com sucesso!';
    }

    public function delete(): void
    {
        $this->authorizedTicket();
        app(DeleteTicket::class)->handle($this->ticketId);
        session()->flash('success', 'Ticket excluído com sucesso!');
        $this->redirect($this->returnUrl, navigate: false);
    }

    private function authorizedTicket(): Ticket
    {
        [, $ticket] = app(AuthorizeTicketFlow::class)->ticket($this->ticketId);

        return $ticket;
    }

    private function uploadKey($file): string
    {
        return is_object($file) && method_exists($file, 'getFilename')
            ? $file->getFilename()
            : md5(serialize($file));
    }

    public function render()
    {
        [$user, $ticket] = app(AuthorizeTicketFlow::class)->ticket($this->ticketId);
        $ticket->load(['user.roles', 'cliente', 'empresa', 'setor', 'categoria', 'analista', 'attachments', 'seguidores']);
        $mentionables = collect();
        if ($this->mentioning) {
            $mentionables = User::with('roles')->where('status', true)
                ->whereHas('roles', fn ($query) => $query->whereIn('name', ['analista', 'supervisor', 'administrador']))
                ->when(trim($this->mentionSearch) !== '', fn ($query) => $query->where('name', 'like', '%'.trim($this->mentionSearch).'%'))
                ->orderBy('name')->limit(12)->get()
                ->filter(fn (User $candidate) => TicketStaffAccess::allows($candidate, $ticket));
        }
        $selectedMentions = User::whereIn('id', $this->mentionedUserIds)->orderBy('name')->get();

        return view('livewire.modern.tickets.ticket-show', [
            'ticket' => $ticket,
            'timeline' => app(TicketTimelineService::class)->paginate($ticket, $this->timelineLimit, 'timeline_page', false, $this->conversationsOnly),
            'following' => $ticket->seguidores->contains('id', $user->id),
            'administrator' => $user->hasRole('administrador'),
            'manager' => $user->hasAnyRole(['supervisor', 'administrador']),
            'sectors' => Setor::orderBy('nome')->get(),
            'assumeCategories' => $this->categoriesFor($this->assumeSector),
            'transferCategories' => $this->categoriesFor($this->transferSector),
            'transferAnalysts' => User::whereHas('roles', fn ($query) => $query->whereIn('name', ['analista', 'supervisor', 'administrador']))->where('status', true)
                ->when($this->transferSector, fn ($query) => $query->where('setor_id', $this->transferSector))
                ->when(! $this->transferSector, fn ($query) => $query->whereRaw('1 = 0'))->orderBy('name')->get(),
            'mentionables' => $mentionables,
            'selectedMentions' => $selectedMentions,
        ]);
    }

    private function categoriesFor($sector)
    {
        return $sector ? Categoria::whereHas('setores', fn ($query) => $query->whereKey($sector))->orderBy('nome')->get() : collect();
    }
}
