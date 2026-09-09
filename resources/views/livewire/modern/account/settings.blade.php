<div class="space-y-6">
    <div class="grid items-start gap-6 xl:grid-cols-2">
        <x-modern.card>
            <form wire:submit="saveProfile" class="space-y-6">
                <div>
                    <h2 class="text-lg font-semibold">Informações do perfil</h2>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Atualize seu nome e o e-mail usado para acessar sua conta.</p>
                </div>
                <x-modern.input name="name" label="Nome completo" wire:model="name" autocomplete="name" maxlength="255" required />
                <x-modern.input name="email" label="E-mail" type="email" wire:model="email" autocomplete="email" maxlength="255" required />
                <x-modern.button type="submit" variant="filled" color="green" wire:loading.attr="disabled" wire:target="saveProfile">Salvar perfil</x-modern.button>
            </form>
        </x-modern.card>
        <x-modern.card>
            <form wire:submit="savePassword" class="space-y-6">
                <h2 class="text-lg font-semibold">Alterar senha</h2>
                @if($passwordSuccess)<x-modern.alert variant="success">{{ $passwordSuccess }}</x-modern.alert>@endif
                <x-modern.input name="current_password" label="Senha atual" type="password" wire:model="current_password" autocomplete="current-password" viewable required />
                <div class="space-y-3">
                    <p id="account-password-guidance" class="text-sm text-zinc-500 dark:text-zinc-400">Use pelo menos 8 caracteres na nova senha.</p>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-modern.input name="password" label="Nova senha" type="password" wire:model="password" autocomplete="new-password" aria-describedby="account-password-guidance" viewable required />
                        <x-modern.input name="password_confirmation" label="Confirmar nova senha" type="password" wire:model="password_confirmation" autocomplete="new-password" aria-describedby="account-password-guidance" viewable required />
                    </div>
                </div>
                <x-modern.button type="submit" variant="filled" color="green" wire:loading.attr="disabled" wire:target="savePassword">Alterar senha</x-modern.button>
            </form>
        </x-modern.card>
    </div>
    <x-modern.card>
        <form wire:submit="savePreferences" class="space-y-6">
            <div>
                <h2 class="text-lg font-semibold">Preferências de notificação</h2>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Escolha onde deseja receber cada tipo de aviso.</p>
            </div>
            @if($preferencesSuccess)<x-modern.alert variant="success">{{ $preferencesSuccess }}</x-modern.alert>@endif
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach(['novo_ticket' => 'Novo ticket', 'nova_mensagem' => 'Nova mensagem', 'sla' => 'Alertas de SLA', 'ticket_resolvido' => 'Ticket resolvido'] as $event => $label)
                    <fieldset class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                        <legend class="px-1 text-sm font-medium">{{ $label }}</legend>
                        <div class="flex flex-wrap gap-5">
                            @foreach(['database' => 'Central interna', 'mail' => 'E-mail'] as $channel => $channelLabel)
                                @php($field = $event.'_'.$channel)
                                <x-modern.checkbox :name="$field" :label="$channelLabel" wire:model="preferences.{{ $field }}" :aria-label="$label.' por '.mb_strtolower($channelLabel)" />
                                @error($field)<x-modern.alert variant="danger">{{ $message }}</x-modern.alert>@enderror
                            @endforeach
                        </div>
                    </fieldset>
                @endforeach
            </div>
            <x-modern.button type="submit" variant="filled" color="green" wire:loading.attr="disabled" wire:target="savePreferences">Salvar preferências</x-modern.button>
        </form>
    </x-modern.card>
</div>
