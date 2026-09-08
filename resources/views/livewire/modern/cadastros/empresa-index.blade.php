<div class="space-y-6">
    @if($success)
        <x-modern.alert variant="success">{{ $success }}</x-modern.alert>
    @endif

    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div class="w-full sm:max-w-sm">
            <x-modern.input type="search" name="search" label="Pesquisar por nome" placeholder="Digite um nome..." wire:model.live.debounce.300ms="search" />
        </div>
        <x-modern.button :href="route('empresas.create')" icon="plus">Nova empresa</x-modern.button>
    </div>

    <div role="status" class="text-sm text-zinc-500 dark:text-zinc-400" wire:loading wire:target="search, gotoPage, nextPage, previousPage">Atualizando lista...</div>

    <x-modern.card class="overflow-hidden">
        <x-modern.table :paginator="$records">
            <x-modern.table.columns>
                <x-modern.table.column>Nome</x-modern.table.column>
                <x-modern.table.column>CNPJ</x-modern.table.column>
                <x-modern.table.column>Ações</x-modern.table.column>
            </x-modern.table.columns>
            <x-modern.table.rows>
                @forelse($records as $record)
                    <x-modern.table.row :key="$record->id">
                        <x-modern.table.cell class="max-w-sm whitespace-normal font-medium">{{ $record->nome }}</x-modern.table.cell>
                        <x-modern.table.cell class="max-w-sm whitespace-normal">{{ $record->cnpj }}</x-modern.table.cell>
                        <x-modern.table.cell>
                            <div class="flex flex-wrap gap-2">
                                <x-modern.button :href="route('empresas.edit', $record)" variant="outline" size="sm" icon="pencil-square" :aria-label="'Editar '.$record->nome">Editar</x-modern.button>
                                <x-modern.button variant="danger" size="sm" icon="trash" wire:click="confirmDelete({{ $record->id }})" :aria-label="'Excluir '.$record->nome">Excluir</x-modern.button>
                            </div>
                        </x-modern.table.cell>
                    </x-modern.table.row>
                @empty
                    <x-modern.table.row>
                        <x-modern.table.cell colspan="3" class="py-10 text-center text-zinc-500">
                            {{ trim($search) !== '' ? 'Nenhum resultado para esta pesquisa.' : 'Nenhuma empresa cadastrada.' }}
                        </x-modern.table.cell>
                    </x-modern.table.row>
                @endforelse
            </x-modern.table.rows>
        </x-modern.table>
    </x-modern.card>

    <x-modern.modal name="delete-record" wire:model.self="showDelete" title="Confirmar exclusão" class="w-full max-w-md">
        <form wire:submit="delete" class="space-y-5">
            <p class="text-sm leading-6">Deseja excluir a empresa <strong>{{ $deleteName }}</strong>?</p>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Os tickets e contatos serão preservados e ficarão sem vínculo com esta empresa.</p>
            @error('delete')
                <x-modern.alert variant="danger">{{ $message }}</x-modern.alert>
            @enderror
            <div class="flex flex-wrap justify-end gap-2">
                <x-modern.button variant="outline" autofocus x-on:click="$flux.modal('delete-record').close()">Cancelar</x-modern.button>
                <x-modern.button type="submit" variant="danger" wire:loading.attr="disabled">Confirmar exclusão</x-modern.button>
            </div>
        </form>
    </x-modern.modal>
</div>
