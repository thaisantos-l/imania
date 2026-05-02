# QA Funcional - Etapa 3 (PF/PJ + Prioridade)

## Pre-condicoes
- Plugin `Imania Pricing Engine` ativo.
- WooCommerce ativo.
- Pelo menos 1 produto simples e 1 produto variavel.
- Pelo menos 1 categoria com regra PF/PJ configurada.

## Cenarios de regra de preco
1. Prioridade `product,category,global`:
   - produto com regra local deve vencer categoria/global.
2. Produto sem regra + categoria com regra:
   - categoria deve definir preco final.
3. Produto sem regra + categoria sem regra + global com regra:
   - global deve definir preco final.
4. Variacao sem regra + pai com regra:
   - herdar do produto pai.
5. Variacao sem regra + pai sem regra + categoria:
   - usar categoria.

## Cenarios de autenticacao e exibicao
1. Visitante:
   - nao exibe preco; mostra CTA de login.
2. Usuario PF logado:
   - aplica regra PF em loja, single, carrinho e checkout.
3. Usuario PJ logado:
   - aplica regra PJ em loja, single, carrinho e checkout.

## Cenarios de cadastro
1. Cadastro PF com CPF invalido:
   - deve bloquear.
2. Cadastro PJ com CNPJ invalido:
   - deve bloquear.
3. Documento duplicado em outra conta:
   - deve bloquear.

## Compatibilidade
1. Melhor Envio:
   - frete calcula normalmente com subtotal ajustado.
2. Mercado Pago e Pix por Piggly:
   - meios de pagamento e total final seguem fluxo normal.

## Seguranca
1. Redirect pos-login:
   - apenas URL valida no mesmo host.
2. Atualizacao de regra em admin:
   - apenas usuarios com permissao de WooCommerce.
