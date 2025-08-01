<script>
    document.addEventListener('DOMContentLoaded', function () {
        const notificationBadge = document.getElementById('notificationBadge'); // Bolinha do ícone
        const notificationList = document.getElementById('notificationList'); // Lista de notificações
        const markAllAsReadButton = document.getElementById('markAllAsRead'); // Botão "Marcar todas como lidas"

        /**
         * Carrega as notificações do servidor.
         */
        function carregarNotificacoes() {
            console.log('Verificando notificações...');
            fetch('{{ route('notificacoes.index') }}')
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Erro na resposta do servidor: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Notificações carregadas:', data);
                    atualizarLista(data); // Atualiza a lista no dropdown
                    atualizarBadge(data); // Atualiza o badge
                    verificarNovasNotificacoes(data); // Detecta novas notificações
                })
                .catch(error => console.error('Erro ao carregar notificações:', error));
        }

        /**
         * Atualiza a lista de notificações no dropdown.
         * @param {Array} notificacoes - Lista de notificações retornadas pelo servidor.
         */
        function atualizarLista(notificacoes) {
            notificationList.innerHTML = ''; // Limpa a lista

            if (notificacoes.length === 0) {
                notificationList.innerHTML = '<p class="text-center text-muted">Nenhuma notificação encontrada.</p>';
            } else {
                notificacoes.forEach(notificacao => {
                    notificationList.innerHTML += `
                        <div class="notification-item ${notificacao.lida ? 'read' : 'unread'}">
                            <div class="notification-text">
                                <strong>${notificacao.titulo}</strong>
                                <p class="mb-0 text-muted">${notificacao.mensagem}</p>
                                <small class="text-muted">${new Date(notificacao.created_at).toLocaleString()}</small>
                            </div>
                            <div class="notification-actions">
                                <button class="mark-as-read" data-id="${notificacao.id}">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    `;
                });

                // Adiciona evento aos botões de "marcar como lida"
                document.querySelectorAll('.mark-as-read').forEach(button => {
                    button.addEventListener('click', function (e) {
                        e.stopPropagation();
                        const notificacaoId = this.getAttribute('data-id');
                        marcarComoLida(notificacaoId);
                    });
                });
            }
        }

        /**
         * Atualiza o badge com o número de notificações não lidas.
         * @param {Array} notificacoes - Lista de notificações retornadas pelo servidor.
         */
        function atualizarBadge(notificacoes) {
            const unreadCount = notificacoes.filter(notificacao => !notificacao.lida).length; // Contagem de não lidas
            notificationBadge.innerText = unreadCount;
            notificationBadge.style.display = unreadCount > 0 ? 'flex' : 'none';

            // Adiciona animação de pulso se houver notificações não lidas
            if (unreadCount > 0) {
                notificationBadge.classList.add('animate');
            } else {
                notificationBadge.classList.remove('animate');
            }
        }

        /**
         * Marca uma notificação como lida.
         * @param {number} id - ID da notificação a ser marcada como lida.
         */
        function marcarComoLida(id) {
            fetch('{{ route('notificacoes.marcarComoLida', ':id') }}'.replace(':id', id), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Erro ao marcar notificação como lida: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log(`Notificação #${id} marcada como lida.`);
                    carregarNotificacoes(); // Recarrega as notificações
                })
                .catch(error => console.error('Erro ao marcar notificação como lida:', error));
        }

        /**
         * Marca todas as notificações como lidas.
         */
        function marcarTodasComoLidas() {
            fetch('{{ route('notificacoes.marcarComoLida', ':id') }}'.replace(':id', 'all'), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Erro ao marcar notificações como lidas: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Todas as notificações foram marcadas como lidas.');
                    carregarNotificacoes();
                })
                .catch(error => console.error('Erro ao marcar todas como lidas:', error));
        }

        /**
         * Detecta novas notificações, toca um som e exibe uma notificação no navegador.
         * @param {Array} notificacoes - Lista atual de notificações.
         */
        function verificarNovasNotificacoes(notificacoes) {
            const notificacoesAntigasIds = JSON.parse(localStorage.getItem('notificacoesAntigas')) || [];
            const novasNotificacoes = notificacoes.filter(
                notificacao => !notificacoesAntigasIds.includes(notificacao.id)
            );

            if (novasNotificacoes.length > 0) {
                console.log('Novas notificações detectadas:', novasNotificacoes);

                // Toca o som de notificação
                const audio = new Audio('/sounds/notification.mp3');
                audio.play();

                // Exibe uma notificação no navegador para cada nova notificação
                novasNotificacoes.forEach(notificacao => exibirNotificacaoNavegador(notificacao));

                // Atualiza o localStorage com as novas IDs
                const novasIds = notificacoes.map(notificacao => notificacao.id);
                localStorage.setItem('notificacoesAntigas', JSON.stringify(novasIds));
            }
        }

        /**
         * Exibe uma notificação no navegador.
         * @param {Object} notificacao - Dados da notificação.
         */
        function exibirNotificacaoNavegador(notificacao) {
            if (!("Notification" in window)) {
                console.warn("Este navegador não suporta notificações do sistema.");
                return;
            }

            if (Notification.permission === "default") {
                Notification.requestPermission().then(permission => {
                    if (permission === "granted") {
                        exibirNotificacao(notificacao);
                    }
                });
            } else if (Notification.permission === "granted") {
                exibirNotificacao(notificacao);
            }
        }

        /**
         * Envia a notificação para o navegador.
         * @param {Object} notificacao - Dados da notificação.
         */
        function exibirNotificacao(notificacao) {
            const notification = new Notification(notificacao.titulo, {
                body: notificacao.mensagem,
                icon: '/path/to/icon.png', // Substitua pelo caminho do ícone
            });

            notification.onclick = () => {
                window.focus();
            };
        }

        setInterval(carregarNotificacoes, 20000); // 20 segundos

        // Carrega notificações ao iniciar a página
        carregarNotificacoes();

        // Evento para marcar todas como lidas
        markAllAsReadButton.addEventListener('click', marcarTodasComoLidas);
    });
</script>
