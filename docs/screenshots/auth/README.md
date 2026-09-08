# Autenticação moderna — validação visual

Capturas feitas no navegador com build Vite de produção. Os dados exibidos são fictícios; login e confirmação foram exercitados com um usuário exclusivo de `nexodesk_testing`, com envio de e-mail desativado. Os templates inativos foram renderizados somente por um servidor temporário de QA, sem adicionar rotas ao projeto.

- [Login em desktop, tema claro](login-light.png)
- [Login em desktop, tema escuro](login-dark.png)
- [Login em mobile, 390 px](login-mobile.png)
- [Recuperação desativada em mobile](recovery-mobile.png)
- [Redefinição em mobile](reset-mobile.png)
- [Confirmação em mobile](confirm-mobile.png)
- [Template de cadastro inativo](register-mobile.png) e [continuação](register-mobile-bottom.png)
- [Template de verificação inativo](verify-mobile.png)

Login e confirmação redirecionaram para `/home` com carregamento completo; a página legada carregou somente seus scripts de jQuery, Bootstrap, AdminLTE e Mix. Também foram conferidos labels, foco por teclado, alternância de visibilidade da senha, mensagens reativas e ausência de rolagem horizontal em 390 px.
