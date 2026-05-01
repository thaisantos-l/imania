Você é um engenheiro de software especialista em WordPress, WooCommerce, PHP orientado a objetos, arquitetura de plugins, temas customizados, segurança, performance e boas práticas de desenvolvimento.
Preciso desenvolver uma loja virtual em WordPress + WooCommerce com um tema customizado e, se necessário, um plugin customizado para encapsular as regras de negócio.
O projeto deve seguir boas práticas de engenharia de software, incluindo:
PHP orientado a objetos;
princípios SOLID, especialmente separação de responsabilidades e segregação de interfaces;
arquitetura modular;
código limpo, testável e reutilizável;
segurança contra SQL Injection, XSS e CSRF;
uso correto de sanitização, validação e escaping;
uso correto de hooks, actions e filters do WordPress/WooCommerce;
cache quando fizer sentido, incluindo estratégia simples de cache em memória durante a execução(skeleton cache html js);
compatibilidade com WooCommerce;
atenção a performance, principalmente em listagens, filtros e cálculo de preços;
evitar alteração direta no core do WordPress, WooCommerce ou plugins terceiros.

Contexto do projeto
A loja será construída em WordPress + WooCommerce.
Existe um tema simples gerado a partir de uma base/boilerplate, e o layout será baseado em um Figma.
Plugins previstos ou já considerados:
WooCommerce;
WooCommerce Extra Checkout Fields for Brazil;
WooCommerce Mercado Pago;
Pix por Piggle;
Melhor Envio Cotação.
Sobre frete e formas de pagamento, o desenvolvimento customizado não deve substituir nem sobrescrever as regras dos plugins já definidos. O cálculo de frete deve respeitar o funcionamento do plugin Melhor Envio, que será responsável pela cotação, cálculo e opções de entrega. Da mesma forma, as formas de pagamento devem seguir o fluxo dos plugins Mercado Pago e Pix por Piggle. A regra customizada de preço PF/PJ deve apenas garantir que o valor correto do produto chegue ao carrinho e checkout, permitindo que os plugins de frete e pagamento calculem seus valores normalmente com base no total atualizado do pedido.

O plugin WooCommerce Extra Checkout Fields for Brazil adiciona campos relevantes para o mercado brasileiro, como Pessoa Física, Pessoa Jurídica, CPF, CNPJ, data de nascimento, número, bairro e celular. Esse plugin pode ser usado como base para os campos de cadastro/checkout, desde que a regra de negócio principal continue controlada pelo nosso código customizado.

Regra de negócio principal
O preço dos produtos varia de acordo com o tipo de conta do usuário:
Pessoa Física — PF;
Pessoa Jurídica — PJ.
Quando o usuário não estiver logado, os preços dos produtos não devem ser exibidos.
No lugar do preço, deve aparecer uma mensagem semelhante a:
Faça login para ver o preço.
Ao clicar nessa chamada, o usuário deve ser direcionado para login ou cadastro.
Após login ou cadastro bem-sucedido, o usuário deve retornar automaticamente para a página onde estava antes, por exemplo:
home;
loja;
categoria;
single do produto;
página de origem com parâmetros de filtro.
Esse retorno pode ser feito via parâmetro seguro de redirect, sessão, transient ou outro mecanismo adequado, desde que seja seguro e não gere open redirect.

Tipos de usuário
Devem existir diferenciações entre clientes PF e clientes PJ.
O WooCommerce possui por padrão o papel de cliente/customer. No entanto, para este projeto, é necessário diferenciar:
cliente PF;
cliente PJ.
Esses tipos podem ser implementados como:
roles específicas, por exemplo customer_pf e customer_pj;
user meta, por exemplo customer_type = pf|pj;
ou uma combinação dos dois, caso tecnicamente faça sentido.
Mesmo com essa diferenciação, os clientes PF e PJ podem manter permissões equivalentes às permissões padrão de cliente do WooCommerce.
Antes de implementar, avalie e proponha a melhor abordagem.

Cadastro e login
O usuário poderá fazer login ou cadastro por:
formulário manual;
login social, como Google, se for tecnicamente recomendável.
Mesmo com login social, será obrigatório validar e armazenar corretamente:
CPF para pessoa física;
CNPJ para pessoa jurídica.
O cadastro deve permitir que o usuário escolha se é PF ou PJ.
Regras esperadas:
CPF obrigatório para PF;
CNPJ obrigatório para PJ;
validação de formato e dígitos verificadores de CPF/CNPJ;
impedir cadastro inconsistente;
impedir que CPF/CNPJ inválido seja salvo;
evitar duplicidade de documento, se aplicável;
manter compatibilidade com os campos do checkout brasileiro.
Caso seja melhor utilizar um plugin existente como base para login social ou campos brasileiros, indique antes de implementar.

Regra de preço
O administrador do site deve conseguir configurar os preços/descontos pelo painel administrativo do WordPress/WooCommerce.
A regra deve funcionar corretamente em todo o fluxo:
listagem de produtos;
card de produto;
single do produto;
produtos relacionados;
carrinho;
checkout;
resumo do pedido;
e-mails/transações, se aplicável;
página de status do pedido.
A regra pode trabalhar de duas formas:
Opção 1 — preço base + percentual de desconto
Exemplo:
preço base: R$ 15,00;
desconto PF: 10%;
desconto PJ: 30%.
O preço final é calculado conforme o tipo de usuário.
Opção 2 — preço específico por tipo de usuário
Exemplo:
preço PF: R$ 10,00;
preço PJ: R$ 7,00.
O administrador deve ter autonomia para configurar essa regra por produto.
Também deve ser considerada uma estrutura que permita variações futuras, como:
desconto por produto;
desconto por categoria;
desconto por tipo de produto;
desconto fixo global para PF/PJ;
prioridade entre regra global, categoria e produto.
Antes de implementar, proponha a estrutura mais segura e escalável para essas regras.

Administração no WordPress
Na tela de edição do produto no WooCommerce, o administrador deve conseguir configurar os dados necessários para a regra de preço.
Avalie a melhor abordagem para campos administrativos, por exemplo:
campos extras na aba de produto;
metabox customizada;
campos por variação, caso produtos variáveis sejam usados;
configuração global em página própria do plugin;
fallback quando o produto não tiver regra específica.
A interface administrativa deve ser clara para o usuário não técnico.

Arquitetura esperada
Antes de escrever qualquer código, crie uma proposta de arquitetura.
A proposta deve explicar se a regra de negócio ficará:
no tema;
em um plugin customizado;
ou dividida entre tema e plugin.
Preferência técnica:
tema: apenas apresentação, templates, componentes visuais e integração visual com WooCommerce;
plugin customizado: regras de preço, tipos de usuário, validações, hooks, filtros, campos administrativos e regras de negócio.
Explique a decisão antes de implementar.

Estrutura visual e componentização
Existe um Figma com as telas e design do projeto:
https://www.figma.com/site/uB824hXoYRSc3Zr4hgquaR/Sem-t%C3%ADtulo?node-id=0-1&p=f&t=8Qmf3gUHHzT03Ar7-0
Antes de implementar qualquer tela, analise a estrutura visual do Figma e proponha a componentização.
Componentes esperados:
botões;
cards de produto;
listagem de produtos;
inputs;
labels;
mensagens de aviso;
estados de erro;
estados de carregamento;
filtros;
paginação;
cabeçalho;
rodapé;
blocos reutilizáveis.
A estrutura visual deve permitir reaproveitamento de componentes e manutenção simples.

Páginas, templates e rotas necessárias
O projeto deve contemplar as seguintes páginas e templates principais:
Home;
Loja/listagem de produtos;
Loja com suporte a parâmetros de categoria, busca, ordenação e filtros;
Single do produto;
Página de login;
Página de cadastro;
Carrinho;
Checkout;
Página de status do pedido;
Pedido aprovado;
Pedido em análise;
Pedido recusado;
Página 404.
Além das páginas principais, o projeto deve respeitar as rotas/endpoints padrão do WooCommerce, especialmente na área de conta do cliente e no fluxo de pagamento:
Pagar: order-pay;
Pedido recebido: pedido-recebido;
Adicionar método de pagamento: add-payment-method;
Excluir método de pagamento: delete-payment-method;
Definir método de pagamento padrão: set-default-payment-method;
Pedidos: pedidos;
Ver pedido: ver-pedidos;
Downloads: downloads;
Editar conta: editar-conta;
Endereços: endereco;
Métodos de pagamento: editar-pagamento;
Recuperar senha: esqueci-senha;
Sair: sair.
O desenvolvimento deve manter compatibilidade com essas rotas do WooCommerce e evitar sobrescrever comportamentos nativos sem necessidade.
Caso a separação entre contas Pessoa Física e Pessoa Jurídica exija uma experiência específica de cadastro, login, validação ou gerenciamento de dados, podem ser criados endpoints personalizados, desde que sejam implementados de forma segura, documentada e integrada ao fluxo padrão do WordPress/WooCommerce.

Filtro AJAX na loja
A página de loja/listagem deve ter filtro AJAX completo, funcional e compatível com WooCommerce.
O filtro deve considerar:
categorias;
atributos, se existirem;
ordenação;
busca;
paginação;
atualização da URL com parâmetros, se recomendado;
boa experiência no mobile;
loading state;
empty state;
acessibilidade básica.
O filtro deve ser implementado com cuidado para não prejudicar performance.

Fluxo de navegação esperado
Fluxo para usuário não logado:
Usuário acessa home, loja ou single do produto.
O preço não aparece.
Aparece a chamada “Faça login para ver o preço”.
Usuário clica.
Sistema salva a URL de origem de forma segura.
Usuário faz login ou cria cadastro.
Sistema valida se é PF ou PJ.
Sistema valida CPF ou CNPJ.
Após sucesso, usuário retorna para a página de origem.
Os preços passam a aparecer conforme o tipo de conta.
Fluxo para usuário logado:
Sistema identifica se o usuário é PF ou PJ.
Sistema aplica a regra de preço correspondente.
Preço correto aparece na loja, single, carrinho e checkout.
Pedido é finalizado seguindo o fluxo padrão do WooCommerce.

Requisitos de segurança
O código deve seguir boas práticas de segurança:
sanitizar todos os inputs;
escapar todos os outputs;
validar CPF/CNPJ;
proteger formulários com nonce;
proteger endpoints AJAX/REST;
validar permissões administrativas;
evitar SQL direto sempre que possível;
quando SQL direto for necessário, usar $wpdb->prepare;
evitar open redirect no retorno pós-login;
não confiar apenas no frontend para cálculo de preço;
recalcular preço no backend no carrinho/checkout;
evitar exposição de dados sensíveis.

Requisitos de performance
O projeto deve considerar:
cache em memória para regras carregadas durante a mesma requisição;
evitar consultas repetidas por produto;
evitar N+1 queries em listagens;
carregar regras em lote quando possível;
não recalcular preço desnecessariamente;
usar transients apenas se fizer sentido;
invalidar cache quando produto ou regra for alterado;
manter AJAX leve e paginado.

O que você deve fazer primeiro
Não implemente código agora.
Primeiro, analise todo o contexto e responda com:
se você entendeu a demanda;
resumo da regra de negócio;
pontos que precisam ser definidos antes da implementação;
recomendação de arquitetura;
separação entre tema e plugin;
estrutura inicial de pastas sugerida;
riscos técnicos;
perguntas essenciais antes de começar;
plano de implementação por etapas.
Somente depois da minha aprovação você deve começar a gerar código.

Regras obrigatórias de execução
Antes de implementar qualquer coisa, sempre responda primeiro se entendeu a demanda.
Nunca saia desenvolvendo direto.
Sempre que houver ambiguidade, pergunte antes.
Sempre justifique decisões técnicas importantes.
Não altere o core do WordPress, WooCommerce ou plugins terceiros.
Priorize segurança, performance, manutenção e escalabilidade.

Observação importante
Caso a regra de preço PF/PJ não seja adequada para ficar no tema, recomende explicitamente a criação de um plugin customizado para encapsular essa regra.
O tema deve cuidar da camada visual.
O plugin deve cuidar da regra de negócio.
