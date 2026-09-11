<x-modern.staff.layout title="Caixas de e-mail" section="administration-mailboxes" :show-create-action="false">
    <div class="space-y-6">
        <x-modern.card title="Nova caixa de entrada" description="Mensagens recebidas neste endereço criarão ou atualizarão tickets no setor selecionado.">
            <form method="POST" action="{{ route('inbound-mailboxes.store') }}" class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto] lg:items-end">
                @csrf
                <x-modern.input type="email" name="address" label="Endereço de e-mail" placeholder="suporte@exemplo.com" :value="old('address')" required />
                <x-modern.select name="setor_id" label="Setor padrão" :options="$setores->pluck('nome', 'id')" :selected="old('setor_id')" placeholder="Selecione um setor" required />
                <div class="flex flex-wrap items-center gap-4 lg:pb-0.5">
                    <input type="hidden" name="active" value="0">
                    <x-modern.checkbox name="active" label="Ativa" value="1" :checked="old('active', true)" />
                    <x-modern.button type="submit" variant="filled" color="green" icon="plus">Adicionar</x-modern.button>
                </div>
            </form>
        </x-modern.card>

        <x-modern.card title="Caixas configuradas" class="overflow-hidden">
            <x-modern.table>
                <x-modern.table.columns>
                    <x-modern.table.column>Endereço</x-modern.table.column>
                    <x-modern.table.column>Setor padrão</x-modern.table.column>
                    <x-modern.table.column>Status</x-modern.table.column>
                    <x-modern.table.column>Ações</x-modern.table.column>
                </x-modern.table.columns>
                <x-modern.table.rows>
                    @forelse($mailboxes as $mailbox)
                        <x-modern.table.row :key="$mailbox->id">
                            <x-modern.table.cell class="font-medium text-zinc-950 dark:text-white">{{ $mailbox->address }}</x-modern.table.cell>
                            <x-modern.table.cell>{{ $mailbox->setor?->nome ?? 'Setor removido' }}</x-modern.table.cell>
                            <x-modern.table.cell><x-modern.badge :color="$mailbox->active ? 'green' : 'zinc'">{{ $mailbox->active ? 'Ativa' : 'Inativa' }}</x-modern.badge></x-modern.table.cell>
                            <x-modern.table.cell>
                                <div class="flex flex-wrap gap-2">
                                    <x-modern.modal :name="'edit-mailbox-'.$mailbox->id" title="Editar caixa de entrada" class="w-full max-w-xl">
                                        <x-slot:trigger><x-modern.button variant="filled" color="amber" size="sm" icon="pencil-square">Editar</x-modern.button></x-slot:trigger>
                                        <form method="POST" action="{{ route('inbound-mailboxes.update', $mailbox) }}" class="space-y-5">
                                            @csrf
                                            @method('PUT')
                                            <x-modern.input type="email" name="address" label="Endereço de e-mail" :value="$mailbox->address" required />
                                            <x-modern.select name="setor_id" label="Setor padrão" :options="$setores->pluck('nome', 'id')" :selected="$mailbox->setor_id" required />
                                            <input type="hidden" name="active" value="0">
                                            <x-modern.checkbox name="active" label="Caixa ativa" value="1" :checked="$mailbox->active" />
                                            <div class="flex justify-end gap-2">
                                                <x-modern.button variant="outline" x-on:click="$flux.modal('edit-mailbox-{{ $mailbox->id }}').close()">Cancelar</x-modern.button>
                                                <x-modern.button type="submit" variant="filled" color="green">Salvar alterações</x-modern.button>
                                            </div>
                                        </form>
                                    </x-modern.modal>

                                    <x-modern.modal :name="'delete-mailbox-'.$mailbox->id" title="Remover caixa de entrada" class="w-full max-w-md">
                                        <x-slot:trigger><x-modern.button variant="filled" color="red" size="sm" icon="trash">Remover</x-modern.button></x-slot:trigger>
                                        <form method="POST" action="{{ route('inbound-mailboxes.destroy', $mailbox) }}" class="space-y-5">
                                            @csrf
                                            @method('DELETE')
                                            <p class="text-sm leading-6">Deseja remover <strong>{{ $mailbox->address }}</strong>? Novos e-mails enviados para este endereço não serão processados por essa configuração.</p>
                                            <div class="flex justify-end gap-2">
                                                <x-modern.button variant="outline" x-on:click="$flux.modal('delete-mailbox-{{ $mailbox->id }}').close()">Cancelar</x-modern.button>
                                                <x-modern.button type="submit" variant="filled" color="red">Confirmar remoção</x-modern.button>
                                            </div>
                                        </form>
                                    </x-modern.modal>
                                </div>
                            </x-modern.table.cell>
                        </x-modern.table.row>
                    @empty
                        <x-modern.table.row><x-modern.table.cell colspan="4" class="py-12 text-center text-zinc-500">Nenhuma caixa de entrada configurada.</x-modern.table.cell></x-modern.table.row>
                    @endforelse
                </x-modern.table.rows>
            </x-modern.table>
        </x-modern.card>
    </div>
</x-modern.staff.layout>
