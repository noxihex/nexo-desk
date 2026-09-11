<x-modern.card class="max-w-3xl">
    <form wire:submit="save" class="space-y-6">
        <x-modern.input name="nome" label="Nome da categoria" wire:model="nome" required autofocus />
        <x-modern.select name="prioridade" label="Prioridade" wire:model="prioridade" :options="['Normal' => 'Normal', 'Alta' => 'Alta', 'Baixa' => 'Baixa']" />
        <div class="grid gap-6 md:grid-cols-2">
            <flux:field>
                <flux:label class="mb-0!">SLA total (min)</flux:label>
                <flux:tooltip class="mb-3 ms-1 align-middle">
                    <button type="button" class="inline-flex size-4 items-center justify-center rounded-full border border-zinc-400 text-xs font-semibold leading-none text-zinc-500 hover:border-zinc-500 hover:text-zinc-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 dark:border-zinc-500 dark:text-zinc-400 dark:hover:border-zinc-400 dark:hover:text-zinc-200" aria-label="Ajuda sobre o SLA total">?</button>
                    <flux:tooltip.content class="max-w-sm whitespace-normal">Prazo máximo, em minutos, entre a abertura e a conclusão do ticket. É usado para calcular o percentual de SLA e indicar atrasos.</flux:tooltip.content>
                </flux:tooltip>
                <flux:input name="slatotal" type="number" min="1" step="1" required wire:model="slatotal" />
                <flux:error name="slatotal" />
            </flux:field>
            <flux:field>
                <flux:label class="mb-0!">SLA de atualização (min)</flux:label>
                <flux:tooltip class="mb-3 ms-1 align-middle">
                    <button type="button" class="inline-flex size-4 items-center justify-center rounded-full border border-zinc-400 text-xs font-semibold leading-none text-zinc-500 hover:border-zinc-500 hover:text-zinc-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 dark:border-zinc-500 dark:text-zinc-400 dark:hover:border-zinc-400 dark:hover:text-zinc-200" aria-label="Ajuda sobre o SLA de atualização">?</button>
                    <flux:tooltip.content class="max-w-sm whitespace-normal">Intervalo máximo, em minutos, sem uma atualização do analista. Ao atingir esse tempo, o ticket requer atenção. Para sugerir as horas gastas, o sistema soma os intervalos entre a abertura, cada mensagem e a finalização, limitando cada intervalo a este valor.</flux:tooltip.content>
                </flux:tooltip>
                <flux:input name="slaupdate" type="number" min="1" step="1" required wire:model="slaupdate" />
                <flux:error name="slaupdate" />
            </flux:field>
        </div>
        <fieldset aria-describedby="setores-description">
            <legend class="text-sm font-medium">Setores</legend>
            <p id="setores-description" class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Deixe vazio para manter a categoria indisponível nos tickets.</p>
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                @forelse($setores as $setor)
                    <div wire:key="setor-{{ $setor->id }}">
                        <x-modern.checkbox :label="$setor->nome" name="setor_ids[]" :value="$setor->id" wire:model="setor_ids" />
                    </div>
                @empty
                    <p class="text-sm text-zinc-500">Nenhum setor cadastrado.</p>
                @endforelse
            </div>
            @foreach($errors->get('setor_ids') + $errors->get('setor_ids.*') as $messages)
                @foreach((array) $messages as $message)
                    <p role="alert" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @endforeach
            @endforeach
        </fieldset>
        <div class="flex flex-wrap gap-3">
            <x-modern.button type="submit" variant="filled" color="green" wire:loading.attr="disabled">{{ $recordId ? 'Salvar alterações' : 'Criar categoria' }}</x-modern.button>
            <x-modern.button :href="route('categorias.index')" variant="outline">Cancelar</x-modern.button>
        </div>
    </form>
</x-modern.card>
