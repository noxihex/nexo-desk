# Cadastros relacionados — validação visual

Capturas com dados fictícios no banco exclusivo `nexodesk_testing`, em navegador local (desktop 1280 × 720 e mobile 390 × 844).

- [Empresa em tema escuro](empresa-dark.png): dados cadastrais, máscara de CPF/CNPJ e início da seção de contatos.
- [Confirmação de status](usuario-modal-dark.png): título acessível e foco inicial em Cancelar. Escape fecha o diálogo e devolve o foco à ação de origem.
- [Edição de usuário em tema claro](usuario-light.png): senha opcional, perfil, setor e permissão entre setores.
- [Usuários no mobile](usuarios-mobile.png): menu recolhido, filtros e tabela com rolagem horizontal dentro do card.
- [Novo usuário no mobile](usuario-form-mobile.png): campos empilhados, labels e foco visível. O restante do formulário segue abaixo da captura.

Exercitados no navegador: criar contato pela empresa e retornar ao mesmo cadastro com mensagem de sucesso; editar usuário mantendo senha e alterando a permissão entre setores; abrir/cancelar o modal por teclado; alternar temas; navegar para `/home` com carregamento completo. Em `/home`, foram conferidos os assets Mix, Bootstrap, AdminLTE e jQuery, sem assets modernos transportados da página anterior.

A suíte automatizada complementa a conferência visual com renderização das nove páginas e os casos de autorização, persistência, filtros e vínculos.

## Ajustes de perfis, ações e senhas

- [Cores dos perfis e desativação](usuarios-cores.png): administrador vermelho, analista verde; supervisor usa amarelo no mesmo componente de badge.
- [Confirmação de desativação vermelha](usuario-modal-vermelho.png).
- [Senhas alinhadas](senhas-alinhadas.png): a instrução fica acima da linha dos dois campos no formulário compartilhado por usuários e contatos, tanto na criação quanto na edição. Conferido no navegador: ambos os inputs começam na mesma coordenada vertical.
