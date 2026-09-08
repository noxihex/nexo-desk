<form wire:submit="submit" class="space-y-5">
    @if($status)
        <x-modern.alert variant="success">{{ $status }}</x-modern.alert>
    @endif
    @if($error)
        <x-modern.alert variant="warning">{{ $error }}</x-modern.alert>
    @endif
    <x-modern.input name="email" label="E-mail" type="email" wire:model="email" autocomplete="email" required autofocus />
    <x-modern.button type="submit" class="w-full" wire:loading.attr="disabled">Enviar link de recuperação</x-modern.button>
    <p class="text-center"><a href="{{ route('login') }}" class="text-sm font-medium text-blue-700 underline-offset-4 hover:underline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-blue-500 dark:text-blue-300">Voltar para o login</a></p>
</form>
