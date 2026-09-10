@php
    $statusColor = match ($ticket->status) {
        'aberto' => 'green', 'pendente cliente' => 'blue', 'pendente analista' => 'yellow', 'fechado' => 'zinc', default => 'zinc',
    };
@endphp

<div class="space-y-6">
    @if($success)
        <x-modern.alert variant="success">{{ $success }}</x-modern.alert>
    @endif

    <x-modern.card>
        <div class="flex flex-col gap-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Ticket #{{ $ticket->id }}</p>
                    <h2 class="mt-1 text-xl font-semibold text-zinc-950 dark:text-white">{{ $ticket->assunto }}</h2>
                </div>
                <x-modern.badge :color="$statusColor">{{ ucfirst($ticket->status) }}</x-modern.badge>
            </div>

            <p class="whitespace-pre-line text-sm leading-6 text-zinc-700 dark:text-zinc-300">{{ $ticket->descricao }}</p>

            <dl class="grid gap-4 border-t border-zinc-200 pt-5 text-sm sm:grid-cols-2 xl:grid-cols-4 dark:border-zinc-700">
                <div><dt class="font-medium text-zinc-500 dark:text-zinc-400">Criado por</dt><dd class="mt-1">{{ $ticket->user?->name ?? 'Usuário removido' }} ({{ $ticket->user?->hasAnyRole(['supervisor', 'analista', 'administrador']) ? 'Equipe' : 'Cliente' }})</dd></div>
                <div><dt class="font-medium text-zinc-500 dark:text-zinc-400">Empresa</dt><dd class="mt-1">{{ $ticket->empresa?->nome ?? 'Sem empresa' }}</dd></div>
                <div><dt class="font-medium text-zinc-500 dark:text-zinc-400">Setor</dt><dd class="mt-1">{{ $ticket->setor?->nome ?? 'Sem setor' }}</dd></div>
                <div><dt class="font-medium text-zinc-500 dark:text-zinc-400">Criado em</dt><dd class="mt-1">{{ $ticket->created_at?->format('d/m/Y - H:i') ?? '—' }}</dd></div>
                <div><dt class="font-medium text-zinc-500 dark:text-zinc-400">Atualizado em</dt><dd class="mt-1">{{ $ticket->updated_at?->format('d/m/Y - H:i') ?? '—' }}</dd></div>
                @if($ticket->status === 'fechado')
                    <div><dt class="font-medium text-zinc-500 dark:text-zinc-400">Horas gastas</dt><dd class="mt-1">{{ intdiv((int) $ticket->horas_gastas, 60) }}h {{ (int) $ticket->horas_gastas % 60 }}min</dd></div>
                @endif
            </dl>

            @if($ticket->status === 'fechado' && $ticket->descricao_final)
                <div class="rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-900 dark:bg-green-950/40">
                    <h3 class="font-medium text-green-900 dark:text-green-200">Relato final</h3>
                    <p class="mt-2 whitespace-pre-line text-sm text-green-800 dark:text-green-300">{{ $ticket->descricao_final }}</p>
                </div>
            @endif

            @if($ticket->attachments->isNotEmpty())
                <div class="border-t border-zinc-200 pt-5 dark:border-zinc-700">
                    <h3 class="text-sm font-semibold">Anexos do ticket</h3>
                    <ul class="mt-2 space-y-2 text-sm">
                        @foreach($ticket->attachments as $attachment)
                            <li><a class="text-blue-600 underline-offset-2 hover:underline dark:text-blue-400" href="{{ asset('storage/'.$attachment->file_path) }}" target="_blank" rel="noopener">{{ basename($attachment->file_path) }}</a></li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="flex flex-wrap justify-between gap-3 border-t border-zinc-200 pt-5 dark:border-zinc-700">
                <x-modern.button :href="$returnUrl" variant="outline" icon="arrow-left">Voltar</x-modern.button>
                @if($canFinalize)
                    <x-modern.button type="button" variant="filled" color="blue" wire:click="openFinalize">Finalizar ticket</x-modern.button>
                @endif
            </div>
        </div>
    </x-modern.card>

    <x-modern.card title="Mensagens">
        <ol class="relative space-y-3 border-s border-zinc-200 ps-6 dark:border-zinc-700">
            @forelse($messages as $messageItem)
                @php $staffAuthor = $messageItem->user?->hasAnyRole(['administrador', 'analista', 'supervisor']); @endphp
                <li wire:key="client-message-{{ $messageItem->id }}" class="relative">
                    <span @class(['absolute -start-[1.95rem] top-1 size-3 rounded-full border-2 border-white dark:border-zinc-900', 'bg-blue-500' => $staffAuthor, 'bg-purple-500' => ! $staffAuthor])></span>
                    <div @class(['rounded-lg border border-zinc-200 p-3 dark:border-zinc-700', 'timeline-message-staff' => $staffAuthor, 'timeline-message-client' => ! $staffAuthor])>
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <p class="text-sm font-semibold">{{ $messageItem->user?->name ?? 'Usuário removido' }} <span class="font-normal text-zinc-500">({{ $staffAuthor ? 'Equipe' : 'Cliente' }})</span></p>
                            <time class="text-xs text-zinc-500">{{ $messageItem->created_at?->format('d/m/Y H:i') }}</time>
                        </div>
                        <p class="mt-2 whitespace-pre-line text-sm leading-5 text-zinc-700 dark:text-zinc-300">{{ $messageItem->descricao }}</p>
                        @if($messageItem->attachments->isNotEmpty())
                            <ul class="mt-3 space-y-1 border-t border-zinc-200 pt-3 text-sm dark:border-zinc-700">
                                @foreach($messageItem->attachments as $attachment)
                                    <li><a class="text-blue-600 hover:underline dark:text-blue-400" href="{{ asset('storage/'.$attachment->file_path) }}" target="_blank" rel="noopener">{{ basename($attachment->file_path) }}</a></li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </li>
            @empty
                <li class="text-sm text-zinc-500">Nenhuma mensagem ainda.</li>
            @endforelse
        </ol>

        @if($messages->hasMorePages())
            <div class="mt-5 flex justify-start">
                <x-modern.button type="button" size="sm" variant="outline" icon="clock" wire:click="loadOlderMessages" wire:loading.attr="disabled" wire:target="loadOlderMessages">Exibir mensagens mais antigas</x-modern.button>
            </div>
        @endif
    </x-modern.card>

    @if($ticket->status !== 'fechado')
        <x-modern.card title="Enviar nova mensagem">
            <form wire:submit="sendMessage" class="space-y-4">
                <x-modern.textarea name="message" label="Mensagem" placeholder="Digite sua mensagem aqui..." rows="3" wire:model="message" />
                <x-modern.file-upload model="newMessageAttachments" :files="$messageAttachments" />
                <div class="flex justify-start">
                    <x-modern.button type="submit" variant="filled" color="green" icon="paper-airplane" wire:loading.attr="disabled">Enviar mensagem</x-modern.button>
                </div>
            </form>
        </x-modern.card>
    @endif

    @if($canFinalize)
        <x-modern.modal name="finalize-client-ticket" wire:model.self="showFinalize" title="Finalizar ticket" class="w-full max-w-lg">
            <form wire:submit="finalize" class="space-y-5">
                <x-modern.alert>As horas gastas serão calculadas automaticamente e não poderão ser alteradas.</x-modern.alert>
                @error('categoria')
                    <x-modern.alert variant="danger">{{ $message }}</x-modern.alert>
                @enderror
                <x-modern.textarea name="finalDescription" label="Relato final" rows="4" wire:model="finalDescription" required />
                <div class="flex justify-end gap-2">
                    <x-modern.button type="button" variant="outline" x-on:click="$flux.modal('finalize-client-ticket').close()">Cancelar</x-modern.button>
                    <x-modern.button type="submit" variant="filled" color="blue" wire:loading.attr="disabled">Finalizar</x-modern.button>
                </div>
            </form>
        </x-modern.modal>
    @endif
</div>
