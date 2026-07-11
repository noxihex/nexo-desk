@props(['icon' => 'fa-inbox', 'title' => 'Nenhum resultado encontrado'])
<div {{ $attributes->merge(['class' => 'btx-empty-state']) }} role="status">
    <i class="fas {{ $icon }}" aria-hidden="true"></i>
    <strong>{{ $title }}</strong>
    @if(trim($slot))<div class="mt-2">{{ $slot }}</div>@endif
</div>
