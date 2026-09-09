# Listagens de tickets — validação visual

Conferência realizada no navegador local com dados de demonstração já existentes:

- Lista Geral em desktop, nos temas escuro e claro: navegação, filtros, contador, card, badges e ações.
- Cards compactos com data completa e ações nas cores azul-claro, amarela e vermelha, próximas ao legado.
- Busca reativa por assunto, limpeza de filtros e sincronização com a URL.
- “Meus tickets” em desktop, incluindo a alternância de tickets fechados e a atualização da URL.
- “Pendentes” em desktop e seu estado vazio.
- Lista Geral e Pendentes em 390 × 844: filtros empilhados, cards, estado vazio e ações sem rolagem horizontal da página.
- Sidebar mobile: foco inicial em “Fechar menu”, fechamento por Escape e retorno do foco ao botão de abertura.
- Modal de exclusão: identificação do ticket, ação destrutiva vermelha, foco inicial em “Cancelar” e fechamento por Escape.
- Transição da lista moderna para os detalhes legados e retorno à mesma listagem por carregamento completo.
- Links de criação, detalhes e edição preservando `return_to`, inclusive depois de filtros Livewire.

As capturas foram inspecionadas durante o QA no navegador. Nenhum arquivo binário novo foi persistido no worktree nesta execução.

Validação automatizada: `ModernTicketListingsTest` com nove testes e 118 asserções; suíte completa com 210 testes e 1684 asserções; `npm run build:modern` e compilação das views Blade concluídos com sucesso.
