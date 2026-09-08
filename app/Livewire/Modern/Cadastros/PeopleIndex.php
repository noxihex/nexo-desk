<?php

namespace App\Livewire\Modern\Cadastros;

use App\Actions\Cadastros\{ChangePersonStatus, ManagePeople};
use App\Models\{Empresa, User};
use Livewire\Attributes\{Locked, Url};
use Livewire\WithPagination;

abstract class PeopleIndex extends CatalogComponent
{
    use WithPagination;
    protected bool $contact = false;
    #[Url(except: '')]
    public string $search = '';
    #[Url(except: false)]
    public bool $todos = false;
    #[Locked]
    public ?int $empresaId = null;
    #[Locked]
    public ?int $statusId = null;
    #[Locked]
    public string $statusName = '';
    #[Locked]
    public bool $desiredStatus = false;
    public bool $showStatus = false;
    public ?string $success = null;

    public function mount(?int $empresaId = null): void
    {
        abort_if($this->contact && ! $empresaId, 404);
        $this->empresaId = $empresaId;
        $this->success = session('success');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedTodos(): void
    {
        $this->resetPage();
    }

    protected function record(int $id): User
    {
        $user = User::findOrFail($id);
        app(ManagePeople::class)->authorize($user, $this->contact);
        abort_if($this->empresaId && $user->empresa_id !== $this->empresaId, 404);
        return $user;
    }

    public function confirmStatus(int $id): void
    {
        $user = $this->record($id);
        $this->resetValidation();
        $this->statusId = $user->id;
        $this->statusName = $user->name;
        $this->desiredStatus = ! $user->status;
        $this->showStatus = true;
    }

    public function changeStatus(): void
    {
        abort_unless($this->statusId, 404);
        $this->record($this->statusId);
        $user = app(ChangePersonStatus::class)->handle($this->statusId, $this->contact, $this->desiredStatus);
        $this->showStatus = false;
        $this->success = ($this->contact ? 'Contato' : 'Usuário').($user->status ? ' ativado' : ' desativado').' com sucesso!';
        if ($user->id === auth()->id() && ! $user->status) {
            auth()->setUser($user);
            auth()->logout();
            session()->invalidate();
            session()->regenerateToken();
            $this->skipRender();
            $this->redirectRoute('login', navigate: false);
            return;
        }
        $this->setPage(min($this->getPage(), max(1, (int) ceil($this->query()->count() / 10))));
    }

    protected function query()
    {
        abort_if($this->contact && ! $this->empresaId, 404);
        if ($this->empresaId) {
            Empresa::findOrFail($this->empresaId);
        }
        return User::role($this->contact ? ['cliente'] : ['analista', 'supervisor', 'administrador'])
            ->with(['empresa', 'setor', 'roles'])
            ->when($this->empresaId, fn ($q) => $q->where('empresa_id', $this->empresaId))
            ->when(! $this->contact && ! $this->todos, fn ($q) => $q->where('status', true))
            ->when(trim($this->search) !== '', fn ($q) => $q->where('name', 'like', '%'.trim($this->search).'%'))
            ->orderBy('name');
    }

    public function render()
    {
        return view('livewire.modern.cadastros.people-index', [
            'records' => $this->empresaId ? $this->query()->get() : $this->query()->paginate(10),
            'contact' => $this->contact,
            'routePrefix' => $this->contact ? 'clientes' : 'usuarios',
            'administrator' => User::find(auth()->id())?->hasRole('administrador'),
        ]);
    }
}
