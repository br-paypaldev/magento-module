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
 * @category      Esmart
 * @package       Esmart_PayPalBrasil
 * @copyright     Copyright (c) 2013 Smart E-commerce do Brasil Tecnologia LTDA. (http://www.e-smart.com.br)
 * @license       http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @author        Tiago Sampaio <tiago.sampaio@e-smart.com.br>
 */

class Esmart_PayPalBrasil_Controller_Express_Login extends Mage_Paypal_Controller_Express_Abstract
{

        /**
     * Start Express Checkout by requesting initial token and dispatching customer to PayPal
     */
    public function startAction()
    {
        /* remove a shipping method to paypal */
        $this->_config->transferShippingOptions = 0;

        parent::startAction();
    }
    
     /**
     * Return from PayPal and dispatch customer to order review page
     */
    public function returnAction()
    {
        
        try {
            $this->_getCheckoutSession()->unsPaypalTransactionData();
            $this->_checkout = $this->_initCheckout();
            $this->_checkout->returnFromPaypal($this->_initToken());

            /*$taxId = $this->_getQuote()->getPayment()->getAdditionalInformation('buyer_tax_id');

            $customer = $this->_getQuote()->getCustomer();

            $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
            $customer->setStore(Mage::app()->getStore());
            $customer->loadByEmail($this->_getQuote()->getPayment()->getAdditionalInformation('paypal_payer_email'));

            if (!$customer->getId()){

                $billing = $this->_getQuote()->getBillingAddress();
                $billing->setCustomerTaxvat($taxId);

                Mage::helper('core')->copyFieldset('checkout_onepage_billing', 'to_customer', $billing, $customer);

                $customer->save();
            }

            Mage::getSingleton('customer/session')->setCustomer($customer);*/
            #$session->login($customer->getEmail());

            /*
             * always go to review page 
             */
            
            $this->_redirect('paypal/express/review');


            return;
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('checkout/session')->addError($e->getMessage());
        }
        catch (Exception $e) {
            Mage::getSingleton('checkout/session')->addError($this->__('Unable to process Express Checkout approval.'));
            Mage::logException($e);
        }
        $this->_redirect('checkout/cart');
    }

    /**
     * Return checkout quote object
     *
     * @return Mage_Sales_Model_Quote
     */
    private function _getQuote()
    {
        if (!$this->_quote) {
            $this->_quote = $this->_getCheckoutSession()->getQuote();
        }
        return $this->_quote;
    }
}
