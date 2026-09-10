<form wire:submit="save" class="space-y-6">
    <x-modern.card title="{{ $recordId ? 'Dados do ticket' : 'Novo ticket' }}" description="Preencha os dados de atendimento e encaminhamento.">
        <div class="space-y-5">
            <x-modern.input name="assunto" label="Assunto" placeholder="Resuma a solicitação" autocomplete="off" wire:model="assunto" />
            <x-modern.textarea name="descricao" label="Descrição" placeholder="Descreva a solicitação com os detalhes necessários" rows="6" wire:model="descricao" />

            <div class="grid gap-5 md:grid-cols-2">
                <x-modern.select name="cliente_id" label="Contato" placeholder="Selecione um contato" :options="$clients->mapWithKeys(fn ($client) => [$client->id => $client->name.' ('.($client->empresa?->nome ?? 'Sem empresa').')'])->all()" wire:model.live="cliente_id" searchable />
                <x-modern.select name="empresa_id" label="Empresa" placeholder="Selecione uma empresa" :options="$companies->pluck('nome', 'id')->all()" wire:model="empresa_id" searchable />
                <x-modern.select name="setor_id" label="Setor" placeholder="Selecione um setor" :options="$sectors->pluck('nome', 'id')->all()" wire:model.live="setor_id" searchable />
                <x-modern.select name="categoria_id" label="Categoria" placeholder="Selecione uma categoria" :options="$categories->pluck('nome', 'id')->all()" wire:model="categoria_id" :disabled="!$setor_id" searchable />
                <x-modern.select name="atribuido_ao_analista_id" label="Analista responsável" placeholder="Sem analista" :options="$analysts->pluck('name', 'id')->all()" wire:model="atribuido_ao_analista_id" :disabled="!$setor_id" searchable />
                @if($recordId)
                    <x-modern.select name="status" label="Status" :options="['aberto' => 'Aberto', 'pendente cliente' => 'Pendente cliente', 'pendente analista' => 'Pendente analista', 'fechado' => 'Fechado']" wire:model="status" />
                @endif
            </div>

            @if(!$recordId)
                <x-modern.file-upload model="newAnexos" :files="$anexos" remove="removeAnexo" error-name="anexos" />
            @endif
        </div>
    </x-modern.card>

    <div class="flex flex-wrap gap-2">
        <x-modern.button type="submit" variant="filled" color="green" icon="check" wire:loading.attr="disabled">
            {{ $recordId ? 'Salvar alterações' : 'Criar ticket' }}
        </x-modern.button>
        <x-modern.button :href="$returnUrl" variant="outline" icon="arrow-left">Cancelar</x-modern.button>
    </div>
</form>
