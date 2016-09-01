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
 */
class Esmart_PayPalBrasil_Block_Adminhtml_Profilername extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Set field readonly
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $value = $element->getEscapedValue();

        if (empty($value)) {
            $helper = $helper = Mage::helper('esmart_paypalbrasil');
            $element->setValue($helper->getProfilerNameSuggestion());
        }

        return $element->getElementHtml();
    }
}