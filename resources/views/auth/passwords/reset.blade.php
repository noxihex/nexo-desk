<x-layouts.auth title="Redefinir senha" description="Escolha uma nova senha para sua conta.">
    <livewire:modern.auth.reset-password :token="$token" :email="$email" />
</x-layouts.auth>
