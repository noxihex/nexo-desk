<?php

namespace App\Livewire\Modern\Cadastros;

use App\Actions\Cadastros\ManagePeople;
use App\Actions\Cadastros\SavePerson;
use App\Models\{Empresa, Setor, User};
use Livewire\Attributes\Locked;

abstract class PersonForm extends CatalogComponent
{
    protected bool $contact = false;
    #[Locked]
    public ?int $recordId = null;
    #[Locked]
    public ?int $empresaId = null;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public $setor_id = null;
    public string $role = 'analista';
    public bool $pode_ver_tickets_outros_setores = false;
    public bool $pode_finalizar_tickets_empresa = false;

    public function mount(?int $recordId = null, ?int $empresaId = null): void
    {
        $this->recordId = $recordId;
        $user = $recordId ? User::findOrFail($recordId) : null;
        if ($user) {
            app(ManagePeople::class)->authorize($user, $this->contact);
        }
        $this->empresaId = $user ? $user->empresa_id : $empresaId;
        abort_if($this->contact && ! $user && ! $this->empresaId, 404);
        if ($this->empresaId) {
            Empresa::findOrFail($this->empresaId);
        }
        foreach (['name', 'email'] as $field) {
            $this->$field = (string) old($field, $user?->$field ?? '');
        }
        $this->setor_id = old('setor_id', $user?->setor_id);
        $this->role = old('role', $user?->roles->first()?->name ?? 'analista');
        $this->pode_ver_tickets_outros_setores = (bool) old('pode_ver_tickets_outros_setores', $user?->pode_ver_tickets_outros_setores ?? false);
        $this->pode_finalizar_tickets_empresa = (bool) old('pode_finalizar_tickets_empresa', $user?->pode_finalizar_tickets_empresa ?? false);
    }

    public function hydrate(): void
    {
        if ($this->recordId) {
            app(ManagePeople::class)->authorize(User::findOrFail($this->recordId), $this->contact);
        }
    }

    public function save(): void
    {
        $this->resetValidation();
        try {
            $user = app(SavePerson::class)->handle([
                'name' => trim($this->name), 'email' => trim($this->email),
                'password' => $this->password, 'password_confirmation' => $this->password_confirmation,
                'empresa_id' => $this->empresaId, 'setor_id' => $this->setor_id ?: null,
                'role' => $this->role, 'pode_ver_tickets_outros_setores' => $this->pode_ver_tickets_outros_setores,
                'pode_finalizar_tickets_empresa' => $this->pode_finalizar_tickets_empresa,
            ], $this->contact, $this->recordId);
        } finally {
            $this->reset('password', 'password_confirmation');
        }
        session()->flash('success', ($this->contact ? 'Contato' : 'Usuário').($this->recordId ? ' atualizado' : ' criado').' com sucesso!');
        if ($user->id === auth()->id() && ! $user->hasAnyRole(['supervisor', 'administrador'])) {
            $this->skipRender();
            $this->redirect('/home', navigate: false);
            return;
        }
        $this->redirect($this->returnUrl(), navigate: false);
    }

    protected function returnUrl(): string
    {
        return $this->contact && $this->empresaId
            ? route('empresas.edit', $this->empresaId)
            : route($this->contact ? 'empresas.index' : 'usuarios.index');
    }

    public function render()
    {
        return view('livewire.modern.cadastros.person-form', [
            'contact' => $this->contact,
            'empresa' => $this->empresaId ? Empresa::find($this->empresaId) : null,
            'setores' => $this->contact ? [] : Setor::orderBy('nome')->pluck('nome', 'id')->all(),
            'roles' => $this->contact ? [] : app(ManagePeople::class)->roles(),
            'returnUrl' => $this->returnUrl(),
        ]);
    }
}
