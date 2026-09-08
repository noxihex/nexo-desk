<?php

namespace App\Livewire\Modern\Cadastros;

use App\Actions\Cadastros\SaveCategoria;
use App\Models\Categoria;
use App\Models\Setor;
use Livewire\Attributes\Locked;

class CategoriaForm extends CatalogComponent
{
    #[Locked]
    public ?int $recordId = null;

    public string $nome = '';
    public string $prioridade = 'Normal';
    public $slatotal = '';
    public $slaupdate = '';
    public array $setor_ids = [];

    public function mount(?int $recordId = null): void
    {
        $this->recordId = $recordId;
        $record = $recordId === null ? null : Categoria::findOrFail($recordId);
        $this->nome = (string) old('nome', $record?->nome ?? '');
        $this->prioridade = (string) old('prioridade', $record?->prioridade ?? 'Normal');
        $this->slatotal = old('slatotal', $record?->slatotal ?? '');
        $this->slaupdate = old('slaupdate', $record?->slaupdate ?? '');
        $this->setor_ids = old('setor_ids', $record?->setores()->pluck('setores.id')->all() ?? []);
    }

    public function save(): void
    {
        $this->resetValidation();
        app(SaveCategoria::class)->handle([
            'nome' => trim($this->nome),
            'prioridade' => $this->prioridade,
            'slatotal' => $this->slatotal,
            'slaupdate' => $this->slaupdate,
            'setor_ids' => $this->setor_ids,
        ], $this->recordId);
        session()->flash('success', $this->recordId
            ? 'Categoria atualizada com sucesso!' : 'Categoria criada com sucesso!');
        $this->redirectRoute('categorias.index', navigate: false);
    }

    public function render()
    {
        return view('livewire.modern.cadastros.categoria-form', ['setores' => Setor::orderBy('nome')->get()]);
    }
}
