# Notificações e integração com e-mail

## Recursos e segurança

O Nexo Desk envia notificações de novo ticket, nova mensagem, SLA e resolução pela central interna e por e-mail. Cada usuário controla evento e canal em **Minha Conta**. As preferências começam ativas, mas os recursos globais ficam desligados enquanto as flags não forem configuradas.

O recebimento usa `POST /api/mailgun/inbound`. A rota valida a assinatura HMAC do Mailgun, a idade do timestamp e a unicidade do token. A identidade é sempre obtida de `sender` (envelope SMTP), nunca do cabeçalho visual `From`.

Administradores devem cadastrar em **Administração → Caixas de e-mail** cada endereço que pode abrir tickets e seu setor padrão. Configure no Mailgun uma route que encaminhe esses endereços e `reply+*@MAILGUN_INBOUND_DOMAIN` para o endpoint HTTPS.

## Configuração

Configure SMTP normalmente pelas variáveis `MAIL_*` e acrescente:

```dotenv
QUEUE_CONNECTION=database
TICKET_NOTIFICATIONS_OUTBOUND_ENABLED=false
TICKET_NOTIFICATIONS_SLA_ENABLED=false
TICKET_EMAIL_INBOUND_ENABLED=false
MAILGUN_WEBHOOK_SIGNING_KEY=
MAILGUN_INBOUND_DOMAIN=inbound.example.com
MAILGUN_REPLY_LOCAL_PART=reply
MAILGUN_WEBHOOK_TIMESTAMP_TOLERANCE=900
MAILGUN_REPLY_TOKEN_LIFETIME_DAYS=180
```

`MAILGUN_WEBHOOK_SIGNING_KEY` é a chave de assinatura de webhooks, não a senha SMTP nem a API key. Nunca versione o valor real.

## Implantação

1. Faça backup e publique o código com as três flags desligadas.
2. Execute `php artisan migrate --force` e `php artisan config:clear`.
3. Inicie um worker persistente: `php artisan queue:work database --queue=notifications --tries=5`.
4. Configure o cron do sistema para executar `php artisan schedule:run` a cada minuto.
5. Configure SMTP, domínio de entrada e route HTTPS no Mailgun.
6. Cadastre as caixas no painel e faça testes de criação, resposta, resolução e SLA com contas não produtivas.
7. Ative juntas as três flags e execute novamente `php artisan config:clear`.

Cada flag pode ser desligada isoladamente em caso de incidente. Jobs com falha ficam em `failed_jobs`; o ledger `notification_deliveries` registra tentativas e erros de e-mail. Entradas aceitas ou rejeitadas ficam em `inbound_emails`, sem armazenar o corpo bruto do webhook.

## Regras de entrada

- Somente clientes ativos podem abrir tickets por e-mail.
- Somente o cliente do ticket e o analista atualmente atribuído podem responder pelo endereço opaco de `Reply-To`.
- Tickets fechados não são reabertos por resposta.
- São aceitos no máximo cinco anexos de 10 MB nos mesmos formatos do portal.
- `200` encerra o processamento, `406` indica rejeição permanente e erros transitórios retornam `500` para permitir retry do Mailgun.
