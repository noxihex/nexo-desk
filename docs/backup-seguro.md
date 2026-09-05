# Armazenamento seguro de backups

Os dumps do banco devem ficar em `storage/app/private/backups`, fora do disco
publico do Laravel. O arquivo `backup.script` e o disco `backups` usam esse
caminho por padrao. Se a instalacao estiver em outro local, configure o mesmo
caminho absoluto em `BACKUP_STORAGE_PATH` no ambiente do cron e no `.env` da
aplicacao.

## Preparacao do servidor

1. Pause temporariamente o cron de backup.
2. Instale `backup.script` como `/usr/local/bin/backup_mariadb.sh` com permissao
   `0750`.
3. Crie `/root/.my.cnf` conforme o cabecalho do script e aplique permissao
   `0600`.
4. Crie o diretorio privado com grupo compartilhado entre o usuario do cron e o
   PHP. O diretorio deve usar `0750` e os arquivos, `0640`.
5. Se o caminho for diferente do padrao, defina `BACKUP_STORAGE_PATH` tanto no
   `.env` quanto no ambiente usado pelo cron.

O PHP precisa de permissao de leitura no diretorio privado, mas o usuario do
servidor web nao precisa de permissao de escrita.

## Migracao dos arquivos existentes

Com o cron pausado:

1. Copie os arquivos de `storage/app/public/backup` para
   `storage/app/private/backups`.
2. Compare a quantidade, o tamanho e o hash SHA-256 dos arquivos nas duas
   pastas.
3. Publique a nova versao da aplicacao e limpe o cache de configuracao com
   `php artisan config:clear` (ou gere novamente o cache usado em producao).
4. Acesse a tela de backups como administrador e teste arquivos novos e
   antigos. Caminhos absolutos antigos no banco sao reconhecidos, mas o arquivo
   sempre e lido da pasta privada.
5. Confirme que `/storage/backup/NOME_DO_ARQUIVO.sql` nao pode ser acessado sem
   autenticacao.
6. Remova os arquivos da pasta publica somente depois dessas verificacoes.
7. Reative o cron e valide a proxima execucao e o download gerado por ela.

Os registros antigos podem ser normalizados posteriormente para conter apenas
o nome do arquivo. Isso nao e requisito para a transicao e evita acoplar uma
migration de banco a operacoes no filesystem de producao.

## Rollback

Antes de apagar a origem publica, o rollback consiste em restaurar a versao
anterior da aplicacao. Depois de apagar a origem publica, nao recoloque dumps no
diretorio publico: corrija a permissao ou configuracao do disco privado.
