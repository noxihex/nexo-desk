<form wire:submit="submit" class="space-y-5">
    <x-modern.input name="email" label="E-mail" type="email" wire:model="email" autocomplete="username" required autofocus />
    <x-modern.input name="password" label="Senha" type="password" wire:model="password" autocomplete="current-password" required viewable />
    <div class="flex flex-wrap items-center justify-between gap-3">
        <x-modern.checkbox name="remember" label="Lembrar de mim" wire:model="remember" />
        <a href="{{ route('password.request') }}" class="text-sm font-medium text-blue-700 underline-offset-4 hover:underline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-blue-500 dark:text-blue-300">Esqueci minha senha</a>
    </div>
    <x-modern.button type="submit" class="w-full" wire:loading.attr="disabled">Entrar</x-modern.button>
</form>
