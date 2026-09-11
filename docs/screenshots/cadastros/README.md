# Cadastros modernos — validação visual

Capturas com build Vite de produção e registros fictícios exclusivos de `nexodesk_testing`. As interações foram executadas como supervisor; a suíte também cobre administrador, analista, cliente, usuário inativo e sessão expirada.

- [Categorias no desktop](categorias-desktop.png) e [busca em mobile](categorias-mobile.png)
- [Edição de categoria](categoria-edit-desktop.png)
- [Criação de categoria em mobile](categoria-create-mobile.png) e [seleção de setores](categoria-create-mobile-bottom.png)
- [Setores no desktop](setores-desktop.png)
- [Novo setor](setor-create-desktop.png)
- [Edição de setor](setor-edit-desktop.png)
- [Exclusão bloqueada por caixa de e-mail](setor-exclusao-bloqueada.png)
- [Setores no tema escuro](setores-dark.png)
- [Setores em mobile](setores-mobile-dark.png)
- [Sidebar em mobile](sidebar-mobile.png)

Conferidos: paginação de categorias; busca sincronizada com a URL e retorno à primeira página; criação e edição; atualização dos setores selecionados; exclusão de um setor fictício com a busca preservada; mensagem de bloqueio por caixa de e-mail; foco inicial em Cancelar, Escape e retorno ao botão de origem; sidebar mobile e ausência de rolagem horizontal da página em 390 px.

O link “Voltar ao sistema” foi conferido: `/home` carregou a visão geral moderna com Livewire e assets Vite, sem jQuery, Bootstrap ou AdminLTE.
