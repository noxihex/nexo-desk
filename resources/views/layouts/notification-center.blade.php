<li class="nav-item dropdown" id="notification-center"
    data-index-url="{{ route('notifications.index') }}"
    data-read-url="{{ route('notifications.read', ['id' => '__ID__']) }}"
    data-read-all-url="{{ route('notifications.read-all') }}">
    <a class="nav-link" data-toggle="dropdown" href="#" aria-label="Notificações" aria-expanded="false">
        <i class="far fa-bell"></i>
        <span class="badge badge-danger navbar-badge d-none" data-notification-count>0</span>
    </a>
    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
        <span class="dropdown-header" data-notification-header>Notificações</span>
        <div data-notification-list>
            <span class="dropdown-item text-muted">Carregando...</span>
        </div>
        <div class="dropdown-divider"></div>
        <button type="button" class="dropdown-item dropdown-footer" data-notification-read-all>Marcar todas como lidas</button>
    </div>
</li>

@once
    @push('js')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const center = document.getElementById('notification-center');
                if (!center) return;
                const list = center.querySelector('[data-notification-list]');
                const count = center.querySelector('[data-notification-count]');
                const csrf = document.querySelector('meta[name="csrf-token"]');
                const request = (url) => fetch(url, {
                    method: 'POST',
                    keepalive: true,
                    headers: {'X-CSRF-TOKEN': csrf ? csrf.content : '', 'Accept': 'application/json'}
                });
                const escapeHtml = (value) => {
                    const node = document.createElement('span');
                    node.textContent = value || '';
                    return node.innerHTML;
                };
                const load = () => fetch(center.dataset.indexUrl, {headers: {'Accept': 'application/json'}})
                    .then(response => response.ok ? response.json() : Promise.reject())
                    .then(data => {
                        count.textContent = data.unread_count;
                        count.classList.toggle('d-none', data.unread_count === 0);
                        list.innerHTML = data.notifications.length ? data.notifications.map(item => `
                            <a href="${encodeURI(item.url || '#')}" class="dropdown-item ${item.read_at ? '' : 'font-weight-bold'}" data-notification-id="${item.id}">
                                <div>${escapeHtml(item.title)}</div>
                                <small class="text-muted text-wrap">${escapeHtml(item.message)}</small>
                            </a><div class="dropdown-divider"></div>`).join('') : '<span class="dropdown-item text-muted">Nenhuma notificação.</span>';
                        list.querySelectorAll('[data-notification-id]').forEach(link => link.addEventListener('click', () => {
                            request(center.dataset.readUrl.replace('__ID__', link.dataset.notificationId));
                        }));
                    })
                    .catch(() => { list.innerHTML = '<span class="dropdown-item text-danger">Não foi possível carregar.</span>'; });
                center.querySelector('[data-notification-read-all]').addEventListener('click', () => request(center.dataset.readAllUrl).then(load));
                load();
                window.setInterval(load, 60000);
            });
        </script>
    @endpush
@endonce
