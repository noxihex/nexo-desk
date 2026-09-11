# Testes

O Nexo Desk utiliza **PHPUnit 11** para verificar regras de negócio, rotas, autorização e respostas da aplicação. Os testes estão organizados em `tests/Unit` e `tests/Feature`, com configuração em [`phpunit.xml`](../phpunit.xml). A versão suportada pelo projeto é definida em `composer.json` (`^11.5`).

Execute os comandos deste guia na raiz do projeto, em um ambiente de desenvolvimento ou integração contínua.

## Preparação do ambiente

Conclua a configuração descrita no [README principal](../README.md) e instale as dependências de desenvolvimento com `composer install` (sem `--no-dev`). O comando `php` deve estar disponível no terminal; caso contrário, utilize o caminho do executável da sua instalação. Em ambientes com containers, execute os comandos no serviço da aplicação.

A suíte principal requer MySQL ou MariaDB e a extensão PHP `pdo_mysql`. Os testes isolados `RoleSeederTest`, `UnifyClientRolesTest`, `CategoriaSetorMigrationTest` e `SanctumExpiresAtMigrationTest` usam SQLite em memória e também exigem `pdo_sqlite`.

### Banco de testes

Crie uma vez o banco exclusivo para os testes:

```sql
CREATE DATABASE nexodesk_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

**Esse banco é descartável.** Os testes com `RefreshDatabase` garantem que o schema esteja atualizado usando as migrations da aplicação e isolam os dados de cada teste, normalmente por meio de transações. Não armazene dados de trabalho nele nem execute duas suítes simultaneamente contra o mesmo banco.

A conexão padrão utiliza `127.0.0.1:3306`, usuário `root` e senha vazia. Para usar outra conexão, defina estas variáveis no ambiente do processo ou no `.env` local:

```dotenv
TEST_DB_HOST=127.0.0.1
TEST_DB_PORT=3306
TEST_DB_USERNAME=seu_usuario_de_testes
TEST_DB_PASSWORD="sua_senha_de_testes"
```

O usuário informado precisa ter permissão para criar, alterar e remover tabelas em `nexodesk_testing`. Não versione credenciais.

O bootstrap fixa o nome do banco como `nexodesk_testing` e ignora `DB_*` e `DATABASE_URL` na seleção dessa conexão. SQLite não substitui o MySQL/MariaDB na suíte completa, pois as migrations históricas contêm operações específicas de MySQL.

Se houver configuração em cache, limpe-a antes da execução:

```bash
php artisan config:clear
```

O bootstrap interrompe os testes quando detecta configuração cacheada.

## Execução

Para executar toda a suíte:

```bash
php artisan test
```

Para executar apenas os testes da API v2:

```bash
php artisan test --filter=ApiV2Test
```

Para investigar dependências entre testes, execute-os em ordem aleatória com uma semente reproduzível:

```bash
php vendor/phpunit/phpunit/phpunit --order-by=random --random-order-seed=20260905
```

Repita a mesma semente para reproduzir a ordem ou altere o número para explorar outras sequências.

Para listar os testes disponíveis sem executá-los:

```bash
./vendor/bin/phpunit --list-tests
```

Para identificar os dez testes mais lentos:

```bash
php artisan test --profile
```

## Isolamento de serviços

O PHPUnit define uma chave de aplicação exclusiva para testes, cache e sessão em memória, filas síncronas e transporte de e-mail `array`, sem envio real de mensagens. As notificações ficam desativadas por padrão e os cenários focados usam os fakes de Mail e Queue; nenhuma integração depende da antiga API Python local.

A classe `Tests\TestCase` usa `Http::fake()` para simular chamadas feitas pelo cliente HTTP do Laravel. Nos testes de integrações, configure respostas específicas com `Http::fake` e valide os pedidos com `Http::assertSent`; a simulação genérica não verifica sozinha o contrato do serviço remoto.

## Orientações para novos testes

- Use nomes de classes e arquivos com o sufixo `Test`. Coloque lógica isolada em `tests/Unit` e testes de rotas, autorização, views e banco em `tests/Feature`.
- Verifique comportamentos observáveis: status HTTP, redirecionamentos, persistência, autorização, JSON e conteúdo renderizado. Não derive a expectativa da implementação testada.
- Crie os dados necessários com factories e utilize `RefreshDatabase` nos testes de banco. Evite depender de IDs fixos, registros locais, ordem de execução ou serviços externos.
- Adicione cobertura de regressão ao corrigir bugs. Quando um requisito mudar, ajuste o teste correspondente; não remova asserções nem ignore testes apenas para obter uma execução sem falhas.
- Evite verificar detalhes de formatação ou estrutura interna quando o requisito é o resultado da página. Testes estáticos de CSS e JavaScript não substituem testes de navegador.
- Para campos sensíveis, confira a ausência das **chaves** no JSON. Passar uma lista de nomes a `assertJsonMissing` não garante que essas propriedades estejam ausentes.

## Diagnóstico de falhas

| Situação | O que verificar |
| --- | --- |
| Falha de conexão ou acesso negado | Disponibilidade do MySQL/MariaDB, variáveis `TEST_DB_*` e permissões do usuário. |
| Banco `nexodesk_testing` inexistente | Crie o banco com o comando SQL deste guia. |
| Driver ou extensão ausente | Verifique as extensões do PHP utilizado no terminal, incluindo `pdo_mysql` para a suíte principal e `pdo_sqlite` para os testes de migrations e seeders isolados. |
| Configuração cacheada | Execute `php artisan config:clear` no ambiente de testes. |
| Falha em uma asserção | Compare o resultado com o requisito atual e investigue a regressão; uma falha antiga não é automaticamente um falso positivo. |
