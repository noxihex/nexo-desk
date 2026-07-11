@props(['cancelUrl' => null, 'submitLabel' => 'Salvar', 'submitIcon' => 'fa-save'])
<div {{ $attributes->merge(['class' => 'btx-form-actions']) }}>
    <button type="submit" class="btn btn-success"><i class="fas {{ $submitIcon }} mr-1" aria-hidden="true"></i>{{ $submitLabel }}</button>
    @if($cancelUrl)<a href="{{ $cancelUrl }}" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1" aria-hidden="true"></i>Voltar</a>@endif
    {{ $slot }}
</div>
