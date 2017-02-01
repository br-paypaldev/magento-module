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
 * @author        Leonardo T Virgilio <leoanardo.virgilio@e-smart.com.br>
 */

class Esmart_PayPalBrasil_Model_Adminhtml_Logo extends Mage_Paypal_Model_System_Config_Source_Logo
{
    /**
     * Return array with js of diferents checkout
     *
     * @return array
     */
    public function toOptionArray()
    {
        $helper = Mage::helper('esmart_paypalbrasil');

        return Mage::getModel('paypal/config')->getAdditionalOptionsLogoTypes();
    }
}
