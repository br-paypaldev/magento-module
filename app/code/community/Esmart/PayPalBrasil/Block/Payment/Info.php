<?php
/**
 * @package Esmart\PayPalBrasil\Block\Express
 * @author Paulo Henrique <paulo@imaginationmedia.com>
 * @copyright Copyright (c) 2020 Imagination Media (http://imaginemage.com/)
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */
class Esmart_PayPalBrasil_Block_Payment_Info extends Mage_PayPal_Block_Payment_Info
{
    /**
     * Internal constructor, that is called from real constructor
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('esmart/paypalbrasil/express/info.phtml');
    }
}