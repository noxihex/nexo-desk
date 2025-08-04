# BTX Desk

Este é o repositório do sistema **BTX Desk**, uma aplicação Laravel para gerenciamento de tickets.

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
    git clone [https://github.com/noxihex/btxdesk.git](https://github.com/noxihex/btxdesk.git)
    cd btxdesk
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
        APP_NAME_INICIO="BTX"
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

6.  **Execute as migrações do banco de dados:**
    ```bash
    php artisan migrate
    ```

7.  **Instale as dependências do Node.js e compile os assets:**
    ```bash
    npm install
    npm run prod
    ```

### Primeiro Acesso (Sistema do Zero)

Se esta for a primeira vez que você está configurando o projeto em um banco de dados novo, você pode popular a base de dados com as permissões e perfis de usuário iniciais usando o seeder.

**AVISO**: Este comando apagará todas as tabelas e dados existentes no banco de dados.

```bash
php artisan migrate:fresh --seed --seeder=RoleSeeder