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
 */
class Esmart_PayPalBrasil_Block_Plus_Form extends Mage_Payment_Block_Form
{
    /**
     * JS module
     * @const string
     */
    #const JS_MODULE = 'esmart/paypalbrasil/Esmart_PaypalBrasil.js';
    const JS_MODULE = 'esmart/paypalbrasil/Esmart_PaypalBrasilPrototype.js';

    /**
     * Internal constructor, that is called from real constructor
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('esmart/paypalbrasil/plus/form.phtml');
    }

    /**
     * Preparing global layout
     *
     * You can redefine this method in child classes for changing layout
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _prepareLayout()
    {
        $head = Mage::app()->getLayout()->getBlock('after_body_start');

        if ($head && false == $head->getChild('js_paypalplus')) {
            $head->append($this->getScriptBlock());
        }

        return parent::_prepareLayout();
    }

    /**
     * Get Block with JS
     *
     * @return Mage_Core_Block_Text
     */
    protected function getScriptBlock()
    {
        $scriptblock = Mage::app()->getLayout()->createBlock('core/text', 'js_paypalplus');

        /** @var Esmart_PayPalBrasil_Helper_Data $helper */
        $helper = Mage::helper('esmart_paypalbrasil');
        $uniq = md5(uniqid(rand(), true));
        $scriptblock->setText(
            sprintf(
                '<script src="%s?%s" type="text/javascript"></script>
                 <script src="%s" type="text/javascript"></script>',
                Mage::getStoreConfig('payment/paypal_plus/js_address'),
                $uniq,
                $helper->getFullJsUrl(self::JS_MODULE)
            )
        );

        return $scriptblock;
    }
}
