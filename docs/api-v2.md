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
| PATCH | `/api/v2/tickets/{id}/status` | Altera o status |
| POST | `/api/v2/tickets/{id}/assumir` | Atribui o ticket a um analista |
| POST | `/api/v2/tickets/{id}/transferir` | Transfere setor, grupo e opcionalmente analista |

Ao assumir um ticket, o status `aberto` passa para `pendente analista`. Os status `pendente cliente` e `pendente analista` são preservados. A mesma regra se aplica à interface web e ao endpoint legado `/api/tickets/{id}/assumir`.

Na transferência, `setor_id` e `grupo_id` são obrigatórios. `analista_id` omitido preserva o analista atual; `analista_id: null` deixa o ticket sem analista.

```json
{
  "setor_id": 1,
  "grupo_id": 2,
  "analista_id": null
}
```

O campo `prazo` foi descontinuado: não é retornado nas APIs e, quando enviado na criação, é ignorado. O endpoint `PATCH /api/v2/tickets/{id}/prazo` foi removido (404). Valores históricos permanecem no banco, sem uso pela aplicação; não é necessária nova migração. O SLA das categorias permanece inalterado.

## Cadastros e usuário autenticado

`GET /api/v2/usuarios`, `/empresas`, `/setores`, `/categorias` e `/grupos` retornam cadastros paginados. `GET /api/v2/me` retorna o usuário do token, seus papéis, permissões, empresa, setor e grupo, sem credenciais ou tokens.

## Erros

- `401`: token ausente ou inválido.
- `400`: regra de negócio impediu a operação.
- `404`: ticket ou recurso não encontrado.
- `422`: parâmetros ou corpo inválidos; a resposta Laravel inclui `message` e `errors`.
