<form wire:submit="save" class="space-y-6">
    <x-modern.card title="Novo ticket" description="Descreva sua solicitação para nossa equipe.">
        <div class="space-y-5">
            <x-modern.input name="assunto" label="Assunto" placeholder="Resuma sua solicitação" autocomplete="off" wire:model="assunto" />
            <x-modern.textarea name="descricao" label="Descrição" placeholder="Descreva sua solicitação com os detalhes necessários" rows="6" wire:model="descricao" />

            <div class="grid gap-5 md:grid-cols-2">
                <x-modern.input label="Contato" :value="$user->name" disabled />
                <x-modern.input label="Empresa" :value="$user->empresa?->nome ?? 'Sem empresa'" disabled />
            </div>

            <x-modern.select name="setor_id" label="Setor" placeholder="Selecione um setor" :options="$sectors->pluck('nome', 'id')->all()" wire:model="setor_id" searchable />
            <x-modern.file-upload model="newAnexos" :files="$anexos" remove="removeAnexo" error-name="anexos" />
        </div>
    </x-modern.card>

    <div class="flex flex-wrap justify-end gap-2">
        <x-modern.button :href="$returnUrl" variant="outline" icon="arrow-left">Cancelar</x-modern.button>
        <x-modern.button type="submit" variant="filled" color="green" icon="check" wire:loading.attr="disabled">Criar ticket</x-modern.button>
    </div>
</form>
