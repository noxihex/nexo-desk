@php
    $statusColor = match ($ticket->status) {
        'aberto' => 'green', 'pendente cliente' => 'blue', 'pendente analista' => 'yellow', 'fechado' => 'zinc', default => 'zinc',
    };
@endphp

<div class="space-y-6">
    @if($success)<x-modern.alert variant="success">{{ $success }}</x-modern.alert>@endif

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
                <div><dt class="font-medium text-zinc-500 dark:text-zinc-400">Contato</dt><dd class="mt-1">{{ $ticket->cliente?->name ?? 'Sem contato' }}</dd></div>
                <div><dt class="font-medium text-zinc-500 dark:text-zinc-400">Empresa</dt><dd class="mt-1">{{ $ticket->empresa?->nome ?? 'Sem empresa' }}</dd></div>
                <div><dt class="font-medium text-zinc-500 dark:text-zinc-400">Setor</dt><dd class="mt-1">{{ $ticket->setor?->nome ?? 'Sem setor' }}</dd></div>
                <div><dt class="font-medium text-zinc-500 dark:text-zinc-400">Categoria</dt><dd class="mt-1">{{ $ticket->categoria?->nome ?? 'Sem categoria' }}</dd></div>
                <div><dt class="font-medium text-zinc-500 dark:text-zinc-400">Analista</dt><dd class="mt-1">{{ $ticket->analista?->name ?? 'Não atribuído' }}</dd></div>
                <div><dt class="font-medium text-zinc-500 dark:text-zinc-400">Criado por</dt><dd class="mt-1">{{ $ticket->user?->name ?? 'Usuário removido' }} ({{ $ticket->user?->hasAnyRole(['supervisor', 'analista', 'administrador']) ? 'Equipe' : 'Cliente' }})</dd></div>
                <div><dt class="font-medium text-zinc-500 dark:text-zinc-400">Criado em</dt><dd class="mt-1">{{ $ticket->created_at?->format('d/m/Y - H:i') }}</dd></div>
                <div><dt class="font-medium text-zinc-500 dark:text-zinc-400">Atualizado em</dt><dd class="mt-1">{{ $ticket->updated_at?->format('d/m/Y - H:i') }}</dd></div>
            </dl>

            @if($ticket->status === 'fechado' && $ticket->descricao_final)
                <div class="rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-900 dark:bg-green-950/40">
                    <h3 class="font-medium text-green-900 dark:text-green-200">Relato final</h3>
                    <p class="mt-2 whitespace-pre-line text-sm text-green-800 dark:text-green-300">{{ $ticket->descricao_final }}</p>
                    <p class="mt-2 text-xs text-green-700 dark:text-green-400">Tempo registrado: {{ intdiv((int) $ticket->horas_gastas, 60) }}h {{ (int) $ticket->horas_gastas % 60 }}min</p>
                </div>
            @endif

            @if($ticket->attachments->isNotEmpty())
                <div class="border-t border-zinc-200 pt-5 dark:border-zinc-700">
                    <h3 class="text-sm font-semibold">Anexos do ticket</h3>
                    <ul class="mt-2 space-y-2 text-sm">
                        @foreach($ticket->attachments as $attachment)
                            <li><a class="text-blue-600 underline-offset-2 hover:underline dark:text-blue-400" href="{{ route('tickets.downloadAttachment', [$ticket, $attachment]) }}">{{ basename($attachment->file_path) }}</a></li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="flex flex-col gap-3 border-t border-zinc-200 pt-5 sm:flex-row sm:items-start sm:justify-between dark:border-zinc-700">
                <div class="flex justify-start"><x-modern.button :href="$returnUrl" variant="outline" icon="arrow-left">Voltar</x-modern.button></div>
                <div class="flex flex-wrap justify-end gap-2">
                    @if($ticket->status !== 'fechado')
                        <x-modern.button type="button" variant="filled" color="green" icon="hand-raised" wire:click="openAssume">Assumir</x-modern.button>
                        <x-modern.button type="button" variant="filled" color="purple" icon="arrow-right-circle" wire:click="openTransfer">Transferir</x-modern.button>
                        <x-modern.button type="button" variant="filled" color="blue" icon="check-circle" wire:click="openFinalize">Finalizar</x-modern.button>
                    @endif
                    @if($manager)
                        <x-modern.button :href="route('tickets.edit', ['ticket' => $ticket, 'return_to' => $returnUrl])" variant="filled" color="amber" icon="pencil-square">Editar</x-modern.button>
                    @endif
                    @if($administrator)
                        <x-modern.button type="button" variant="filled" color="red" icon="trash" wire:click="$set('showDelete', true)">Excluir</x-modern.button>
                    @endif
                </div>
            </div>
            <flux:error name="categoria" />
        </div>
    </x-modern.card>

    <x-modern.card title="Histórico do ticket">
        <x-slot:actions>
            <x-modern.button type="button" size="sm" variant="outline" :icon="$following ? 'bell-slash' : 'bell'" wire:click="toggleFollowing">{{ $following ? 'Deixar de seguir' : 'Seguir ticket' }}</x-modern.button>
        </x-slot:actions>
        <div class="mb-5 flex justify-end">
            <x-modern.checkbox name="conversationsOnly" :label="$conversationsOnly ? 'Exibir toda a atividade' : 'Ocultar sistema e alterações'" wire:model.live="conversationsOnly" />
        </div>

        <ol class="relative space-y-3 border-s border-zinc-200 ps-6 dark:border-zinc-700">
            @forelse($timeline as $event)
                @php
                    $eventColor = match ($event['type']) { 'interna' => 'amber', 'publica' => (($event['author_role'] ?? null) === 'client' ? 'purple' : 'blue'), 'alteracao' => 'zinc', 'criacao' => 'green', default => 'zinc' };
                    $eventDot = match ($eventColor) { 'amber' => 'bg-amber-500', 'purple' => 'bg-purple-500', 'blue' => 'bg-blue-500', 'green' => 'bg-green-500', default => 'bg-zinc-500' };
                    $eventLabel = match ($event['type']) { 'interna' => 'Nota interna', 'publica' => 'Resposta pública', 'alteracao' => 'Alteração', 'criacao' => 'Criação', default => 'Sistema' };
                @endphp
                <li wire:key="{{ $event['key'] }}" class="relative">
                    <span class="absolute -start-[1.95rem] top-1 size-3 rounded-full border-2 border-white {{ $eventDot }} dark:border-zinc-900"></span>
                    <div @class([
                        'rounded-lg border border-zinc-200 p-3 dark:border-zinc-700',
                        'timeline-message-client' => ($event['author_role'] ?? null) === 'client',
                        'timeline-message-staff' => ($event['author_role'] ?? null) === 'staff',
                    ])>
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <p class="text-sm font-semibold">{{ $event['actor'] }} @if(!empty($event['author_role_label']))<span class="font-normal text-zinc-500">({{ $event['author_role_label'] }})</span>@endif</p>
                            <time class="text-xs text-zinc-500">{{ $event['created_at']->format('d/m/Y H:i') }}</time>
                        </div>
                        <x-modern.badge :color="$eventColor" size="sm" class="mt-1.5">{{ $eventLabel }}</x-modern.badge>
                        <div class="mt-2 text-sm leading-5 text-zinc-700 dark:text-zinc-300">
                            @if($event['type'] === 'alteracao')
                                @foreach($event['changes'] as $change)
                                    <p><strong>{{ $change['label'] }}:</strong> {{ $change['old'] }} → {{ $change['new'] }}</p>
                                @endforeach
                            @else
                                <p class="whitespace-pre-line">{{ $event['description'] ?? '' }}</p>
                                @if(!empty($event['mentions']) && $event['mentions']->isNotEmpty())
                                    <p class="mt-2 text-xs text-zinc-500">Mencionados: {{ $event['mentions']->pluck('name')->join(', ') }}</p>
                                @endif
                                @if(!empty($event['attachments']) && $event['attachments']->isNotEmpty())
                                    <ul class="mt-3 space-y-1 border-t border-zinc-200 pt-3 dark:border-zinc-700">
                                        @foreach($event['attachments'] as $attachment)
                                            <li><a class="text-blue-600 hover:underline dark:text-blue-400" href="{{ ($attachment->disk ?? 'public') === 'local' ? route('tickets.internal-attachments.show', [$ticket, $attachment]) : asset('storage/'.$attachment->file_path) }}">{{ basename($attachment->file_path) }}</a></li>
                                        @endforeach
                                    </ul>
                                @endif
                            @endif
                        </div>
                    </div>
                </li>
            @empty
                <li class="text-sm text-zinc-500">Nenhum evento ainda.</li>
            @endforelse
        </ol>
        @if($timeline->hasMorePages())
            <div class="mt-5 flex justify-start">
                <x-modern.button type="button" size="sm" variant="outline" icon="clock" wire:click="loadOlderMessages" wire:loading.attr="disabled" wire:target="loadOlderMessages">Exibir mensagens mais antigas</x-modern.button>
            </div>
        @endif
    </x-modern.card>

    @if($ticket->status !== 'fechado')
        <x-modern.card title="Enviar nova mensagem">
            <div class="space-y-4">
                <div class="relative">
                    <x-modern.textarea name="message" label="Mensagem" description="Use @ para mencionar integrantes da equipe em notas internas." placeholder="Digite sua mensagem aqui..." rows="3" wire:model.live.debounce.250ms="message" />
                    @if($mentioning && $mentionables->isNotEmpty())
                        <div class="absolute z-20 mt-1 max-h-48 w-full overflow-y-auto rounded-lg border border-zinc-200 bg-white p-1 shadow-lg dark:border-zinc-700 dark:bg-zinc-900" role="listbox" aria-label="Sugestões de menção">
                            @foreach($mentionables as $person)
                                <button type="button" wire:click="addMention({{ $person->id }})" class="flex w-full items-center rounded-md px-3 py-2 text-left text-sm hover:bg-zinc-100 focus:bg-zinc-100 focus:outline-none dark:hover:bg-zinc-800 dark:focus:bg-zinc-800" role="option">{{ '@'.$person->name }}</button>
                            @endforeach
                        </div>
                    @endif
                </div>
                @if($selectedMentions->isNotEmpty())
                    <div class="flex flex-wrap gap-2" aria-label="Pessoas mencionadas">
                        @foreach($selectedMentions as $person)
                            <button type="button" wire:click="removeMention({{ $person->id }})" class="rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-900 hover:bg-amber-200 focus:outline-none focus:ring-2 focus:ring-amber-500 dark:bg-amber-900 dark:text-amber-100">{{ '@'.$person->name }} ×</button>
                        @endforeach
                    </div>
                @endif
                <flux:error name="mentionedUserIds" />

                <x-modern.file-upload model="newMessageAttachments" :files="$messageAttachments" />

                <div class="flex flex-wrap items-center gap-2">
                    <flux:button.group>
                        <x-modern.button type="button" variant="filled" color="green" icon="paper-airplane" wire:click="sendPublicReply" wire:loading.attr="disabled" wire:target="sendPublicReply,sendInternalNote">Responder publicamente</x-modern.button>
                        <flux:dropdown position="bottom" align="end">
                            <x-modern.button type="button" variant="filled" color="green" icon="chevron-down" aria-label="Outras opções de resposta" />
                            <flux:menu>
                                <flux:menu.item wire:click="sendPublicReply('pendente cliente')">Responder e alterar para Pendente Cliente</flux:menu.item>
                                <flux:menu.item wire:click="sendPublicReply('pendente analista')">Responder e alterar para Pendente Analista</flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                    </flux:button.group>
                    <x-modern.button type="button" variant="filled" color="amber" icon="lock-closed" wire:click="sendInternalNote" wire:loading.attr="disabled" wire:target="sendPublicReply,sendInternalNote">Adicionar nota interna</x-modern.button>
                </div>
            </div>
        </x-modern.card>
    @endif

    <x-modern.modal name="assume-ticket" wire:model.self="showAssume" title="Assumir ticket" class="w-full max-w-lg">
        <form wire:submit="assume" class="space-y-5">
            <p class="text-sm">Você será definido como responsável pelo ticket #{{ $ticket->id }}.</p>
            <x-modern.select name="assumeSector" label="Setor" placeholder="Selecione" :options="$sectors->pluck('nome', 'id')->all()" wire:model.live="assumeSector" />
            <x-modern.select name="assumeCategory" label="Categoria" placeholder="Selecione" :options="$assumeCategories->pluck('nome', 'id')->all()" wire:model="assumeCategory" />
            <div class="flex justify-end gap-2"><x-modern.button type="button" variant="outline" autofocus x-on:click="$flux.modal('assume-ticket').close()">Cancelar</x-modern.button><x-modern.button type="submit" variant="filled" color="green">Assumir</x-modern.button></div>
        </form>
    </x-modern.modal>

    <x-modern.modal name="transfer-ticket" wire:model.self="showTransfer" title="Transferir ticket" class="w-full max-w-lg">
        <form wire:submit="transfer" class="space-y-5">
            <x-modern.select name="transferSector" label="Setor" placeholder="Selecione" :options="$sectors->pluck('nome', 'id')->all()" wire:model.live="transferSector" />
            <x-modern.select name="transferCategory" label="Categoria" placeholder="Selecione" :options="$transferCategories->pluck('nome', 'id')->all()" wire:model="transferCategory" />
            <x-modern.select name="transferAnalyst" label="Analista" placeholder="Sem analista" :options="$transferAnalysts->pluck('name', 'id')->all()" wire:model="transferAnalyst" />
            <div class="flex justify-end gap-2"><x-modern.button type="button" variant="outline" autofocus x-on:click="$flux.modal('transfer-ticket').close()">Cancelar</x-modern.button><x-modern.button type="submit" variant="filled" color="purple">Transferir</x-modern.button></div>
        </form>
    </x-modern.modal>

    <x-modern.modal name="finalize-ticket" wire:model.self="showFinalize" title="Finalizar ticket" class="w-full max-w-lg">
        <form wire:submit="finalize" class="space-y-5">
            <x-modern.textarea name="finalDescription" label="Relato final" rows="4" wire:model="finalDescription" />
            <div class="grid gap-5 sm:grid-cols-2"><x-modern.input name="finalHours" type="number" label="Horas" min="0" wire:model="finalHours" /><x-modern.input name="finalMinutes" type="number" label="Minutos" min="0" max="59" wire:model="finalMinutes" /></div>
            <p class="text-xs text-zinc-500">A sugestão foi calculada com base nas interações públicas e no SLA da categoria.</p>
            <div class="flex justify-end gap-2"><x-modern.button type="button" variant="outline" autofocus x-on:click="$flux.modal('finalize-ticket').close()">Cancelar</x-modern.button><x-modern.button type="submit" variant="filled" color="blue">Finalizar</x-modern.button></div>
        </form>
    </x-modern.modal>

    @if($administrator)
        <x-modern.modal name="delete-ticket-detail" wire:model.self="showDelete" title="Excluir ticket" class="w-full max-w-md">
            <form wire:submit="delete" class="space-y-5">
                <p class="text-sm">Deseja excluir o ticket <strong>#{{ $ticket->id }} — {{ $ticket->assunto }}</strong>? Mensagens e anexos também serão removidos.</p>
                <div class="flex justify-end gap-2"><x-modern.button type="button" variant="outline" autofocus x-on:click="$flux.modal('delete-ticket-detail').close()">Cancelar</x-modern.button><x-modern.button type="submit" variant="filled" color="red">Confirmar exclusão</x-modern.button></div>
            </form>
        </x-modern.modal>
    @endif
</div>
