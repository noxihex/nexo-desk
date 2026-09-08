<?php

namespace App\Livewire\Modern\Cadastros;

use App\Actions\Cadastros\SaveSetor;
use App\Models\Setor;
use Livewire\Attributes\Locked;

class SetorForm extends CatalogComponent
{
    #[Locked]
    public ?int $recordId = null;

    public string $nome = '';

    public function mount(?int $recordId = null): void
    {
        $this->recordId = $recordId;
        $record = $recordId === null ? null : Setor::findOrFail($recordId);
        $this->nome = (string) old('nome', $record?->nome ?? '');
    }

    public function save(): void
    {
        $this->resetValidation();
        app(SaveSetor::class)->handle([
            'nome' => trim($this->nome),
        ], $this->recordId);
        session()->flash('success', $this->recordId
            ? 'Setor atualizado com sucesso!' : 'Setor criado com sucesso!');
        $this->redirectRoute('setores.index', navigate: false);
    }

    public function render()
    {
        return view('livewire.modern.cadastros.setor-form');
    }
}
