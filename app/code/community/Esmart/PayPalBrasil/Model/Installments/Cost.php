<?php

class Esmart_PayPalBrasil_Model_Installments_Cost extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    protected $installmentModel;

    public function __construct()
    {

        $this->setCode('esmart_paypalbrasil_cost');
        /** @var Esmart_PayPalBrasil_Model_Installments installmentModel */
        $this->installmentModel = Mage::getModel('esmart_paypalbrasil/installments');
    }

    protected function _config()
    {
        /** @var Esmart_PayPalBrasil_Model_Installments_Config $config */
        return Mage::getModel('esmart_paypalbrasil/installments_config');
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

        if ( ($address->getQuote()->getPayment()->getMethod() == Esmart_PayPalBrasil_Model_Plus::CODE) || ($address instanceof TM_FireCheckout_Model_Quote_Address) ) {
            $payment_id = $payment->getAdditionalInformation('paypal_plus_payment_id');
            $dataDiscount = $this->discountPayPal($payment_id);
            $discountPayPal = $dataDiscount['discount_amount']['value'];

            if(($dataDiscount == false) || ($discountPayPal == '0.00') ){
                $address->setEsmartPaypalbrasilDiscountAmount(0);
                $address->setBaseEsmartPaypalbrasilDiscountAmount(0);
            }
        }

        $installmentModel = Mage::getModel('esmart_paypalbrasil/installments');

        if(($installmentModel->getStatusInstallments() == false) && empty($dataDiscount['discount_amount']['value'])) {
            return $this;
        }

        $method = Mage::app()->getRequest()->getParam('payment');
        if( isset($method['method']) ) {
            $method = $method['method'];
        } else {
            $method = null;
        }

        if($address instanceof TM_FireCheckout_Model_Quote_Address){
            if( ($method != null) && ($method != Esmart_PayPalBrasil_Model_Plus::CODE) ){
                $this->_setAmount(0);
                $this->_setBaseAmount(0);

                //Fix bug with When loading the page, it is showing the cost calculated last time
                $payment->unsAdditionalInformation('paypal_plus_installments_cost');
                $payment->unsAdditionalInformation('paypal_discount');

                //Fix bug with When loading the page, it is showing the discount calculated last time
                $address->setEsmartPaypalbrasilDiscountAmount(0);
                $address->setBaseEsmartPaypalbrasilDiscountAmount(0);
                return $this;
            }
        }else{
            if( ( empty($_POST) ) && ($method != Esmart_PayPalBrasil_Model_Plus::CODE) && (!$payment->getAdditionalInformation('paypal_express_checkout_payer_id')) ){
                $this->_setAmount(0);
                $this->_setBaseAmount(0);

                //Fix bug with When loading the page, it is showing the cost calculated last time
                $payment->unsAdditionalInformation('paypal_plus_installments_cost');
                $payment->unsAdditionalInformation('paypal_discount');

                //Fix bug with When loading the page, it is showing the discount calculated last time
                $address->setEsmartPaypalbrasilDiscountAmount(0);
                $address->setBaseEsmartPaypalbrasilDiscountAmount(0);
                return $this;
            }
        }

        //AHEADWORKS - Clean Cost after change shipping method - just after shipping method
        $addresschanged = Mage::app()->getRequest()->getParam('addresschanged');
        $paymentchanged = Mage::app()->getRequest()->getParam('paymentchanged');
        if( $addresschanged || $paymentchanged) {
            $this->_setAmount(0);
            $this->_setBaseAmount(0);

            //Fix bug with When loading the page, it is showing the cost calculated last time
            $payment->unsAdditionalInformation('paypal_plus_installments_cost');
            $payment->unsAdditionalInformation('paypal_discount');

            //Fix bug with When loading the page, it is showing the discount calculated last time
            $address->setEsmartPaypalbrasilDiscountAmount(0);
            $address->setBaseEsmartPaypalbrasilDiscountAmount(0);

            $payment->save();
            $address->save();

            $finalPrice =  Mage::getModel('esmart_paypalbrasil/plus_iframe')->getGranTotalClean($address->getQuote());

            $address->setGrandTotal($finalPrice);
            $address->setBaseGrandTotal($finalPrice);

            $address->getQuote()->setGrandTotal($finalPrice);
            $address->getQuote()->setBaseGrandTotal($finalPrice);

            return $this;
        }

        $items = $address->getAllItems();
        if (!count($items)) {
            return $this;
        }

        $cost = $payment->getAdditionalInformation('paypal_plus_installments_cost');

        //Paypal Express Shortcut 1x Discount
        $paypal_express_discount = $payment->getAdditionalInformation('paypal_express_discount');
        if($paypal_express_discount){
            $discountPayPal = $paypal_express_discount;
        }

        if (($cost > 0) && (($method == "paypal_plus") || ($payment->getMethod() == "paypal_plus"))) {

            $this->_setAmount($cost);
            $this->_setBaseAmount($cost);

            $grandTotal = $address->getGrandTotal();

            $finalPrice =  Mage::getModel('esmart_paypalbrasil/plus_iframe')->getGranTotalClean($address->getQuote());

            if( $grandTotal <= $finalPrice ){
                $address->setGrandTotal($finalPrice + $cost);
                $address->setBaseGrandTotal($finalPrice + $cost);
            }

            Mage::getSingleton('checkout/session')->setPayPalPlusCostGrandTotal(true);
        } elseif ((isset($discountPayPal) && ($discountPayPal > 0)) && (($method == "paypal_plus") || ($payment->getMethod() == "paypal_plus"))) {
            $payment->setAdditionalInformation('paypal_discount',$discountPayPal);

            $grandTotal = $address->getGrandTotal();
            $baseGrandTotal = $address->getBaseGrandTotal();

            $address->setEsmartPaypalbrasilDiscountAmount(-$discountPayPal);
            $address->setBaseEsmartPaypalbrasilDiscountAmount(-$discountPayPal);

            $address->setGrandTotal($grandTotal + $address->getEsmartPaypalbrasilDiscountAmount());
            $address->setBaseGrandTotal($baseGrandTotal + $address->getEsmartPaypalbrasilDiscountAmount());
        }

        return $this;
    }

    /**
     * @param Mage_Sales_Model_Quote_Address $address
     * @return $this
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $amt = $address->getBaseEsmartPaypalbrasilDiscountAmount();
        if (($address->getData('address_type') == 'billing') && ($amt == 0)){
            return $this;
        }

        if ($amt != 0) {
            $payment = $address->getQuote()->getPayment();
            if( $payment->getMethod() == "paypal_plus") {
                $address->addTotal(
                    [
                        'code' => $this->getCode(),
                        'title' => $this->_config()->getCustomizeTextDiscount(),
                        'value' => -$address->getBaseEsmartPaypalbrasilDiscountAmount()
                    ]
                );

                $payment->save();
            }
            if( $payment->getMethod() == "paypal_express" ) {
                $address->addTotal(
                    [
                        'code' => $this->getCode(),
                        'title' => "Desconto",
                        'value' =>  $address->getBaseEsmartPaypalbrasilDiscountAmount()
                    ]
                );

                //Clean Paypal Express 1x Discount
                $payment->unsAdditionalInformation('paypal_discount');
                $address->setEsmartPaypalbrasilDiscountAmount(0);
                $address->setBaseEsmartPaypalbrasilDiscountAmount(0);

                $payment->unsAdditionalInformation('paypal_express_discount');

                // End Clean Paypal Express 1x Discount

                $payment->save();
            }
        }

        if($address->getEsmartPaypalbrasilCostAmount() == 0){
            return $this;
        }


        if($this->installmentModel->getStatusInstallments() == true) {

            Mage::getSingleton('checkout/session')->setPayPalPlusCost(true);
            $payment = $address->getQuote()->getPayment();
            $address->addTotal(
                [
                    'code' => $this->getCode(),
                    'title' => $this->_config()->getCustomizeText(),
                    'value' => $address->getEsmartPaypalbrasilCostAmount()
                ]
            );
            $payment->save();
        }

        return $this;
    }

    /**
     * @param Esmart_PayPalBrasil_Model_Plus
     * @return $creditFinancingOffered
     */
    public function discountPayPal($payment_id)
    {
        if(isset($payment_id))
        {
            if(!empty($payment_id)) {
                $modelPayPalPlus = Mage::getModel('esmart_paypalbrasil/plus');
                $creditFinancingOffered = $modelPayPalPlus->getDiscountPayPal($payment_id);
                return $creditFinancingOffered;
            }
        }
        return false;
    }
}