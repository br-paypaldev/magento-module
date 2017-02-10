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
class Esmart_PayPalBrasil_Model_Adminhtml_CustomerAddressAttributes
{
    /**
     * Return array with customer attributes
     *
     * @return array
     */
    public function toOptionArray()
    {
        $helper = Mage::helper('esmart_paypalbrasil');

        $attributes = array();

        $resource = Mage::getResourceSingleton('customer/address');
        $attrArray = Mage::getSingleton('eav/config')->getEntityAttributeCodes('customer_address', null);

        foreach ($attrArray as $attrCode) {

            $attr = $resource->getAttribute($attrCode);

            if (!$attr->getStoreLabel()) {
                continue;
            }

            $label = $attr->getData('frontend_label');

            if ($attrCode == 'region') {
                $label = "Name {$attr->getData('frontend_label')}";
            }

            $attributes[$attrCode] = array(
                'value' => $attr->getData('attribute_code'),
                'label' => $helper->__($label." (%s)", $attr->getData('attribute_code'))
            );
        }
        
        $attributes['empty'] = array(
            'value' => '',
            'label' => $helper->__('Empty')
        );

        $qtyAddressLine = Mage::getStoreConfig('customer/address/street_lines');
        for ($count = 1; $count <= $qtyAddressLine; $count++) {
            $attributes["street{$count}"] = array(
                'value' => $count,
                'label' => $helper->__("Street {$count} (street_%s)", $count),
            );
        }

        ksort($attributes);

        return $attributes;
    }


    /**
     * Return array with customer attributes
     *
     * @return array
     */
    public function toArray()
    {
        $helper = Mage::helper('esmart_paypalbrasil');

        $attributes = array();

        $resource = Mage::getResourceSingleton('customer/address');
        $attrArray = Mage::getSingleton('eav/config')->getEntityAttributeCodes('customer_address', null);

        foreach($attrArray as $attrCode) {

            $attr = $resource->getAttribute($attrCode);

            if(!$attr->getStoreLabel()) {
                continue;
            }
            
            $attributes[] = array(
                $attr->getData('attribute_code') => "[{$helper->__("Address")}] {$helper->__($attr->getData('frontend_label'))}"
            );
        }

        $qtyAddressLine = Mage::getStoreConfig('customer/address/street_lines');
        for ($count = 1; $count <= $qtyAddressLine; $count++) {
            $attributes[] = array(
                $count => $helper->__("Street {$count}"),
            );
        }

        return $attributes;
    }

}