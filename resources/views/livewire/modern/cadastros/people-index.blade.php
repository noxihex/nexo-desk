<div class="space-y-6">
    @if($success)<x-modern.alert variant="success">{{ $success }}</x-modern.alert>@endif
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div class="w-full sm:max-w-sm">
            <x-modern.input type="search" name="search" label="Pesquisar por nome" wire:model.live.debounce.300ms="search" />
        </div>
        @if(!$contact)<x-modern.checkbox name="todos" label="Incluir inativos" wire:model.live="todos" />@endif
        <x-modern.button :href="route($routePrefix.'.create', $empresaId ? ['empresa_id' => $empresaId] : [])" icon="plus">{{ $contact ? 'Novo contato' : 'Novo usuário' }}</x-modern.button>
    </div>
    <p role="status" wire:loading class="text-sm text-zinc-500">Atualizando...</p>
    <x-modern.card class="overflow-hidden">
        <x-modern.table :paginator="$empresaId ? null : $records">
            <x-modern.table.columns>
                <x-modern.table.column>Nome / E-mail</x-modern.table.column>
                @if($contact)
                    <x-modern.table.column>Empresa</x-modern.table.column>
                @else
                    <x-modern.table.column>Perfil</x-modern.table.column>
                    <x-modern.table.column>Setor</x-modern.table.column>
                @endif
                <x-modern.table.column>Status</x-modern.table.column>
                <x-modern.table.column>Ações</x-modern.table.column>
            </x-modern.table.columns>
            <x-modern.table.rows>
                @forelse($records as $record)
                    <x-modern.table.row :key="$record->id">
                        <x-modern.table.cell class="whitespace-normal"><span class="font-medium">{{ $record->name }}</span><br><span class="text-sm text-zinc-500">{{ $record->email }}</span></x-modern.table.cell>
                        <x-modern.table.cell class="whitespace-normal">
                            @if($contact)
                                {{ $record->empresa?->nome ?? 'Sem empresa' }}
                            @else
                                @php($profile = $record->roles->first()?->name ?? '')
                                <x-modern.badge :color="match ($profile) { 'administrador' => 'red', 'supervisor' => 'yellow', 'analista' => 'green', default => 'zinc' }">{{ ucfirst($profile) }}</x-modern.badge>
                            @endif
                        </x-modern.table.cell>
                        @if(!$contact)
                            <x-modern.table.cell class="whitespace-normal">{{ $record->setor?->nome ?? 'Sem setor' }}</x-modern.table.cell>
                        @endif
                        <x-modern.table.cell>{{ $record->status ? 'Ativo' : 'Inativo' }}</x-modern.table.cell>
                        <x-modern.table.cell>
                            @if($contact || $administrator || !$record->hasAnyRole(['supervisor', 'administrador']))
                                <div class="flex flex-wrap gap-2">
                                    <x-modern.button :href="route($routePrefix.'.edit', $record)" variant="outline" size="sm" :aria-label="'Editar '.$record->name">Editar</x-modern.button>
                                    @if($contact || $record->id !== 1)
                                        <x-modern.button :variant="$record->status ? 'danger' : 'outline'" size="sm" :icon="$record->status ? 'stop-circle' : null" wire:click="confirmStatus({{ $record->id }})" :aria-label="($record->status ? 'Desativar ' : 'Ativar ').$record->name">{{ $record->status ? 'Desativar' : 'Ativar' }}</x-modern.button>
                                    @endif
                                </div>
                            @endif
                        </x-modern.table.cell>
                    </x-modern.table.row>
                @empty
                    <x-modern.table.row><x-modern.table.cell :colspan="$contact ? 4 : 5" class="py-10 text-center">Nenhum registro encontrado.</x-modern.table.cell></x-modern.table.row>
                @endforelse
            </x-modern.table.rows>
        </x-modern.table>
    </x-modern.card>
    <x-modern.modal name="person-status" wire:model.self="showStatus" title="Confirmar alteração de status" class="w-full max-w-md">
        <form wire:submit="changeStatus" class="space-y-5">
            <p>Deseja {{ $desiredStatus ? 'ativar' : 'desativar' }} <strong>{{ $statusName }}</strong>?</p>
            @if(!$desiredStatus)<p class="text-sm text-zinc-500">O acesso será bloqueado e as sessões serão encerradas. Os tickets serão preservados.</p>@endif
            @error('status')<x-modern.alert variant="danger">{{ $message }}</x-modern.alert>@enderror
            <div class="flex flex-wrap justify-end gap-2">
                <x-modern.button variant="outline" autofocus x-on:click="$flux.modal('person-status').close()">Cancelar</x-modern.button>
                <x-modern.button type="submit" :variant="$desiredStatus ? 'primary' : 'danger'" wire:loading.attr="disabled">Confirmar</x-modern.button>
            </div>
        </form>
    </x-modern.modal>
</div>
