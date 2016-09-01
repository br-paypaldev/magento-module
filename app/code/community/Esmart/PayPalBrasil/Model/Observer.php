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
 * @author        Thiago H Oliveira <thiago.oliveira@e-smart.com.br>
 */
class Esmart_PayPalBrasil_Model_Observer
{
    /**
     * Observer to create a Web Profiler in admin panel
     *
     * @param Varien_Event_Observer $observer
     *
     * @return Esmart_PayPalBrasil_Model_Observer
     */
    public function createWebProfilerPaypalPlus(Varien_Event_Observer $observer)
    {
        if (!Mage::getStoreConfig('payment/paypal_plus/active')) {
            return $this;
        }

        $profilerId = Mage::getStoreConfig('payment/paypal_plus/profiler_id');
        if (empty($profilerId)) {
            $model = Mage::getModel('esmart_paypalbrasil/plus');
            $model->createWebProfiler();
        }

        return $this;
    }

    /**
     * Observer to execute payment in Order place
     *
     * @param Varien_Event_Observer $observer
     *
     * @return Esmart_PayPalBrasil_Model_Observer
     */
    public function executePayment(Varien_Event_Observer $observer)
    {
        if (!Mage::getStoreConfig('payment/paypal_plus/active')) {
            return $this;
        }

        $helper = Mage::helper('esmart_paypalbrasil');

        $quote  = $helper->getQuote();

        $payment = $quote->getPayment();

        if ($payment->getMethod() === Esmart_PayPalBrasil_Model_Plus::CODE) {
            Mage::getModel('esmart_paypalbrasil/plus')->executePayment();
        }

        return $this;
    }

    /**
     * Append JS event after block Form
     *
     * @param $observer
     *
     * @return $this
     */
    public function appendJsEvents(Varien_Event_Observer $observer)
    {
        if (!Mage::getStoreConfig('payment/paypal_plus/active')) {
            return $this;
        }

        $block = $observer->getBlock();

        if (!($block instanceof Esmart_PayPalBrasil_Block_Plus_Form)) {
            return $this;
        }

        /** @var Varien_Object $transport */
        $transport  = $observer->getTransport();
        $html       = $transport->getHtml();
        $html      .= $this->_helper()->getEventsScriptBlock();

        $transport->setHtml($html);

        return $this;
    }

    /**
     * Delete payment method existent
     *
     * @return $this
     */
    public function deletePaymentMethodExistent($observer)
    {
        if (!Mage::getStoreConfig('payment/paypal_plus/active')) {
            return $this;
        }

        /** @var Esmart_PayPalBrasil_Helper_Data $helper */
        $helper = Mage::helper('esmart_paypalbrasil');

        if ($helper->getCheckoutType(false)) {
            return $this;
        }

        $quote  = $helper->getQuote();

        $payment = $quote->getPayment();

        if ($payment->getMethod() === Esmart_PayPalBrasil_Model_Plus::CODE) {
            $payment->setData('method', null);
            $payment->setData('additional_information', null);
            $payment->save();
        }

        return $this;
    }

    /**
     * Cancel Order if payment was DECLINED
     *
     * @return $this
     */
    public function PaypalDeclinedOrder(Varien_Event_Observer $observer)
    {
        

        $order  = $observer->getOrder();

        $payment = $order->getPayment();

        if ($payment->getMethod() === Esmart_PayPalBrasil_Model_Plus::CODE) {
            if($order->getData('paypal_set_cancel')){
                $order->cancel()
                    ->save();
            }

        }

        return $this;
    }


    /**
     * @return Esmart_PayPalBrasil_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('esmart_paypalbrasil');
    }
    
}
