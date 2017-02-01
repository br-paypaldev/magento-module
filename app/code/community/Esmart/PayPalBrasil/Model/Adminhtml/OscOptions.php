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
 * @category  Esmart
 * @package   Esmart_PayPalBrasil
 * @copyright Copyright (c) 2015 Smart E-commerce do Brasil Tecnologia LTDA. (http://www.e-smart.com.br)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @author        Thiago H Oliveira <thiago.oliveira@e-smart.com.br>
 */
class Esmart_PayPalBrasil_Model_Adminhtml_OscOptions
{
    /**
     * Return array with js of diferents checkout
     *
     * @return array
     */
    public function toOptionArray()
    {   
        $helper = Mage::helper('esmart_paypalbrasil');

        return array(
            array('value'=> Esmart_PayPalBrasil_Helper_Data::JS_EVENTS_DEFAULT, 'label'=>  $helper->__('Checkout_Default')),
            array('value'=> Esmart_PayPalBrasil_Helper_Data::JS_EVENTS_MOIP, 'label' =>  $helper->__('MOIP_Onestepcheckout')),
            array('value'=> Esmart_PayPalBrasil_Helper_Data::JS_EVENTS_INOVARTI, 'label'=>  $helper->__('Inovarti_Onestepcheckout')),
            array('value'=> Esmart_PayPalBrasil_Helper_Data::JS_EVENTS_FIRECHECKOUT, 'label'=>  $helper->__('TM_FireCheckout')),
            array('value'=> Esmart_PayPalBrasil_Helper_Data::JS_EVENTS_AMASTY, 'label'=>  $helper->__('Amasty_Scheckout')),
            array('value'=> Esmart_PayPalBrasil_Helper_Data::JS_EVENTS_SMARTCHECKOUT, 'label'=>  $helper->__('Esmart_SmartCheckout')),
            array('value'=> Esmart_PayPalBrasil_Helper_Data::JS_EVENTS_AHEADWORKS, 'label'=>  $helper->__('AW_OneStepCheckout')),
        );
    }
}