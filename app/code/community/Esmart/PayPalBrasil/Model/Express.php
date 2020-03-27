<?php

/**
 *
 * PayPal Express Module
 */
class Esmart_PayPalBrasil_Model_Express extends Mage_Paypal_Model_Express
{
    /**
     * Place an order with authorization or capture action
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param float $amount
     * @return Esmart_PayPalBrasil_Model_Express
     */
    protected function _placeOrder(Mage_Sales_Model_Order_Payment $payment, $amount)
    {
        $order = $payment->getOrder();

        $lenharo = Mage::getStoreConfig('payment/moip_transparente_standard/validador_retorno');

        //If exists paypal discount 1x
        if( empty($lenharo) && ($pp_express_discount =  Mage::app()->getRequest()->getParam('pp_express_discount')) ){
            $amount = $amount + $pp_express_discount;
        }

        // prepare api call
        $token = $payment->getAdditionalInformation(Mage_Paypal_Model_Express_Checkout::PAYMENT_INFO_TRANSPORT_TOKEN);
        $api = $this->_pro->getApi()
            ->setToken($token)
            ->setPayerId($payment->
                getAdditionalInformation(Mage_Paypal_Model_Express_Checkout::PAYMENT_INFO_TRANSPORT_PAYER_ID))
            ->setAmount($amount)
            ->setPaymentAction($this->_pro->getConfig()->paymentAction)
            ->setNotifyUrl(Mage::getUrl('paypal/ipn/'))
            ->setInvNum($order->getIncrementId())
            ->setCurrencyCode($order->getBaseCurrencyCode())
            ->setPaypalCart(Mage::getModel('paypal/cart', array($order)))
            ->setIsLineItemsEnabled($this->_pro->getConfig()->lineItemsEnabled);

        // call api and get details from it
        $api->callDoExpressCheckoutPayment();

        $this->_importToPayment($api, $payment);
        return $this;
    }

    /**
     * Retrieve payment method title
     *
     * @return string
     */
    public function getTitle()
    {
        return 'PayPal ' . $this->getConfigData('title');
    }
}
