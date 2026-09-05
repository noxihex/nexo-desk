# Unificação dos clientes

O perfil único é `cliente`, com a permissão `acesso cliente`. A migração
`2026_09_05_000000_unify_client_roles` converte os vínculos de `clientedc`,
remove duplicações e substitui `acesso cliente noc` e `acesso cliente dc`.
Os demais perfis dos usuários são preservados. Permissões adicionais dos
perfis de cliente passam a ser permissões diretas dos membros existentes,
para não concedê-las a todos os clientes. Os guards são tratados separadamente.
Não há alterações em usuários, empresas ou chamados.

## Aplicação

Faça backup do banco antes da atualização. Publique o código e execute a migração
na mesma janela de manutenção, sem atender requisições entre essas etapas:

```sh
php artisan down
php artisan migrate --force
php artisan optimize:clear
php artisan permission:cache-reset
php artisan up
```

Se houver workers persistentes, reinicie-os após a atualização. Não é necessário
rodar seeders no banco existente. Verifique o login, o menu e a consulta de chamados
com um cliente anteriormente DC e outro NOC.

A migração é transacional e pode ser repetida sem duplicar vínculos. Sua reversão
automática é bloqueada: após unir os perfis, não é possível deduzir quem era NOC,
DC ou ambos. Para reverter, restaure o backup e a versão anterior do código juntos.

## Validação

```sh
php artisan test --do-not-cache-result
```

Os testes específicos da migração usam SQLite em memória; os testes de integração
da aplicação utilizam o banco `nexodesk_testing` definido em `phpunit.xml`.
