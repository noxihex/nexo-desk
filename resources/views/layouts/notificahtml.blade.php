@section('content_top_nav_right')
<!-- Dropdown de Notificações -->
<li class="nav-item dropdown">
    <a id="notificationDropdown" class="nav-link notification-icon" href="#" role="button" data-toggle="dropdown" aria-expanded="false">
        <i class="far fa-bell"></i>
        <span id="notificationBadge" class="notification-badge" style="display: none;">0</span>
    </a>
    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="notificationDropdown" style="min-width: 300px;">
        <div class="dropdown-header">Notificações</div>
        <div id="notificationList" style="max-height: 400px; overflow-y: auto;">
            <!-- Notificações serão carregadas aqui via JS -->
            <p class="text-center text-muted">Carregando notificações...</p>
        </div>
        <div class="dropdown-divider"></div>
        <a href="#" id="markAllAsRead" class="dropdown-item text-center text-primary" style="cursor: pointer;">
            Marcar todas como lidas
        </a>
    </div>
</li>
@endsection
