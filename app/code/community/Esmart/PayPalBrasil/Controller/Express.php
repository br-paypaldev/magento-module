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

class Esmart_PayPalBrasil_Controller_Express extends Mage_Paypal_Controller_Express_Abstract
{

    /**
     * @var Esmart_PayPalBrasil_Model_Express_Checkout
     */
    protected $_checkout = null;


    /**
     * Instantiate quote and checkout
     *
     * @throws Mage_Core_Exception
     */
    protected function _initCheckout()
    {
        /** @var Mage_Sales_Model_Quote $quote */
        $quote = $this->_getQuote();
        if (!$quote->hasItems() || $quote->getHasError()) {
            $this->getResponse()->setHeader('HTTP/1.1', '403 Forbidden');
            Mage::throwException(Mage::helper('paypal')->__('Unable to initialize Express Checkout.'));
        }
        $quote->getPayment()->setMethod($this->_configMethod);

        $config = array(
            'config' => $this->_config,
            'quote'  => $quote,
        );

        $this->_checkout = Mage::getSingleton($this->_checkoutType, $config);
    }


    /**
     * Return checkout quote object
     *
     * @return Mage_Sale_Model_Quote
     */
    private function _getQuote()
    {
        if (!$this->_quote) {
            $this->_quote = $this->_getCheckoutSession()->getQuote();
        }
        return $this->_quote;
    }


    /**
     * Returns the express checkout object
     *
     * @return Esmart_PayPalBrasil_Model_Express_Checkout
     */
    protected function _getCheckout()
    {
        return $this->_checkout;
    }


    /**
     * Returns the API object
     *
     * @return Mage_Paypal_Model_Api_Nvp
     */
    protected function _getApi()
    {
        return $this->_getCheckout()->getApi();
    }


    /**
     * Return checkout session object
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }


    /**
     * PayPal session instance getter
     *
     * @return Mage_PayPal_Model_Session
     */
    private function _getSession()
    {
        return Mage::getSingleton('paypal/session');
    }


    /**
     * Validates if the shipping method really exists and is available
     *
     * @param null $code
     *
     * @return bool
     */
    protected function _validateShippingMethod($code = null)
    {
        $_isValid = (bool) false;

        if (!is_null($code)) {
            /**
             * @todo Implement logic validation for shipping method to make sure it exists
             */
            $_isValid = (bool) true;
        }

        return $_isValid;
    }


    /**
     * Return from PayPal and dispatch customer to order review page if it's necessary.
     * Otherwise the order is placed without pass trough review page.
     */
    public function returnAction()
    {
        try {

            /**
             * Initializes the checkout model
             */
            $this->_initCheckout();
            $this->_checkout->returnFromPaypal($this->_initToken());

            /**
             * @var $shippingAddress Mage_Sales_Model_Quote_Address
             */
            $shippingAddress = $this->_getQuote()->getShippingAddress();
            $shippingRateCode = $this->_getApi()->getShippingRateCode();

            $isCustomerNew = true;
            if ($this->_getApi()->getEmail()) {
                $customer = Mage::getModel('customer/customer');
                $customer->setWebsiteId(Mage::app()->getWebsite()->getId())
                    ->loadByEmail($this->_getApi()->getEmail());
                if ($customer->getId()) {
                    $isCustomerNew = false;
                }
            }

            $shippingMethod = null;

            /**
             * This is a configuration created in configuration page.
             * If enabled it tries to assign the shipping method automatically to quote object
             * and avoid the review page of the original module.
             */
            if ($this->_checkout->isAvoidReviewPageEnabled()) {
                if (!is_null($shippingRateCode) && $shippingRateCode && ($shippingRateCode != 'no_rate')) {

                    $code = $this->_checkout->matchShippingMethodCode(
                        $shippingAddress,
                        $this->_getApi()->getShippingRateCode()
                    );

                    if ($code) {
                        $shippingMethod = $code;
                    }
                } else {
                    if (!is_null($shippingAddress->getShippingMethod())) {
                        $shippingMethod = $shippingAddress->getShippingMethod();
                        /* Used only for test forcing the shipping method. */
                        //$shippingMethod = 'ups_XPD';
                    }
                }

                if (!is_null($shippingMethod)) {
                    // possible bug of double collecting rates :-/
                    $shippingAddress->setShippingMethod($shippingMethod)
                        ->setCollectShippingRates(true)
                        ->save();
                }
            }

            /**
             * If there's no shipping method already choosen by customer it's necessary to redirect customer
             * to order review page.
             */
            if (is_null($shippingMethod) || !$this->_validateShippingMethod($shippingMethod)) {
                $this->_redirect('*/*/review');
                return;
            }

            /**
             * If there's a shipping method already set to ShippingAddres then avoid the paypal/express/review page
             * and place the order.
             */
            //Obsolete since PayPal return never send POST values/parameters, and agreement will never populated here
            /*
             $requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds();
            if ($requiredAgreements) {
                $postedAgreements = array_keys($this->getRequest()->getPost('agreement', array()));
                if (array_diff($requiredAgreements, $postedAgreements)) {
                    Mage::throwException(
                        Mage::helper('paypal')->__(
                            'Please agree to all the terms and conditions before placing the order.'
                        )
                    );
                }
            }
            */

            /**
             * Places the Order
             */
            $this->_checkout->place($this->_initToken(), $shippingMethod);

            // prepare session to success or cancellation page
            $session = $this->_getCheckoutSession();
            $session->clearHelperData();

            // "last successful quote"
            $quoteId = $this->_getQuote()->getId();
            $session->setLastQuoteId($quoteId)->setLastSuccessQuoteId($quoteId);

            // an order may be created
            $order = $this->_checkout->getOrder();
            if ($order) {
                $session->setLastOrderId($order->getId())
                    ->setLastRealOrderId($order->getIncrementId());
                // as well a billing agreement can be created
                $agreement = $this->_checkout->getBillingAgreement();
                if ($agreement) {
                    $session->setLastBillingAgreementId($agreement->getId());
                }

                /**
                 * Forces the new order e-mail
                 */
                $order->sendNewOrderEmail();

                /* @var $customer Mage_Customer_Model_Customer */
                $customer = $this->_getQuote()->getCustomer();
                if($isCustomerNew === true) {
                    $customer->sendNewAccountEmail();
                }
            }

            // recurring profiles may be created along with the order or without it
            $profiles = $this->_checkout->getRecurringPaymentProfiles();
            if ($profiles) {
                $ids = array();
                foreach ($profiles as $profile) {
                    $ids[] = $profile->getId();
                }
                $session->setLastRecurringProfileIds($ids);
            }

            // redirect if PayPal specified some URL (for example, to Giropay bank)
            $url = $this->_checkout->getRedirectUrl();
            if ($url) {
                $this->getResponse()->setRedirect($url);
                return;
            }

            $this->_initToken(false); // no need in token anymore
            $this->_redirect('checkout/onepage/success');

            return;

        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('checkout/session')->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::getSingleton('checkout/session')->addError($this->__('Unable to process Express Checkout approval.'));
            Mage::logException($e);
        }

        $this->_redirect('checkout/cart');
    }


    /**
     * Submit the order
     */
    public function placeOrderAction()
    {
        try {
            $requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds();
            if ($requiredAgreements) {
                $postedAgreements = array_keys($this->getRequest()->getPost('agreement', array()));
                if (array_diff($requiredAgreements, $postedAgreements)) {
                    Mage::throwException(Mage::helper('paypal')->__(
                        'Please agree to all the terms and conditions before placing the order.')
                    );
                }
            }

            $this->_initCheckout();

            /**
             * This is a essential piece of code for saving the customer when order is placed.
             * It doesn't perform well but makes the things work.
             */
            $this->_checkout->returnFromPaypal($this->_initToken());

            $this->_checkout->place($this->_initToken());

            // prepare session to success or cancellation page
            $session = $this->_getCheckoutSession();
            $session->clearHelperData();

            // "last successful quote"
            $quoteId = $this->_getQuote()->getId();
            $session->setLastQuoteId($quoteId)->setLastSuccessQuoteId($quoteId);

            // an order may be created
            $order = $this->_checkout->getOrder();
            if ($order) {
                $session->setLastOrderId($order->getId())
                    ->setLastRealOrderId($order->getIncrementId());
                // as well a billing agreement can be created
                $agreement = $this->_checkout->getBillingAgreement();
                if ($agreement) {
                    $session->setLastBillingAgreementId($agreement->getId());
                }
            }

            // recurring profiles may be created along with the order or without it
            $profiles = $this->_checkout->getRecurringPaymentProfiles();
            if ($profiles) {
                $ids = array();
                foreach($profiles as $profile) {
                    $ids[] = $profile->getId();
                }
                $session->setLastRecurringProfileIds($ids);
            }

            // redirect if PayPal specified some URL (for example, to Giropay bank)
            $url = $this->_checkout->getRedirectUrl();
            if ($url) {
                $this->getResponse()->setRedirect($url);
                return;
            }
            $this->_initToken(false); // no need in token anymore
            $this->_redirect('checkout/onepage/success');
            return;
        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        catch (Exception $e) {
            $this->_getSession()->addError($this->__('Unable to place the order.'));
            Mage::logException($e);
        }
        $this->_redirect('*/*/review');
    }

}
