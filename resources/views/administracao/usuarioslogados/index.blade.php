<x-modern.staff.layout title="Usuários logados" section="administration-sessions" :show-create-action="false">
    <x-modern.card title="Sessões ativas" description="Consulte os acessos atuais e encerre sessões quando necessário." class="overflow-hidden">
        <x-modern.table>
            <x-modern.table.columns>
                <x-modern.table.column>Usuário</x-modern.table.column>
                <x-modern.table.column>Perfil</x-modern.table.column>
                <x-modern.table.column>Origem</x-modern.table.column>
                <x-modern.table.column>Última atividade</x-modern.table.column>
                <x-modern.table.column>Ações</x-modern.table.column>
            </x-modern.table.columns>
            <x-modern.table.rows>
                @forelse($sessions as $session)
                    @php
                        [$roleLabel, $roleColor] = match ($session->role) {
                            'cliente' => ['Cliente', 'green'],
                            'analista' => ['Analista', 'blue'],
                            'supervisor' => ['Supervisor', 'amber'],
                            'administrador' => ['Administrador', 'red'],
                            default => ['Desconhecido', 'zinc'],
                        };
                    @endphp
                    <x-modern.table.row :key="$session->session_id">
                        <x-modern.table.cell>
                            <p class="font-medium text-zinc-950 dark:text-white">{{ $session->name }}</p>
                            <p class="mt-1 text-xs text-zinc-500">ID {{ $session->user_id }}</p>
                        </x-modern.table.cell>
                        <x-modern.table.cell><x-modern.badge :color="$roleColor">{{ $roleLabel }}</x-modern.badge></x-modern.table.cell>
                        <x-modern.table.cell class="max-w-md whitespace-normal">
                            <p class="text-sm">{{ $session->ip_address ?: 'IP não informado' }}</p>
                            <p class="mt-1 break-words text-xs text-zinc-500 dark:text-zinc-400">{{ $session->user_agent ?: 'Navegador não informado' }}</p>
                        </x-modern.table.cell>
                        <x-modern.table.cell class="whitespace-nowrap">{{ $session->last_activity_at->format('d/m/Y H:i:s') }}</x-modern.table.cell>
                        <x-modern.table.cell>
                            <x-modern.modal :name="'logout-session-'.$session->session_id" title="Encerrar sessão" class="w-full max-w-md">
                                <x-slot:trigger>
                                    <x-modern.button variant="filled" color="red" size="sm" icon="arrow-right-start-on-rectangle">Deslogar</x-modern.button>
                                </x-slot:trigger>
                                <form method="POST" action="{{ route('administracao.usuarioslogados.deslogar', $session->session_id) }}" class="space-y-5">
                                    @csrf
                                    @method('DELETE')
                                    <p class="text-sm leading-6">Deseja encerrar a sessão de <strong>{{ $session->name }}</strong>?</p>
                                    <div class="flex justify-end gap-2">
                                        <x-modern.button variant="outline" x-on:click="$flux.modal('logout-session-{{ $session->session_id }}').close()">Cancelar</x-modern.button>
                                        <x-modern.button type="submit" variant="filled" color="red">Encerrar sessão</x-modern.button>
                                    </div>
                                </form>
                            </x-modern.modal>
                        </x-modern.table.cell>
                    </x-modern.table.row>
                @empty
                    <x-modern.table.row><x-modern.table.cell colspan="5" class="py-12 text-center text-zinc-500">Nenhuma sessão ativa encontrada.</x-modern.table.cell></x-modern.table.row>
                @endforelse
            </x-modern.table.rows>
        </x-modern.table>
    </x-modern.card>
</x-modern.staff.layout>
