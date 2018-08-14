# Módulo PayPal para Magento 1
![](https://raw.githubusercontent.com/wiki/paypal/PayPal-PHP-SDK/images/homepage.jpg)

Página oficial do módulo PayPal com as soluções utilizadas no mercado Brasileiro para Magento 1.

## Descrição

Este módulo contém os principais produtos PayPal para o mercado Brasileiro:
- **Express Checkout (In-context)**: Solução de carteira digital aonde o cliente paga com a sua conta PayPal ou cria uma no momento da compra.
- **PayPal Plus**: Checkout transparente PayPal aonde o cliente paga somente utilizando o seu cartão de crédito, sem a necessidade de ter uma conta PayPal.
- **PayPal Login**: O cliente utiliza a sua conta PayPal para fazer login na plataforma e comprar com PayPal;
- **PayPal no Carrinho**: O cliente utiliza a sua conta PayPal para comprar diretamente do carrinho;
- **PayPal no Produto**: O cliente utiliza a sua conta PayPal para comprar diretamente do produto;

**É recomendado que o PayPal Plus seja utilizado juntamente com o Express Checkout, oferecendo assim ao cliente uma experiência de checkout completa com as soluções transparente e de carteira.**

## Requisitos

Para o correto funcionamento das soluções, é necessário verificar que a sua loja e servidor suporte alguns recursos:
1. Para o checkout transparente (PayPal Plus), a sua loja precisa ter suporte ao TAX_VAT, portanto antes de ativar a solução garanta que a sua loja está devidamente configurada para suportar este campo;
2. O servidor precisa ter suporte à TLS 1.2 ou superior e HTTPS 1.1 [(Referência Oficial)](https://www.paypal.com/sg/webapps/mpp/tls-http-upgrade).

## PayPal Plus (Checkout Transparente)

Diferente dos outros produtos, o PayPal Plus só está disponível para contas PayPal criadas com CNPJ (Conta Empresa) e a sua utilização funciona mediante aprovação comercial. Caso já tenha uma conta PayPal do tipo Empresa, você pode solicitar a utilização do PayPal Plus pelo email: comercial@paypal.com.

Caso a sua conta seja de pessoa física, você deve abrir uma conta PayPal de pessoa jurídica por este [link](https://www.paypal.com/bizsignup/).

***O PayPal Plus só irá funcionar caso tenha sido aprovado pelo PayPal.**

## Compatibilidade
### - Versão Magento

Este módulo é compatível com as versões do Magento 1.7.2 até 1.9.x. 

### - Módulos OneStepCheckout

Para o PayPal Plus, atualmente este módulo trabalha com os seguintes módulos OneStepCheckout:
1. Firecheckout
2. MOIP v1
3. Inovarti (OSC6 Brasil)
4. Amasty
5. Esmart Checkout
6. AheadWorks OneStepCheckout

## Instalação

Copie as pastas "app, js, lib, skin" para dentro do diretório da sua instalação Magento.

## Configuração
### - Credenciais de API
Para configurar as soluções PayPal, você deverá gerar as credenciais de API respectivas de cada produto, são 02 possibilidades, as credenciais de tipo CLASSIC e as de REST. Siga este [**tutorial**](tutorial/Credenciais_API.pdf) para gerar os 02 tipos de credenciais.

### - PayPal Plus
Para o PayPal Plus, o campo CPF/CNPJ é obrigatório, para habilitá-lo siga os passos abaixo dentro do painel administrativo do Magento:

**Habilitar como obrigatório o Tax/VAT Number no endereço do Cliente:**
- System -> Configuration -> Customers -> Customer Configuration -> Name and Address Options -> Show Tax/VAT Number	 (Habilitar como "Required")

## Atualização

Para atualizar o módulo simplesmente substitua as pastas "app, js, lib, skin" e os respectivos arquivos pelos novos deste repositório.

## Dúvidas/Suporte

Caso a sua dúvida não tenha sido respondida aqui, entre em contato com o PayPal pelo número 0800 047 4482.

E caso necessite de algum suporte técnico e/ou acredita ter encontrado algum problema com este módulo abra um "issue" aqui no Github que iremos verificar.

## Changelog

Para visulizar as últimas atualizações acesse o [**CHANGELOG.md**](CHANGELOG.md).
