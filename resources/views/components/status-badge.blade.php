@props(['status'])
@php
    $normalized = strtolower($status);
    $styles = [
        'aberto' => ['success', 'fa-circle'],
        'pendente cliente' => ['primary', 'fa-user-clock'],
        'pendente analista' => ['warning', 'fa-clock'],
        'fechado' => ['secondary', 'fa-check-circle'],
    ];
    $style = $styles[$normalized] ?? ['info', 'fa-info-circle'];
@endphp
<span {{ $attributes->merge(['class' => 'badge badge-' . $style[0]]) }}><i class="fas {{ $style[1] }} mr-1" aria-hidden="true"></i>{{ ucfirst($status) }}</span>
