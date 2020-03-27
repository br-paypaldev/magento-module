**1.0.4**
+ Adição de suporte para a última versão do OneStepCheckout da MOIP
+ Diversas otimizações e bugfixes:
	+ Descontos indevidamente sendo aplicados no Checkout Transparente
	+ Checkout Transparente não abrindo corretamente em alguns OneStepCheckout's
	+ PayPal Checkout via Mini-Browser não abrindo corretamente em alguns OneStepCheckout's

**1.0.3**
+ Adição de novas funcionalidades para o PayPal Plus:
	+ Suporte ao Repasse de Juros ao consumidor final
	+ Suporte melhor integrado ao pagamento à vista com desconto
	+ Melhoria na comunicação  dos detalhes da compra
+ Adição de compatibilidade com os seguintes módulos de OneStepCheckout para a funcionalidade de Repasse de Juros:
	+ iPAGARE 1.8.0
	+ Firecheckout 4.3.6
	+ Aheadwoks 1.3.11
	+ Amasty 3.2.6
	+ Inovarti 6

**1.0.2**
+ Diversas otimizações e mudanças para o PayPal Plus:
	+ Otimização na validação de caracteres do cliente
	+ Otimização na arquitetura com um ganho significativo em performance
	+ Remoção de Querystring na importação da biblioteca do produto
	+ Inclusão de nova compatibilidade para o OneStepCheckout do iPAGARE
	+ Remoção da compatibilidade para o OneStepCheckout do MOIP
+ Para o Express Checkout, agora a ação de venda padrão é "Venda" ao invés de "Autorização" 

**1.0.1**
+ Atualização do SDK PayPal para suporte à TLS 1.2
+ Atualização do README

**1.0.0**
+ Versão stable 1.0.0
+ README totalmente em português
+ Tutorial para geração de credenciais de API