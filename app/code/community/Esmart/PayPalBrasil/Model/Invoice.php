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
 */
class Esmart_PayPalBrasil_Model_Invoice extends Mage_Payment_Model_Method_Abstract
{

  const CODE = 'paypal_invoice';

	protected $_canUseCheckout  = false;
	protected $_isGateway       = true;
  protected $_canAuthorize    = true;
  protected $_canCapture      = true;
  protected $_isInitializeNeeded  = true;
  protected $_canFetchTransactionInfo     = true;


	protected $_code	= self::CODE;

  protected $_infoBlockType   = 'esmart_paypalbrasil/invoice_info';

    public function __construct()
    {
        parent::__construct();

        Esmart_PayPalBrasil_Model_Plus_Paypal_Autoload::register();

    }

	  public function isAvailable($quote = null)
    {
    	return Mage::getStoreConfig('payment/paypal_invoice/active');
    }

    /**
     * Get config payment action url
     * Used to universalize payment actions when processing payment place
     *
     * @return string
     */
    public function getConfigPaymentAction()
    {
        return Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE;
    }


    /**
     * Method that will be executed instead of authorize or capture
     * if flag isInitializeNeeded set to true
     *
     * @param string $paymentAction
     * @param object $stateObject
     *
     * @return Mage_Payment_Model_Abstract
     */
    public function initialize($paymentAction, $stateObject)
    {

        $payment = $this->getInfoInstance();
 
       
        $order = $payment->getOrder();

        $items = $order->getAllVisibleItems();

        /* Merchant Information */
        $paypalinvoice = $this->_preparePaypalInvoice();

        /* set number to IPN */
        $paypalinvoice->setNumber($order->getIncrementId());


        /* all items */
        $invoiceItems = array();


          foreach ($items as $_item) {

            $invoiceItem = new PayPal\Api\InvoiceItem();

            $invoiceItem->setName($_item->getName())
                ->setQuantity($_item->getQtyOrdered());

            $currency    = new  PayPal\Api\Currency();

            $currency->setCurrency("BRL")
                    ->setValue($_item->getPrice());

            $invoiceItem->setUnitPrice($currency);

            $invoiceItems[] = $invoiceItem;

          }

          /* discount cost Order */
          $cost = new PayPal\Api\Cost();
          $discount   = new  PayPal\Api\Currency();

          $discount->setCurrency("BRL")
                  ->setValue(abs($order->getDiscountAmount()));

          $cost->setAmount($discount);

          $paypalinvoice->setDiscount($cost);


          /* shipping cost Order*/
          $frete = new \PayPal\Api\ShippingCost();

          $frete_price    = new  PayPal\Api\Currency();

          $frete_price->setCurrency("BRL")
                  ->setValue($order->getShippingAmount());

           //$frete->setUnitPrice($frete_price);

          $frete->setAmount($frete_price);

          $paypalinvoice->setShippingCost($frete);

          $billingAddress = $order->getBillingAddress();
          $shippingAddress = $order->getShippingAddress();


          /* billing information Customer */
          $billing = new PayPal\Api\BillingInfo();

          /* customer need to have a Paypal account */
          $billing->setEmail($order->getCustomerEmail());
          $billing->setBusinessName($order->getCustomerName());

          $billing->setPhone(new PayPal\Api\Phone())
              ->setAddress(new PayPal\Api\InvoiceAddress());

         $billing->getPhone()
              ->setCountryCode("55")
              ->setNationalNumber($billingAddress->getTelephone());

          $billing->getAddress()
              ->setLine1($billingAddress->getStreetFull())
              ->setCity($billingAddress->getCity())
              ->setState($billingAddress->getRegionCode())
              ->setPostalCode($billingAddress->getPostcode())
              ->setCountryCode("BR");

              

          $paypalinvoice->setBillingInfo(array($billing));



          /* shipping information */
           $shippingInfo = new PayPal\Api\ShippingInfo();

          $shippingInfo
              ->setFirstName($shippingAddress->getFirstname())
              ->setLastName($shippingAddress->getLastname())
              ->setPhone(new PayPal\Api\Phone())
              ->setAddress(new PayPal\Api\InvoiceAddress());

         $shippingInfo->getPhone()
              ->setCountryCode("55")
              ->setNationalNumber($shippingAddress->getTelephone());

          $shippingInfo->getAddress()
              ->setLine1($shippingAddress->getStreetFull())
              ->setCity($shippingAddress->getCity())
              ->setState($shippingAddress->getRegionCode())
              ->setPostalCode($shippingAddress->getPostcode())
              ->setCountryCode("BR");


          $paypalinvoice->setShippingInfo($shippingInfo);

                
          try {
                $ApiContext =  $this->_getOAuthToken();
                $paypalinvoice->setItems($invoiceItems);
                //print_r($clientId. $appSecret);


              $paypalinvoice->create($ApiContext);

              $payment->setAdditionalInformation('paypal_invoice_id', $paypalinvoice->getId());

              $paypalinvoice->send($ApiContext);


              $invoice = new PayPal\Api\Invoice();

              $info =  $invoice->get($paypalinvoice->getId(),$ApiContext);
              $payerUrl = preg_replace('/paypal.com/', 'paypal.com/br', $info->getMetadata()->getPayerViewUrl());
              $payment->setAdditionalInformation('paypal_invoice_payerviewurl', $payerUrl);
              
              
          } catch (Exception $e) {
               Mage::throwException($e->getMessage);
          }

        if (!$this->canAuthorize()) {
            Mage::throwException(Mage::helper('payment')->__('Authorize action is not available.'));
        }
        return $this;
    }

    public function processInvoice($invoice, $payment)
    {
        $invoice->setTransactionId($payment->getLastTransId());
        return $this;
    }


    protected function _preparePaypalInvoice()
    {
       $invoice = new PayPal\Api\Invoice();

        $merchantinfo  = new PayPal\Api\MerchantInfo();

        $merchantinfo->setEmail(Mage::getStoreConfig('paypal/general/business_account'));

        $invoice->setMerchantInfo($merchantinfo);
        return $invoice;
    }


    protected function _getOAuthToken(){

        $helper    = Mage::helper('core');
        $clientId  = $helper->decrypt(Mage::getStoreConfig('payment/paypal_invoice/app_client_id'));
        $appSecret = $helper->decrypt(Mage::getStoreConfig('payment/paypal_invoice/app_secret'));

        $oAuthToken =  new PayPal\Auth\OAuthTokenCredential($clientId, $appSecret);


        $apiContext  = new PayPal\Rest\ApiContext($oAuthToken);

        $apiContext->addRequestHeader("PayPal-Partner-Attribution-Id" , 'Magento_Cart_CE_BR_Invoice');

        if(Mage::getStoreConfig('payment/paypal_invoice/sandbox_flag')){

          $apiContext->setConfig(array('mode' => 'sandbox'));

        }else{

           $apiContext->setConfig(array('mode' => 'live'));

        }

        

        return $apiContext;
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
        return  $this->_pro->fetchTransactionInfo($payment, $transactionId);
    }

}