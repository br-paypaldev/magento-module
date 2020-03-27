<?php

class Esmart_PayPalBrasil_Model_Plus extends Mage_Payment_Model_Method_Abstract
{
    /**
     * Code payment method
     * @const string
     */
    const LOG_FILENAME = 'ppplusbrasil_exception.log';

    /**
     * Code payment method
     * @const string
     */
    const CODE = 'paypal_plus';

    /**
     * Tax ID type default
     * @const string
     */
    const PAYER_TAX_ID_TYPE_CPF = 'BR_CPF';

    /**
     * Tax ID type default
     * @const string
     */
    const PAYER_TAX_ID_TYPE_CNPJ = 'BR_CPNJ';

    /**
     * Mode sandbox
     * @const string
     */
    const MODE_SANDBOX = 'sandbox';

    /**
     * Mode live
     * @const string
     */
    const MODE_LIVE = 'live';

    /**
     * Payment method default
     * @const string
     */
    const PROFILER_BASE_NAME = '%s #%d (Module Magento)';

    protected $_code = self::CODE;
    protected $_formBlockType = 'esmart_paypalbrasil/plus_form';
    protected $_infoBlockType = 'esmart_paypalbrasil/plus_info';
    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = false;
    protected $_canRefund = true;
    protected $_canVoid = true;
    protected $_canUseInternal = false;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = true;
    protected $_canSaveCc = false;
    protected $_canOrder = true;
    protected $_canFetchTransactionInfo = true;

    /**
     * Website Payments Pro instance type
     *
     * @var $_proType string
     */
    protected $_proType = 'paypal/pro';

    /**
     * Website Payments Pro instance
     *
     * @var Mage_Paypal_Model_Pro
     */
    protected $_pro = null;

    /**
     * Constructor
     *
     * By default is looking for first argument as array and assignes it as object attributes
     * This behaviour may change in child classes
     */
    public function __construct()
    {
        parent::__construct();

        Esmart_PayPalBrasil_Model_Plus_Paypal_Autoload::register();

        $proInstance = null;
        if ($proInstance && ($proInstance instanceof Mage_Paypal_Model_Pro)) {
            $this->_pro = $proInstance;
        } else {
            $this->_pro = Mage::getModel($this->_proType);
        }
        $this->_pro->setMethod($this->_code);
    }

    /**
     * Get OAuth credential
     *
     * @return PayPal\Auth\OAuthTokenCredential
     */
    public function getOAuthCredential()
    {
        $helper = Mage::helper('core');
        $clientId = $helper->decrypt(Mage::getStoreConfig('payment/paypal_plus/app_client_id'));
        $appSecret = $helper->decrypt(Mage::getStoreConfig('payment/paypal_plus/app_secret'));

        $oAuthToken = new PayPal\Auth\OAuthTokenCredential($clientId, $appSecret);

        return $oAuthToken;
    }

    /**
     * Get API Context
     *
     * @return PayPal\Rest\ApiContext
     */
    public function getApiContext()
    {
        /** @var PayPal\Rest\ApiContext $apiContext */
        $apiContext = new PayPal\Rest\ApiContext($this->getOAuthCredential());

        $mode = array(
            'mode' => $this->getMode(),
        );

        $apiContext->setConfig($mode);
        $apiContext->addRequestHeader("PayPal-Partner-Attribution-Id", 'Magento_Cart_CE_BR_PPPlus');

        Esmart_PayPalBrasil_Model_Debug::appendContent('[Plus][OPERATION MODE]', 'default', $mode);

        return $apiContext;
    }

    /**
     * Get mode SANDBOX | LIVE
     *
     * @return string
     */
    public function getMode()
    {
        $sandboxWork = Mage::getStoreConfig('payment/paypal_plus/sandbox_flag');

        if ($sandboxWork) {
            return self::MODE_SANDBOX;
        }

        return self::MODE_LIVE;
    }

    /**
     * Create and return FlowConfig
     *
     * @return PayPal\Api\FlowConfig
     */
    protected function createFlowConfig()
    {
        $flowConfig = new \PayPal\Api\FlowConfig();

        return $flowConfig;
    }

    /**
     * Create and return Presentation
     *
     * @param string $name
     *
     * @return PayPal\Api\Presentation
     */
    protected function createPresentation($name)
    {
        $presentation = new \PayPal\Api\Presentation();

        $presentation->setBrandName($name)
            ->setLocaleCode("BR");

        return $presentation;
    }

    /**
     * Create and return InputFields
     *
     * @return PayPal\Api\InputFields
     */
    protected function createInputFields()
    {
        $inputFields = new \PayPal\Api\InputFields();

        $inputFields->setNoShipping(0)
            ->setAddressOverride(1);

        return $inputFields;
    }

    /**
     * Save return Paypal
     *
     * @param array
     */
    public function saveReturnPaypal(array $data)
    {

        Esmart_PayPalBrasil_Model_Debug::appendContent('[Plus][DATA FORM IFRAME]', 'executePayment', $data);

        if (is_null($data) || !($data['payerId'])) {
            throw new Exception('Prezado cliente, ocorreu um erro inesperado, por favor tente novamente. Caso o erro persista entre em contato.');
        }
        $customer = Mage::getSingleton('customer/session')->getCustomer();

        if ($customer && $customer->getId()) {
            $rememberedCards = $data['rememberedCards'];

            if ($rememberedCards) {
                $customer->setPpalRememberedCards($rememberedCards)->save();
            }
        }

        $helper = Mage::helper('esmart_paypalbrasil');

        /** @var Mage_Sales_Model_Quote_Payment $quotePayment */
        $quotePayment = $helper->getQuote()->getPayment();

        $payment_id = $quotePayment->getAdditionalInformation('paypal_plus_payment_id');

        $modelPayPalPlus = Mage::getModel('esmart_paypalbrasil/plus');
        $creditFinancingOffered = $modelPayPalPlus->getDiscountPayPal($payment_id);

        $installmentModel = Mage::getModel('esmart_paypalbrasil/installments');
        $discountAdmin = $installmentModel->getInstallmentDiscount();

        if(isset($creditFinancingOffered['discount_percentage'])) {

            if ($creditFinancingOffered['discount_percentage'] != $discountAdmin) {
                $data = array(
                    'config_discount_paypal' => $creditFinancingOffered['discount_percentage'],
                    'config_discount_magento' => $discountAdmin
                );
                Esmart_PayPalBrasil_Model_Debug::appendContent('[Plus][DATA FORM IFRAME]', 'executePaymentDiscount', $data);
                Mage::throwException('Houve um problema com seu pagamento. Por favor entre em contato com a loja.');
            }
        }

        $quotePayment->setAdditionalInformation('paypal_plus_payer_id', $data['payerId'])
            ->setAdditionalInformation('paypal_plus_payer_status', $data['payerStatus'])
            ->setAdditionalInformation('paypal_plus_checkout_token', $data['checkoutId'])
            ->setAdditionalInformation('paypal_plus_checkout_state', $data['checkoutState'])
            ->setAdditionalInformation('paypal_plus_cards', $data['cards']);

        $helper = Mage::helper('esmart_paypalbrasil');
        $quote = $helper->getQuote(null);

        if (!$quote->getReservedOrderId()) {
            $quote->reserveOrderId()->save();
        }

        $quotePayment->save();
    }


    /**
     * Order payment abstract method (executePayment)
     *
     * @param Varien_Object $payment
     * @param float $amount
     *
     * @return Mage_Payment_Model_Abstract
     */
    public function order(Varien_Object $payment, $amount)
    {
        parent::order($payment, $amount);

        /** @var Esmart_PayPalBrasil_Helper_Data $helper */
        $helper = Mage::helper('esmart_paypalbrasil');
        $quote = $helper->getQuote(null);

        // Data to transactions
        $invoice = $quote->getReservedOrderId();
        $nameStore = Mage::getStoreConfig('payment/paypal_plus/paypal_custom');
        $transactionText = 'Pedido ' . $invoice . ' - ' . $nameStore;

        // Get data on order and payment
        $order = $payment->getOrder();
        $payment = $order->getPayment();

        $apiContext = $this->getApiContext();
        $paypalPayment = \PayPal\Api\Payment::get($payment->getAdditionalInformation('paypal_plus_payment_id'), $apiContext);

        /** @var Esmart_PayPalBrasil_Model_Plus_Iframe $model */
        $model = Mage::getModel('esmart_paypalbrasil/plus_iframe');

        if ($payment->getAdditionalInformation('paypal_plus_installments_cost') > 0) {
            $amountTransaction = $model->createAmountInstallment($quote, $order);
        } else {
            $amountTransaction = $model->createAmount($quote);
        }

        $discount = -$payment->getAdditionalInformation('paypal_discount');

        if ($discount != 0) {
            $grandTotal = $quote->getBaseGrandTotal();
            $discount = (-$discount);
            $restoreTotal = round($grandTotal + $discount, 2);
            $amountTransaction->setTotal($restoreTotal);
        }

        // Set new data on transaction
        $transaction = new \PayPal\Api\Transaction();
        $transaction->setAmount($amountTransaction);
        $transaction->setDescription($transactionText);
        $transaction->setCustom($transactionText);
        $transaction->setInvoiceNumber($invoice);

        // Payment Execution
        $paymentExecution = new \PayPal\Api\PaymentExecution();
        $paymentExecution->setPayerId($payment->getAdditionalInformation('paypal_plus_payer_id'));
        $paymentExecution->setTransactions(array($transaction));


        Esmart_PayPalBrasil_Model_Debug::appendContent('[Plus][EXECUTE PAYMENT REQUEST]', 'executePayment', array(var_export($paymentExecution->toArray(), true)));

        if (!$paymentExecution->getPayerId()) {
            Esmart_PayPalBrasil_Model_Debug::writeLog();
            Mage::throwException('Prezado cliente, ocorreu um erro inesperado, por favor tente novamente. Caso o erro persista entre em contato.');
        }

        try {

            // Execute the payment
            $paypalPayment->execute($paymentExecution, $apiContext);

            Esmart_PayPalBrasil_Model_Debug::appendContent('[Plus][EXECUTE PAYMENT RESPONSE]', 'executePayment', array(var_export($paypalPayment->toArray(), true)));


            $transactions = $paypalPayment->getTransactions();

            $saleId = null;

            if ($transactions) {
                /** @var \PayPal\Api\Transaction $transaction */
                $transaction = $transactions[0];

                $relatedResources = $transaction->getRelatedResources();

                /** @var \PayPal\Api\RelatedResources $relatedResource */
                $relatedResource = $relatedResources[0];

                /** @var \PayPal\Api\Sale $sale */
                $sale = $relatedResource->getSale();

                $saleId = $sale->getId();

                $state = $sale->getState();
            }


            $payment->setAdditionalInformation('paypal_plus_payment_state', $state)
                ->setAdditionalInformation('paypal_payment_status', $state)
                ->setAdditionalInformation('paypal_plus_sale_id', $saleId)
                ->setTransactionId($saleId);

            /* if is not pending create a a invoice Paid */
            $invoice = $order->prepareInvoice();

            if (!$invoice->getTotalQty()) {
                Mage::throwException(Mage::helper('core')->__('Cannot create an invoice without products.'));
            }

            /* create a invoice pendent */
            $invoice->register();

            $order->addRelatedObject($invoice);

            $invoice->save();
            $invoice->sendEmail(true);

            /* logic of "payment review" */
            if ($state == 'pending') {
                Mage::getSingleton('core/session')->addNotice(Mage::helper('core')->__('Your Payment is being reviewed.'));
                $payment->setIsTransactionClosed(false);
                $payment->setIsTransactionPending(true);

                return $this;
            }

            $payment->setSkipOrderProcessing(true);

        } catch (Exception $e) {

            $errorData = Mage::helper('core')->jsonDecode($e->getData());
            $cancel = Mage::getModel('esmart_paypalbrasil/cancel', $errorData);

            $createFailure = Mage::getStoreConfig('payment/paypal_plus/order_failure', Mage::app()->getStore());
            if ($createFailure && $cancel->cancelOrder($payment)) {
                return $this;
            }
            $errorData['invoice_number'] = $order->getIncrementId();
            $e->setData(json_encode($errorData));
            $helper->logException(__FILE__, __CLASS__, __FUNCTION__, __LINE__, self::LOG_FILENAME, $e);

            Esmart_PayPalBrasil_Model_Debug::writeLog();
            Mage::throwException('Sua transação não pode ser concluida devido a problemas com seu meio de pagamento, tente novamente com outro cartão.');
        }

        Esmart_PayPalBrasil_Model_Debug::writeLog();

        return $this;
    }

    /**
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote(Mage_Sales_Model_Quote_Address $address = null)
    {
        if ($address && $address->getQuote()) {
            return $address->getQuote();
        }

        return $this->_helper()->getQuote();
    }


    /**
     * @return Esmart_PayPalBrasil_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('esmart_paypalbrasil');
    }

    /**
     * Fetch transaction details info
     *
     * @param Mage_Payment_Model_Info $payment
     * @param string $transactionId
     * @return array
     */
    public function fetchTransactionInfo(Mage_Payment_Model_Info $payment, $transactionId)
    {
        return $this->_pro->fetchTransactionInfo($payment, $transactionId);
    }


    /**
     * Refund capture
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param float $amount
     * @return Mage_Paypal_Model_Express
     */
    public function refund(Varien_Object $payment, $amount)
    {
        $this->_pro->refund($payment, $amount);
        return $this;
    }

    /**
     * Discount PayPal admin to one installment
     *
     * @param  $payment_id
     * @return  paypalCreditFinancingOffered
     */
    public function getDiscountPayPal($payment_id)
    {

        if((Mage::getStoreConfig("payment/paypal_plus/instalments_active"))) {
            /** @var Esmart_PayPalBrasil_Helper_Data $helper */
            $helper = Mage::helper('esmart_paypalbrasil');
            $quote = $helper->getQuote(null);

            $subtotal = $quote->getShippingAddress()->getSubtotal();
            $shipping = $quote->getShippingAddress()->getShippingAmount();
            $discount_amount = $quote->getShippingAddress()->getDiscountAmount();

            if($discount_amount < 0) {
                $discount_amount = $discount_amount * (-1);
            }

            $total = ($subtotal + $shipping) - $discount_amount;

            $discount = Mage::getModel('esmart_paypalbrasil/installments_config')->getInstallmentDiscount();

            $totalDiscount = $total * ($discount / 100);

            $paypalPaymentData['credit_financing_offered']['discount_amount']['value'] = $totalDiscount;

            return $paypalPaymentData['credit_financing_offered'];
        } else {
            return false;
        }

    }


    /**
     * Discount PayPal admin to one installment clear
     *
     * @param  $payment_id
     * @return  paypalCreditFinancingOffered
     */
    public function clearDiscountPayPal()
    {
        $helper = Mage::helper('esmart_paypalbrasil');
        $quote  = $helper->getQuote(null);
        $quote->getPayment()->unsAdditionalInformation();
        $quote->collectTotals();

    }

    /**
     * Retrieve payment method title
     *
     * @return string
     */
    public function getTitle()
    {
        return 'Cartão de Crédito ' . $this->getConfigData('title');
    }
}


