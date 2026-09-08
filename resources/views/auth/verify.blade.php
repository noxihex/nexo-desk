<x-layouts.auth title="Verificar e-mail" description="A confirmação do endereço de e-mail ainda não está disponível.">
    <div class="space-y-5">
        @if(session('resent'))
            <x-modern.alert variant="success">Um novo link de verificação foi enviado para seu e-mail.</x-modern.alert>
        @endif
        <x-modern.alert>Quando a verificação estiver habilitada, você poderá confirmar seu endereço pelo link recebido por e-mail.</x-modern.alert>
        <x-modern.button disabled class="w-full">Reenviar link de verificação</x-modern.button>
    </div>
</x-layouts.auth>
