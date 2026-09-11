# Visão Geral — validação visual

Conferência realizada no navegador local com dados temporários identificados e removidos ao final:

- Administrador em desktop nos temas claro e escuro: indicadores, filtros pesquisáveis e quatro colunas do quadro.
- Expansão do indicador de tickets que requerem atenção e acesso ao ID exibido.
- Pesquisa e aplicação do filtro de empresa com sincronização em `/home?empresa=...`.
- Abertura dos detalhes e retorno à Visão Geral com o filtro preservado.
- Layout em largura mobile: sidebar recolhida, indicadores em grade compacta, filtros empilhados e colunas do quadro empilhadas sem rolagem horizontal da página.
- Supervisor com quadro completo e somente o filtro de ordenação, e analista somente com os quatro indicadores, validados pela suíte automatizada.
- Transição convencional entre Visão Geral e páginas modernas de tickets.

As capturas foram inspecionadas durante o QA e não foram persistidas no worktree.

Validação automatizada: `ModernStaffOverviewTest` com cinco testes; suíte completa com 232 testes e 1876 asserções; `npm run build` concluído com sucesso.
