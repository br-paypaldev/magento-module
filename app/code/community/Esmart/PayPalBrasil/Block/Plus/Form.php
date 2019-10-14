<?php

class Esmart_PayPalBrasil_Block_Plus_Form extends Mage_Payment_Block_Form
{
    /**
     * JS module
     * @const string
     */
    const JS_MODULE = 'esmart/paypalbrasil/Esmart_PaypalBrasilPrototype.js';

    /**
     * Internal constructor, that is called from real constructor*
     */
    protected function _construct()
    {
        $this->setTemplate('esmart/paypalbrasil/plus/form.phtml');
        parent::_construct();
    }

    /**
     * Get config of installments
     */
    protected function _config(){
        return Mage::getModel('esmart_paypalbrasil/installments_config');
    }

    /**
     * Preparing global layout
     *
     * You can redefine this method in child classes for changing layout
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _prepareLayout()
    {
        $head = Mage::app()->getLayout()->getBlock('after_body_start');

        if($this->_config()->getOscCheckout() == 'default')
        {
            if($this->_config()->getStatusInstallments())
            {
                $this->setInstallments($this->installments());
            }
        }


        if ($head && false == $head->getChild('js_paypalplus')) {
            $head->append($this->getScriptBlock());
        }

        return parent::_prepareLayout();
    }

    /**
     * Get Block with JS
     *
     * @return Mage_Core_Block_Text
     */
    protected function getScriptBlock()
    {
        if($this->_config()->getOscCheckout() != 'default')
        {
            if ($this->_config()->getStatusInstallments())
            {
                $this->setInstallments($this->installments());
            }
         }
        $scriptblock = Mage::app()->getLayout()->createBlock('core/text', 'js_paypalplus');

        /** @var Esmart_PayPalBrasil_Helper_Data $helper */
        $helper = Mage::helper('esmart_paypalbrasil');
        $uniq = md5(uniqid(rand(), true));
        $scriptblock->setText(
            sprintf(
                '<script src="%s%s" type="text/javascript"></script>
                 <script src="%s" type="text/javascript"></script>',
                Mage::getStoreConfig('payment/paypal_plus/js_address'),
                '',
                $helper->getFullJsUrl(self::JS_MODULE)
            )
        );

        return $scriptblock;
    }

    /**
     * Installments
     *
     * @return Esmart_PayPalBrasil_Model_Installments
     */
    protected function installments(){
        $quote = Mage::getModel('checkout/cart')->getQuote();
        if($this->getData('method')) {
            $quote->getPayment()->setMethod($this->getMethod());
            $quote->setTotalsCollectedFlag(false);
            $quote->collectTotals();
            $quote->save();
        }
        $grandTotal = $quote->getGrandTotal();
        $cost = $quote->getEsmartPaypalbrasilCostAmount();

        /** @var Esmart_PayPalBrasil_Model_Installments $model */
        return Mage::getModel('esmart_paypalbrasil/installments')->installments($grandTotal - $cost,$this->_config()->merchantInstallmentSelection());

    }

    public function getInstallments() {
        return $this->installments();
    }

    /**
     * Return price format Magento
     *
     * @return Esmart_PayPalBrasil_Model_Installments
     */
    public function _formatPrice($price){
        return Mage::helper('core')->currency($price, true, false);
    }

    /**
     * Configuration of Payment with PayPal
     *
     * @return Esmart_PayPalBrasil_Model_Installments
     */
    protected function paymentConfig(){
        $windowPayment  = array(
            'installments' => $this->_config()->getStatusInstallments(),
            'oscCheckout'  => $this->_config()->getOscCheckout(true)
        );
        return json_encode($windowPayment);
    }

}
