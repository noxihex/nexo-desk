<?php

namespace App\Livewire\Modern\Tickets;

use App\Actions\Tickets\AuthorizeTicketFlow;
use App\Actions\Tickets\SaveTicket;
use App\Models\Categoria;
use App\Models\Empresa;
use App\Models\Setor;
use App\Models\Ticket;
use App\Models\User;
use App\Support\AttachmentRules;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

class TicketForm extends Component
{
    use WithFileUploads;

    #[Locked]
    public ?int $recordId = null;

    #[Locked]
    public string $returnUrl;

    public string $assunto = '';
    public string $descricao = '';
    public $cliente_id = null;
    public $empresa_id = null;
    public $setor_id = null;
    public $categoria_id = null;
    public $atribuido_ao_analista_id = null;
    public string $status = 'aberto';
    public array $anexos = [];
    public array $newAnexos = [];

    public function boot(): void
    {
        app(AuthorizeTicketFlow::class)->staff();
    }

    public function mount(?int $recordId, string $returnUrl): void
    {
        $this->recordId = $recordId;
        $this->returnUrl = $returnUrl;
        $ticket = $recordId ? Ticket::findOrFail($recordId) : null;

        $this->assunto = (string) old('assunto', $ticket?->assunto ?? '');
        $this->descricao = (string) old('descricao', $ticket?->descricao ?? '');
        foreach (['cliente_id', 'empresa_id', 'setor_id', 'categoria_id', 'atribuido_ao_analista_id'] as $field) {
            $this->$field = old($field, $ticket?->$field);
        }
        $this->status = (string) old('status', $ticket?->status ?? 'aberto');

        if (! $ticket && ! $this->setor_id) {
            $user = app(AuthorizeTicketFlow::class)->staff();
            $this->setor_id = $user->setor_id;
            $this->atribuido_ao_analista_id = $user->setor_id ? $user->id : null;
        }
    }

    public function hydrate(): void
    {
        if ($this->recordId) {
            Ticket::findOrFail($this->recordId);
        }
    }

    public function updatedClienteId($value): void
    {
        $this->empresa_id = $value ? User::whereHas('roles', fn ($query) => $query->where('name', 'cliente'))
            ->whereKey($value)->value('empresa_id') : null;
    }

    public function updatedSetorId(): void
    {
        $this->categoria_id = null;
        $this->atribuido_ao_analista_id = null;
        $this->resetValidation(['categoria_id', 'atribuido_ao_analista_id']);
    }

    public function updatedNewAnexos(): void
    {
        $files = collect(array_merge($this->anexos, $this->newAnexos))
            ->unique(fn ($file) => $this->uploadKey($file))
            ->values()
            ->all();
        $validator = Validator::make(['anexos' => $files], AttachmentRules::for('anexos'));

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->addError('anexos', $message);
            }
            $this->newAnexos = $this->anexos;

            return;
        }

        $this->anexos = $files;
        $this->resetValidation(['anexos', 'anexos.*']);
    }

    public function removeAnexo(int $index): void
    {
        if (! array_key_exists($index, $this->anexos)) {
            return;
        }

        $file = $this->anexos[$index];
        $key = $this->uploadKey($file);
        foreach (['anexos', 'newAnexos'] as $property) {
            $this->$property = collect($this->$property)
                ->reject(fn ($attachment) => $this->uploadKey($attachment) === $key)
                ->values()
                ->all();
        }
        if (is_object($file) && method_exists($file, 'delete')) {
            $file->delete();
        }
        $this->resetValidation(['anexos', 'anexos.*']);
    }

    public function save(): void
    {
        $this->resetValidation();
        app(SaveTicket::class)->handle([
            'assunto' => trim($this->assunto),
            'descricao' => trim($this->descricao),
            'cliente_id' => $this->cliente_id ?: null,
            'empresa_id' => $this->empresa_id ?: null,
            'setor_id' => $this->setor_id ?: null,
            'categoria_id' => $this->categoria_id ?: null,
            'atribuido_ao_analista_id' => $this->atribuido_ao_analista_id ?: null,
            'status' => $this->status,
        ], $this->recordId ? [] : $this->anexos, $this->recordId);

        session()->flash('success', $this->recordId ? 'Ticket atualizado com sucesso!' : 'Ticket criado com sucesso!');
        $this->redirect($this->returnUrl, navigate: false);
    }

    public function render()
    {
        $categories = Categoria::query()
            ->when($this->setor_id, fn ($query) => $query->whereHas('setores', fn ($setores) => $setores->whereKey($this->setor_id)))
            ->when(! $this->setor_id, fn ($query) => $query->whereRaw('1 = 0'));
        if ($this->recordId) {
            $ticket = Ticket::findOrFail($this->recordId);
            if ($ticket->categoria_id && (string) $ticket->setor_id === (string) $this->setor_id) {
                $categories->orWhere('categorias.id', $ticket->categoria_id);
            }
        }

        return view('livewire.modern.tickets.ticket-form', [
            'categories' => $categories->orderBy('nome')->get(),
            'clients' => User::whereHas('roles', fn ($query) => $query->where('name', 'cliente'))->with('empresa')->where('status', true)->orderBy('name')->get(),
            'companies' => Empresa::orderBy('nome')->get(),
            'sectors' => Setor::orderBy('nome')->get(),
            'analysts' => User::whereHas('roles', fn ($query) => $query->whereIn('name', ['analista', 'supervisor', 'administrador']))->where('status', true)
                ->when($this->setor_id, fn ($query) => $query->where('setor_id', $this->setor_id))
                ->when(! $this->setor_id, fn ($query) => $query->whereRaw('1 = 0'))->orderBy('name')->get(),
        ]);
    }

    private function uploadKey($file): string
    {
        return is_object($file) && method_exists($file, 'getFilename')
            ? $file->getFilename()
            : md5(serialize($file));
    }
}
