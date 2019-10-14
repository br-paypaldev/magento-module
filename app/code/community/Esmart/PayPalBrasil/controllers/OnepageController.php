<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Scheckout
 */

require_once 'Mage/Checkout/controllers/OnepageController.php';
class Esmart_PayPalBrasil_OnepageController extends Mage_Checkout_OnepageController
{
    public function updateDropdownAction()
    {
        Mage::dispatchEvent('controller_action_layout_render_before_' . $this->getFullActionName());

        $html = $this->_getPaymentMethodsHtml();

        $this->getResponse()->clearHeaders()->setHeader('content-type', 'application/json', true);
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array(
            "html" => $html
        )));
    }
}