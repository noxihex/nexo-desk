# Nexo Desk

O **Nexo Desk** é um sistema de gerenciamento de chamados que centraliza o atendimento entre clientes e equipes de suporte. Desenvolvido com **Laravel 8** e **AdminLTE**, permite organizar solicitações por empresa, setor e categoria, acompanhar o histórico de atendimento e consultar indicadores de trabalho.

## Funcionalidades

- **Gestão de tickets:** abertura, atribuição a analistas, transferência, acompanhamento de status e finalização com registro de horas.
- **Comunicação no chamado:** mensagens e anexos para documentar solicitações e atendimentos.
- **Organização do suporte:** cadastro de empresas, clientes, usuários, setores e categorias com prioridade e parâmetros de SLA.
- **Visão gerencial:** painel de visão geral e relatórios de horas e por analista.
- **Controle de acesso:** perfis de cliente, analista, supervisor e administrador, com acesso conforme o papel do usuário.
- **Administração e integrações:** auditoria, gerenciamento de sessões e API autenticada com Laravel Sanctum.

## Instalação e configuração

As instruções abaixo são para uma instalação local a partir de um banco vazio. Execute os comandos na raiz do projeto, salvo indicação contrária.

### Pré-requisitos

- **PHP 8.1 ou superior**, compatível com as dependências fixadas em `composer.lock`. Embora o `composer.json` declare suporte a versões anteriores, o lock atual contém pacotes que exigem PHP 8.1.
- **Composer**, para instalar as dependências PHP.
- **MySQL ou MariaDB**, com um banco criado e um usuário autorizado a criar e alterar tabelas. As migrations contêm operações específicas de MySQL; não use SQLite nesta instalação.
- **Node.js e npm**, para instalar e compilar os arquivos de frontend.
- **Git**, para clonar o repositório.

Habilite as extensões PHP exigidas pelo Composer e o driver `pdo_mysql`. O usuário que executa a aplicação deve ter permissão de escrita em `storage/` e `bootstrap/cache/`.

### 1. Obtenha o código e configure o ambiente

Substitua `<URL_DO_REPOSITORIO>` pelo endereço deste repositório:

```bash
git clone <URL_DO_REPOSITORIO> nexodesk
cd nexodesk
cp .env.example .env
```

Crie o banco no MySQL/MariaDB antes de executar as migrations:

```sql
CREATE DATABASE nexodesk CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Edite o `.env` com os dados do seu ambiente:

```dotenv
APP_NAME="Nexo Desk"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nexodesk
DB_USERNAME=seu_usuario
DB_PASSWORD="sua_senha"
```

Se o banco estiver em outro servidor ou container, ajuste `DB_HOST` e `DB_PORT`. Não versione o `.env` nem credenciais.

### 2. Instale as dependências e gere a chave

```bash
composer install
composer check-platform-reqs
php artisan key:generate
```

O Composer verifica a compatibilidade do PHP e das extensões. Corrija eventuais requisitos ausentes antes de prosseguir. A chave gerada é salva em `APP_KEY`; mantenha-a nas futuras atualizações da instalação.

### 3. Prepare o banco e os anexos

```bash
php artisan migrate --seed
php artisan storage:link
```

As migrations criam as tabelas, incluindo as sessões usadas pela configuração padrão. O seeder cadastra os perfis e permissões padrão, mas **não cria usuários**. O link de armazenamento permite acessar os anexos utilizados pela interface.

Para reaplicar os acessos padrão em uma instalação existente:

```bash
php artisan db:seed --class=RoleSeeder
```

Esse seeder pode ser repetido sem duplicações, preserva permissões adicionais dos perfis e não atribui perfis a usuários existentes.

### 4. Compile o frontend

```bash
npm install
npm run prod
```

O Laravel Mix gera os arquivos JavaScript e CSS em `public/`. Durante o desenvolvimento, use `npm run dev` para uma compilação ou `npm run watch` para recompilar a cada alteração.

### 5. Crie o primeiro administrador

```bash
php artisan app:create-admin
```

Informe nome, e-mail e uma senha de **12 a 72 caracteres**, seguida da confirmação. A senha fica oculta no terminal e é armazenada como hash; não precisa ser colocada no `.env` nem passada como argumento.

O comando prepara os perfis e permissões padrão e cria um usuário ativo com o perfil `administrador`, que possui a permissão `acesso admin`. O vínculo com empresa e setor pode ser definido depois pelo sistema.

Se já existir um administrador, mesmo inativo, o comando encerra sem criar outro usuário ou alterar sua senha. E-mails já cadastrados são rejeitados; contas existentes não são promovidas automaticamente.

O comando exige um terminal interativo. Se você utiliza Docker, execute-o no serviço da aplicação:

```bash
docker compose exec <serviço> php artisan app:create-admin
```

### 6. Inicie o sistema

```bash
php artisan serve
```

Acesse [http://127.0.0.1:8000](http://127.0.0.1:8000) e entre com o e-mail e a senha do administrador criado. Mantenha o terminal aberto enquanto utilizar o servidor local.

## Primeiro uso

O sistema não oferece cadastro público de contas. Após entrar como administrador, prepare os cadastros antes de abrir o primeiro chamado:

1. Em **Cadastros → Setores**, crie as áreas responsáveis pelo atendimento.
2. Em **Cadastros → Categorias**, cadastre os tipos de solicitação, associando o setor, a prioridade e os parâmetros de SLA.
3. Em **Cadastros → Empresas**, cadastre as organizações atendidas. Na edição de cada empresa, adicione os contatos que acessarão o sistema como clientes.
4. Em **Cadastros → Usuários**, crie as contas da equipe, escolha os perfis e vincule os analistas aos seus setores.
5. Em **Tickets → Criar novo ticket**, informe assunto, descrição e categoria; preencha os vínculos e o responsável conforme o atendimento. Adicione anexos quando necessário.
6. Acompanhe o chamado pelas listagens de tickets, registre mensagens e, ao concluir, finalize com a descrição da solução e as horas de atendimento.

Clientes utilizam uma área própria para abrir e acompanhar chamados. Analistas operam os tickets; supervisores e administradores também acessam cadastros e relatórios. As ferramentas de administração ficam restritas a administradores.

## Personalização e e-mail

Para personalizar o nome exibido na interface, ajuste no `.env`:

```dotenv
APP_NAME_INICIO="Nexo"
APP_NAME_FINAL="Desk"
```

A logo e o favicon não são incluídos no repositório. Para utilizar sua identidade visual, adicione os arquivos:

- `public/vendor/adminlte/dist/img/logodesk.png`
- `public/favicon.ico`

A recuperação de senha por e-mail vem desativada (`PASSWORD_RESET_ENABLED=false`). Para habilitá-la, configure as variáveis `MAIL_*` com um servidor SMTP válido e defina `PASSWORD_RESET_ENABLED=true`. O host `mailhog` do exemplo precisa ser substituído se esse serviço não estiver disponível no seu ambiente.

Após alterar o `.env`, execute `php artisan config:clear` caso a configuração esteja em cache.

## API e chaves de acesso

A API v2 está disponível em `/api/v2`. Consulte os endpoints, exemplos e formatos de resposta na [documentação da API](docs/api-v2.md) e na [especificação OpenAPI](docs/openapi-v2.yaml).

Para gerenciar chaves pelo menu interativo:

```bash
php artisan app:api-key
```

Ele permite criar uma chave, revogar uma chave específica ou revogar todas as chaves de um usuário. Na criação, selecione o usuário ativo, informe o nome da integração e confirme. **O segredo é exibido somente nessa execução**; guarde-o em local seguro.

Para uso em scripts, também é possível criar uma chave diretamente:

```bash
php artisan app:api-key create usuario@example.com --name=integracao
```

Envie a chave nas requisições com os cabeçalhos:

```http
Authorization: Bearer SEU_TOKEN
Accept: application/json
```

O comando informa o ID da chave, que deve ser usado para revogá-la sem colocar o segredo no histórico do shell:

```bash
php artisan app:api-key revoke 12 --force
```

Para revogar todas as chaves de um usuário:

```bash
php artisan app:api-key revoke usuario@example.com --all --force
```

Também são aceitos os verbos `criar` e `revogar`. Sem `--force`, a revogação pede confirmação quando executada em um terminal interativo.

## Atualizações e produção

Em produção, configure `APP_ENV=production`, `APP_DEBUG=false` e `APP_URL` com o endereço definitivo. Configure o servidor web para servir a pasta `public/`; `php artisan serve` é destinado ao desenvolvimento local.

Antes de atualizar, faça backup do banco e dos arquivos armazenados. Após obter a nova versão do código, instale as dependências, recompile o frontend e aplique as migrations:

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run prod
php artisan migrate --force
```

Caso seja necessário reaplicar os perfis padrão em produção, use `php artisan db:seed --class=RoleSeeder --force`. Não recrie o administrador nem gere uma nova `APP_KEY` a cada atualização. **Não use `migrate:fresh` em produção: ele apaga as tabelas e seus dados.**

## Testes

Prepare o banco descartável `nexodesk_testing` conforme o [guia de testes](tests/README.md) e execute:

```bash
php artisan test
```

Para verificar apenas a API v2, use `php artisan test --filter=ApiV2Test`.

## Licença

O Nexo Desk é distribuído sob a **GNU Affero General Public License v3.0**
(`AGPL-3.0-only`). Consulte o arquivo [LICENSE](LICENSE) para conhecer os termos.
