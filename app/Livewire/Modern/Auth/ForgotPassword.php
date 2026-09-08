<?php

namespace App\Livewire\Modern\Auth;

use App\Actions\Auth\SendPasswordResetLink;
use Illuminate\Support\Facades\Password;
use Livewire\Component;

class ForgotPassword extends Component
{
    public string $email = '';
    public ?string $status = null;
    public ?string $error = null;

    public function mount(): void
    {
        $this->email = (string) old('email', '');
    }

    public function submit(): void
    {
        $this->reset('status', 'error');
        $this->resetValidation();
        $status = app(SendPasswordResetLink::class)->handle(['email' => trim($this->email)]);

        if ($status === null) {
            $this->error = SendPasswordResetLink::DISABLED_MESSAGE;
        } elseif ($status === Password::RESET_LINK_SENT) {
            $this->status = trans($status);
        } else {
            $this->addError('email', trans($status));
        }
    }

    public function render()
    {
        return view('livewire.modern.auth.forgot-password');
    }
}
