<x-modern.staff.layout title="Backups" section="administration-backups" :show-create-action="false">
    <x-modern.card title="Arquivos de backup" description="Somente backups concluídos com sucesso podem ser baixados." class="overflow-hidden">
        <x-modern.table>
            <x-modern.table.columns>
                <x-modern.table.column>ID</x-modern.table.column>
                <x-modern.table.column>Data e hora</x-modern.table.column>
                <x-modern.table.column>Status</x-modern.table.column>
                <x-modern.table.column>Arquivo</x-modern.table.column>
                <x-modern.table.column>Ações</x-modern.table.column>
            </x-modern.table.columns>
            <x-modern.table.rows>
                @forelse($backups as $backup)
                    @php($filename = basename(str_replace('\\', '/', $backup->local_arquivo)))
                    <x-modern.table.row :key="$backup->id">
                        <x-modern.table.cell class="font-medium">#{{ $backup->id }}</x-modern.table.cell>
                        <x-modern.table.cell class="whitespace-nowrap">{{ \Carbon\Carbon::parse($backup->data_hora)->format('d/m/Y H:i:s') }}</x-modern.table.cell>
                        <x-modern.table.cell>
                            <x-modern.badge :color="$backup->status === 'sucesso' ? 'green' : 'red'">{{ $backup->status === 'sucesso' ? 'Sucesso' : 'Falha' }}</x-modern.badge>
                        </x-modern.table.cell>
                        <x-modern.table.cell class="max-w-sm whitespace-normal break-all">{{ $filename }}</x-modern.table.cell>
                        <x-modern.table.cell>
                            @if($backup->status === 'sucesso')
                                <x-modern.button :href="route('backup.download', $backup)" variant="filled" color="blue" size="sm" icon="arrow-down-tray">Baixar</x-modern.button>
                            @else
                                <x-modern.button variant="outline" size="sm" icon="arrow-down-tray" disabled>Indisponível</x-modern.button>
                            @endif
                        </x-modern.table.cell>
                    </x-modern.table.row>
                @empty
                    <x-modern.table.row><x-modern.table.cell colspan="5" class="py-12 text-center text-zinc-500">Nenhum backup encontrado.</x-modern.table.cell></x-modern.table.row>
                @endforelse
            </x-modern.table.rows>
        </x-modern.table>
    </x-modern.card>
</x-modern.staff.layout>
