<?php

namespace App\Livewire\Modern\Auth;

use App\Actions\Auth\ConfirmPassword as ConfirmPasswordAction;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ConfirmPassword extends Component
{
    public string $password = '';

    public function submit(): void
    {
        $this->resetValidation();
        if (! Auth::guard('web')->check()) {
            $this->reset('password');
            $this->redirectRoute('login', navigate: false);
            return;
        }

        try {
            app(ConfirmPasswordAction::class)->handle(['password' => $this->password]);
        } finally {
            $this->reset('password');
        }

        $this->redirectIntended(RouteServiceProvider::HOME, navigate: false);
    }

    public function render()
    {
        return view('livewire.modern.auth.confirm-password');
    }
}
