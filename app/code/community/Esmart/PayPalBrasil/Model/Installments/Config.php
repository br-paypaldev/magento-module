<?php

class Esmart_PayPalBrasil_Model_Installments_Config
{

    const XPATH_CONFIG_ACTIVE = 'payment/paypal_plus/instalments_active';

    const XPATH_CONFIG_INSTALMENT_COST = 'payment/paypal_plus/instalment_';

    const XPATH_CONFIG_INSTALMENT_MINIMAL = 'payment/paypal_plus/installments_minimal';

    const XPATH_CONFIG_INSTALLMENT_SELECTION = 'payment/paypal_plus/merchantInstallmentSelection';

    const XPATH_CONFIG_INSTALLMENT_TEXT = 'payment/paypal_plus/installment_text';

    const XPATH_CONFIG_OSC = 'payment/paypal_plus/oscoptions';

    const XPATH_CONFIG_INSTALMENT_DISCOUNT = 'payment/paypal_plus/instalments_discount';

    const XPATH_CONFIG_INSTALMENT_DISCOUNT_VALUE = 'payment/paypal_plus/installments_discount_value';

    /*
     * Get valeu fron admin active or not Instalmments on module
     * @return int $this
     */
    public function getStatusInstallments(){

        if (!Mage::getStoreConfig(self::XPATH_CONFIG_ACTIVE)) {
            return false;
        }

        return true;
    }

    /*
     * getInstallmentDiscount
     * @return $discount
     */
    public function getInstallmentDiscount(){

        $discountValue = Mage::getStoreConfig(self::XPATH_CONFIG_INSTALMENT_DISCOUNT_VALUE);

        $discount = Mage::getStoreConfig(self::XPATH_CONFIG_INSTALMENT_DISCOUNT);

        if($discount == true){

            if(empty($discountValue)){
                $discountValue = 0;
            }

            return $discountValue;
        }
        return false;
    }

    /*
     * getOscCheckout
     * @return $file
     */
    public function getOscCheckout($name = true){
        $file = Mage::getStoreConfig(self::XPATH_CONFIG_OSC);
        if ($name) {
            return $this->getNameOSC($file);
        }

        return $file;
    }

    /*
     * Get valeu fron admin cost of installments
     * @return int $this
     */
    public function costInstallments(){


        for($x = 1; $x<=12; $x++){

            if($x == 1){
                $cost[$x] = 0;
            }
            $cost[$x] =  Mage::getStoreConfig(self::XPATH_CONFIG_INSTALMENT_COST.$x);
        }


        return $cost;
    }

    /*
     * Get valeu fron admin to minimal installment
     * @return int $this
     */
    public function getMinimalValueInstallment()
    {
        $numberOfInstallments =  Mage::getStoreConfig(self::XPATH_CONFIG_INSTALMENT_MINIMAL);
        if($numberOfInstallments <= 9){
            $numberOfInstallments = 10;
        }
        return $numberOfInstallments ;
    }

    /*
     * Get valeu fron admin to merchant Installment Selection
     * @return int $this
     */
    public function merchantInstallmentSelection(){

        return Mage::getStoreConfig(self::XPATH_CONFIG_INSTALLMENT_SELECTION);
    }

    /*
     * Get valeu fron admin customize text cost
     * @return int $this
     */
    public function getCustomizeText(){

        $text =  Mage::getStoreConfig(self::XPATH_CONFIG_INSTALLMENT_TEXT);
        if(!empty($text)){
            return $text;
        }
        return __('Juros');
    }

    /*
     * Get valeu fron admin customize text discount
     * @return int $this
     */
    public function getCustomizeTextDiscount(){

        $helper = Mage::helper('esmart_paypalbrasil');
        return $helper->getTextDiscount();
    }

    protected function getNameOSC($value){
        $value = explode('.',$value);
        $nameOSC = str_replace('','.',$value[2]);
        return $nameOSC;
    }

}