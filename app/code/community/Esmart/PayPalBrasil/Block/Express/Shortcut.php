<?php


class Esmart_PayPalBrasil_Block_Express_Shortcut extends Mage_Paypal_Block_Express_Shortcut
{
    
    protected function _beforeToHtml(){
        parent::_beforeToHtml();    
        $list = Mage::getSingleton('core/session')->getIdsButtons();
        if (!is_array($list)){
            $list= array();
        }
        $id = $this->getShortcutHtmlId();
        if (!empty($id)){
            $list[] = $id;  
        }
        $list = array_unique($list);
        Mage::getSingleton('core/session')->setIdsButtons($list);

        /* set # in href button */
        if ($this->isActiveIncontext()) {
           $this->setCheckoutUrl('#');
        }
    }

    public function isActiveIncontext()
    {
        return (bool)Mage::getStoreConfigFlag('payment/incontext/enable');
    }
 
}