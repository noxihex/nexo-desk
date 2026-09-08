<form wire:submit="submit" class="space-y-5">
    <x-modern.input name="password" label="Senha atual" type="password" wire:model="password" autocomplete="current-password" required autofocus viewable />
    <x-modern.button type="submit" class="w-full" wire:loading.attr="disabled">Confirmar senha</x-modern.button>
    <p class="text-center"><a href="{{ route('password.request') }}" class="text-sm font-medium text-blue-700 underline-offset-4 hover:underline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-blue-500 dark:text-blue-300">Esqueci minha senha</a></p>
</form>
