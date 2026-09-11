# Minha conta — validação visual

Capturas com dados fictícios em `nexodesk_testing`:

- [Desktop claro](desktop-claro.png): perfil, troca de senha e preferências.
- [Desktop escuro](desktop-escuro.png): aparência alternativa da mesma tela.
- [Mobile escuro](mobile-escuro.png): trecho dos formulários de perfil e senha, com campos empilhados em 390 × 844.

Conferidos no navegador: salvar uma preferência desmarcada, retorno de sucesso, persistência após recarregar, menu Minha conta e troca de tema. A largura total do documento no mobile foi de 390 px, igual à viewport, sem transbordamento horizontal. Os campos de nova senha e confirmação compartilham a mesma linha no desktop e instrução acima de ambos.

A suíte completa passou com 193 testes e 1515 asserções. `ModernAccountTest` inclui oito testes de renderização, perfil, senha, preferências, sessão, status, isolamento entre contas e requisição JSON real do Livewire. Build Vite concluído.
