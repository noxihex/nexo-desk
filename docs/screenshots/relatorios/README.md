# Relatórios — validação visual

Capturas feitas com dados fictícios no banco exclusivo `nexodesk_testing`:

- [Relatório por empresa no tema escuro](empresa-escuro.png): filtros, período, totais e ação de impressão.
- [Gráfico por empresa](grafico-empresa-escuro.png): série diária e tabela acessível com os mesmos valores.
- [Relatório por analista no tema escuro](analista-escuro.png): filtros e indicadores de tickets.
- [Relatório por analista no tema claro](analista-claro.png): segunda aparência da mesma tela.
- [Relatório por analista no mobile](analista-mobile-escuro.png): filtros empilhados em 390 × 844.

Foram conferidos no navegador os dois relatórios, filtros sincronizados com a URL, temas claro/escuro, gráficos SVG, tabela de dados do gráfico, totais, badges de status/SLA, abertura da impressão e retorno ao sistema legado. No mobile, a largura do documento permaneceu igual à viewport de 390 px; tabelas extensas rolam dentro do card.

A impressão remove sidebar, cabeçalho móvel, filtros, botões e coluna de ações; preserva identificação, período, totais, gráficos e resultados em fundo branco. Os links de impressão não substituem as rotas normais dos tickets.

Validação automatizada: `ModernReportsTest` com oito testes e 72 asserções, suíte completa com 201 testes e 1577 asserções, além de `npm run build:modern`.
