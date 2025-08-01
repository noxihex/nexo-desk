<style>
    /* Estilo para o ícone de notificações */
    .notification-icon {
        position: relative;
        cursor: pointer;
        font-size: 18px;
    }

    /* Badge para número de notificações */
    .notification-badge {
        position: absolute;
        top: -2px;
        right: -2px;
        background: #dc3545; /* Vermelho elegante */
        color: white;
        font-size: 12px;
        padding: 4px 6px;
        border-radius: 50%;
        font-weight: bold;
        text-align: center;
        line-height: 1;
        min-width: 20px;
        height: 20px;
        display: flex;
        justify-content: center;
        align-items: center;
        animation: pulse 1.5s infinite ease-in-out;
    }

    /* Efeito de pulso */
    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.2);
        }
    }

    /* Estilo para cada item de notificação */
    .notification-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px;
        border-bottom: 1px solid #e9ecef;
    }

    .notification-item:last-child {
        border-bottom: none;
    }

    .notification-item.unread {
        background-color: #f8f9fa; /* Cor mais escura para não lidas */
        font-weight: bold;
    }

    .notification-item.read {
        background-color: #fff; /* Fundo branco para lidas */
        color: #6c757d; /* Texto mais claro */
    }

    .notification-item:hover {
        background-color: #e9ecef; /* Hover para todas */
        cursor: pointer;
    }

    .notification-text {
        flex-grow: 1;
    }

    .notification-actions {
        margin-left: 10px;
    }

    .notification-actions .mark-as-read {
        background: none;
        border: none;
        color: #007bff;
        font-size: 1rem;
        padding: 0;
    }

    .notification-actions .mark-as-read:hover {
        text-decoration: none;
        color: #0056b3;
    }
</style>
