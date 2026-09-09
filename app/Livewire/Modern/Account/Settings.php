<?php

namespace App\Livewire\Modern\Account;

use App\Actions\Account\UpdateAccount;
use App\Models\NotificationPreference;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Settings extends Component
{
    #[Locked]
    public int $accountId;
    public string $name = '';
    public string $email = '';
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';
    public array $preferences = [];
    public ?string $passwordSuccess = null;
    public ?string $preferencesSuccess = null;

    public function boot(): void
    {
        app(UpdateAccount::class)->user();
    }

    public function mount(): void
    {
        $user = app(UpdateAccount::class)->user();
        $this->accountId = $user->id;
        $this->name = (string) old('name', $user->name);
        $this->email = (string) old('email', $user->email);
        foreach (NotificationPreference::EVENTS as $event) {
            foreach (NotificationPreference::CHANNELS as $channel) {
                $field = $event.'_'.$channel;
                $this->preferences[$field] = (bool) old($field, $user->notificationPreference?->$field ?? true);
            }
        }
    }

    public function hydrate(): void
    {
        abort_unless(app(UpdateAccount::class)->user()->id === $this->accountId, 403);
    }

    public function saveProfile(): void
    {
        $this->resetValidation();
        app(UpdateAccount::class)->profile(['name' => trim($this->name), 'email' => trim($this->email)]);
        session()->flash('success', 'Perfil atualizado com sucesso!');
        $this->redirectRoute('minhaconta.edit', navigate: false);
    }

    public function savePassword(): void
    {
        $this->resetValidation();
        $this->passwordSuccess = null;
        try {
            $updated = app(UpdateAccount::class)->password($this->only(['current_password', 'password', 'password_confirmation']));
        } finally {
            $this->reset('current_password', 'password', 'password_confirmation');
        }
        if (! $updated) {
            $this->addError('current_password', 'A senha atual está incorreta.');
            return;
        }
        $this->passwordSuccess = 'Senha alterada com sucesso!';
    }

    public function savePreferences(): void
    {
        $this->resetValidation();
        $this->preferencesSuccess = null;
        app(UpdateAccount::class)->preferences($this->preferences);
        $this->preferencesSuccess = 'Preferências de notificação atualizadas.';
    }

    public function render()
    {
        return view('livewire.modern.account.settings');
    }
}
