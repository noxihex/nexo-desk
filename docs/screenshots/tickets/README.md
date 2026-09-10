# Tickets — validação visual

Conferência realizada no navegador local com dados de demonstração já existentes:

- Lista Geral em desktop, nos temas escuro e claro: navegação, filtros, contador, card, badges e ações.
- Cards compactos com data completa e ações nas cores azul-claro, amarela e vermelha, próximas ao legado.
- Busca reativa por assunto, limpeza de filtros e sincronização com a URL.
- “Meus tickets” em desktop, incluindo a alternância de tickets fechados e a atualização da URL.
- “Pendentes” em desktop e seu estado vazio.
- Lista Geral e Pendentes em 390 × 844: filtros empilhados, cards, estado vazio e ações sem rolagem horizontal da página.
- Sidebar mobile: foco inicial em “Fechar menu”, fechamento por Escape e retorno do foco ao botão de abertura.
- Modal de exclusão: identificação do ticket, ação destrutiva vermelha, foco inicial em “Cancelar” e fechamento por Escape.
- Criação, detalhes e edição no stack moderno, em desktop e em 390 × 844, sem rolagem horizontal da página.
- Criação e mensagens com dropzone de anexos, seleção múltipla acumulativa e remoção individual; detalhes com histórico e ações de assumir, transferir, finalizar, seguir e excluir.
- Criação e edição com busca por texto nos seletores de contato, empresa, setor, categoria e analista, inclusive com nomes acentuados.
- Modal de finalização no mobile: foco inicial em “Cancelar”, fechamento por Escape e retorno do foco a “Finalizar”.
- Ações do ticket agrupadas à direita, com “Voltar” isolado à esquerda; seguir/deixar de seguir no cabeçalho do histórico.
- Composição no rodapé do histórico, inspirada no legado: textarea, botão dividido de resposta/status e botão separado de nota interna, sem modal.
- Menções acionadas por `@` dentro do texto, com sugestões de integrantes autorizados e inserção do nome selecionado.
- Histórico em ordem decrescente, inicialmente limitado aos três eventos mais recentes e com carregamento progressivo dos anteriores.
- Horas gastas ocultas para tickets ainda não fechados.
- Uploader em formato de dropzone, com progresso, três arquivos acumulados em duas seleções, lista e controles de remoção conferidos em desktop; composição completa conferida no rodapé mobile.
- Formulários e detalhes conferidos nos temas claro e escuro.
- Transição entre o stack moderno e a visão geral legada, com retorno aos detalhes por carregamento completo.
- Links de criação, detalhes e edição preservando `return_to`, inclusive depois de filtros Livewire.
- Portal do cliente em desktop: lista pessoal, alternância para tickets da empresa, busca, criação e detalhes; os mesmos breakpoints responsivos do layout moderno são cobertos pelos componentes compartilhados.
- Criação do cliente com setor pesquisável e uploader acumulativo; resposta pública em card próprio com múltiplos anexos.
- Histórico do cliente somente com mensagens públicas, três itens iniciais em ordem decrescente e carregamento dos anteriores de dez em dez.
- Ticket fechado no portal do cliente com horas e relato final, sem compositor de nova mensagem.

As capturas foram inspecionadas durante o QA no navegador. Nenhum arquivo binário novo foi persistido no worktree nesta execução.

Validação automatizada: `ModernTicketFlowTest`, `ModernTicketListingsTest` e `ModernClientTicketFlowTest`; suíte completa com 227 testes e 1847 asserções; `npm run build:modern` concluído com sucesso.
