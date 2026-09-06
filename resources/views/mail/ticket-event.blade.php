@component('mail::message')
# {{ $payload['title'] }}

{{ $payload['message'] }}

@if($payload['summary'])
@component('mail::panel')
{{ $payload['summary'] }}
@endcomponent
@endif

@component('mail::button', ['url' => $payload['url']])
Acessar ticket
@endcomponent

@if($payload['reply_enabled'] ?? false)
Você pode responder a este e-mail para adicionar uma mensagem ao ticket.
@endif

Atenciosamente,  
{{ config('app.name') }}
@endcomponent
