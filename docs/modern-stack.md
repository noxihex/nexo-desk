# Stack moderna: Vite, Tailwind, Livewire e Flux

Esta fundação permite criar páginas modernas sem alterar as telas existentes em Bootstrap e AdminLTE. O legado continua compilado pelo Laravel Mix; novas páginas usam exclusivamente Vite, Tailwind CSS 4, Livewire 4 e Flux 2 Free.

## Requisitos e instalação

- PHP 8.2 ou superior.
- Composer.
- Node.js 20.19 ou superior.
- npm.

Depois de obter o projeto, instale as dependências bloqueadas nos arquivos de lock:

```bash
composer install
npm install
```

Não execute os comandos de publicação de componentes do Flux. Os componentes internos do pacote permanecem no `vendor/`; a API da aplicação fica nos wrappers anônimos `x-modern:*`.

## Desenvolvimento e produção

| Comando | Função |
|---|---|
| `npm run dev` | Compila o legado uma vez com Mix. |
| `npm run watch` | Observa e recompila somente o legado. |
| `npm run dev:modern` | Inicia o Vite moderno com HMR em `5173`. |
| `npm run dev:all` | Executa o watcher do Mix e o Vite em paralelo. |
| `npm run build:legacy` | Gera o build de produção do legado. |
| `npm run build:modern` | Gera o build de produção moderno. |
| `npm run prod` ou `npm run production` | Executa os dois builds de produção e interrompe se algum falhar. |

Para trabalhar nas duas interfaces:

```bash
php artisan serve
npm run dev:all
```

Os dois servidores de frontend não compartilham arquivo HMR. O Mix usa `public/hot`, enquanto o Vite usa `storage/vite.hot`, a porta fixa `5173` e `strictPort`. Se a porta estiver ocupada, o Vite falha explicitamente em vez de escolher outra porta.

## Isolamento dos assets

| Stack | Entrada | Saída/manifesto | HMR |
|---|---|---|---|
| Legada | `resources/js/app.js` e `resources/sass/app.scss` | `public/js`, `public/css` e `public/mix-manifest.json` | `public/hot` |
| Moderna | `resources/js/modern.js` e `resources/css/modern.css` | `public/build` e `public/build/manifest.json` | `storage/vite.hot` |

`modern.js` não importa Bootstrap, AdminLTE, jQuery, Sass ou scripts legados. O Tailwind usa `source(none)` e examina somente os layouts modernos, as views de autenticação, os wrappers modernos e as classes/views Livewire. Não inclua `@vite`, diretivas Livewire, Flux ou classes Tailwind em uma view que estenda `adminlte::page` ou `adminlte::auth.*`.

## Primeira página moderna

Gere o componente Livewire em classe PHP e Blade:

```bash
php artisan make:livewire Modern/NomeDaPagina --class
```

O layout padrão já está configurado como `components.layouts.modern`. Uma página de classe também pode declará-lo explicitamente no método `render`:

```php
public function render()
{
    return view('livewire.modern.nome-da-pagina')
        ->layout('components.layouts.modern', [
            'title' => 'Nome da página',
            'heading' => 'Nome da página',
        ]);
}
```

Em uma etapa futura, registre a página preservando o nome da rota e os mesmos middlewares de autenticação, permissão e contexto da versão legada. Não substitua a rota antiga até concluir a validação funcional.

Para adicionar a navegação, preencha o slot `navigation` do layout apenas com rotas modernas:

```blade
<x-slot:navigation>
    <a
        href="{{ route('nome-da-rota-moderna') }}"
        wire:navigate
        class="block rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100 dark:text-zinc-200 dark:hover:bg-zinc-800"
    >
        Nome da página
    </a>
</x-slot:navigation>
```

## Componentes `x-modern:*`

Os wrappers são a API pública das novas páginas. Atributos adicionais como `class`, `wire:*`, `x-*`, `data-*` e atributos ARIA são encaminhados ao elemento Flux correspondente.

### Botão

```blade
<x-modern.button type="submit" variant="primary" size="sm" icon="check" wire:click="save">
    Salvar
</x-modern.button>

<x-modern.button href="{{ route('tickets.index') }}" variant="outline" :disabled="$blocked">
    Voltar
</x-modern.button>
```

O estado de loading é automático para `type="submit"` e `wire:click`; use `:loading="false"` para desativá-lo ou `:loading="true"` para forçá-lo.

### Campo

```blade
<x-modern.input
    name="subject"
    label="Assunto"
    description="Resuma a solicitação."
    placeholder="Ex.: acesso ao sistema"
    wire:model="subject"
/>
```

O nome pode ser inferido de `wire:model`. O wrapper mostra automaticamente o erro de validação correspondente.

### Select

```blade
<x-modern.select
    name="priority"
    label="Prioridade"
    placeholder="Selecione"
    :options="['normal' => 'Normal', 'alta' => 'Alta']"
    wire:model="priority"
/>

<x-modern.select name="status" label="Status" :selected="$status">
    <x-modern.select.option value="open">Aberto</x-modern.select.option>
    <x-modern.select.option value="closed" disabled>Fechado</x-modern.select.option>
</x-modern.select>
```

### Badge

```blade
<x-modern.badge color="green" size="sm">Ativo</x-modern.badge>
```

### Card

```blade
<x-modern.card title="Resumo" description="Dados atuais do chamado">
    <x-slot:actions>
        <x-modern.button size="sm">Editar</x-modern.button>
    </x-slot:actions>

    Conteúdo do card.

    <x-slot:footer>Atualizado agora.</x-slot:footer>
</x-modern.card>
```

### Tabela

```blade
<x-modern.table :paginator="$tickets">
    <x-modern.table.columns>
        <x-modern.table.column>Chamado</x-modern.table.column>
        <x-modern.table.column>Status</x-modern.table.column>
    </x-modern.table.columns>

    <x-modern.table.rows>
        @foreach ($tickets as $ticket)
            <x-modern.table.row :key="$ticket->id">
                <x-modern.table.cell>{{ $ticket->subject }}</x-modern.table.cell>
                <x-modern.table.cell><x-modern.badge>{{ $ticket->status }}</x-modern.badge></x-modern.table.cell>
            </x-modern.table.row>
        @endforeach
    </x-modern.table.rows>
</x-modern.table>
```

A tabela já possui rolagem horizontal responsiva. O paginator pode ser fornecido na própria tabela ou renderizado separadamente.

### Modal

```blade
<x-modern.modal name="confirm-delete" title="Excluir item" description="Esta ação não pode ser desfeita.">
    <x-slot:trigger>
        <x-modern.button variant="danger">Excluir</x-modern.button>
    </x-slot:trigger>

    Confirme a exclusão do registro.

    <x-slot:actions>
        <x-modern.button variant="outline" x-on:click="$flux.modal('confirm-delete').close()">Cancelar</x-modern.button>
        <x-modern.button variant="danger" wire:click="delete">Confirmar</x-modern.button>
    </x-slot:actions>
</x-modern.modal>
```

O atributo `name` é obrigatório e identifica tanto o gatilho quanto o diálogo.

### Paginação

```blade
<x-modern.pagination :paginator="$tickets" scroll-to="#conteudo" />
```

O atributo `paginator` é obrigatório. `scroll-to` é opcional e pode ser um seletor CSS ou `true` para voltar ao topo da página.

## Regra para novos componentes

Quando o Flux Free possuir um equivalente, o wrapper deve delegar ao Flux e compor seus elementos públicos. Quando não possuir, implemente HTML semântico com Blade, Tailwind e, quando necessário, Alpine/Livewire. Em ambos os casos:

- mantenha propriedades e slots consistentes com os wrappers existentes;
- encaminhe atributos HTML e diretivas reativas;
- valide propriedades obrigatórias;
- preserve acessibilidade, foco e navegação por teclado;
- não exponha ao consumidor qual implementação interna está sendo usada;
- não publique nem copie os componentes internos do Flux para o projeto.

## Checklist antes de substituir uma tela legada

- Preserve a URL, o nome da rota e os middlewares existentes.
- Compare autenticação, autorização, validação e mensagens de erro.
- Compare filtros, ordenação, paginação e estados vazios.
- Valide todos os fluxos de criação, edição, exclusão e ações específicas do módulo.
- Confirme anexos, notificações, jobs, eventos e integrações envolvidos.
- Teste teclado, foco, leitor de tela, responsividade e tema claro/escuro.
- Adicione testes de regressão para regras de negócio e contratos da página.
- Valide a página moderna com usuários de cada perfil relevante.
- Somente então substitua a rota/view antiga; mantenha uma estratégia simples de reversão.
- Remova dependências legadas apenas quando nenhuma tela ainda depender delas.

## Autenticação moderna

A primeira migração atende `/login`, `/password/reset`, `/password/reset/{token}` e `/password/confirm`. Os GETs e nomes de rota continuam nos controllers existentes; as views `auth.*` montam o layout `components.layouts.auth` e os componentes em `App\Livewire\Modern\Auth`.

Os formulários ativos usam `wire:submit`. As operações em `App\Actions\Auth` são compartilhadas com os POSTs existentes, mantendo validações, respostas HTTP/JSON, guard, broker, mensagens e redirecionamentos. O login compartilha o limite de cinco tentativas por minuto por e-mail/IP entre os dois transportes. A confirmação revalida a sessão em cada submissão. As senhas são limpas do estado dos componentes após a tentativa, e o token de redefinição é uma propriedade bloqueada do Livewire.

Os links e redirecionamentos usam carregamento completo, inclusive na entrada das telas internas legadas. O layout de autenticação carrega somente Vite, Livewire e Flux, oferece os temas claro/escuro/sistema e não utiliza a sidebar moderna. Os wrappers adicionais `x-modern.checkbox` (`name`, `label`) e `x-modern.alert` (`variant`, `heading`, conteúdo no slot padrão) encaminham atributos ao Flux Free; o aviso define `role="alert"` para erros e `role="status"` nos demais casos.

A recuperação respeita `PASSWORD_RESET_ENABLED` a cada envio. O padrão continua desativado, com a mesma orientação para procurar o administrador. Não há alteração de configuração de e-mail nem de banco de dados.

`auth.register` e `auth.verify` são somente templates modernos preparados para uma etapa futura. Não possuem ações Livewire nem rotas públicas, e seus controles de envio ficam desabilitados. Ativar esses fluxos exige uma entrega própria, incluindo as regras de cadastro e verificação; não basta expor os templates.

### Verificação e reversão

- Executar `php artisan test --filter=ModernAuthenticationTest` para os contratos Livewire e HTTP, incluindo o envelope JSON real do Livewire.
- Executar `php artisan test` no banco exclusivo `nexodesk_testing` e `npm run build:modern` antes da entrega.
- Conferir login, recuperação, redefinição e confirmação em desktop/mobile, teclado, temas e navegação para o legado; notificações de teste devem ser simuladas.
- Para reverter, reverter o commit desta migração e executar novamente o build moderno. Os endpoints POST, o logout, os assets Mix e os templates das páginas internas continuam disponíveis; não há migrações a desfazer.
