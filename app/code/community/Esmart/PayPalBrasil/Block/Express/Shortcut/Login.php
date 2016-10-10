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
 * @category    Esmart
 * @package     Esmart_PayPalBrasil
 * @copyright   Copyright (c) 2013 Smart E-commerce do Brasil Tecnologia LTDA. (http://www.e-smart.com.br)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @author      Ricardo Martins <ricardo.martins@e-smart.com.br>
 * @author      Thiago H Oliveira <thiago.oliveira@e-smart.com.br>
 *
 */

/**
 * Paypal Shotcut Login Block
 * 
 * it's a function that just extends #Mage_Paypal_Block_Express_Shortcut
 */
class Esmart_PayPalBrasil_Block_Express_Shortcut_Login extends Mage_Paypal_Block_Express_Shortcut
{

    protected $_startAction = 'paypalbrasil/login/start/button/1';

	/**
     * @return Mage_Core_Block_Abstract
     */
    protected function _beforeToHtml()
    {   
        $quote = Mage::getSingleton('checkout/session')->getQuote();

        $result = parent::_beforeToHtml();

        if (!$quote->hasItems()) {
            $this->_shouldRender = false;
            return $result;
        }
            
      
        
        if (!Mage::getStoreConfigFlag("payment/login_ec/enable") || !Mage::getStoreConfigFlag("payment/paypal_express/active")) {
            $this->_shouldRender = false;
            return $result;
        }

        if ($this->_getCustomerSession()->isLoggedIn()) {
            $this->_shouldRender = false;
            return $result;
        }



        $this->setImageUrl($this->getSkinUrl('esmart/paypalbrasil/image/login-paypal.png', array('_secure' => true)));

        
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

        return $result;
    }

    protected function _getCustomerSession(){
        return Mage::getSingleton("customer/session");
    }

    public function isActiveIncontext()
    {
        return (bool)Mage::getStoreConfigFlag('payment/incontext/enable');
    }
}
    