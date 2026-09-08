<form wire:submit="submit" class="space-y-5">
    <x-modern.input name="email" label="E-mail" type="email" wire:model="email" autocomplete="username" required autofocus />
    <x-modern.input name="password" label="Nova senha" type="password" wire:model="password" autocomplete="new-password" required viewable />
    <x-modern.input name="password_confirmation" label="Confirme a nova senha" type="password" wire:model="password_confirmation" autocomplete="new-password" required viewable />
    @error('token')
        <x-modern.alert variant="danger">{{ $message }}</x-modern.alert>
    @enderror
    <x-modern.button type="submit" class="w-full" wire:loading.attr="disabled">Redefinir senha</x-modern.button>
    <p class="text-center"><a href="{{ route('login') }}" class="text-sm font-medium text-blue-700 underline-offset-4 hover:underline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-blue-500 dark:text-blue-300">Voltar para o login</a></p>
</form>
