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

/**
 * Class Esmart_PayPalBrasil_Model_Express_Checkout defines checkout type
 */
class Esmart_PayPalBrasil_Model_Express_Checkout extends Mage_Paypal_Model_Express_Checkout
{

    /**
     * Api Model Type
     *
     * @var string
     */
    protected $_apiType = 'paypal/api_nvp';


    /**
     * Try to find whether the code provided by PayPal corresponds to any of possible shipping rates
     * This method was created only because PayPal has issues with returning the selected code.
     * If in future the issue is fixed, we don't need to attempt to match it. It would be enough to set the method code
     * before collecting shipping rates
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @param string $selectedCode
     *
     * @return string
     */
    public function matchShippingMethodCode(Mage_Sales_Model_Quote_Address $address, $selectedCode)
    {
        return $this->_matchShippingMethodCode($address, $selectedCode);
    }


    /**
     * Gets the API Model
     *
     * @return Mage_Paypal_Model_Api_Nvp
     */
    public function getApi()
    {
        return $this->_getApi();
    }


    /**
     * Checks if the avoid_review_page config field is enabled
     *
     * @return string|null
     */
    public function isAvoidReviewPageEnabled()
    {
        return $this->_helper()->getExpressConfig('avoid_review_page');
    }


    /**
     * Gets the helper singleton object
     *
     * @return Esmart_PayPalBrasil_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('esmart_paypalbrasil');
    }

    /**
     * Update quote when returned from PayPal
     * rewrite billing address by paypal
     * save old billing address for new customer
     * export shipping address in case address absence
     *
     * @param string $token
     */
    public function returnFromPaypal($token)
    {
        $this->_getApi();
        $this->_api->setToken($token)
            ->callGetExpressCheckoutDetails();
        $quote = $this->_quote;
        $infoButton = 'button';
        if (defined('self::PAYMENT_INFO_BUTTON')) {
            $infoButton = self::PAYMENT_INFO_BUTTON;
        }

        // $this->_ignoreAddressValidation();
        $this->_quote->getBillingAddress()->setShouldIgnoreValidation(true);
        if (!$this->_quote->getIsVirtual()) {
            $this->_quote->getShippingAddress()->setShouldIgnoreValidation(true);
            if (!$this->_config->requireBillingAddress && !$this->_quote->getBillingAddress()->getEmail()) {
                $this->_quote->getBillingAddress()->setSameAsBilling(1);
            }
        }

        // import shipping address
        $exportedShippingAddress = $this->_api->getExportedShippingAddress();
        if (!$quote->getIsVirtual()) {
            $shippingAddress = $quote->getShippingAddress();
            if ($shippingAddress) {
                if ($exportedShippingAddress
                    && $quote->getPayment()->getAdditionalInformation($infoButton) == 1
                ) {
                    $this->_setExportedAddressData($shippingAddress, $exportedShippingAddress);
                    // PayPal doesn't provide detailed shipping info: prefix, middlename, lastname, suffix
                    $shippingAddress->setPrefix(null);
                    $shippingAddress->setMiddlename(null);
                    $shippingAddress->setLastname(null);
                    $shippingAddress->setSuffix(null);
                    $shippingAddress->setCollectShippingRates(true);
                    $shippingAddress->setSameAsBilling(0);
                }

                // import shipping method
                $code = '';
                if ($this->_api->getShippingRateCode()) {
                    if ($code = $this->_matchShippingMethodCode($shippingAddress, $this->_api->getShippingRateCode())) {
                        // possible bug of double collecting rates :-/
                        $shippingAddress->setShippingMethod($code)->setCollectShippingRates(true);
                    }
                }
                $quote->getPayment()->setAdditionalInformation(
                    self::PAYMENT_INFO_TRANSPORT_SHIPPING_METHOD,
                    $code
                );
            }
        }

        // import billing address
        $portBillingFromShipping = $quote->getPayment()->getAdditionalInformation($infoButton) == 1
            && $this->_config->requireBillingAddress != Mage_Paypal_Model_Config::REQUIRE_BILLING_ADDRESS_ALL
            && !$quote->isVirtual();
        if ($portBillingFromShipping) {
            $billingAddress = clone $shippingAddress;
            $billingAddress->unsAddressId()
                ->unsAddressType();
            $data = $billingAddress->getData();
            $data['save_in_address_book'] = 0;
            $quote->getBillingAddress()->addData($data);
            $quote->getShippingAddress()->setSameAsBilling(1);
        } else {
            $billingAddress = $quote->getBillingAddress();
        }
        $exportedBillingAddress = $this->_api->getExportedBillingAddress();
        //$this->_setExportedAddressData($billingAddress, $exportedBillingAddress);
        $billingAddress->setCustomerNotes($exportedBillingAddress->getData('note'));
        $quote->setBillingAddress($billingAddress);

        // import payment info
        $payment = $quote->getPayment();
        $payment->setMethod($this->_methodType);
        Mage::getSingleton('paypal/info')->importToPayment($this->_api, $payment);
        $payment->setAdditionalInformation(self::PAYMENT_INFO_TRANSPORT_PAYER_ID, $this->_api->getPayerId())
            ->setAdditionalInformation(self::PAYMENT_INFO_TRANSPORT_TOKEN, $token)
        ;
        $quote->collectTotals()->save();
    }
}
