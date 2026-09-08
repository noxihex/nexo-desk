<x-layouts.auth title="Criar conta" description="O cadastro de contas é realizado pelo administrador.">
    <div class="space-y-5">
        <x-modern.alert>O cadastro público não está disponível. Entre em contato com o administrador.</x-modern.alert>
        <fieldset disabled class="space-y-5" aria-label="Cadastro indisponível">
            <x-modern.input name="name" label="Nome" autocomplete="name" />
            <x-modern.input name="email" label="E-mail" type="email" autocomplete="email" />
            <x-modern.input name="password" label="Senha" type="password" autocomplete="new-password" />
            <x-modern.input name="password_confirmation" label="Confirme a senha" type="password" autocomplete="new-password" />
            <x-modern.button disabled class="w-full">Criar conta</x-modern.button>
        </fieldset>
    </div>
</x-layouts.auth>
