<?php

/**
 * Smart E-commerce do Brasil Tecnologia LTDA
 *
 * INFORMAÇÕES SOBRE LICENÇA
 *
 * Open Software License (OSL 3.0).
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Não edite este arquivo caso você pretenda atualizar este módulo futuramente
 * para novas versões.
 *
 * @category  Esmart
 * @package   Esmart_PayPalBrasil
 * @copyright Copyright (c) 2015 Smart E-commerce do Brasil Tecnologia LTDA. (http://www.e-smart.com.br)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @author        Ricardo Martins <ricardo.martins@e-smart.com.br>
 * @author        Thiago H Oliveira <thiago.oliveira@e-smart.com.br>
 * @author        Rafael K Ventura <rafael.silva@e-smart.com.br>
 */
class Esmart_PayPalBrasil_Model_Plus_Iframe extends Mage_Payment_Block_Form
{
    /**
     * Non-persisted data
     * @var Varien_Object
     */
    protected $nonPersistedData;

    /**
     * Custom information
     * @const string
     */
    const CUSTOM_BASE_INFORMATION = '%s (Pedido: #%d)';

    /**
     * Allowed payment method default
     * @const string
     */
    const ALLOWED_PAYMENT_METHOD = 'IMMEDIATE_PAY';

    /**
     * Intent of the payment default
     * @const string
     */
    const INTENT_PAYMENT = 'sale';
    /**
     * Code payment method
     * @const string
     */
    const LOG_FILENAME = 'ppplusbrasil_exception.log';

    /**
     * Payment method default
     * @const string
     */
    const PAYMENT_METHOD = 'paypal';

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

    /*
     * Installment
     * @var  $installment Installment value
     */
    protected $installment;

    /*
    * Installment cost
    * @var  $costOfInstallment value calculete of cost the installment
    */
    protected $costOfInstallment;


    protected $configInstallments;


    function __construct()
    {
        $this->nonPersistedData = new Varien_Object();

        Esmart_PayPalBrasil_Model_Plus_Paypal_Autoload::register();
        $this->configInstallments = Mage::getModel('esmart_paypalbrasil/installments_config');
    }

    /**
     * Set non-persisted data
     *
     * @param array $postData
     *
     * @return $this
     */
    public function setNonPersistedData(array $postData, $firstCall = true)
    {
        foreach ($postData as $key => $value) {
            if (is_array($value)) {
                $this->setNonPersistedData($value, false);
                continue;
            }

            if (is_numeric($key)) {
                $key = $key + 1;
            }

            if (!empty($value)) {
                $this->nonPersistedData->setData($key, $value);
            }
        }

        if ($firstCall) {
            $data = $this->nonPersistedData->toArray();
            Esmart_PayPalBrasil_Model_Debug::appendContent('[Plus][FRONTEND FORM DATA]', 'createPayment', $data);
        }

        return $this;
    }

    /**
     * Get customer information to use in JS
     *
     * @param Mage_Sales_Model_Quote $quote (Quote object (Mage_Sales_Model_Quote) | Quote ID | null)
     *
     * @return array
     */
    public function getCustomerInformation($quote = null,$installment = null)
    {
        /** @var Esmart_PayPalBrasil_Helper_Data $helper */
        $helper = $this->_helper();

        /** @var Mage_Sales_Model_Quote $quote */
        $quote = $helper->getQuote($quote);

        /**
         * @var Mage_Customer_Model_Customer $customer
         * @var Mage_Sales_Model_Quote_Address|Mage_Customer_Model_Address $address
         */
        $customer = $quote->getCustomer();
        $address = $quote->getBillingAddress();

        $firstname = $this->_getFirstname($address);
        $lastname = $this->_getLastname($address);
        $email = strtolower($this->_getEmail($address));
        $payerTaxId = $this->_getPayerTaxId($address);
        $phone = $this->_getTelephone($address);

        $return = array(
            'payerFirstName' => $firstname,
            'payerLastName' => $lastname,
            'payerEmail' => $email,
            'payerTaxIdType' => $helper->checkIsCpfOrCnpj($payerTaxId),
            'payerTaxId' => $payerTaxId,
            'payerPhone' => $phone,
            'rememberedCards' => $customer->getPpalRememberedCards()
        );

        Esmart_PayPalBrasil_Model_Debug::appendContent('[Plus][RETURN getCustomerInformation()]', 'createPayment', $return);

        if (!Esmart_PayPalBrasil_Model_Paypal_Validate::is(array($firstname, $lastname), 'OnlyWords', true) ||
            !Esmart_PayPalBrasil_Model_Paypal_Validate::is($email, 'AddressMail', false) ||
            !Esmart_PayPalBrasil_Model_Paypal_Validate::is($phone, 'OnlyNumbers', true) ||
            !Esmart_PayPalBrasil_Model_Paypal_Validate::isValidTaxvat($payerTaxId)) {
            throw new Exception("getCustomerInformation Exception", 1);
        }

        return $return;
    }



    /**
     * Get customer information to use in JS with installmets
     *
     * @param Mage_Sales_Model_Quote $quote (Quote object (Mage_Sales_Model_Quote) | Quote ID | null)
     *
     * @return array
     */
    public function getCustomerInformationInstallments(Mage_Sales_Model_Quote $quote, $installment, $payerInfo)
    {

        if( empty($installment) || empty($payerInfo)){
            return;
        }

//        $installmentModel = Mage::getModel('esmart_paypalbrasil/installments');
//        $installmentModel->cleanInstallments($quote);

        $quote->getPayment()->setAdditionalInformation('paypal_plus_installments',$installment);

        $configInstallments = Mage::getStoreConfig('payment/paypal_plus/instalments_active');

        if($configInstallments){
            $addInstallment =  array(
                'merchantInstallmentSelectionOptional'=>false,
                'merchantInstallmentSelection' => $installment
            );
        }

        return array_merge($payerInfo,$addInstallment);
    }

    /**
     * @param Mage_Sales_Model_Quote_Address $address
     *
     * @return string
     */
    protected function _getFirstname(Mage_Sales_Model_Quote_Address $address)
    {
        $fieldId = Mage::getStoreConfig('payment/paypal_plus/firstname');
        return $this->_extractData($address, 'firstname', $fieldId);
    }

    /**
     * @param Mage_Sales_Model_Quote_Address $address
     *
     * @return string
     */
    protected function _getLastname(Mage_Sales_Model_Quote_Address $address)
    {
        $fieldId = Mage::getStoreConfig('payment/paypal_plus/lastname');
        return $this->_extractData($address, 'lastname', $fieldId);
    }

    /**
     * @param Mage_Sales_Model_Quote_Address $address
     *
     * @return null|string
     */
    protected function _getEmail(Mage_Sales_Model_Quote_Address $address)
    {
        /**
         * @var Mage_Sales_Model_Quote $quote
         */
        $quote = $this->_getQuote($address);
        $email = $quote ? $quote->getCustomer()->getData('email') : null;

        if (empty($email)) {
            $email = $address->getEmail();
        }

        if (empty($email)) {
            $email = $quote->getCustomerEmail();
        }

        if (empty($email)) {
            $email = $this->_getFromRequest('email');
        }

        return $email;
    }

    /**
     * @param Mage_Sales_Model_Quote_Address $address
     *
     * @return null|string
     */
    protected function _getPayerTaxId(Mage_Sales_Model_Quote_Address $address)
    {
        /**
         * @var Mage_Sales_Model_Quote $quote
         * @var Mage_Customer_Model_Customer $customer
         */
        $quote = $this->_getQuote($address);
        $customer = $quote ? $quote->getCustomer() : null;

        $payerTaxId = $this->_helper()->getCpfCnpjOrTaxvat($customer, $this->nonPersistedData);

        if (empty($payerTaxId)) {
            $payerTaxId = $this->_helper()->getCpfCnpjOrTaxvat($address, $this->nonPersistedData);
        }

        if (empty($payerTaxId)) {
            $payerTaxId = $quote->getCustomerTaxvat();
        }

        if (empty($payerTaxId)) {
            $index = Mage::getStoreConfig('payment/paypal_plus/cpf');
            $payerTaxId = $this->_getFromRequest($index);
        }

        if (empty($payerTaxId)) {
            $payerTaxId = $this->_getFromRequest('taxvat');
        }

        return $payerTaxId;
    }

    /**
     * @param Mage_Sales_Model_Quote_Address $address
     *
     * @return string
     */
    protected function _getTelephone(Mage_Sales_Model_Quote_Address $address)
    {
        $fieldId = Mage::getStoreConfig('payment/paypal_plus/phone');
        return $this->_extractData($address, 'telephone', $fieldId);
    }


    /**
     * Return Approval URL PaypalPlus
     *
     * @param mixed $quote (Mage_Sales_Model_Quote | Quote ID | null)
     *
     * @return array
     */
    public function getApprovalUrlPaypalPlus($quote = null)
    {
        $payment = $this->createPayment($quote);

        $data = array(
            'approvalUrl' => $payment->getApprovalLink(),
            'mode' => $this->getMode(),
        );

        Esmart_PayPalBrasil_Model_Debug::appendContent('[Plus][APPROVAL URL]', 'createPayment', $data);

        return $data;
    }

    /**
     * Create and return Payment
     *
     * @param mixed $quote (Quote object (Mage_Sales_Model_Quote) | Quote ID | null)
     *
     * @return PayPal\Api\Payment
     */
    public function createPayment($quote = null)
    {
        $helper = Mage::helper('esmart_paypalbrasil');
        $quote = $helper->getQuote($quote);

        if ($quote->getIsVirtual() == 1) {
            $shippingPreference = 'NO_SHIPPING';
        } else {
            $shippingPreference = 'SET_PROVIDED_ADDRESS';
        }

        $transaction = $this->createTransaction($quote);

        $payment = new PayPal\Api\Payment();

        $payment->setIntent(self::INTENT_PAYMENT)
            ->setPayer($this->createPayer())
            ->setRedirectUrls($this->createRedirectUrls())
            ->setTransactions(array($transaction))
            ->setApplicationContext(array(
                'brand_name' => Mage::getStoreConfig('general/store_information/name', Mage::app()->getStore()),
                'shipping_preference' => $shippingPreference,
                'locale' => 'PT-BR'
            ));

        try {
            Esmart_PayPalBrasil_Model_Debug::appendContent(
                '[Plus][CREATE PAYMENT REQUEST]', 'createPayment',
                array(var_export($payment->toArray(), true))
            );

            $payment->create($this->getApiContext());

            Esmart_PayPalBrasil_Model_Debug::appendContent(
                '[Plus][CREATE PAYMENT RESPONSE]', 'createPayment',
                array(var_export($payment->toArray(), true))
            );

            $quote->getPayment()
                ->setAdditionalInformation('paypal_plus_payment_id', $payment->getId())
                ->setAdditionalInformation('paypal_plus_payment_state', $payment->getState())
                ->setAdditionalInformation('paypal_plus_installments', $this->getInstallment())
                ->save();

        } catch (Exception $e) {
            throw new Exception("Call createPayment Exception", 1);
        }

        return $payment;
    }

    /**
     * Create and return Transaction
     *
     * @param Mage_Sales_Model_Quote $quote Quote object
     *
     * @return PayPal\Api\Transaction
     */
    protected function createTransaction(Mage_Sales_Model_Quote $quote)
    {
        /** @var PayPal\Api\Transaction $transaction */
        $transaction = new PayPal\Api\Transaction();

        $payment = $quote->getPayment();
        $cost = $this->getCostOfInstallment($quote);


        if(!empty($payment->getAdditionalInformation('paypal_plus_installments')) ||
            ($cost > 0) &&
            ($this->configInstallments->getStatusInstallments() == true)
        )
        {
            $transaction->setAmount($this->createAmountInstallment($quote))
                ->setPaymentOptions($this->createPaymentOptions())
                ->setItemList($this->createItemList($quote))
                ->setNotifyUrl(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . 'paypal/ipn');
        }else{

            $transaction->setAmount($this->createAmount($quote))
                ->setPaymentOptions($this->createPaymentOptions())
                ->setItemList($this->createItemList($quote))
                ->setNotifyUrl(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . 'paypal/ipn');
        }

        return $transaction;
    }

    /**
     * Create and return Amount
     *
     * @param Mage_Sales_Model_Quote $quote Quote object
     *
     * @return PayPal\Api\Amount
     */
    public function createAmountInstallment(Mage_Sales_Model_Quote $quote, Mage_Sales_Model_Order $order = null)
    {
        $cost = $this->getCostOfInstallment($quote);

        $installmentGrandTotal = $this->getGranTotalClean($quote) + $cost;

        /** @var PayPal\Api\Amount $amount */
        $amount = new PayPal\Api\Amount();

        $amount->setCurrency($quote->getBaseCurrencyCode())
            ->setTotal($installmentGrandTotal)
            ->setDetails($this->createDetailsInstallmets($quote, $order));

        $data = array(
            'Base Currency' => $quote->getBaseCurrencyCode(),
            'Total' => $installmentGrandTotal,
        );

        Esmart_PayPalBrasil_Model_Debug::appendContent('[CREATE AMOUNT Installment]', 'createPayment', $data);

        return $amount;
    }

    /**
     * Create and return Amount
     *
     * @param Mage_Sales_Model_Quote $quote Quote object
     *
     * @return PayPal\Api\Amount
     */
    public function createAmount(Mage_Sales_Model_Quote $quote)
    {
        /** @var PayPal\Api\Amount $amount */
        $amount = new PayPal\Api\Amount();

        $subtotal = $quote->getSubtotal();
        $shipping_amount = $quote->getShippingAddress()->getShippingAmount();
        $discount = Mage::helper('esmart_paypalbrasil')->getCorrectDiscount($quote);

        $grandTotal = ($subtotal + $shipping_amount) - $discount;

        $amount->setCurrency($quote->getBaseCurrencyCode())
            ->setTotal($grandTotal)
            ->setDetails($this->createDetails($quote));

        $data = array(
            'Base Currency' => $quote->getBaseCurrencyCode(),
            'Total' => $grandTotal,
        );
        Esmart_PayPalBrasil_Model_Debug::appendContent('[CREATE AMOUNT]', 'createPayment', $data);

        return $amount;
    }

    /**
     * Create and return ItemList
     *
     * @param Mage_Sales_Model_Quote $quote Quote object
     *
     * @return PayPal\Api\ItemList
     */
    protected function createItemList(Mage_Sales_Model_Quote $quote)
    {
        $itemList = new PayPal\Api\ItemList();

        $quoteItems = $quote->getAllVisibleItems();

        $payment = $quote->getPayment();

        $data = array();

        /** @var Mage_Sales_Model_Quote_Item $item */
        foreach ($quoteItems as $item) {
            $objItem = new PayPal\Api\Item();

            $objItem->setName($item->getName())
                ->setCurrency($quote->getBaseCurrencyCode())
                ->setQuantity($item->getQty())
                ->setPrice($item->getPrice());

            $itemList->addItem($objItem);

            $data[] = $objItem->toJSON();
        }



        $discount = Mage::helper('esmart_paypalbrasil')->getCorrectDiscount($quote);
        if ($discount > 0) {
            $objItem = new PayPal\Api\Item();

            $objItem->setName('Descontos')
                ->setCurrency($quote->getBaseCurrencyCode())
                ->setQuantity(1)
                ->setPrice($discount);

            $itemList->addItem($objItem);

            $data[] = $objItem->toJSON();
        }


        // Cost of installment
        if(($payment->getAdditionalInformation('paypal_plus_installments_cost') > 0) &&
            ($this->configInstallments->getStatusInstallments() == true)
        ) {

            $objItem = new PayPal\Api\Item();
            $objItem->setName(__('Juros'))
                ->setCurrency($quote->getBaseCurrencyCode())
                ->setQuantity(1)
                ->setPrice($payment->getAdditionalInformation('paypal_plus_installments_cost'));

            $itemList->addItem($objItem);

            $data[] = $objItem->toJSON();
        }

        Esmart_PayPalBrasil_Model_Debug::appendContent('[Plus][CREATE ITEM LIST]', 'createPayment', $data);

        if ($quote->getIsVirtual() != 1) {
            // append shipping information
            $itemList->setShippingAddress($this->createShippingAddress($quote));
        }
        return $itemList;
    }


    /**
     * Create and return Payment Options
     *
     * @return PayPal\Api\PaymentOptions
     */
    protected function createPaymentOptions()
    {
        /** @var PayPal\Api\PaymentOptions $paymentOptions */
        $paymentOptions = new PayPal\Api\PaymentOptions();

        $paymentOptions->setAllowedPaymentMethod(self::ALLOWED_PAYMENT_METHOD);

        $data = array(
            'Allowed Payment Method' => $paymentOptions->getAllowedPaymentMethod(),
        );
        #Esmart_PayPalBrasil_Model_Debug::appendContent('[PAYMENT OPTIONS]', 'createPayment', $data);

        return $paymentOptions;
    }

    /**
     * Create and return Details
     *
     * @param Mage_Sales_Model_Quote $quote Quote object
     *
     * @return PayPal\Api\Details
     */
    protected function createDetails(Mage_Sales_Model_Quote $quote)
    {
        /** @var PayPal\Api\Details $details */
        $details = new PayPal\Api\Details();

        $totals = $quote->getTotals();

        $shipping = isset($totals['shipping']) ? $totals['shipping'] : null;
        if ($shipping instanceof Mage_Sales_Model_Quote_Address_Total) {
            $details->setShipping($shipping->getValue());
        }

        $tax = isset($totals['tax']) ? $totals['tax'] : null;
        if ($tax instanceof Mage_Sales_Model_Quote_Address_Total) {
            $details->setTax($tax->getValue());
        }

        $discount = Mage::helper('esmart_paypalbrasil')->getCorrectDiscount($quote);

        $details->setSubtotal($totals['subtotal']->getValue() - $discount);

        $data = array(
            'Shipping' => $details->getShipping(),
            'Tax' => $details->getTax(),
            'Subtotal' => $details->getSubtotal(),
        );
        #Esmart_PayPalBrasil_Model_Debug::appendContent('[CREATE AMOUNT - DETAILS]', 'createPayment', $data);

        return $details;
    }

    /**
     * Create and return Details
     *
     * @param Mage_Sales_Model_Quote $quote Quote object
     *
     * @return PayPal\Api\Details
     */
    protected function createDetailsInstallmets(Mage_Sales_Model_Quote $quote, Mage_Sales_Model_Order $order = null)
    {
        $totals = $quote->getTotals();
        $payment = $quote->getPayment();
        $cost = $payment->getAdditionalInformation('paypal_plus_installments_cost');

        if(empty($cost)) {
            $costTo = isset($totals['esmart_paypalbrasil_cost']) ? $totals['esmart_paypalbrasil_cost'] : null;
            if ($costTo instanceof Mage_Sales_Model_Quote_Address_Total) {
                if($cost == 0 ){
                    $totals['esmart_paypalbrasil_cost']->setValue(0);
                }
                $cost = $totals['esmart_paypalbrasil_cost']->getValue();
            }
        }

        /** @var PayPal\Api\Details $details */
        $details = new PayPal\Api\Details();

        $shipping = isset($totals['shipping']) ? $totals['shipping'] : null;
        if ($shipping instanceof Mage_Sales_Model_Quote_Address_Total) {
            $details->setShipping($shipping->getValue());
        }

        $tax = isset($totals['tax']) ? $totals['tax'] : null;
        if ($tax instanceof Mage_Sales_Model_Quote_Address_Total) {
            $details->setTax($tax->getValue());
        }

        $discount = 0;
        if (isset($totals['discount'])) {
            $discount = $totals['discount']->getValue();
        }

        $details->setSubtotal($totals['subtotal']->getValue() + $discount + $cost);

        $data = array(
            'Shipping' => $details->getShipping(),
            'Tax' => $details->getTax(),
            'Subtotal' => $details->getSubtotal(),
        );
        Esmart_PayPalBrasil_Model_Debug::appendContent('[CREATE AMOUNT - DETAILS ]', 'createDetailsInstallmets', $data);

        return $details;
    }


    /**
     * Create and return Details
     *
     * @param Mage_Sales_Model_Quote $quote Quote object
     *
     * @return PayPal\Api\Details
     */
    protected function createDetailsInstallment(Mage_Sales_Model_Quote $quote)
    {
        /** @var PayPal\Api\Details $details */
        $details = new PayPal\Api\Details();

        $totals = $quote->getTotals();
        $payment = $quote->getPayment();

        $shipping = isset($totals['shipping']) ? $totals['shipping'] : null;
        if ($shipping instanceof Mage_Sales_Model_Quote_Address_Total) {
            $details->setShipping($shipping->getValue());
        }

        $tax = isset($totals['tax']) ? $totals['tax'] : null;
        if ($tax instanceof Mage_Sales_Model_Quote_Address_Total) {
            $details->setTax($tax->getValue());
        }

        $discount = 0;
        if (isset($totals['discount'])) {
            $discount = $totals['discount']->getValue();
        }

        $cost =  $payment->getAdditionalInformation('paypal_plus_installments_cost');

        $details->setSubtotal($totals['subtotal']->getValue() + $discount + $cost);

        $data = array(
            'Shipping' => $details->getShipping(),
            'Tax' => $details->getTax(),
            'Subtotal' => $details->getSubtotal(),
        );
        #Esmart_PayPalBrasil_Model_Debug::appendContent('[CREATE AMOUNT - DETAILS]', 'createPayment', $data);

        return $details;
    }


    /**
     * Get shipping information to use in JS
     *
     * @param Mage_Sales_Model_Quote $quote (Quote object (Mage_Sales_Model_Quote) | Quote ID | null)
     *
     * @return \PayPal\Api\ShippingAddress
     */
    public function createShippingAddress($quote = null)
    {
        /** @var Esmart_PayPalBrasil_Helper_Data $helper */
        $helper = Mage::helper('esmart_paypalbrasil');

        $quote = $helper->getQuote($quote);

        $addressShipping = $quote->getShippingAddress();

        $shipping = new \PayPal\Api\ShippingAddress();

        $firstname = Mage::getStoreConfig('payment/paypal_plus/firstname');
        $firstname = $helper->getDataFromObject($addressShipping, $this->nonPersistedData, $firstname);

        $lastname = Mage::getStoreConfig('payment/paypal_plus/lastname');
        $lastname = $helper->getDataFromObject($addressShipping, $this->nonPersistedData, $lastname);

        $city = Mage::getStoreConfig('payment/paypal_plus/city');
        $city = $helper->getDataFromObject($addressShipping, $this->nonPersistedData, $city);

        $countryCode = Mage::getStoreConfig('payment/paypal_plus/country_code');
        $countryCode = $helper->getDataFromObject($addressShipping, $this->nonPersistedData, $countryCode);
        $countryCode = 'BR';
        $postalCode = Mage::getStoreConfig('payment/paypal_plus/postal_code');
        $postalCode = $helper->getDataFromObject($addressShipping, $this->nonPersistedData, $postalCode);

        $state = Mage::getStoreConfig('payment/paypal_plus/state');
        $state = $helper->getDataFromObject($addressShipping, $this->nonPersistedData, $state);

        if(is_null($state)) {
            $state = '-';
        }

        if (is_numeric($state)) {
            /** @var Mage_Directory_Model_Region $directoryRegion */
            $directoryRegion = Mage::getModel('directory/region')->load($state);
            $state = $directoryRegion->getName();
        }
        /* address street, number , complement and neighborhood info*/

        $ship_phone = Mage::getStoreConfig('payment/paypal_plus/phone');
        if (!empty($ship_phone)) {
            $ship_phone = $helper->getDataFromObject($addressShipping, $this->nonPersistedData, $ship_phone);
        }

        $numberOfLines = Mage::getStoreConfig('customer/address/street_lines');

        switch ($numberOfLines) {
            case 2:
                $line1 = $helper->limitAddres($addressShipping->getStreet(1));
                $line2 = $helper->limitAddres($addressShipping->getStreet(2));
                break;
            case 3:
                $line1 = $helper->limitAddres($addressShipping->getStreet(1));
                $line2 = $helper->limitAddres($addressShipping->getStreet(2) . ' ' . $addressShipping->getStreet(3));
                break;
            case 4:

                $line1 = $helper->limitAddres($addressShipping->getStreet(1));
                $line2 = $helper->limitAddres($addressShipping->getStreet(2) . ' ' . $addressShipping->getStreet(3) . ' ' . $addressShipping->getStreet(4));
                break;
            default:
                $line1 = $helper->limitAddres($addressShipping->getStreetFull());
        }

        if(!empty($line2)){
            $shipping->setLine2($line2);
        }

        $shipping->setRecipientName("{$firstname} {$lastname}")
            ->setCity($city)
            ->setCountryCode($countryCode)
            ->setPostalCode($postalCode)
            ->setLine1($line1)
            ->setPhone($ship_phone)
            ->setState($state);

        $data = array(
            'Recipient Name' => $shipping->getRecipientName(),
            'Line 1' => $helper->limitAddres($shipping->getLine1()),
            'Line 2' => $shipping->getLine2(),
            'City' => $shipping->getCity(),
            'Phone' => $shipping->getPhone(),
            'Postal Code' => $shipping->getPostalCode(),
            'Country Code' => $shipping->getCountryCode(),
            'State' => $shipping->getState(),
        );

        Esmart_PayPalBrasil_Model_Debug::appendContent('[Plus][PAYPAL SHIPPING ADDRESS]', 'createPayment', $data);

        Esmart_PayPalBrasil_Model_Debug::appendContent('[Plus][MAGENTO ADDRESS DATA]', 'createPayment', $addressShipping->toArray());

        return $shipping;
    }


    /**
     * Create and return Payer
     *
     * @return PayPal\Api\Payer
     */
    protected function createPayer()
    {
        $payer = new \PayPal\Api\Payer();

        $payer->setPaymentMethod(self::PAYMENT_METHOD);

        $data = array('payment_method' => self::PAYMENT_METHOD);
        #Esmart_PayPalBrasil_Model_Debug::appendContent('[PAYMENT_METHOD]', 'createPayment', $data);

        return $payer;
    }

    /**
     * Create and return RedirectUrls
     *
     * @return PayPal\Api\RedirectUrls
     */
    protected function createRedirectUrls()
    {
        $redirectUrls = new PayPal\Api\RedirectUrls();
        $baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);

        $redirectUrls->setReturnUrl("$baseUrl/ExecutePayment.php?success=true")
            ->setCancelUrl("$baseUrl/ExecutePayment.php?success=false");

        return $redirectUrls;
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
     * @param Mage_Sales_Model_Quote_Address $address
     * @param string $defaultFieldId
     * @param string $configFieldId
     *
     * @return null|string
     */
    protected function _extractData(Mage_Sales_Model_Quote_Address $address, $defaultFieldId, $configFieldId = null)
    {
        $data = null;

        if (!empty($defaultFieldId)) {
            $data = $address->getData($configFieldId);
        }

        if (empty($data)) {
            $data = $address->getData($defaultFieldId);
        }

        if (!empty($defaultFieldId) && empty($data)) {
            $data = $this->_getFromRequest($configFieldId);
        }

        if (empty($data)) {
            $data = $this->_getFromRequest($defaultFieldId);
        }

        return $data;
    }


    /**
     * @param string|array $index
     *
     * @return mixed
     */
    protected function _getFromRequest($index)
    {
        if (!is_array($index)) {
            $index = array($index);
        }

        $data = null;

        $addressTypes = array(
            Mage_Sales_Model_Quote_Address::TYPE_BILLING,
            Mage_Sales_Model_Quote_Address::TYPE_SHIPPING
        );

        foreach ($addressTypes as $addressType) {
            foreach ($index as $idx) {
                $address = Mage::app()->getRequest()->getParam($addressType);
                $data = isset($address[$idx]) && $address[$idx] ? $address[$idx] : null;

                if (!empty($data)) {
                    return $data;
                }
            }
        }

        return $data;
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
     * Send a patch and returns the payment
     *
     * @return \PayPal\Api\Payment
     */
    public function patchAndGetPayment($paypalPaymentId, $invoice)
    {
        // Add Invoice Number
        $invoiceNumber = new \PayPal\Api\Patch();
        $invoiceNumber->setOp('add')
            ->setPath('/transactions/0/invoice_number')
            ->setValue($invoice);

        // Value to custom and description
        $text = 'Pedido '.$invoice.' - '. $this->getNameStoreFormat();

        $custom = new \PayPal\Api\Patch();
        $custom->setOp('add')
            ->setPath('/transactions/0/custom')
            ->setValue($text);

        // Add description
        $description = new \PayPal\Api\Patch();
        $description->setOp('add')
            ->setPath('/transactions/0/description')
            ->setValue($text);

        // Add patch
        $patchRequest = new \PayPal\Api\PatchRequest();
        $patchRequest->addPatch($invoiceNumber);
        $patchRequest->addPatch($custom);
        $patchRequest->addPatch($description);

        try{
            $data = $this->updatePath($patchRequest,$paypalPaymentId);
        }catch (Exception $e){
            throw new Exception($e->getData());
        }
    }

    /**
     * Getting and formatting name the store
     *
     * @return \PayPal\Api\Payment
     */
    protected function getNameStoreFormat(){
        $name = Mage::getStoreConfig('payment/paypal_plus/paypal_custom');
        return substr($name, 0, 100);
    }


    /**
     * Restores the payment updatePath
     *
     * @return \PayPal\Api\Payment
     */

    protected function updatePath($patchRequest,$paypalPaymentId)
    {
        $payPalSdk = new \PayPal\Api\Payment();
        $payPalSdk->setId($paypalPaymentId);
        $res = $payPalSdk->update($patchRequest,$this->getApiContext());

        return $res;
    }

    protected function getCostOfInstallment(Mage_Sales_Model_Quote $quote){

        $payment = $quote->getPayment();
        $installment = $payment->getAdditionalInformation('paypal_plus_installments');

        if(is_null($installment)){
            $cost =  $quote->getPayment()->getAdditionalInformation('paypal_plus_installments_cost');
        }else {
            /** @var Esmart_PayPalBrasil_Model_Installments */
            $modelInstallments = Mage::getModel('esmart_paypalbrasil/installments');
            $cost = $modelInstallments->calculateTotalCost($this->getGranTotalClean($quote), $installment);

            $quote->getPayment()->unsAdditionalInformation('paypal_plus_installments_cost');
            $quote->getPayment()->setAdditionalInformation('paypal_plus_installments_cost', $cost);
        }
        return $cost;
    }

    public function getGranTotalClean(Mage_Sales_Model_Quote $quote)
    {

        $totals = $quote->getTotals();

        $shipping = isset($totals['shipping']) ? $totals['shipping'] : null;
        $totalShipping = 0;
        if ($shipping instanceof Mage_Sales_Model_Quote_Address_Total) {
            $totalShipping = $shipping->getValue();
        }

        $tax = isset($totals['tax']) ? $totals['tax'] : null;
        $totalTax = 0;
        if ($tax instanceof Mage_Sales_Model_Quote_Address_Total) {
            $totalTax = $tax->getValue();
        }

        $discount = 0;
        if (isset($totals['discount'])) {
            $discount = $totals['discount']->getValue();
        }

        $subtotal = $totals['subtotal']->getValue();
        $grandTotal = $subtotal + $totalTax + $totalShipping + $discount;

        return $grandTotal;
    }

}
