<?php

class Esmart_PayPalBrasil_Model_Installments{

    protected function _config()
    {
        /** @var Esmart_PayPalBrasil_Mode $this->_config() */
        return Mage::getModel('esmart_paypalbrasil/installments_config');
    }

    /*
     * Get all values of installments
     * @param  $grandTotal total
     * @param $numberOfInstallments Max number of installments
     * @return  array $installments
     */
    public function installments($grandTotal,$numberOfInstallments)
    {
        /** @var Esmart_PayPalBrasil_Helper_Data $helper */
        $helper     = Mage::helper('esmart_paypalbrasil');
        $quote      = $helper->getQuote(null);

//        $grandTotal = $this->cleanInstallments($quote);
        $aheadworks = $_SERVER['HTTP_REFERER'];

        if(  substr_count($aheadworks, 'onestepcheckout/index') ){ //IF AHEADWORKS OSC
            if($grandTotal > 0) {
                /** @var Esmart_PayPalBrasil_Model_Plus_Iframe $model */
                $model = Mage::getModel('esmart_paypalbrasil/plus_iframe');
                $grandTotal = $model->getGranTotalClean($quote);
            }
        }

        $tax = '';
        $installments = array();

        if($numberOfInstallments <= 0){
            $numberOfInstallments = 1;
        }

        for($installmentValue = 1; $installmentValue <= $numberOfInstallments; $installmentValue++)
        {

            $valueFinal = $this->calculateCost($grandTotal,$installmentValue);
            $installment = $this->calculateInstallment($valueFinal, $installmentValue);
            $total = $installmentValue * $installment;
            $minimalInstallment = $this->_config()->getMinimalValueInstallment();
            $totalDiscount = $total;
            $discount = 0;

            if($installmentValue == 1){
                $discount = $this->getInstallmentDiscount();
                $totalDiscount = $this->getDiscount($total,$discount);
                $totalWithDiscount = number_format($total - $totalDiscount, 2, '.', '');
                $totaltoShow = $total - $totalWithDiscount;
            }

            if($grandTotal <= $total){
                $tax = $this->getCosts($installmentValue);
            }

            $return = array(
                'number' => $installmentValue,
                'installment_value' => $helper->formatValueInstallments($installment),
                'installment_value_total' => $helper->formatValueInstallments($total),
                'installent_flag' => str_replace(",", ".", $tax),
                'discount_paypal' => $discount,
                'installment_value_total_with_discount' => $helper->formatValueInstallments($totaltoShow),
                'installment_value_total_discount' => $helper->formatValueInstallments($total - $totalDiscount)
            );

            if($installment <= $minimalInstallment){

                if($installmentValue == 1){
                    $installments[] = $return;
                }
                return $installments;
            }

            // Limit of PayPal
            if($installment <= 5){

                if($installmentValue == 1){
                    $installments[] = $return;
                }

                return $installments;
            }

            $installments[] = $return;
        }

        if(empty($installments)){
            $defaultExceptionMessage = Mage::helper('paygate')->__('Erro ao gerar parcelas.');

            Mage::throwException($defaultExceptionMessage);
        }

        return $installments;

    }

    /*
     * Calculate cost with values of cost set on admin
     * @param  $grandTotal total
     * @param $instalment installament to calculate
     * @return  float $valueFinal calculate
     */
    public function calculateCost($grandTotal,$instalment)
    {
        $valueFinal = $this->calculateCostGrandTotal($grandTotal,$instalment);
        return $valueFinal;
    }

    /*
     * Calculate cost Total with values of cost set on admin
     * @param  $grandTotal total
     * @param $instalment installament to calculate
     * @return  float $valueFinal calculate
     */
    public function calculateCostGrandTotal($grandTotal,$instalment)
    {
        $valueFinal = $this->calculateGrandTotalCost($grandTotal,$instalment);
        return $valueFinal;
    }

    /*
     * Calculate value of Installment
     * @param  $valueFinal value to calculate a installment
     * @param $installmentValue installament to share
     * @return  float $valueFinal calculate
     */
    public function calculateInstallment($valueFinal, $installmentValue)
    {

        $installment = $valueFinal / $installmentValue;

        return $installment;
    }

    /*
     * Get costs set of admin the 1º to 12º installment
     * @return  array $cost all values the configs on payment
     */
    public function getCosts($installment =  null)
    {

        $cost = $this->_config()->costInstallments();
        if(is_null($installment)){
            return $cost;
        }

        return $cost[$installment];
    }

    /*
     * Calculate costTotal and only tax with values of cost set on admin
     * @param  $grandTotal total
     * @param $instalment installament to calculate
     * @return  float $valueFinal calculate
     */
    public function calculateTotalCost($grandTotal,$instalment)
    {

        if(!empty($instalment) && !empty($grandTotal)) {

            $valueFinal = $this->getCostSelect($grandTotal, $instalment);
            return $valueFinal - $grandTotal;
        }
    }

    /*
     * Calculate costGrandTotal and only tax with values of cost set on admin
     * @param  $grandTotal total
     * @param $instalment installament to calculate
     * @return  float $valueFinal calculate
     */
    public function calculateGrandTotalCost($grandTotal, $instalment)
    {
        $valueFinal = 0;

        if(!empty($instalment) && !empty($grandTotal)) {
            $valueFinal = $this->getCostSelect($grandTotal, $instalment);
        }

        return $valueFinal;
    }

    /*
     * Get Cost of installment
     * @param  $grandTotal total
     * @param $instalment installament to calculate
     * @return  float $valueFinal calculate
     */
    public function getCostSelect($grandTotal, $instalment){

        if(!empty($instalment) && !empty($grandTotal)) {

            $costs = $this->getCosts();

            $interestCard = str_replace(",", ".", $costs[$instalment]);
            $valueFinal = $grandTotal * (1 + ($interestCard / 100));

        }
        return $valueFinal;
    }

    /*
     * Clean Installments
     * @param  Mage_Sales_Model_Quote $quote total
     * @return  float $grandTotal
    */
//    public function cleanInstallments(Mage_Sales_Model_Quote $quote = null){
//
//        if(is_null($quote)){
//            /** @var Esmart_PayPalBrasil_Helper_Data $helper */
//            $helper     = Mage::helper('esmart_paypalbrasil');
//            $quote      = $helper->getQuote(null);
//        }
//
//        $payment =  $quote->getPayment();
//        $grandTotal = $quote->getGrandTotal();
//
//        $cost = $payment->getAdditionalInformation('paypal_plus_installments_cost');
//
//        if($cost > 0){
//            $grandTotal =  $grandTotal - $cost;
//
//            if(empty($payment->getAdditionalInformation('paypal_plus_payment_id'))) {
//                $payment->unsAdditionalInformation('paypal_plus_installments');
//                $payment->unsAdditionalInformation('paypal_plus_installments_cost');
//            }
//
//            $payment->setEsmartPaypalbrasilCostAmount(0);
//            $payment->setBaseEsmartPaypalbrasilCostAmount(0);
//            $payment->setCostOfInstallment(0);
//
//        }
//
//        $payment->setGrandTotal($grandTotal);
//        $payment->setBaseGrandTotal($grandTotal);
//
//        /** @var Esmart_PayPalBrasil_Model_Plus_Iframe $model */
//        $model = Mage::getModel('esmart_paypalbrasil/plus_iframe');
//        $grandTotalClen = $model->getGranTotalClean($quote);
//
//        if($grandTotal != $grandTotalClen){
//            $quote->setGrandTotal($grandTotalClen);
//            $quote->setBaseGrandTotal($grandTotalClen);
//        }
//
//        return $grandTotal;
//    }

    /*
     *  Get Status Installments
     * @return  $this
     */
    public function getStatusInstallments(){

        return $this->_config()->getStatusInstallments();

    }

    /*
     *  Applay discount getDiscount
     * @return  $selling_price
     */
    public function getDiscount($actual_price,$discount){
        $selling_price = $actual_price - ($actual_price * ($discount / 100));
        return $selling_price;
    }

    public function getInstallmentDiscount(){
        return $this->_config()->getInstallmentDiscount();
    }
}