@props(['percent' => 0])
@php
    $value = max(0, (float) $percent);
    $width = min(100, $value);
    $tone = $value >= 100 ? 'danger' : ($value >= 50 ? 'warning' : 'success');
    $label = $value >= 100 ? 'SLA excedido' : ($value >= 50 ? 'SLA em atenção' : 'SLA dentro do prazo');
@endphp
<div {{ $attributes->merge(['class' => 'd-flex align-items-center']) }} aria-label="{{ $label }}: {{ floor($value) }}%">
    <i class="fas {{ $value >= 100 ? 'fa-exclamation-circle text-danger' : 'fa-clock text-' . $tone }} mr-2" aria-hidden="true"></i>
    <div class="progress flex-grow-1" style="height: .625rem;" role="progressbar" aria-valuenow="{{ floor($value) }}" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar bg-{{ $tone }}" style="width: {{ $width }}%"></div></div>
    <strong class="ml-2">{{ floor($value) }}%</strong>
</div>
