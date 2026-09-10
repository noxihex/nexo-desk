<?php

namespace App\Livewire\Modern\ClientTickets;

use App\Actions\Tickets\AuthorizeClientTicketFlow;
use App\Actions\Tickets\CreateClientTicket;
use App\Models\Setor;
use App\Support\AttachmentRules;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

class ClientTicketCreate extends Component
{
    use WithFileUploads;

    #[Locked]
    public string $returnUrl;

    public string $assunto = '';
    public string $descricao = '';
    public $setor_id = null;
    public array $anexos = [];
    public array $newAnexos = [];

    public function boot(): void
    {
        app(AuthorizeClientTicketFlow::class)->client();
    }

    public function mount(string $returnUrl): void
    {
        $this->returnUrl = $returnUrl;
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
        app(CreateClientTicket::class)->handle([
            'assunto' => trim($this->assunto),
            'descricao' => trim($this->descricao),
            'setor_id' => $this->setor_id ?: null,
        ], $this->anexos);

        session()->flash('success', 'Ticket criado com sucesso!');
        $this->redirect($this->returnUrl, navigate: false);
    }

    public function render()
    {
        $user = app(AuthorizeClientTicketFlow::class)->client();

        return view('livewire.modern.client-tickets.client-ticket-create', [
            'user' => $user->loadMissing('empresa'),
            'sectors' => Setor::orderBy('nome')->get(),
        ]);
    }

    private function uploadKey($file): string
    {
        return is_object($file) && method_exists($file, 'getFilename')
            ? $file->getFilename()
            : md5(serialize($file));
    }
}
