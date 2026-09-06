# API V2

A API V2 é isolada da API legada e está disponível sob `/api/v2`. Todas as chamadas exigem um token Laravel Sanctum no cabeçalho:

```http
Authorization: Bearer SEU_TOKEN
Accept: application/json
```

As listagens de tickets, usuários, empresas, setores, categorias e grupos usam a paginação padrão do Laravel, com 100 registros por página. Use `?page=N` para navegar. A especificação completa, incluindo schemas e códigos de erro, está em [`openapi-v2.yaml`](openapi-v2.yaml).

## Tickets

| Método | URL | Uso |
|---|---|---|
| GET | `/api/v2/tickets` | Lista tickets |
| GET | `/api/v2/tickets/search` | Pesquisa por `assunto`, `setor_id`, `grupo_id` e `status` |
| GET | `/api/v2/tickets/{id}` | Detalha um ticket |
| POST | `/api/v2/tickets` | Cria um ticket |
| POST | `/api/v2/tickets/{id}/finalizar` | Finaliza um ticket |
| POST | `/api/v2/tickets/{id}/messages` | Adiciona mensagem |
| GET | `/api/v2/tickets/{id}/timeline` | Lista a linha do tempo interna, 50 eventos por página |
| POST | `/api/v2/tickets/{id}/followers/me` | Segue o ticket |
| DELETE | `/api/v2/tickets/{id}/followers/me` | Deixa de seguir o ticket |
| GET | `/api/v2/tickets/{id}/messages/attachments/{attachment}` | Baixa anexo privado autorizado |
| PATCH | `/api/v2/tickets/{id}/status` | Altera o status |
| POST | `/api/v2/tickets/{id}/assumir` | Atribui o ticket a um analista |
| POST | `/api/v2/tickets/{id}/transferir` | Transfere setor, grupo, categoria quando necessária e opcionalmente analista |

Ao assumir um ticket, o status `aberto` passa para `pendente analista`. Os status `pendente cliente` e `pendente analista` são preservados. A mesma regra se aplica à interface web e ao endpoint legado `/api/tickets/{id}/assumir`.

Na transferência, `setor_id` e `grupo_id` são obrigatórios. `categoria_id` é opcional quando a categoria atual pertence ao setor de destino; caso contrário, deve indicar uma categoria associada ao novo setor. `analista_id` omitido preserva o analista atual; `analista_id: null` deixa o ticket sem analista.

Ao adicionar uma mensagem, `tipo` pode ser `publica` (padrão) ou `interna`. Notas internas e `mentioned_user_ids` são exclusivos de staff ativo com acesso ao ticket; menções só são aceitas em notas internas. `status` pode acompanhar apenas respostas públicas. Notas internas são omitidas para clientes e integrações sem papel de staff. Seguir um ticket não concede acesso e produz somente notificações internas.

```json
{
  "setor_id": 1,
  "grupo_id": 2,
  "categoria_id": 3,
  "analista_id": null
}
```

O campo `prazo` foi descontinuado: não é retornado nas APIs e, quando enviado na criação, é ignorado. O endpoint `PATCH /api/v2/tickets/{id}/prazo` foi removido (404). Valores históricos permanecem no banco, sem uso pela aplicação; não é necessária nova migração. O SLA das categorias permanece inalterado.

## Cadastros e usuário autenticado

`GET /api/v2/usuarios`, `/empresas`, `/setores`, `/categorias` e `/grupos` retornam cadastros paginados. Cada categoria contém `setor_ids` com todas as associações. O campo `setor_id` continua representando a associação principal apenas para compatibilidade e será removido em uma futura versão da API. `GET /api/v2/me` retorna o usuário do token, seus papéis, permissões, empresa, setor e grupo, sem credenciais ou tokens.

## Erros

- `401`: token ausente ou inválido.
- `400`: regra de negócio impediu a operação.
- `403`: usuário autenticado sem autorização para o recurso interno.
- `404`: ticket ou recurso não encontrado.
- `422`: parâmetros ou corpo inválidos; a resposta Laravel inclui `message` e `errors`.
