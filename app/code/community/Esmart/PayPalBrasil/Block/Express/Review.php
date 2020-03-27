<?php

/**
 *
 * @package Esmart/PayPalBrasil
 * @author Paulo H. Araujo <paulo@imaginationmedia.com>
 * @copyright Copyright (c) 2019 Imagination Media (https://www.imaginationmedia.com/)
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */


class Esmart_PayPalBrasil_Block_Express_Review extends Mage_Paypal_Block_Express_Review
{
    /**
     * Retrieve payment method and assign additional template values
     *
     * @return Esmart_PayPalBrasil_Block_Express_Review
     */
    protected function _beforeToHtml()
    {
        $methodInstance = $this->_quote->getPayment()->getMethodInstance();
        $this->setPaymentMethodTitle($methodInstance->getTitle());

        $this->setShippingRateRequired(true);
        if ($this->_quote->getIsVirtual()) {
            $this->setShippingRateRequired(false);
        } else {
            // prepare shipping rates
            $this->_address = $this->_quote->getShippingAddress();
            $groups = $this->_address->getGroupedAllShippingRates();
            if ($groups && $this->_address) {
                $this->setShippingRateGroups($groups);
                // determine current selected code & name
                foreach ($groups as $code => $rates) {
                    foreach ($rates as $rate) {
                        if ($this->_address->getShippingMethod() == $rate->getCode()) {
                            $this->_currentShippingRate = $rate;
                            break(2);
                        }
                    }
                }
            }

            $parcelas = $this->_quote->getPayment()->getAdditionalInformation('plots');
            $valorParcela = $this->_quote->getPayment()->getAdditionalInformation('plots_val');
            $this->setParcelas($parcelas);
            $this->setValorParcela($valorParcela);

            $canEditShippingAddress = $this->_quote->getMayEditShippingAddress() && $this->_quote->getPayment()
                    ->getAdditionalInformation(Mage_Paypal_Model_Express_Checkout::PAYMENT_INFO_BUTTON) == 1;
            // misc shipping parameters
            $this->setShippingMethodSubmitUrl($this->getUrl("{$this->_paypalActionPrefix}/express/saveShippingMethod"))
                ->setCanEditShippingAddress($canEditShippingAddress)
                ->setCanEditShippingMethod($this->_quote->getMayEditShippingMethod())
            ;
        }

        $this->setEditUrl($this->getUrl("{$this->_paypalActionPrefix}/express/edit"))
            ->setPlaceOrderUrl($this->getUrl("{$this->_paypalActionPrefix}/express/placeOrder"));

        return parent::_beforeToHtml();
    }
}
