# Stack moderna: Vite, Tailwind, Livewire e Flux

A interface web usa exclusivamente Vite, Tailwind CSS 4, Livewire 4 e Flux 2 Free. AdminLTE, Laravel Mix e o scaffolding do Laravel UI foram removidos da aplicação.

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
| `npm run dev` | Inicia o Vite com HMR em `5173`. |
| `npm run build` | Gera os assets otimizados de produção. |

Para desenvolver a interface:

```bash
php artisan serve
npm run dev
```

O Vite registra o estado de desenvolvimento em `storage/vite.hot` e usa a porta fixa `5173` com `strictPort`. Se a porta estiver ocupada, ele falha explicitamente em vez de escolher outra porta.

## Isolamento dos assets

| Stack | Entrada | Saída/manifesto | HMR |
|---|---|---|---|
| Vite | `resources/js/modern.js` e `resources/css/modern.css` | `public/build` e `public/build/manifest.json` | `storage/vite.hot` |

`modern.js` não importa Bootstrap, AdminLTE, jQuery, Sass ou scripts legados. O Tailwind usa `source(none)` e examina somente os layouts modernos, as views de autenticação, os wrappers modernos e as classes/views Livewire. Nenhuma view da aplicação estende namespaces do AdminLTE.

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

Registre novas páginas preservando os padrões de autenticação, permissão e contexto já adotados pela aplicação. Ao alterar uma rota existente, mantenha seu contrato público ou documente explicitamente a incompatibilidade.

Para adicionar a navegação, preencha o slot `navigation` do layout apenas com rotas modernas:

```blade
<x-slot:navigation>
    <a
        href="{{ route('nome-da-rota-moderna') }}"
        class="block rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100 dark:text-zinc-200 dark:hover:bg-zinc-800"
    >
        Nome da página
    </a>
</x-slot:navigation>
```

Os links usam navegação convencional. Não use `wire:navigate` sem validar todo o ciclo de vida da página, pois os componentes atuais foram projetados para carregamento completo.

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

As páginas modernas preservam a semântica visual do legado com uma apresentação mais suave: ações principais e buscas usam azul; criar, salvar e ativar usam `variant="filled" color="green"`; visualizar e detalhes usam `filled` com `sky`; editar usa `filled` com `amber`; excluir e desativar usam `filled` com `red`; cancelar e voltar permanecem `outline`. O vermelho sólido de `variant="danger"` não deve ser usado nas ações comuns de exclusão, pois compete excessivamente com o restante da interface.

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

## Checklist antes de alterar uma página

- Preserve a URL, o nome da rota e os middlewares existentes.
- Compare autenticação, autorização, validação e mensagens de erro.
- Compare filtros, ordenação, paginação e estados vazios.
- Valide todos os fluxos de criação, edição, exclusão e ações específicas do módulo.
- Confirme anexos, notificações, jobs, eventos e integrações envolvidos.
- Teste teclado, foco, leitor de tela, responsividade e tema claro/escuro.
- Adicione testes de regressão para regras de negócio e contratos da página.
- Valide a página moderna com usuários de cada perfil relevante.
- Mantenha uma estratégia simples de reversão para alterações de maior risco.
- Adicione dependências somente quando os recursos atuais não atenderem ao caso de uso.

## Autenticação moderna

A autenticação atende `/login`, `/password/reset`, `/password/reset/{token}` e `/password/confirm`. Os GETs e nomes de rota ficam nos controllers dedicados; as views `auth.*` montam o layout `components.layouts.auth` e os componentes em `App\Livewire\Modern\Auth`.

Os formulários ativos usam `wire:submit`. As operações em `App\Actions\Auth` são compartilhadas com os POSTs existentes, mantendo validações, respostas HTTP/JSON, guard, broker, mensagens e redirecionamentos. O login compartilha o limite de cinco tentativas por minuto por e-mail/IP entre os dois transportes. A confirmação revalida a sessão em cada submissão. As senhas são limpas do estado dos componentes após a tentativa, e o token de redefinição é uma propriedade bloqueada do Livewire.

Os links e redirecionamentos usam carregamento completo. O layout de autenticação carrega somente Vite, Livewire e Flux, exibe a marca e o favicon do Nexo Desk e oferece os temas claro/escuro/sistema por três botões de ícone acessíveis. Ele não utiliza a sidebar moderna. Os wrappers adicionais `x-modern.checkbox` (`name`, `label`) e `x-modern.alert` (`variant`, `heading`, conteúdo no slot padrão) encaminham atributos ao Flux Free; o aviso define `role="alert"` para erros e `role="status"` nos demais casos.

A recuperação respeita `PASSWORD_RESET_ENABLED` a cada envio. O padrão continua desativado, com a mesma orientação para procurar o administrador. Não há alteração de configuração de e-mail nem de banco de dados.

Cadastro público e verificação de e-mail permanecem deliberadamente indisponíveis. Suas rotas, controllers e templates inativos foram removidos; habilitar esses fluxos no futuro exige uma entrega própria com regras de negócio, autorização e cobertura específicas.

### Verificação e reversão

- Executar `php artisan test --filter=ModernAuthenticationTest` para os contratos Livewire e HTTP, incluindo o envelope JSON real do Livewire.
- Executar `php artisan test` no banco exclusivo `nexodesk_testing` e `npm run build` antes da entrega.
- Conferir login, recuperação, redefinição e confirmação em desktop/mobile, teclado, temas e navegação para o sistema; notificações de teste devem ser simuladas.
- Para reverter alterações nesse fluxo, restaurar os controllers, Actions e rotas em conjunto e executar novamente o build. Os endpoints POST e o logout não exigem migrações de banco.

## Cadastros modernos: categorias e setores

As páginas de listagem, criação e edição em `/cadastros/categorias` e `/cadastros/setores` usam o layout moderno por meio de `x-modern.cadastros.layout`. Os GETs continuam nos controllers existentes e os formulários são componentes em `App\Livewire\Modern\Cadastros`. Os endpoints POST, PUT/PATCH e DELETE mantêm URLs, nomes de rota e redirecionamentos. Grupos não faz parte da interface web; o modelo e a consulta da API v2 permanecem para compatibilidade histórica.

Categorias, empresas e setores não possuem página individual de detalhes. Por isso, seus recursos HTTP não registram as rotas `categorias.show`, `empresas.show` e `setores.show`; os demais endpoints CRUD permanecem disponíveis.

A sidebar desses cadastros mostra Categorias e Setores com indicação da seção atual. “Voltar ao sistema” abre `/home`. Os links usam carregamento completo para manter o estado das páginas previsível.

### Comportamento e autorização

- A busca por nome usa `wire:model.live.debounce.300ms` e o parâmetro `search` na URL. Categorias mantém dez registros por página; setores permanece sem paginação. Ambos mantêm ordenação por nome.
- Criação e edição usam páginas próprias. A categoria aceita múltiplos setores por checkboxes, com a orientação de que uma categoria sem setor não fica disponível nos tickets.
- O modal de exclusão identifica o registro e seus efeitos, recebe um título acessível e foca inicialmente “Cancelar”. Escape fecha o diálogo e devolve o foco ao botão de origem. Após excluir, a listagem mantém a busca e ajusta a página, se necessário.
- Supervisores e administradores ativos podem listar, criar, editar e excluir. `AuthorizeCatalogs` consulta novamente o usuário e suas funções em cada requisição Livewire e nas ações de persistência. IDs de edição e de confirmação de exclusão são propriedades bloqueadas; os registros são consultados novamente antes da operação.
- `SaveCategoria`, `SaveSetor`, `DeleteCategoria` e `DeleteSetor` são compartilhadas por Livewire e pelos controllers HTTP. Preservam validação, transações, eventos de auditoria, pivot `categoria_setor` e o campo de compatibilidade `setor_id`.
- Excluir categorias preserva os tickets, removendo o vínculo com a categoria. Excluir setores preserva usuários e tickets, remove seus vínculos com o setor e promove outro setor associado nas categorias, quando houver.
- Setores vinculados a caixas de e-mail, inclusive inativas, não podem ser excluídos. A resposta informa que a caixa deve ser transferida para outro setor; a transação não deixa alterações parciais.
- As mensagens de sucesso ficam no componente da listagem e são substituídas a cada operação. O layout moderno mantém `showSuccess=true` como padrão; o layout dos cadastros desativa essa cópia estática com `:show-success="false"`.

### Verificação e reversão dos cadastros

Executar `php artisan test --filter=ModernCatalogsTest`, os testes existentes de categorias compartilhadas e a suíte completa em `nexodesk_testing`. A cobertura inclui HTTP e Livewire, JSON real, auditoria, perda de permissão/sessão, manipulação de IDs, validações, filtros, paginação, preservação de vínculos e bloqueio por caixa de e-mail.

Executar `npm run build` e conferir desktop/mobile, temas, teclado, foco dos modais e navegação entre os módulos. As capturas e os cenários exercitados estão em [screenshots/cadastros](screenshots/cadastros/README.md).

A reversão dessas páginas exige restaurar suas entradas e componentes em conjunto e recompilar o build. Não há migrações de banco, alterações da autenticação ou novas dependências a desfazer.

## Cadastros relacionados: empresas, contatos e usuários

As páginas de listagem, criação e edição em `/cadastros/empresas`, `/cadastros/clientes` e `/cadastros/usuarios` usam o layout moderno. A navegação compartilhada inclui Empresas e Usuários, além de Categorias e Setores. Contatos são acessados somente dentro de cada empresa; a URL da antiga listagem independente redireciona para Empresas. URLs, nomes de rotas, middlewares e endpoints HTTP públicos foram preservados.

### Regras e relacionamentos

- Empresas mantêm os dados cadastrais, CPF/CNPJ com máscara e envio sem pontuação, seleção de UF e horas contratadas. A validação existente continua compartilhada com os endpoints HTTP, inclusive a unicidade do documento. A exclusão usa confirmação e preserva contatos e tickets, removendo o vínculo com a empresa pelas regras existentes do banco.
- A edição de empresa contém a lista de seus contatos e o acesso a “Novo contato” com a empresa de origem. Esse vínculo é somente leitura na interface e bloqueado no estado Livewire; editar um contato não permite transferi-lo de empresa. Criar um contato exige empresa válida, tanto no POST quanto no Livewire; sem contexto de empresa, o GET de criação redireciona para Empresas. O contato representa o cliente que acessa o portal para acompanhar os tickets de sua empresa. O perfil é sempre `cliente`, independentemente de valores enviados pelo cliente.
- Usuários mantêm nome, e-mail, senha, perfil, setor e a opção de visualizar tickets de outros setores, válida somente para analistas. A interface não acrescenta seleção de empresa aos usuários da equipe; o vínculo já existente é preservado pelo formulário Livewire. O endpoint HTTP continua aceitando o campo opcional conforme seu contrato anterior.
- Supervisores ativos gerenciam analistas e contatos. Administradores ativos também gerenciam supervisores e administradores. `ManagePeople` reconsulta as permissões do ator e verifica o perfil do registro; `CatalogComponent` revalida a sessão e o status em cada requisição. Formulários revalidam também o registro na hidratação, e todas as ações consultam novamente o alvo antes de persistir.
- Senhas exigem oito caracteres e confirmação na criação; na edição, deixar em branco mantém a senha atual. O estado das senhas é limpo após cada tentativa. Não há envio de convites nem alteração da configuração de e-mail.
- Ativar/desativar usa modal com o nome e o estado desejado, ambos bloqueados contra alteração do cliente. Desativar encerra sessões e limpa o remember token; usuários e tickets são preservados. O usuário de ID 1 mantém sua proteção contra alteração de status. A autodesativação encerra a sessão; a troca do próprio perfil para analista retorna a `/home` por carregamento completo.
- Empresas, contatos e usuários têm busca por nome com debounce de 300 ms e `search` na URL. As listas de empresas e usuários têm dez registros por página e ordenação por nome. Usuários mostram apenas ativos por padrão e aceitam `todos` para incluir inativos; contatos mostram ambos os estados. A lista dentro da empresa permanece sem paginação. A página é ajustada após exclusão ou desativação, preservando a busca.

`SaveEmpresa`, `DeleteEmpresa`, `SavePerson` e `ChangePersonStatus` concentram validação e persistência para controllers e Livewire. As alterações usam transações e os eventos de auditoria dos modelos. Os componentes nunca chamam controllers. A varredura do Tailwind e o refresh do Vite incluem as views atuais da aplicação.

### Validação e reversão dos cadastros relacionados

`ModernRelatedCatalogsTest` cobre as telas migradas e o redirecionamento da antiga listagem independente, CRUD por HTTP e Livewire, envio JSON real, filtros, paginação, validação, senhas, perfis, IDs bloqueados, perda de sessão, alteração do próprio usuário, encerramento de sessões e preservação de contatos/tickets ao excluir empresas. Os testes anteriores de autorização, contatos, categorias e contratos de rotas/API continuam na suíte.

Executar `php artisan test` exclusivamente em `nexodesk_testing` e `npm run build`. As verificações visuais e capturas estão em [screenshots/relacionados](screenshots/relacionados/README.md).

Para reverter, reverter o commit que contiver esta entrega e reconstruir os assets modernos. Não há migrações, novas dependências ou configuração de e-mail a desfazer. Se a entrega for agrupada com a migração de categorias/setores no mesmo commit, sua reversão também abrangerá esses cadastros; separar os commits permite reversão independente.

## Minha conta

Capturas e cenários de conferência visual: [screenshots/minha-conta](screenshots/minha-conta/README.md).

`/minhaconta` usa o layout moderno e o componente `App\Livewire\Modern\Account\Settings`, com formulários independentes para nome/e-mail, senha e preferências de notificação. O menu do usuário nas páginas modernas inclui “Minha conta”. Clientes e analistas acessam sua própria conta sem links administrativos; supervisores e administradores também têm os atalhos de cadastros.

`App\Actions\Account\UpdateAccount` compartilha validação e persistência com os PUTs existentes. As URLs, mensagens e redirecionamentos HTTP permanecem preservados. O componente reconsulta o usuário ativo em cada requisição e bloqueia a submissão se a sessão passar a pertencer a outra conta. Não aceita um identificador de usuário para decidir quem será atualizado.

A senha atual é obrigatória para alterar a senha, e a nova senha exige oito caracteres e confirmação. Os três campos de senha são limpos após cada tentativa. As instruções ficam acima dos campos de nova senha e confirmação para preservar o alinhamento. A edição do perfil retorna por carregamento completo à própria página para atualizar também o nome no menu. Senha e preferências exibem retorno reativo em suas seções.

As oito opções de notificação preservam os quatro eventos e os canais central interna/e-mail. Sem preferência persistida, todas começam habilitadas, como antes. Salvar a preferência não altera a configuração de envio de e-mail. Minha Conta usa exclusivamente os assets gerados pelo Vite.

Validar com `php artisan test --filter=ModernAccountTest`, suíte completa em `nexodesk_testing` e `npm run build`. A cobertura inclui os dois transportes, JSON real, permissões, validação de perfil e senha, preferências desmarcadas, sessão expirada, usuário inativo e tentativa de troca de conta. Para reverter, reverter o commit da entrega e recompilar os assets modernos; não há migração de banco nem dependências adicionais.

## Relatórios modernos

Os relatórios ativos `/relatorios/horas` e `/relatorios/analista` usam o layout moderno e componentes Livewire. Os nomes das rotas, middleware de supervisor/administrador e links dos tickets foram preservados. Os controllers GET continuam como entradas das views; `CompanyReport` e `AnalystReport`, em `App\Actions\Reports`, concentram validação, consultas e séries para uso no carregamento inicial e nas submissões Livewire.

O relatório por empresa mantém período, empresa, setor opcional e a opção “Desconsiderar tickets abertos”. A tabela segue a regra anterior: com a opção habilitada, lista tickets fechados no período; desabilitada, inclui tickets criados ou fechados no período. Exibe total de tickets e soma de `horas_gastas`. O gráfico diário mantém contagens de tickets abertos e finalizados para a empresa e o setor selecionados.

O relatório por analista mantém período e usuário da equipe. A tabela lista tickets atribuídos ao usuário e criados no período, com empresa, status e percentual de SLA. Os indicadores mostram total, abertos e fechados. O gráfico registra criações, finalizações, transferências e tickets assumidos pelo usuário, agrupados por hora em períodos de até 24 horas e por dia acima disso. O cálculo visual de SLA usa minutos decorridos positivos por meio de `ElapsedTime`.

Os filtros usam `wire:submit`, mostram erros por campo e ficam sincronizados com a URL para permitir recarregar ou compartilhar a consulta. Antes da primeira consulta há um estado de orientação; resultados sem tickets possuem estado vazio próprio. O usuário ativo e suas permissões são revalidados em cada requisição. Os filtros efetivamente aplicados ficam bloqueados no estado Livewire para que mudanças nos controles só afetem o resultado depois de “Gerar relatório”.

Os gráficos são SVGs locais, sem Chart.js, adaptadores de data ou CDN. Cada gráfico inclui descrição acessível, títulos nos pontos e uma tabela expansível com os valores. O tema acompanha claro/escuro/sistema. A impressão exclui navegação, filtros e ações, mantendo identificação, período, indicadores, gráficos e tabela. A visualização mobile mantém os filtros empilhados e as tabelas com rolagem contida.

Executar `php artisan test --filter=ModernReportsTest`, a suíte completa em `nexodesk_testing` e `npm run build`. A cobertura verifica os dois relatórios por HTTP e Livewire, envelope JSON real, permissões, sessão expirada, referências inválidas, períodos, setor, opção de tickets abertos, horas, séries de atividade e SLA. Capturas e cenários visuais estão em [screenshots/relatorios](screenshots/relatorios/README.md).

Para reverter, reverter o commit da entrega e reconstruir os assets modernos. Não há migrações de banco, dependências novas ou alterações nos tickets e APIs a desfazer.

## Listagens modernas de tickets

As rotas staff `/tickets`, `/tickets/my` e `/tickets/pendentes` preservam URLs, nomes e middlewares e agora montam o componente `App\Livewire\Modern\Tickets\TicketIndex`. Os GETs permanecem no `TicketController` como entradas pequenas das views. Criação, detalhes e edição também usam a stack moderna e recebem `return_to` apontando para a listagem e seus filtros atuais. A sidebar apresenta Geral, Criar ticket, Meus tickets e Pendentes nessa ordem; no portal do cliente, Criar ticket aparece antes de Meus tickets.

`App\Actions\Tickets\TicketListing` concentra as consultas. A lista geral mantém busca por ID ou assunto, filtros de setor, categoria e empresa, inclusão de fechados, dez registros por página e ordenação por criação, modificação ou maior percentual de SLA. Uma busca iniciada sem o parâmetro `showClosed` inclui fechados, como antes. “Meus tickets” mostra somente os atribuídos ao usuário autenticado e oculta fechados por padrão. “Pendentes” mantém o significado legado de tickets sem categoria e não fechados.

Analistas sem a permissão de visualizar outros setores continuam limitados ao próprio setor em Geral e Pendentes; supervisores, administradores e analistas com essa opção veem todos os setores. `AuthorizeTicketLists` reconsulta o usuário autenticado, ativo e com perfil staff em cada requisição Livewire. O tipo da listagem e o alvo de exclusão são bloqueados no estado do componente e os registros são consultados novamente antes da operação.

A exclusão continua visível somente para administradores e foi extraída para `DeleteTicket`, compartilhada pelo Livewire e pelo endpoint DELETE existente. O ticket e seus vínculos são removidos em transação; anexos correspondentes são removidos do disco público. Não houve alteração das APIs.

Executar `php artisan test --filter=ModernTicketListingsTest`, a suíte completa em `nexodesk_testing` e `npm run build`. Conferir as três listas em desktop e mobile, temas claro/escuro/sistema, teclado, filtros e paginação, além das transições para criação, detalhes, edição e retorno à listagem. Para reverter, restaurar as entradas e componentes em conjunto e reconstruir os assets; não há migrações nem dependências novas.

## Fluxo moderno de tickets

As rotas staff de criação, detalhes e edição mantêm os contratos REST existentes e usam `TicketForm` e `TicketShow`, em `App\Livewire\Modern\Tickets`. As views `tickets.create`, `tickets.show` e `tickets.edit` são entradas pequenas do layout moderno. Não há `wire:navigate`; a navegação entre páginas usa carregamento completo.

`SaveTicket`, `AssumeTicket`, `TransferTicket`, `FinalizeTicket`, `FollowTicket` e `UpdateTimelinePreference` concentram as mutações compartilhadas entre Livewire e os controllers HTTP. Criação e atualização preservam a associação histórica entre categoria e setor, validam analistas ativos do setor escolhido e usam transação. Contato, empresa, setor, categoria e analista usam comboboxes pesquisáveis por teclado, preservando o preenchimento automático da empresa e os filtros dependentes de setor. Na criação, o uploader acumula até cinco anexos escolhidos em uma ou mais seleções, mostra cada arquivo e permite removê-lo antes do envio. Anexos de abertura são públicos; anexos de respostas públicas continuam no disco público; anexos de notas internas ficam no disco local privado. Falhas após armazenamento removem os arquivos já gravados.

A tela de detalhes reúne dados do ticket, anexos, timeline paginada, preferência por exibir apenas conversas, seguidores e todas as ações de atendimento. Respostas públicas podem alterar o ticket para “Pendente cliente” ou “Pendente analista”. Notas internas não alteram status, aceitam menções somente a integrantes ativos que podem visualizar o ticket e não ficam visíveis no portal do cliente. Download de anexo verifica novamente o ticket, o vínculo do arquivo e a permissão do usuário.

No card de dados, “Voltar” fica isolado à esquerda e as ações operacionais ficam agrupadas à direita. Horas gastas aparecem somente em tickets fechados. Seguir/deixar de seguir pertence ao cabeçalho do histórico, acima da preferência da timeline. A timeline exibe primeiro os três eventos mais recentes, sem rolagem interna, e permite carregar os anteriores em grupos de dez pelo botão compacto “Exibir mensagens mais antigas”, alinhado à esquerda.

A composição de mensagens fica em um card próprio, próxima ao legado: textarea, anexos, botão dividido para resposta pública e mudança opcional para Pendente Cliente ou Pendente Analista, além do botão separado de nota interna. Digitar `@` no texto apresenta integrantes autorizados, e a seleção completa a menção; menções continuam restritas às notas internas. Os uploaders da criação e das mensagens seguem o padrão visual de dropzone e lista de arquivos do Flux, aceitam até cinco anexos acumulados em uma ou mais seleções e permitem remoção individual antes do envio, sem depender do componente Pro.

Assumir define o usuário atual como responsável e exige setor/categoria compatíveis. Transferir exige categoria disponível no setor de destino e aceita deixar o ticket sem analista. Finalizar exige categoria, relato final e tempo em horas/minutos; a sugestão continua baseada nas interações não internas e no SLA de atualização. Todas essas operações registram os eventos de sistema e auditoria já existentes. Editar permanece disponível visualmente para supervisores e administradores, e excluir somente para administradores.

O componente reconsulta o usuário ativo e o ticket autorizado em todas as requisições Livewire. O identificador do ticket e a URL de retorno ficam bloqueados, e o registro é consultado novamente antes de cada mutação. `ModernTicketFlowTest` cobre entradas modernas, criação, edição histórica, mensagens, menções, anexos públicos/privados, seguidores, ações, identidade bloqueada e download seguro. Executar esse teste, a suíte completa em `nexodesk_testing`, `npm run build` e o QA de desktop/mobile, temas e teclado antes da entrega.

## Portal moderno de tickets do cliente

As rotas existentes em `/tickets/cliente` usam o layout moderno e os componentes `ClientTicketIndex`, `ClientTicketCreate` e `ClientTicketShow`, em `App\Livewire\Modern\ClientTickets`. URLs, nomes de rota, middlewares e endpoints POST foram preservados. As mutações HTTP e Livewire compartilham `CreateClientTicket`, `ReplyToClientTicket` e `FinalizeClientTicket`; a finalização permanece disponível apenas pelo endpoint de compatibilidade, sem ação visível no portal.

A listagem começa nos tickets do próprio contato, permite alternar para todos os tickets da mesma empresa e pesquisar por ID ou assunto. Contatos sem empresa continuam limitados aos próprios tickets. A autorização reconsulta o usuário ativo com perfil cliente em cada requisição e restringe detalhes e mutações à empresa autenticada; o identificador do ticket e a URL de retorno são bloqueados no estado Livewire.

Na criação, contato e empresa são derivados da sessão e exibidos como dados somente leitura. Setor usa o mesmo seletor pesquisável do fluxo staff. O uploader moderno acumula até cinco arquivos escolhidos em seleções sucessivas, aceita remoção individual e grava anexos públicos vinculados ao novo ticket.

Os detalhes mostram horas gastas e relato final somente quando o ticket está fechado. O histórico expõe apenas mensagens públicas, em ordem da mais recente para a mais antiga, começa com três itens e carrega os anteriores em grupos de dez pelo botão alinhado à esquerda. Notas internas e eventos de sistema nunca são enviados para a view do cliente. A composição fica em card próprio, aceita texto ou anexos múltiplos e, ao responder, altera o status para “Pendente analista”. Tickets fechados não exibem o compositor.

`ModernClientTicketFlowTest` cobre entradas modernas, escopo pessoal/empresa, pesquisa, criação e resposta com anexos acumulados, privacidade do histórico, carregamento progressivo, revalidação de acesso, identidade bloqueada e apresentação de tickets fechados. Executar também `ClienteTicketSearchTest`, a suíte completa em `nexodesk_testing`, `npm run build` e o QA visual em desktop/mobile e temas claro/escuro. Não há migrações nem dependências novas; a reversão consiste em reverter a entrega e recompilar os assets modernos.

## Visão Geral moderna da staff

Para analistas, supervisores e administradores, `/home` monta `App\Livewire\Modern\Overview\StaffOverview` no layout moderno. A URL, o nome `home`, os middlewares e o endpoint `/tickets/atencao` foram preservados; o controller escolhe a entrada correspondente ao perfil sem transportar assets antigos. Clientes recebem sua entrada moderna própria e usuários sem um perfil reconhecido recebem HTTP 403. O antigo `HomeController`, a view genérica `home.blade.php` e suas consultas auxiliares foram removidos, portanto não existe mais fallback para o painel legado.

Os quatro indicadores mantêm as regras anteriores: tickets abertos atribuídos ao usuário, tickets abertos em seu setor, tickets não assumidos no setor e tickets atribuídos que ultrapassaram o SLA de atualização. Os dois últimos podem ser expandidos para acessar diretamente os IDs envolvidos. Analistas recebem somente os indicadores; supervisores e administradores também recebem o quadro por status. Consultas do quadro nem sequer são executadas para analistas.

O quadro mantém as colunas Aberto, Pendente cliente, Pendente analista e Fechado, com rolagem interna por coluna e limite de 100 fechados. Cada card mostra empresa, assunto, categoria e analista, e usa o botão azul-claro para os detalhes. A URL de retorno aceita `/home` e preserva os filtros do painel ao abrir um ticket.

Supervisores podem ordenar o quadro por tickets mais novos ou antigos. Administradores também filtram por analista, setor e empresa usando seletores pesquisáveis. Cada alteração atualiza o Kanban imediatamente, sem botão de aplicação. Os filtros ficam sincronizados na URL, podem ser limpos em conjunto e respeitam a limitação de setor dos analistas onde ela se aplica. O usuário autenticado, ativo e com perfil staff é reconsultado em todas as requisições Livewire.

`ModernStaffOverviewTest` cobre a separação entre as entradas staff/cliente, indicadores, alerta de SLA, endpoint de atenção, filtros, ordenação, retorno seguro, diferenças de perfil e perda de autorização. A reversão consiste em reverter a entrega e recompilar os assets modernos; não há migrações ou dependências novas.

## Visão Geral moderna do cliente

Clientes também usam `/home` com uma entrada própria, `home-client`, e o componente `App\Livewire\Modern\Overview\ClientOverview`. O painel mantém os quatro indicadores do legado: tickets pessoais ativos, tickets ativos da empresa, tickets da empresa aguardando resposta e tickets fechados da empresa. Cada indicador permite expandir os IDs acessíveis e abrir os detalhes com retorno à Visão Geral.

O escopo pessoal usa `cliente_id`, igual à listagem moderna, inclusive para tickets abertos pela equipe em nome do contato. Os demais indicadores são limitados à empresa autenticada; clientes sem empresa veem somente seus tickets pessoais. O componente revalida papel e status em toda requisição Livewire. A navegação do portal exibe “Visão geral” acima da categoria Tickets e a ação principal abre um novo ticket preservando o retorno para `/home`.

`ModernClientOverviewTest` cobre entrada e assets modernos, ordem da navegação, métricas pessoais e corporativas, isolamento entre empresas, clientes sem empresa e perda de autorização. Executar também `ModernStaffOverviewTest`, a suíte completa no banco `nexodesk_testing` e `npm run build` antes da entrega.

## Central moderna de notificações

O layout moderno compartilhado agora apresenta um sino ao lado do perfil na sidebar desktop e no canto direito do cabeçalho móvel. O contador destaca notificações não lidas e é atualizado a cada minuto; quando a contagem aumenta, um aviso breve aparece sem interromper o trabalho. O painel lateral mostra as 20 notificações mais recentes, permite abrir o ticket relacionado e marcar todas como lidas.

A rota existente `/notificacoes` preserva o envelope JSON quando a requisição pede JSON, mantendo o contrato para consumidores programáticos. Em navegação comum, ela abre a página moderna completa, paginada em 15 itens, com filtro de todas ou somente não lidas e ações individuais e em lote. Nenhuma migração ou dependência adicional foi necessária.

As URLs persistidas dentro da notificação não são usadas diretamente. Ao abrir um item, o destino é reconstruído a partir do ticket e do perfil autenticado, usando a rota staff ou cliente correspondente. Leituras individuais são sempre limitadas às notificações do próprio usuário; conta ativa e autorização são revalidadas em cada requisição Livewire.

Executar `php artisan test --filter=ModernNotificationCenterTest`, a suíte completa e `npm run build`. A cobertura verifica layouts staff/cliente, compatibilidade JSON, limite do painel, paginação, filtro, ações de leitura, isolamento entre contas, destino seguro e perda de autorização.

## Administração moderna

As quatro rotas administrativas existentes usam agora o layout moderno: usuários logados, caixas de e-mail, auditoria e backups. A sidebar apresenta uma seção Administração somente para administradores, com indicação da página ativa. Essas páginas não exibem a ação genérica de criação de ticket.

A navegação moderna é centralizada em dois componentes independentes. `modern.staff.navigation` é compartilhado por Visão geral, Tickets, Cadastros, Relatórios, Minha conta, Notificações e Administração; `modern.client.navigation` permanece restrito ao portal do cliente. Analistas veem Visão geral e Tickets, supervisores recebem também Cadastros e Relatórios, e administradores recebem ainda Administração. Assim, tipografia, espaçamento, ordem e estado ativo permanecem idênticos entre páginas do mesmo perfil.

Usuários logados lista perfil, IP, navegador e última atividade, ordenando pelas sessões mais recentes, e solicita confirmação antes de encerrar uma sessão. Caixas de e-mail mantém o CRUD HTTP existente em formulários e modais modernos, incluindo setor padrão e estado ativo. Auditoria preserva a paginação e apresenta valores anteriores e novos em modal. Backups continua expondo apenas o nome seguro do arquivo e habilita download somente para registros bem-sucedidos.

As URLs, nomes de rota, middlewares administrativos e regras de download foram preservados. Não há migrações nem dependências novas. Executar `php artisan test --filter=ModernAdministrationTest`, `php artisan test --filter=BackupSecurityTest`, a suíte completa e `npm run build`.

## Páginas de erro modernas

As respostas HTML 401, 403, 404, 419, 429, 500 e 503 usam um layout público próprio do Nexo Desk, com favicon, tema claro/escuro, tipografia e ações consistentes com a interface moderna. Fallbacks `4xx` e `5xx` cobrem os demais códigos sem retornar ao template genérico do framework. As ações levam ao login, à visão geral ou permitem tentar novamente conforme o erro. No 403, uma conta autenticada sem perfil suportado pode encerrar a sessão sem entrar em um ciclo entre login e visão geral. A página 419 mantém o código HTTP, explica que a sessão expirou e oferece a entrada no sistema sem redirecionamento automático.

O `Handler` não intercepta mais esses códigos. O tratamento padrão do Laravel seleciona as views e preserva a negociação de conteúdo, portanto requisições que esperam JSON continuam recebendo o envelope JSON correspondente em vez de HTML. `ModernErrorPagesTest` cobre os status específicos, os fallbacks, ausência de AdminLTE e scripts de redirecionamento, ações de recuperação e negociação JSON. Não há migrações nem dependências novas; executar o teste focado, a suíte completa, `php artisan view:cache` e `npm run build`.

## Remoção de arquivos legados órfãos

Foram removidos o controller e as três views da antiga administração web de grupos, sem alterar o modelo, as associações históricas ou o endpoint de consulta da API v2. O instalador web antigo também foi excluído: ele não possuía rotas registradas, e a criação inicial segura de administradores permanece disponível pelo comando de console dedicado.

Também foram excluídos oito componentes Blade baseados em Bootstrap que não tinham consumidores (`action-menu`, `attachment-uploader`, `empty-state`, `filter-bar`, `form-actions`, `page-header`, `sla-indicator` e `status-badge`) e os layouts sem uso `layouts.app` e `layouts.notification-center`. Os templates inativos de cadastro e verificação foram retirados posteriormente junto ao Laravel UI. Os overrides do AdminLTE foram removidos na etapa seguinte.

`LegacyOrphansRemovedTest` impede a reintrodução desses arquivos e das rotas do instalador ou do CRUD web de grupos. A verificação inclui ainda cache de views, testes de autenticação e componentes modernos, suíte completa e build Vite; não há migrações ou dependências alteradas nesta etapa.

## Remoção do AdminLTE

O pacote Composer `jeroennoten/laravel-adminlte` e sua dependência exclusiva `almasaeed2010/adminlte` foram removidos. Também saíram `config/adminlte.php`, os overrides e traduções publicados, os assets em `public/vendor` e os inicializadores de tema que existiam apenas para o layout antigo. A configuração global `Paginator::useBootstrap()` foi retirada; as listagens modernas usam o paginador do Flux.

Os testes que validavam o sidebar e o tema do AdminLTE foram substituídos por `AdminLteRemovedTest`, que verifica a ausência do pacote, configuração, views, traduções, assets e referências Blade. Os testes funcionais continuam verificando que nenhuma resposta moderna transporta AdminLTE, jQuery ou Bootstrap.

## Remoção do Laravel Mix

O Vite passou a ser o único pipeline de frontend. Foram excluídos `webpack.mix.js`, os fontes antigos em `resources/js/app.js`, `resources/js/bootstrap.js` e `resources/sass`, os bundles em `public/js` e `public/css` e o manifesto `public/mix-manifest.json`.

As dependências npm exclusivas do pipeline anterior — Laravel Mix, Bootstrap, Popper, Axios, Lodash, Sass, loaders e `concurrently` — também foram retiradas. O `package.json` expõe somente `npm run dev` para o servidor com HMR e `npm run build` para produção. `LaravelMixRemovedTest` protege essa configuração e impede a reintrodução dos arquivos e pacotes removidos.

## Remoção do Laravel UI

O pacote `laravel/ui` foi removido após a substituição de `Auth::routes()` por rotas explícitas. Os controllers de login, logout, recuperação, redefinição e confirmação de senha agora usam diretamente as Actions da aplicação e recursos nativos do Laravel 12, preservando URLs, nomes de rota, middlewares e respostas HTML/JSON.

O limite de cinco tentativas de login por minuto continua compartilhado entre HTTP e Livewire por e-mail/IP, agora implementado diretamente em `App\Actions\Auth\Login` com o `RateLimiter` do framework. Os controllers e templates inativos de cadastro público e verificação de e-mail foram excluídos. `LaravelUiRemovedTest` protege a ausência do pacote, do macro de rotas, dos traits antigos e desses artefatos desativados; `ModernAuthenticationTest` mantém a cobertura funcional completa.

## Auditoria final da migração

O método `MensagemController::index`, sem rota e dependente de uma view inexistente, foi removido. A documentação operacional não orienta mais transições para telas antigas, e `LegacyAssetsRemovedTest` faz uma verificação transversal das rotas, views, referências literais dos controllers, dependências, fontes e assets públicos.

A API original em `/api`, os endpoints de compatibilidade ainda documentados e os modelos ou campos históricos permanecem deliberadamente fora da remoção visual. Excluí-los alteraria contratos externos ou dados persistidos e exige uma decisão de versão própria. A nomenclatura interna `Modern` também permanece por organização arquitetural e não carrega dependências da interface retirada.
