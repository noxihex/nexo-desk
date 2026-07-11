@props(['label' => 'Ações'])
<div {{ $attributes->merge(['class' => 'dropdown']) }}>
    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-ellipsis-v mr-1" aria-hidden="true"></i>{{ $label }}</button>
    <div class="dropdown-menu dropdown-menu-right">{{ $slot }}</div>
</div>
