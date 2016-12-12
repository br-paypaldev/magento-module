<?php
class Esmart_PayPalBrasil_Block_Express_Shortcut_Incontext_Js extends Mage_Core_Block_Template{
    
    public function _construct(){
        $this->setTemplate('esmart/paypalbrasil/js.phtml');
    }
    
    public function getJsonConfig(){
        $_secure = false;
        $sheme = Mage::app()->getRequest()->getScheme();
        if ($sheme=='https'){
            $_secure = true;
        }
        
        $merchantId = Mage::getStoreConfig('payment/incontext/merchantid');
        $isActive = Mage::getStoreConfig('payment/incontext/enable');
        
        $environment = 'production';
        // get config of Express Checkout
        if (Mage::getStoreConfig('paypal/wpp/sandbox_flag')){
            $environment = 'sandbox';
        }
        if (empty($merchantId)){
            $isActive = false;
        }

        $config = new Varien_Object();
        $config->setData('isActive', $isActive);
        $config->setData('environment', $environment);
        $config->setData('merchantid',  $merchantId);   
        $config->setData('setExpressCheckout', $this->getUrl('paypalbrasil/incontext/start', array('_secure' => $_secure)));
                
        return Mage::helper('core')->jsonEncode($config);
    }

    public function getJButtonsIds(){
        $buttonlist = Mage::getSingleton('core/session')->getIdsButtons();
        Mage::getSingleton('core/session')->unsIdsButtons();
        
        return Mage::helper('core')->jsonEncode($buttonlist);
    }
    
    public function isInContextActive()
    {
        return  Mage::getStoreConfig('payment/incontext/enable');
    }
}