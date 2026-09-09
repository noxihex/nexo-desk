<div class="space-y-8">
<x-modern.card>
    <form wire:submit="save" class="space-y-6">
        <div class="grid gap-5 sm:grid-cols-2">
            <x-modern.input name="nome" label="Nome" wire:model="nome" maxlength="255" required />
            <x-modern.input name="razao_social" label="Razão social" wire:model="razao_social" maxlength="255" />
            <x-modern.input name="cnpj" label="CPF / CNPJ" inputmode="numeric" x-mask:dynamic="$input.replace(/\D/g, '').length &gt; 11 ? '99.999.999/9999-99' : '999.999.999-99'" wire:model="cnpj" maxlength="18" required />
            <x-modern.input name="endereco" label="Endereço" wire:model="endereco" maxlength="255" required />
            <x-modern.input name="bairro" label="Bairro" wire:model="bairro" maxlength="255" required />
            <x-modern.input name="cidade" label="Cidade" wire:model="cidade" maxlength="255" required />
            <x-modern.select name="estado" label="Estado" wire:model="estado" required>
                <x-modern.select.option value="">Selecione o estado</x-modern.select.option>
                <x-modern.select.option value="AC">Acre</x-modern.select.option>
                <x-modern.select.option value="AL">Alagoas</x-modern.select.option>
                <x-modern.select.option value="AP">Amapá</x-modern.select.option>
                <x-modern.select.option value="AM">Amazonas</x-modern.select.option>
                <x-modern.select.option value="BA">Bahia</x-modern.select.option>
                <x-modern.select.option value="CE">Ceará</x-modern.select.option>
                <x-modern.select.option value="DF">Distrito Federal</x-modern.select.option>
                <x-modern.select.option value="ES">Espírito Santo</x-modern.select.option>
                <x-modern.select.option value="GO">Goiás</x-modern.select.option>
                <x-modern.select.option value="MA">Maranhão</x-modern.select.option>
                <x-modern.select.option value="MT">Mato Grosso</x-modern.select.option>
                <x-modern.select.option value="MS">Mato Grosso do Sul</x-modern.select.option>
                <x-modern.select.option value="MG">Minas Gerais</x-modern.select.option>
                <x-modern.select.option value="PA">Pará</x-modern.select.option>
                <x-modern.select.option value="PB">Paraíba</x-modern.select.option>
                <x-modern.select.option value="PR">Paraná</x-modern.select.option>
                <x-modern.select.option value="PE">Pernambuco</x-modern.select.option>
                <x-modern.select.option value="PI">Piauí</x-modern.select.option>
                <x-modern.select.option value="RJ">Rio de Janeiro</x-modern.select.option>
                <x-modern.select.option value="RN">Rio Grande do Norte</x-modern.select.option>
                <x-modern.select.option value="RS">Rio Grande do Sul</x-modern.select.option>
                <x-modern.select.option value="RO">Rondônia</x-modern.select.option>
                <x-modern.select.option value="RR">Roraima</x-modern.select.option>
                <x-modern.select.option value="SC">Santa Catarina</x-modern.select.option>
                <x-modern.select.option value="SP">São Paulo</x-modern.select.option>
                <x-modern.select.option value="SE">Sergipe</x-modern.select.option>
                <x-modern.select.option value="TO">Tocantins</x-modern.select.option>
            </x-modern.select>
            <x-modern.input name="horas_contratadas" label="Horas contratadas" wire:model="horas_contratadas" type="number" step="1" required />
        </div>
        <div class="flex flex-wrap gap-3">
            <x-modern.button type="submit" variant="filled" color="green" wire:loading.attr="disabled">{{ $recordId ? 'Salvar alterações' : 'Criar empresa' }}</x-modern.button>
            <x-modern.button :href="route('empresas.index')" variant="outline">Cancelar</x-modern.button>
        </div>
    </form>
</x-modern.card>
@if($recordId)
    <section aria-label="Contatos da empresa" class="space-y-4">
        <h2 class="text-xl font-semibold">Contatos da empresa</h2>
        <livewire:modern.cadastros.contato-index :empresa-id="$recordId" />
    </section>
@endif
</div>
