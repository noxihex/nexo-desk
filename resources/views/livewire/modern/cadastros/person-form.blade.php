<x-modern.card class="max-w-3xl">
    <form wire:submit="save" class="space-y-6">
        @if($contact)
            <x-modern.alert>Empresa: {{ $empresa?->nome ?? 'Sem empresa' }}. Este contato acessará o portal do cliente para acompanhar os tickets da empresa.</x-modern.alert>
            <input type="hidden" name="empresa_id" value="{{ $empresaId }}">
            @error('empresa_id')
                <x-modern.alert variant="danger">{{ $message }}</x-modern.alert>
            @enderror
        @endif
        <x-modern.input name="name" label="Nome" wire:model="name" autocomplete="name" maxlength="255" required autofocus />
        <x-modern.input name="email" label="E-mail" type="email" wire:model="email" autocomplete="email" maxlength="255" required />
        <div class="space-y-3">
            <p id="password-guidance" class="text-sm text-zinc-500 dark:text-zinc-400">{{ $recordId ? 'Deixe em branco para manter a senha atual.' : 'Use pelo menos 8 caracteres.' }}</p>
            <div class="grid gap-5 sm:grid-cols-2">
                <x-modern.input name="password" label="Senha" type="password" wire:model="password" autocomplete="new-password" aria-describedby="password-guidance" viewable :required="!$recordId" />
                <x-modern.input name="password_confirmation" label="Confirmar senha" type="password" wire:model="password_confirmation" autocomplete="new-password" aria-describedby="password-guidance" viewable :required="!$recordId" />
            </div>
        </div>
        @if(!$contact)
            <x-modern.select name="role" label="Perfil" wire:model.live="role" required>
                @foreach($roles as $option)
                    <x-modern.select.option :value="$option">{{ ucfirst($option) }}</x-modern.select.option>
                @endforeach
            </x-modern.select>
            <x-modern.select name="setor_id" label="Setor" wire:model="setor_id" :options="['' => 'Sem setor'] + $setores" />
            @if($role === 'analista')
                <x-modern.checkbox name="pode_ver_tickets_outros_setores" wire:model="pode_ver_tickets_outros_setores" label="Permitir visualizar tickets de outros setores" />
            @endif
        @endif
        <div class="flex flex-wrap gap-3">
            <x-modern.button type="submit" variant="filled" color="green" wire:loading.attr="disabled">{{ $recordId ? 'Salvar alterações' : ($contact ? 'Criar contato' : 'Criar usuário') }}</x-modern.button>
            <x-modern.button :href="$returnUrl" variant="outline">Cancelar</x-modern.button>
        </div>
    </form>
</x-modern.card>
