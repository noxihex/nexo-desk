<x-modern.staff.layout title="Auditoria" section="administration-audit" :show-create-action="false">
    <x-modern.card title="Registro de alterações" description="Histórico das operações realizadas no sistema." class="overflow-hidden">
        <x-modern.table>
            <x-modern.table.columns>
                <x-modern.table.column>Data e hora</x-modern.table.column>
                <x-modern.table.column>Usuário</x-modern.table.column>
                <x-modern.table.column>Ação</x-modern.table.column>
                <x-modern.table.column>Objeto</x-modern.table.column>
                <x-modern.table.column>Alterações</x-modern.table.column>
            </x-modern.table.columns>
            <x-modern.table.rows>
                @forelse($audits as $audit)
                    @php
                        $eventLabel = match ($audit->event) {
                            'created' => 'Criação',
                            'updated' => 'Alteração',
                            'deleted' => 'Exclusão',
                            'restored' => 'Restauração',
                            default => ucfirst($audit->event),
                        };
                    @endphp
                    <x-modern.table.row :key="$audit->id">
                        <x-modern.table.cell class="whitespace-nowrap">{{ $audit->created_at?->format('d/m/Y H:i') }}</x-modern.table.cell>
                        <x-modern.table.cell class="font-medium">{{ $audit->user?->name ?? 'Sistema' }}</x-modern.table.cell>
                        <x-modern.table.cell><x-modern.badge color="blue">{{ $eventLabel }}</x-modern.badge></x-modern.table.cell>
                        <x-modern.table.cell>{{ class_basename($audit->auditable_type) }} #{{ $audit->auditable_id }}</x-modern.table.cell>
                        <x-modern.table.cell>
                            <x-modern.modal :name="'audit-details-'.$audit->id" title="Detalhes da alteração" class="w-full max-w-3xl">
                                <x-slot:trigger><x-modern.button variant="filled" color="sky" size="sm" icon="eye">Ver detalhes</x-modern.button></x-slot:trigger>
                                <div class="grid gap-5 md:grid-cols-2">
                                    <section>
                                        <h3 class="mb-2 text-sm font-semibold text-zinc-900 dark:text-white">Valores anteriores</h3>
                                        <pre class="max-h-96 overflow-auto rounded-lg bg-zinc-100 p-4 text-xs leading-5 text-zinc-700 dark:bg-zinc-950 dark:text-zinc-300">{{ json_encode($audit->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                    </section>
                                    <section>
                                        <h3 class="mb-2 text-sm font-semibold text-zinc-900 dark:text-white">Novos valores</h3>
                                        <pre class="max-h-96 overflow-auto rounded-lg bg-zinc-100 p-4 text-xs leading-5 text-zinc-700 dark:bg-zinc-950 dark:text-zinc-300">{{ json_encode($audit->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                    </section>
                                </div>
                                <div class="mt-5 flex justify-end"><x-modern.button variant="outline" x-on:click="$flux.modal('audit-details-{{ $audit->id }}').close()">Fechar</x-modern.button></div>
                            </x-modern.modal>
                        </x-modern.table.cell>
                    </x-modern.table.row>
                @empty
                    <x-modern.table.row><x-modern.table.cell colspan="5" class="py-12 text-center text-zinc-500">Nenhum registro de auditoria encontrado.</x-modern.table.cell></x-modern.table.row>
                @endforelse
            </x-modern.table.rows>
        </x-modern.table>

        @if($audits->hasPages())
            <div class="mt-5"><x-modern.pagination :paginator="$audits" /></div>
        @endif
    </x-modern.card>
</x-modern.staff.layout>
