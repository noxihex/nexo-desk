<?php

namespace App\Livewire\Modern\Cadastros;

use App\Actions\Cadastros\AuthorizeCatalogs;
use App\Actions\Cadastros\DeleteCategoria;
use App\Models\Categoria;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class CategoriaIndex extends CatalogComponent
{
    use WithPagination;

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
        $this->resetPage();
    }

    public function confirmDelete(int $id): void
    {
        app(AuthorizeCatalogs::class)->handle();
        $record = Categoria::findOrFail($id);
        $this->resetValidation();
        $this->deleteId = $record->id;
        $this->deleteName = $record->nome;
        $this->showDelete = true;
    }

    public function delete(): void
    {
        abort_if($this->deleteId === null, 404);
        $this->resetValidation();
        app(DeleteCategoria::class)->handle($this->deleteId);
        $this->reset('showDelete', 'deleteId', 'deleteName');
        $this->success = 'Categoria excluída com sucesso!';
        $lastPage = max(1, (int) ceil($this->query()->count() / 10));
        $this->setPage(min($this->getPage(), $lastPage));
    }

    protected function query()
    {
        return Categoria::query()->with('setores')
            ->when(trim($this->search) !== '', fn ($query) => $query->where('nome', 'like', '%'.trim($this->search).'%'))
            ->orderBy('nome');
    }

    public function render()
    {
        return view('livewire.modern.cadastros.categoria-index', [
            'records' => $this->query()->paginate(10),
        ]);
    }
}
