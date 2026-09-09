<x-modern.card class="max-w-2xl">
    <form wire:submit="save" class="space-y-6">
        <x-modern.input name="nome" label="Nome do setor" wire:model="nome" maxlength="255" required autofocus />
        <div class="flex flex-wrap gap-3">
            <x-modern.button type="submit" variant="filled" color="green" wire:loading.attr="disabled">{{ $recordId ? 'Salvar alterações' : 'Criar setor' }}</x-modern.button>
            <x-modern.button :href="route('setores.index')" variant="outline">Cancelar</x-modern.button>
        </div>
    </form>
</x-modern.card>
