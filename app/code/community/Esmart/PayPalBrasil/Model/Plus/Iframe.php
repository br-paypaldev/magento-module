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


    function __construct()
    {
        $this->nonPersistedData = new Varien_Object();

        Esmart_PayPalBrasil_Model_Plus_Paypal_Autoload::register();
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
            Esmart_PayPalBrasil_Model_Debug::appendContent('[FRONTEND FORM DATA]', 'createPayment', $data);
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
    public function getCustomerInformation($quote = null)
    {
        /** @var Esmart_PayPalBrasil_Helper_Data $helper */
        $helper = $this->_helper();

        /** @var Mage_Sales_Model_Quote $quote */
        $quote  = $helper->getQuote($quote);

        /**
         * @var Mage_Customer_Model_Customer                               $customer
         * @var Mage_Sales_Model_Quote_Address|Mage_Customer_Model_Address $address
         */
        $customer   = $quote->getCustomer();
        $address    = $quote->getBillingAddress();

        $firstname  = $this->_getFirstname($address);
        $lastname   = $this->_getLastname($address);
        $email      = $this->_getEmail($address);
        $payerTaxId = $this->_getPayerTaxId($address);
        $phone      = $this->_getTelephone($address);

        $return = array(
            'payerFirstName' => $firstname,
            'payerLastName'  => $lastname,
            'payerEmail'     => $email,
            'payerTaxIdType' => $helper->checkIsCpfOrCnpj($payerTaxId),
            'payerTaxId'     => $payerTaxId,
            'payerPhone'     => $phone,
            'rememberedCards'=> $customer->getPpalRememberedCards(),
        );

        Esmart_PayPalBrasil_Model_Debug::appendContent('[RETURN getCustomerInformation()]', 'createPayment', $return);
        #Esmart_PayPalBrasil_Model_Debug::appendContent('[MAGENTO CUSTOMER DATA]', 'createPayment', $customer->toArray());

        if(!Esmart_PayPalBrasil_Model_Paypal_Validate::is(array($firstname,$lastname), 'OnlyWords', true) ||
           !Esmart_PayPalBrasil_Model_Paypal_Validate::is($email, 'AddressMail', false) ||
           !Esmart_PayPalBrasil_Model_Paypal_Validate::is($phone, 'OnlyNumbers', true) ||
           !Esmart_PayPalBrasil_Model_Paypal_Validate::isValidTaxvat($payerTaxId)){
            throw new Exception("getCustomerInformation Exception", 1);            
        }

        return $return;
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
         * @var Mage_Sales_Model_Quote       $quote
         * @var Mage_Customer_Model_Customer $customer
         */
        $quote    = $this->_getQuote($address);
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
            'mode'        => $this->getMode(),
        );

        Esmart_PayPalBrasil_Model_Debug::appendContent('[APPROVAL URL]', 'createPayment', $data);

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

        $transaction = $this->createTransaction($quote);

        $payment = new PayPal\Api\Payment();

        $profileId = Mage::getStoreConfig('payment/paypal_plus/profiler_id');

        $data = array('profile_id' => $profileId);
        Esmart_PayPalBrasil_Model_Debug::appendContent('[PROFILE]', 'createPayment', $data);

        $payment->setIntent(self::INTENT_PAYMENT)
            ->setPayer($this->createPayer())
            ->setRedirectUrls($this->createRedirectUrls())
            ->setTransactions(array($transaction))
            ->setExperienceProfileId($profileId);

        try {
            Esmart_PayPalBrasil_Model_Debug::appendContent(
                '[CREATE PAYMENT REQUEST]', 'createPayment',
                array(var_export($payment->toArray(), true))
            );
            
            $payment->create($this->getApiContext());

             Esmart_PayPalBrasil_Model_Debug::appendContent(
                '[CREATE PAYMENT RESPONSE]', 'createPayment',
                array(var_export($payment->toArray(), true))
            );

            $quote->getPayment()
                ->setAdditionalInformation('paypal_plus_payment_id', $payment->getId())
                ->setAdditionalInformation('paypal_plus_payment_state', $payment->getState())
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

        if (!$quote->getReservedOrderId()) {
            $quote->reserveOrderId()->save();
        }
        $InvoiceNumber = $quote->getReservedOrderId();
        $data = array('order' => $quote->getReservedOrderId());
        #Esmart_PayPalBrasil_Model_Debug::appendContent('[QUOTE MAGENTO]', 'createPayment', $data);

        $customInfo = array(Mage::getStoreConfig('payment/paypal_plus/paypal_custom'), $quote->getReservedOrderId());
        $customInfo = vsprintf(self::CUSTOM_BASE_INFORMATION, $customInfo);

        $transaction->setAmount($this->createAmount($quote))
            ->setPaymentOptions($this->createPaymentOptions())
            ->setItemList($this->createItemList($quote))
            ->setCustom($customInfo);
        $transaction->setInvoiceNumber($InvoiceNumber);

        return $transaction;
    }

    /**
     * Create and return Amount
     *
     * @param Mage_Sales_Model_Quote $quote Quote object
     *
     * @return PayPal\Api\Amount
     */
    protected function createAmount(Mage_Sales_Model_Quote $quote)
    {
        /** @var PayPal\Api\Amount $amount */
        $amount = new PayPal\Api\Amount();

        $amount->setCurrency($quote->getBaseCurrencyCode())
            ->setTotal($quote->getGrandTotal())
            ->setDetails($this->createDetails($quote));

        $data = array(
            'Base Currency' => $quote->getBaseCurrencyCode(),
            'Total'         => $quote->getGrandTotal(),
        );
        #Esmart_PayPalBrasil_Model_Debug::appendContent('[CREATE AMOUNT]', 'createPayment', $data);

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

        $totals   = $quote->getTotals();
        if (isset($totals['discount'])) {
            $objItem = new PayPal\Api\Item();

            $objItem->setName('Descontos')
                ->setCurrency($quote->getBaseCurrencyCode())
                ->setQuantity(1)
                ->setPrice($totals['discount']->getValue());

            $itemList->addItem($objItem);

            $data[] = $objItem->toJSON();
        }

        Esmart_PayPalBrasil_Model_Debug::appendContent('[CREATE ITEM LIST]', 'createPayment', $data);

        // append shipping information
        $itemList->setShippingAddress($this->createShippingAddress($quote));

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

        $totals   = $quote->getTotals();

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

        $details->setSubtotal($totals['subtotal']->getValue() + $discount);

        $data = array(
            'Shipping' => $details->getShipping(),
            'Tax'      => $details->getTax(),
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

        $quote  = $helper->getQuote($quote);

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

        if (is_numeric($state)) {
            /** @var Mage_Directory_Model_Region $directoryRegion */
            $directoryRegion = Mage::getModel('directory/region')->load($state);
            $state = $directoryRegion->getName();
        }
        /* address street, number , complement and neighborhood info*/

        $line1_p1 = Mage::getStoreConfig('payment/paypal_plus/address_line_1_p1');
        $line1_p2 = Mage::getStoreConfig('payment/paypal_plus/address_line_1_p2');
        $line1_p3 = Mage::getStoreConfig('payment/paypal_plus/address_line_1_p3');
        $line2 = Mage::getStoreConfig('payment/paypal_plus/address_line_2');
        $ship_phone = Mage::getStoreConfig('payment/paypal_plus/phone');

        if ($line1_p1 == 'street') {
            $line1_data1 = str_replace(PHP_EOL, ', ', $addressShipping->getStreetFull());
        }elseif (!empty($line1_p1)){
            $line1_data1 = $helper->getDataFromObject($addressShipping, $this->nonPersistedData, $line1_p1);
        }
        if ($line1_p1){ $line1 = "{$line1_data1}"; }
        
        if($line1_p1 == 'street' || empty($line1_p2)){
            $line1_p2 = true;
        }elseif (!empty($line1_p2)) {
            $line1_data2 = $helper->getDataFromObject($addressShipping, $this->nonPersistedData, $line1_p2);
            if ($line1_data2) { $line1 = $line1 . ", {$line1_data2}"; }            
        }
        
        if (!empty($line1_p3)) {
            $line1_p3 = $helper->getDataFromObject($addressShipping, $this->nonPersistedData, $line1_p3);
            if ($line1_p3) { $line1 = $line1 . ", {$line1_p3}"; }            
        }
        
        if (!empty($line2)) {
            $line2 = $helper->getDataFromObject($addressShipping, $this->nonPersistedData, $line2);
            /* set shipping*/
            $shipping->setLine2($line2);
        }
       
        if (!empty($ship_phone)) {
            $ship_phone = $helper->getDataFromObject($addressShipping, $this->nonPersistedData, $ship_phone);
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
            'Line 1'         => $shipping->getLine1(),
            'Line 2'         => $shipping->getLine2(),
            'City'           => $shipping->getCity(),
            'Phone'          => $shipping->getPhone(),
            'Postal Code'    => $shipping->getPostalCode(),
            'Country Code'   => $shipping->getCountryCode(),
            'State'          => $shipping->getState(),
        );
        
        Esmart_PayPalBrasil_Model_Debug::appendContent('[PAYPAL SHIPPING ADDRESS]', 'createPayment', $data);

        Esmart_PayPalBrasil_Model_Debug::appendContent('[MAGENTO ADDRESS DATA]', 'createPayment', $addressShipping->toArray());

        if(!Esmart_PayPalBrasil_Model_Paypal_Validate::is(array($firstname, $lastname, $city, $state), 'OnlyWords', true) ||
           !Esmart_PayPalBrasil_Model_Paypal_Validate::is($ship_phone, 'OnlyNumbers', true) ||
           empty($line1_data1) || empty($line1_p2)){
            throw new Exception("[SHIPPING ADDRESS] Exception", 2);            
        }

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
        $baseUrl      = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);

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
        $apiContext  = new PayPal\Rest\ApiContext($this->getOAuthCredential());

        $mode = array(
            'mode' => $this->getMode(),
        );

        $apiContext->setConfig($mode);
        $apiContext->addRequestHeader("PayPal-Partner-Attribution-Id" , 'Magento_Cart_CE_BR_PPPlus');

        Esmart_PayPalBrasil_Model_Debug::appendContent('[OPERATION MODE]', 'default', $mode);

        return $apiContext;
    }

    /**
     * Get OAuth credential
     *
     * @return PayPal\Auth\OAuthTokenCredential
     */
    public function getOAuthCredential()
    {
        $helper    = Mage::helper('core');
        $clientId  = $helper->decrypt(Mage::getStoreConfig('payment/paypal_plus/app_client_id'));
        $appSecret = $helper->decrypt(Mage::getStoreConfig('payment/paypal_plus/app_secret'));

        $oAuthToken =  new PayPal\Auth\OAuthTokenCredential($clientId, $appSecret);

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
     * @param string                         $defaultFieldId
     * @param string                         $configFieldId
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
                $data    = isset($address[$idx]) && $address[$idx] ? $address[$idx] : null;

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
}