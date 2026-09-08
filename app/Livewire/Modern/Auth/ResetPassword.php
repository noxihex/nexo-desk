<?php

namespace App\Livewire\Modern\Auth;

use App\Actions\Auth\ResetPassword as ResetPasswordAction;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ResetPassword extends Component
{
    #[Locked]
    public string $token = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount(string $token, ?string $email = null): void
    {
        $this->token = $token;
        $this->email = (string) old('email', $email ?? '');
    }

    public function submit(): void
    {
        $this->resetValidation();

        try {
            $status = app(ResetPasswordAction::class)->handle([
                'token' => $this->token,
                'email' => trim($this->email),
                'password' => $this->password,
                'password_confirmation' => $this->password_confirmation,
            ]);
        } finally {
            $this->reset('password', 'password_confirmation');
        }

        if ($status !== Password::PASSWORD_RESET) {
            $this->addError('email', trans($status));
            return;
        }

        session()->flash('status', trans($status));
        $this->redirect(RouteServiceProvider::HOME, navigate: false);
    }

    public function render()
    {
        return view('livewire.modern.auth.reset-password');
    }
}
