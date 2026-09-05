# Testes

Execute `php artisan test` na raiz do projeto. No Windows com XAMPP fora do PATH:

```powershell
C:\xampp\php\php.exe artisan test
```

## Banco e serviços

A suíte usa exclusivamente o banco descartável MySQL/MariaDB `nexodesk_testing`.
Crie esse banco vazio uma vez no servidor local:

```sql
CREATE DATABASE nexodesk_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

`RefreshDatabase` recria as tabelas com as migrations reais e desfaz os dados de cada teste.
Não coloque dados de trabalho nesse banco e não execute duas suítes simultaneamente nele.
SQLite não substitui essa execução: as migrations históricas contêm operações específicas de MySQL.
O teste isolado de permissões pode continuar usando SQLite em memória e requer `pdo_sqlite`.

Os padrões são `127.0.0.1:3306`, usuário `root`, senha vazia. Para outro ambiente,
defina `TEST_DB_HOST`, `TEST_DB_PORT`, `TEST_DB_USERNAME` e `TEST_DB_PASSWORD` no ambiente
do processo ou no `.env` local (nunca versione credenciais). `DB_*` e `DATABASE_URL` da
aplicação não selecionam o banco de testes. O bootstrap rejeita configuração cacheada;
nesse caso execute `php artisan config:clear` no ambiente local.

O PHPUnit define uma chave de aplicação exclusiva para testes, cache e sessão em memória
e transporte de e-mail `array`. `Tests\TestCase` simula chamadas feitas pelo cliente `Http`
do Laravel. Isso mantém os observers ativos sem depender do serviço Python de e-mail.
Nos testes de integração HTTP, configure respostas com `Http::fake` e confira os pedidos
com `Http::assertSent`; o fake genérico não valida sozinho o contrato do serviço remoto.

## Como manter a suíte útil

- Teste o comportamento observável: status, destino de redirecionamento, dados persistidos,
  autorização, JSON e conteúdo renderizado. Não derive a expectativa do código que está sendo testado.
- Quando o requisito mudar intencionalmente, ajuste o teste correspondente na mesma alteração.
  Não remova uma asserção ou pule um teste apenas para obter uma execução verde.
- Evite exigir contagens de arquivos, indentação, LF/CRLF, ordem de classes CSS ou diretivas
  Blade quando o requisito é o resultado da página. Os testes estáticos restantes de CSS/JS
  verificam trechos específicos e não substituem testes de navegador.
- Crie os próprios dados com factories e `RefreshDatabase`; não dependa de IDs fixos,
  registros do banco local, ordem de execução ou serviços externos.
- Para campos sensíveis, verifique ausência das **chaves** no JSON; procurar uma lista
  de nomes com `assertJsonMissing` não garante que essas propriedades estejam ausentes.

Validações úteis:

```sh
php artisan test --filter=ApiV2Test
php vendor/phpunit/phpunit/phpunit --order-by=random --random-order-seed=20260905
```

Falhas de conexão, extensões ausentes ou configuração cacheada são problemas de preparação
do ambiente. Falhas de comportamento precisam ser confrontadas com o requisito atual;
uma falha antiga não é automaticamente um falso positivo.
