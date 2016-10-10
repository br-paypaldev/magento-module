<?php
/**
 * Magento
 *
 */

/**
 * 
 */
class Esmart_PayPalBrasil_Block_Express_Form extends Mage_Paypal_Block_Express_Form
{

	protected function _construct()
    {
        $result =  parent::_construct();

        $locale = Mage::app()->getLocale();
        $mark = Mage::getConfig()->getBlockClassName('core/template');
        $mark = new $mark;
        $mark->setTemplate('esmart/paypalbrasil/express/mark.phtml')
            ->setPaymentAcceptanceMarkHref($this->_config->getPaymentMarkWhatIsPaypalUrl($locale))
            ->setPaymentAcceptanceMarkSrc($this->_config->getPaymentMarkImageUrl($locale->getLocaleCode()))
        ;

        $this->setMethodLabelAfterHtml($mark->toHtml());

         return $result;
    }
}