<?php

namespace App\Livewire\Modern\Auth;

use App\Actions\Auth\Login as LoginAction;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Symfony\Component\HttpFoundation\InputBag;

class Login extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    public function mount(): void
    {
        $this->email = (string) old('email', '');
        $this->remember = (bool) old('remember', false);
    }

    public function submit(): void
    {
        $this->resetValidation();
        if (Auth::check()) {
            $this->reset('password');
            $this->redirect(RouteServiceProvider::HOME);
            return;
        }

        $credentials = [
            'email' => trim($this->email),
            'password' => $this->password,
            'remember' => $this->remember,
        ];
        $input = request()->duplicate(null, $credentials);
        // Livewire submits JSON; Request::input() must read these credentials,
        // rather than the original Livewire envelope.
        $input->setJson(new InputBag($credentials));

        try {
            app(LoginAction::class)->handle($input);
        } finally {
            $this->reset('password');
        }

        $this->redirectIntended(RouteServiceProvider::HOME, navigate: false);
    }

    public function render()
    {
        return view('livewire.modern.auth.login');
    }
}
