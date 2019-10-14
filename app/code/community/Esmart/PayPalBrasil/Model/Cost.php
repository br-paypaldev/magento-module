<?php

class Esmart_PayPalBrasil_Model_Cost extends Mage_Sales_Model_Quote_Address_Total_Abstract
{

    public function __construct()
    {

        $this->setCode('esmart_paypalbrasil_cost');
    }

    /**
     * @param Mage_Sales_Model_Quote_Address $address
     * @return $this
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);

        /** @var Mage_Sales_Model_Quote_Payment $payment */
        $payment = $address->getQuote()->getPayment();

        if ($address->getQuote()->getPayment()->getMethod() != 'paypal_plus') {
            return $this;
        }

        $items = $address->getAllItems();
        if (!count($items)) {
            return $this;
        }


        $cost = $payment->getAdditionalInformation('paypal_plus_installments_cost');

        if ($cost > 0) {
            
            $this->_setAmount($cost);
            $this->_setBaseAmount($cost);

            $address->setGrandTotal($address->getGrandTotal() + $cost);
            $address->setBaseGrandTotal($address->getGrandTotal());

        }

        return $this;
    }

    /**
     * @param Mage_Sales_Model_Quote_Address $address
     * @return $this
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        //if ((Mage::getSingleton('checkout/session')->getCanCalculatePaypalCost() === true) && (Mage::getSingleton('checkout/session')->getPayPalPlusCost() != true)) {
            Mage::getSingleton('checkout/session')->setPayPalPlusCost(true);
            $payment = $address->getQuote()->getPayment();
            $address->addTotal(
                [
                    'code' => $this->getCode(),
                    'title' => Mage::helper('esmart_paypalbrasil')->__('Cost'),
                    'value' => $address->getEsmartPaypalbrasilCostAmount()
                ]
            );
            $payment->save();
      //  }

        return $this;
    }
}

