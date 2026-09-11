# Guia de construção da interface moderna

O Nexo Desk foi modernizado a partir de uma interface baseada em AdminLTE, Bootstrap, jQuery, Laravel UI e Laravel Mix. A aplicação atual usa Laravel 12, Vite, Tailwind CSS 4, Livewire 4 e Flux 2 Free. Este documento descreve a arquitetura vigente e os padrões para construir e manter páginas nessa stack.

## Visão geral da arquitetura

| Camada | Responsabilidade | Localização principal |
|---|---|---|
| Rotas e controllers | Entrada HTTP, middlewares, negociação HTML/JSON e redirecionamentos | `routes/`, `app/Http/Controllers/` |
| Actions | Regras de negócio, autorização, consultas, transações e persistência compartilhadas | `app/Actions/` |
| Livewire | Estado e interação das páginas | `app/Livewire/Modern/`, `resources/views/livewire/` |
| Apresentação | Layouts, componentes Blade e estilos | `resources/views/components/`, `resources/css/modern.css` |

Os controllers devem permanecer pequenos. Quando uma operação também for usada por um componente Livewire ou por mais de uma entrada HTTP, concentre a regra em uma Action. Componentes Livewire não devem chamar controllers.

## Requisitos e instalação

- PHP 8.2 ou superior;
- Composer;
- Node.js 20.19 ou superior;
- npm.

Instale as dependências definidas nos arquivos de lock:

```bash
composer install
npm install
```

Para iniciar o backend e o servidor de assets:

```bash
php artisan serve
npm run dev
```

Para gerar os assets de produção:

```bash
npm run build
```

O Vite usa `resources/js/modern.js` como entrada, grava os bundles e o manifesto em `public/build` e registra o HMR em `storage/vite.hot`. A porta de desenvolvimento é `5173`; como `strictPort` está habilitado, uma porta ocupada produz erro em vez de uma troca silenciosa.

## Assets e Tailwind

`resources/js/modern.js` importa `resources/css/modern.css`. A folha carrega Tailwind e os estilos do Flux, define as cores de destaque, o tema escuro e as regras de impressão dos relatórios.

O Tailwind usa `source(none)`. Toda nova pasta que contenha classes utilitárias deve ser adicionada explicitamente com `@source` em `resources/css/modern.css`. Inclua a mesma pasta em `refresh` no `vite.config.mjs` quando suas alterações precisarem acionar HMR.

Evite construir nomes de classes Tailwind por interpolação. Prefira classes completas e mapeamentos explícitos para que a varredura consiga incluí-las no build.

## Layouts e navegação

O layout principal é `components.layouts.modern`. A autenticação usa `components.layouts.auth`, sem a navegação interna da aplicação.

A navegação é separada por público:

- `x-modern.staff.navigation` atende analistas, supervisores e administradores;
- `x-modern.client.navigation` atende o portal do cliente;
- layouts como `x-modern.tickets.layout`, `x-modern.cadastros.layout` e `x-modern.reports.layout` compõem cada seção.

Use carregamento completo nos links entre páginas. Só adote `wire:navigate` depois de validar o ciclo de vida de todos os componentes, scripts, modais, foco e estado envolvidos.

## Criando uma página

Gere um componente Livewire baseado em classe:

```bash
php artisan make:livewire Modern/NomeDaPagina --class
```

O layout padrão está configurado como `components.layouts.modern`. Quando precisar definir título e cabeçalho explicitamente:

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

Ao registrar a rota, aplique os middlewares de autenticação e perfil correspondentes ao domínio. Se a página substituir uma entrada já publicada, preserve URL, nome da rota, métodos HTTP e formato das respostas.

### Estrutura recomendada

1. O controller GET seleciona e retorna a view de entrada.
2. A view monta o componente Livewire da página.
3. O componente gerencia estado de interface, entrada e feedback.
4. Uma Action autoriza novamente o ator, consulta os registros e executa a regra de negócio.
5. O componente atualiza seu estado ou redireciona depois da operação.

Para ações sensíveis, reconsulte o usuário e o registro no momento da execução. Identificadores, tipo da página e URLs de retorno que não podem ser alterados pelo navegador devem usar propriedades bloqueadas do Livewire. Nunca confie apenas nos dados recebidos em `mount()`.

## Componentes `x-modern:*`

Os wrappers `x-modern:*` são a API visual da aplicação. Use-os antes de acessar `flux:*` diretamente: eles mantêm validação, acessibilidade, temas e variações consistentes. Atributos como `class`, `wire:*`, `x-*`, `data-*` e ARIA são encaminhados ao elemento interno.

Os componentes do Flux permanecem no pacote; não execute comandos de publicação nem copie suas implementações para o projeto.

### Botão

```blade
<x-modern.button type="submit" variant="primary" icon="check">
    Salvar
</x-modern.button>

<x-modern.button href="{{ route('tickets.index') }}" variant="outline" :disabled="$blocked">
    Voltar
</x-modern.button>
```

O loading é automático em botões `submit` e com `wire:click`. Use `:loading="false"` para desabilitá-lo ou `:loading="true"` para forçá-lo.

Convenções de ação:

- azul para ações principais e busca;
- `variant="filled" color="green"` para criar, salvar e ativar;
- `variant="filled" color="sky"` para visualizar detalhes;
- `variant="filled" color="amber"` para editar;
- `variant="filled" color="red"` para excluir e desativar;
- `variant="outline"` para cancelar e voltar.

### Campos de formulário

```blade
<x-modern.input
    name="subject"
    label="Assunto"
    description="Resuma a solicitação."
    wire:model="subject"
/>

<x-modern.textarea
    name="description"
    label="Descrição"
    rows="6"
    wire:model="description"
/>

<x-modern.checkbox
    name="notify"
    label="Receber notificações"
    wire:model="notify"
/>
```

O `name` pode ser inferido de `wire:model`. Os wrappers exibem automaticamente o erro de validação correspondente. Use `description` para orientação persistente e `placeholder` apenas como exemplo.

### Select

```blade
<x-modern.select
    name="priority"
    label="Prioridade"
    placeholder="Selecione"
    :options="['normal' => 'Normal', 'alta' => 'Alta']"
    wire:model="priority"
/>
```

Para listas extensas, habilite `searchable`. Esse modo requer `wire:model`:

```blade
<x-modern.select
    name="companyId"
    label="Empresa"
    :options="$companies"
    searchable
    wire:model.live="companyId"
/>
```

Também é possível declarar opções no slot:

```blade
<x-modern.select name="status" label="Status" :selected="$status">
    <x-modern.select.option value="open">Aberto</x-modern.select.option>
    <x-modern.select.option value="closed" disabled>Fechado</x-modern.select.option>
</x-modern.select>
```

### Alertas e badges

```blade
<x-modern.alert variant="success" heading="Dados salvos">
    As alterações já estão disponíveis.
</x-modern.alert>

<x-modern.badge color="green" size="sm">Ativo</x-modern.badge>
```

Alertas de erro recebem `role="alert"`; os demais usam `role="status"`.

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

### Tabela e paginação

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
                <x-modern.table.cell>
                    <x-modern.badge>{{ $ticket->status }}</x-modern.badge>
                </x-modern.table.cell>
            </x-modern.table.row>
        @endforeach
    </x-modern.table.rows>
</x-modern.table>
```

A tabela oferece rolagem horizontal responsiva. Passe o paginator para ela ou renderize-o separadamente:

```blade
<x-modern.pagination :paginator="$tickets" scroll-to="#conteudo" />
```

### Modal

```blade
<x-modern.modal
    name="confirm-delete"
    title="Excluir item"
    description="Esta ação não pode ser desfeita."
>
    <x-slot:trigger>
        <x-modern.button variant="filled" color="red">Excluir</x-modern.button>
    </x-slot:trigger>

    Confirme a exclusão do registro.

    <x-slot:actions>
        <x-modern.button variant="outline" x-on:click="$flux.modal('confirm-delete').close()">
            Cancelar
        </x-modern.button>
        <x-modern.button variant="filled" color="red" wire:click="delete">
            Confirmar
        </x-modern.button>
    </x-slot:actions>
</x-modern.modal>
```

O `name` é obrigatório. Em confirmações destrutivas, descreva o registro e o impacto, prefira o foco inicial em “Cancelar” e garanta que fechar devolva o foco ao gatilho.

### Upload de arquivos

```blade
<x-modern.file-upload
    model="pendingAttachments"
    :files="$attachments"
    remove="removeAttachment"
    error-name="attachments"
/>
```

O wrapper fornece dropzone, feedback de carregamento, lista de arquivos e remoção individual. A validação final de quantidade, tamanho, extensão e autorização deve permanecer no componente e na Action. Defina conscientemente o disco e a visibilidade: arquivos públicos e privados têm fluxos de download diferentes.

## Criando ou ampliando componentes

Quando o Flux Free tiver um equivalente, componha-o em um wrapper `x-modern:*`. Quando não tiver, implemente HTML semântico com Blade, Tailwind e, se necessário, Alpine ou Livewire.

Todo componente deve:

- manter propriedades e slots coerentes com o catálogo existente;
- encaminhar atributos HTML e diretivas reativas;
- validar propriedades obrigatórias;
- funcionar com teclado, foco e leitor de tela;
- suportar temas claro e escuro e layouts responsivos;
- ocultar do consumidor os detalhes da implementação interna.

Prefira componentes de domínio quando a composição for específica de uma seção e um wrapper genérico quando o padrão for reutilizável em toda a aplicação.

## Padrões Livewire

### Filtros e paginação

Use `wire:model.live.debounce.300ms` para buscas textuais durante a digitação. Sincronize filtros relevantes com a URL quando o usuário puder recarregar ou compartilhar a consulta. Ao mudar um filtro, redefina o paginator; depois de excluir o último item de uma página, ajuste-a para uma página válida.

Filtros com custo alto ou vários campos podem usar `wire:submit`. Nesse caso, separe o estado dos controles dos filtros efetivamente aplicados para que os resultados só mudem após a submissão.

### Validação e feedback

Valide no servidor em toda mutação. Exiba erros junto ao campo e use alertas para resultados gerais. Campos sensíveis, especialmente senhas e tokens temporários, devem ser limpos depois de cada tentativa.

Use estados distintos para primeira visita, carregamento, resultado vazio, erro recuperável e operação concluída.

### Autorização e integridade

- Revalide sessão, status e perfil do usuário em cada requisição relevante.
- Consulte novamente o registro antes de editar, excluir ou alterar seu estado.
- Restrinja as consultas ao escopo autorizado, não apenas a exibição na Blade.
- Use transações em operações que alteram múltiplos registros ou arquivos.
- Em falhas após upload, remova arquivos já gravados.
- Reconstrua destinos internos a partir de rotas e registros autorizados.

### Navegação e retorno

Parâmetros como `return_to` devem aceitar somente destinos internos previamente autorizados. Preserve filtros e paginação quando o usuário abre um registro e volta à listagem, sem permitir redirecionamento externo.

## Experiência e acessibilidade

- Mantenha título, heading e estado ativo da navegação coerentes.
- Ofereça labels visíveis e mensagens de erro associadas aos campos.
- Garanta ordem de tabulação, foco visível e operação completa por teclado.
- Use `aria-live`, `role="status"` ou `role="alert"` conforme a urgência.
- Evite rolagem interna desnecessária; tabelas largas devem rolar no próprio contêiner.
- Verifique desktop, mobile e temas claro, escuro e sistema.
- Em gráficos, forneça descrição textual e tabela de dados equivalente.
- Em impressão, oculte navegação e controles sem remover identificação e conteúdo.

## Mapa dos módulos

| Área | Componentes e Actions principais | Teste focado |
|---|---|---|
| Autenticação | `App\Livewire\Modern\Auth`, `App\Actions\Auth` | `ModernAuthenticationTest` |
| Cadastros | `App\Livewire\Modern\Cadastros`, `App\Actions\Cadastros` | `ModernCatalogsTest`, `ModernRelatedCatalogsTest` |
| Minha conta | `Account\Settings`, `Account\UpdateAccount` | `ModernAccountTest` |
| Relatórios | `Modern\Reports`, `Actions\Reports` | `ModernReportsTest` |
| Tickets da equipe | `Modern\Tickets`, `Actions\Tickets` | `ModernTicketListingsTest`, `ModernTicketFlowTest` |
| Portal do cliente | `Modern\ClientTickets`, `Actions\Tickets` | `ModernClientTicketFlowTest` |
| Visão geral | `Modern\Overview`, `Actions\Overview` | `ModernStaffOverviewTest`, `ModernClientOverviewTest` |
| Notificações | `Modern\Notifications`, `Actions\Notifications` | `ModernNotificationCenterTest` |
| Administração | views em `administracao` e componentes modernos | `ModernAdministrationTest` |
| Páginas de erro | `resources/views/errors` | `ModernErrorPagesTest` |

Use esse mapa para localizar exemplos antes de criar uma implementação. Listagens de tickets demonstram filtros e paginação; cadastros demonstram CRUD e modais; relatórios demonstram gráficos acessíveis e impressão; os fluxos de tickets demonstram upload, autorização contextual e operações transacionais.

## Checklist de implementação

Antes de concluir uma página ou componente:

- preserve o contrato de rotas e respostas públicas aplicável;
- confirme autenticação, autorização e escopo das consultas;
- cubra validação, mensagens, estados vazios, loading e falhas;
- teste filtros, ordenação, paginação e retorno à listagem;
- revise uploads, downloads, notificações, jobs e eventos envolvidos;
- valide teclado, foco, leitor de tela, responsividade, temas e impressão;
- adicione testes de regressão para a regra de negócio e o contrato;
- execute o teste focado, a suíte completa e o build de produção.

```bash
php artisan test --filter=NomeDoTeste
php artisan test
npm run build
```

Quando a alteração afetar Blade compilado ou páginas de erro, valide também:

```bash
php artisan view:cache
```

Use exclusivamente o banco de testes configurado para a suíte. Nunca execute comandos destrutivos de migração em uma base com dados reais.
