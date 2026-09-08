<?php

namespace App\Livewire\Modern\Cadastros;

use App\Actions\Cadastros\AuthorizeCatalogs;
use App\Actions\Cadastros\DeleteSetor;
use App\Models\Setor;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;

class SetorIndex extends CatalogComponent
{

    #[Url(except: '')]
    public string $search = '';

    #[Locked]
    public ?int $deleteId = null;

    #[Locked]
    public string $deleteName = '';

    public bool $showDelete = false;
    public ?string $success = null;

    public function mount(): void
    {
        $this->success = session('success');
    }

    public function updatedSearch(): void
    {
        $this->resetValidation();
    }

    public function confirmDelete(int $id): void
    {
        app(AuthorizeCatalogs::class)->handle();
        $record = Setor::findOrFail($id);
        $this->resetValidation();
        $this->deleteId = $record->id;
        $this->deleteName = $record->nome;
        $this->showDelete = true;
    }

    public function delete(): void
    {
        abort_if($this->deleteId === null, 404);
        $this->resetValidation();
        app(DeleteSetor::class)->handle($this->deleteId);
        $this->reset('showDelete', 'deleteId', 'deleteName');
        $this->success = 'Setor excluído com sucesso!';
    }

    protected function query()
    {
        return Setor::query()
            ->when(trim($this->search) !== '', fn ($query) => $query->where('nome', 'like', '%'.trim($this->search).'%'))
            ->orderBy('nome');
    }

    public function render()
    {
        return view('livewire.modern.cadastros.setor-index', [
            'records' => $this->query()->get(),
        ]);
    }
}
