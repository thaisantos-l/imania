# QA Funcional - Etapa V2 (Varejo/Atacado)

## Pre-condicoes
- Plugin `Imania Pricing Engine` ativo.
- WooCommerce ativo.
- Cadastro com clientes PF e PJ.
- Pelo menos 3 produtos ativos.

## Cenarios de autenticacao e exibicao
1. Visitante:
   - nao exibe preco; mostra CTA de login.
2. Usuario logado (PF ou PJ):
   - preco aparece normalmente em loja, single, carrinho e checkout.

## Cenarios de regra comercial
1. Varejo (PJ):
   - subtotal sem frete menor que R$ 49,90 bloqueia checkout.
2. Atacado (PF):
   - subtotal sem frete menor que R$ 350,00 bloqueia checkout.
3. Atacado (PF):
   - quantidade menor que 3 unidades por item bloqueia checkout.
4. Promocao 10+2:
   - com 12 unidades do mesmo item no carrinho, desconto equivalente a 2 unidades deve ser aplicado automaticamente.
5. Primeira compra Varejo (PJ):
   - cliente sem pedidos anteriores deve receber desconto de 10% quando minimo for atendido.
6. Nao primeira compra Varejo (PJ):
   - cliente com pedido anterior nao deve receber desconto de primeira compra.

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
