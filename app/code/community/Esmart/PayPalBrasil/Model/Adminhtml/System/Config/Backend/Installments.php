<?php

class Esmart_PayPalBrasil_Model_Adminhtml_System_Config_Backend_Installments extends Mage_Core_Model_Config_Data
{

    const XPATH_CONFIG_ACTIVE_OSCOPTIONS = 'payment/paypal_plus/oscoptions';

    /*
     * Check if have a OSC options select
     * @param  Mage_Core_Model_Config_Data $groups
     * @return  this
     */
    protected function _beforeSave()
    {
       $groups = $this->getGroups();

       if($groups['ppplus_attributes2']['fields']['installments']['value'] == 1) {

           if (!empty($groups['paypal_plus']['fields']['oscoptions']['value'])) {
               $checkoutType = $groups['paypal_plus']['fields']['oscoptions']['value'];
               $name = $this->getNameOSC($checkoutType);
               switch ($checkoutType) {
                   case 'esmart/paypalbrasil/Esmart_PaypalBrasilPrototype.events.default.js':
                   case 'esmart/paypalbrasil/Esmart_PaypalBrasilPrototype.events.inovarti.js':
                   case 'esmart/paypalbrasil/Esmart_PaypalBrasilPrototype.events.firecheckout.js':
                   case 'esmart/paypalbrasil/Esmart_PaypalBrasilPrototype.events.amasty.js':
                   case 'esmart/paypalbrasil/Esmart_PaypalBrasilPrototype.events.aheadworks.js';
                   case 'esmart/paypalbrasil/Esmart_PaypalBrasilPrototype.events.vendamais.js';
                       break;
                   default:
                       $error = 'A funcionalidade de repasse de juros não funciona com módulo de checkout ' . $name . '.';
                       Mage::throwException($error);
               }
           }
       }
        parent::_beforeSave();
    }

    protected function getNameOSC($value){
        $value = explode('.',$value);
        $nameOSC = str_replace('','.',$value[2]);
        return ucwords($nameOSC);
    }
}
