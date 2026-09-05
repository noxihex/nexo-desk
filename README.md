# Nexo Desk

Este é o repositório do sistema **Nexo Desk**, uma aplicação Laravel para gerenciamento de tickets.

## Instalação e Configuração

Siga os passos abaixo para configurar o projeto em seu ambiente local.

### Pré-requisitos

Certifique-se de ter as seguintes ferramentas instaladas:

* **PHP**: Versão 7.3 ou superior (recomendável até a 8.1).
* **Composer**: Gerenciador de dependências PHP.
* **Node.js e NPM**: Para gerenciar os assets de frontend.

### Passos para Instalação

1.  **Clone o repositório:**
    ```bash
    git clone <URL_DO_REPOSITORIO> nexodesk
    cd nexodesk
    ```

2.  **Instale as dependências do PHP com o Composer:**
    ```bash
    composer install
    ```

3.  **Configure o arquivo `.env`:**
    Copie o arquivo de exemplo `.env.example` para criar o seu arquivo de configuração `.env`.
    ```bash
    cp .env.example .env
    ```
    Em seguida, abra o arquivo `.env` e configure as seguintes variáveis:
    * **Variáveis de banco de dados**: `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.
    * **Variáveis de nome do sistema**:
        ```
        APP_NAME_INICIO="Nexo"
        APP_NAME_FINAL="Desk"
        ```

4.  **Gere a chave da aplicação:**
    ```bash
    php artisan key:generate
    ```

5.  **Copie os arquivos estáticos (logo e favicon):**
    O repositório não inclui a logo e o favicon para permitir a personalização por ambiente. Certifique-se de que os seguintes arquivos existem no seu projeto:
    * `public/vendor/adminlte/dist/img/logodesk.png`
    * `public/favicon.ico`

6.  **Execute as migrações e cadastre os perfis e permissões padrão:**
    ```bash
    php artisan migrate --seed
    ```

    Para reaplicar os acessos padrão em uma instalação existente, execute `php artisan db:seed --class=RoleSeeder`. O seeder pode ser repetido sem duplicações e preserva permissões adicionais já atribuídas aos perfis. Em produção, acrescente `--force` aos comandos. O seeder não cria usuários nem atribui perfis a usuários existentes.

7.  **Instale as dependências do Node.js e compile os assets:**
    ```bash
    npm install
    npm run prod
    ```

### Primeiro Acesso (Sistema do Zero)

Após configurar o `.env` e executar as migrations, abra um terminal no servidor (ou dentro do container da aplicação):

```bash
php artisan migrate --force
php artisan app:create-admin
```

Informe nome, e-mail e uma senha de 12 a 72 caracteres, seguida da confirmação. A senha fica oculta no terminal e é armazenada como hash; não precisa ser colocada no `.env` nem passada como argumento. O comando exige um terminal interativo (em Docker, use `docker compose exec <serviço> php artisan app:create-admin`).

### Chaves de API

Para abrir o menu interativo:

```bash
php artisan app:api-key
```

Ele permite criar uma chave, revogar uma chave específica ou revogar todas as chaves de um usuário. Na criação, selecione o usuário ativo, informe o nome da integração e confirme. O segredo é exibido somente nessa execução.

Para uso em scripts, também é possível criar uma chave diretamente:

```bash
php artisan app:api-key create usuario@example.com --name=integracao
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

O comando prepara os perfis e permissões padrão e cria um usuário ativo com o perfil `administrador` e a permissão `acesso admin`. Empresa, setor e grupo podem ser definidos depois pelo sistema. Entre na tela de login usando o e-mail e a senha informados.

Se já existir um administrador, mesmo inativo, o comando encerra sem criar outro usuário ou alterar sua senha. E-mails já cadastrados são rejeitados; contas existentes não são promovidas automaticamente. Nos próximos deploys, execute apenas as migrations necessárias. Não use `migrate:fresh` em produção, pois ele apaga os dados.

## Licença

O Nexo Desk é distribuído sob a **GNU Affero General Public License v3.0**
(`AGPL-3.0-only`). Consulte o arquivo [LICENSE](LICENSE) para conhecer os termos.
