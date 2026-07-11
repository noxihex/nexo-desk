@props(['title', 'breadcrumbs' => []])
<div {{ $attributes->merge(['class' => 'btx-page-header']) }}>
    <div><h1 class="btx-page-header__title">{{ $title }}</h1>
        @if($breadcrumbs)<nav class="btx-breadcrumb" aria-label="Navegação estrutural">@foreach($breadcrumbs as $breadcrumb)<span>{{ $breadcrumb }}</span>@if(!$loop->last)<i class="fas fa-angle-right btx-breadcrumb__separator" aria-hidden="true"></i>@endif @endforeach</nav>@endif
    </div>
    @if(trim($slot))<div class="btx-page-header__actions">{{ $slot }}</div>@endif
</div>
