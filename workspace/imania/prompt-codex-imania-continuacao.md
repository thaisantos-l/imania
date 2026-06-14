Você está continuando o projeto WordPress/WooCommerce da loja Imania.

OBJETIVO DESTA CONTINUIDADE

- Dar sequência na implementação frontend da etapa “Design System + Home dinâmica” no tema custom.
- NÃO perder o que já foi feito.
- NÃO fazer commit/push sem comando explícito do usuário.

CONTEXTO DE BRANCH E STATUS

- Branch atual: feat/theme-front-end
- Working tree com alterações locais (ainda sem commit).
- Git status atual:
  - M wp-content/themes/imania-store/footer.php
  - M wp-content/themes/imania-store/functions.php
  - M wp-content/themes/imania-store/header.php
  - ?? wp-content/themes/imania-store/README.frontend.md
  - ?? wp-content/themes/imania-store/assets/
  - ?? wp-content/themes/imania-store/front-page.php
  - ?? wp-content/themes/imania-store/inc/home.php
  - ?? wp-content/themes/imania-store/template-parts/home/

ESCOPO E REGRAS DEFINIDAS PELO USUÁRIO

- Bootstrap: usar apenas para GRID (container/row/col).
- CSS e JS: 100% custom, baseados no Figma.
- Home deve ser dinâmica, sem mock/placeholder.
- Frontend deve consumir dados reais WooCommerce.
- Regra de preço vem do plugin custom imania-pricing-engine.
- Tema não recalcula preço; apenas renderiza get_price_html() etc.
- Não alterar core WP/Woo/plugins terceiros.

FIGMA (já validado)

- Arquivo: sJ2174W7zOb6TSAnAeynKG
- Design System: node 0:436
- Home: node 0:435
- Observação: já houve limitação de chamadas MCP Starter em leituras profundas; screenshots/contexto básico foram acessados.

O QUE JÁ FOI IMPLEMENTADO

1. Fundação de frontend no tema

- Enqueue de:
  - Google Fonts (Raleway)
  - Bootstrap Grid CDN
  - CSS custom: assets/css/imania-theme.css
  - JS custom: assets/js/imania-theme.js
- Arquivo alterado: wp-content/themes/imania-store/functions.php
- Também foi incluído require de: inc/home.php

2. Layout base e estrutura da Home

- Criado template dedicado: wp-content/themes/imania-store/front-page.php
- Seções implementadas:
  - Hero
  - Categorias principais
  - Seções de produtos: featured, bestsellers, new, sale

3. Componentização Home

- Criados template parts:
  - wp-content/themes/imania-store/template-parts/home/hero.php
  - wp-content/themes/imania-store/template-parts/home/product-card.php
  - wp-content/themes/imania-store/template-parts/home/product-section.php

4. Helpers dinâmicos WooCommerce/Home

- Criado: wp-content/themes/imania-store/inc/home.php
- Funções implementadas:
  - URL atual e login com redirect seguro para “ver preço”
  - Query dinâmica por segmento de produto
  - Hero product
  - Categorias principais
  - Estatísticas da Home
- Integração de preço: render via $product->get_price_html() para respeitar imania-pricing-engine.

5. Header/Footer custom

- Alterados:
  - wp-content/themes/imania-store/header.php
  - wp-content/themes/imania-store/footer.php
- Header com menu mobile toggle e ações de conta/carrinho.
- Footer com bloco institucional simplificado.

6. Design System inicial (tokens + componentes visuais)

- Criado: wp-content/themes/imania-store/assets/css/imania-theme.css
- Inclui:
  - Tokens CSS (:root, --im-\*)
  - Botões/estados
  - Tipografia/base
  - Header/Footer
  - Hero
  - Chips de categoria
  - Product cards
  - Estados vazios
  - Responsividade desktop/mobile

7. JS custom inicial

- Criado: wp-content/themes/imania-store/assets/js/imania-theme.js
- Toggle de menu mobile.

8. Documentação interna

- Criado: wp-content/themes/imania-store/README.frontend.md
- Convenções de tokens, componentes e dinâmica.

VALIDAÇÕES JÁ EXECUTADAS

- php -l nos arquivos PHP novos/alterados: sem erro de sintaxe.

PENDÊNCIAS (PRIORIDADE)

1. Fidelidade visual final

- Ajustar pixel-perfect da Home (desktop e mobile) conforme Figma.
- Revisar spacing, proporções, pesos tipográficos, raios/sombras, hierarquia visual.

2. Revisão dinâmica real

- Confirmar que todas as seções da Home estão puxando produtos reais conforme estratégia (featured/bestsellers/new/sale) com boa performance.
- Revisar fallback quando seções vierem vazias.

3. Integração UX de preço

- Validar na Home:
  - visitante: CTA de login para ver preço
  - usuário logado PF/PJ: preço correto renderizado (via plugin).

4. Acessibilidade e comportamento

- Focus states, contraste, navegação teclado, aria mínima.
- Ajustes de menu mobile e experiência em breakpoints.

5. QA funcional da etapa frontend

- Verificar render de cards/produtos reais em desktop e mobile.
- Verificar que nada no tema conflita com WooCommerce cart/checkout.

RESTRIÇÕES OPERACIONAIS

- Não fazer commit/push sem autorização explícita.
- Se surgir alteração inesperada fora do escopo, pausar e informar.
- Manter padrão de código limpo, segurança e performance.

PRÓXIMA AÇÃO ESPERADA

- Continue a partir do estado atual e faça os refinos visuais/funcionais pendentes da Home + Design System, mantendo tudo dinâmico e integrado ao backend/plugin.
